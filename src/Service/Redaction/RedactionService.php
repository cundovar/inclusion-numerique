<?php

declare(strict_types=1);

namespace App\Service\Redaction;

use App\Entity\Category;
use App\Entity\CourseMedia;
use App\Entity\Menus;
use App\Entity\NiveauCours;
use App\Entity\Page;
use App\Entity\PageContent;
use App\Entity\SuperMenu;
use App\Service\Capture\AutomaticCaptureService;
use App\Service\Capture\CaptureFailed;
use App\Service\Capture\ImageDepositService;
use App\Service\Capture\InvalidCaptureRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Ce que l'assistant de rédaction peut faire : lire le catalogue, préparer des brouillons, faire relire
 * un brouillon par un autre agent, puis le publier (contrôles passés et relecture favorable d'un autre agent)
 * ou le soumettre à la relecture humaine. Un cours publié reste modifiable par l'agent.
 *
 * `$agent` est l'identité de l'agent (un connecteur), enregistrée comme auteur ou relecteur.
 */
final class RedactionService
{
    // Un agent ne modifie pas un cours en relecture humaine
    private const EDITABLE_STATUSES = [PageContent::STATUS_DRAFT, PageContent::STATUS_TO_REVISE, PageContent::STATUS_PUBLISHED];
    // Statuts d'où un cours part en relecture humaine ou en publication
    private const UNPUBLISHED_STATUSES = [PageContent::STATUS_DRAFT, PageContent::STATUS_TO_REVISE];
    private const MAX_CONTENT_LENGTH = 200_000;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private DraftChecker $checker,
        private AutomaticCaptureService $automaticCapture,
        private ImageDepositService $imageDeposit,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function catalogue(): array
    {
        return [
            'rubriques' => array_map(fn (SuperMenu $r) => [
                'id' => $r->getId(),
                'nom' => $r->getName(),
                'ordre' => $r->getOrdre(),
            ], $this->entityManager->getRepository(SuperMenu::class)->findBy([], ['name' => 'ASC'])),
            'parcours' => array_map(fn (Category $c) => [
                'id' => $c->getId(),
                'nom' => $c->getName(),
                'rubriqueId' => $c->getSuperMenu()?->getId(),
                'description' => $c->getDescription(),
                'niveau' => $c->getNiveauCours()?->getName(),
                'visible' => $c->isVisible() !== false,
                'ordre' => $c->getOrdre(),
                'illustrationId' => $c->getIllustration()?->getId(),
                'illustrationUrl' => $c->getIllustrationUrl(),
            ], $this->entityManager->getRepository(Category::class)->findBy([], ['name' => 'ASC'])),
            'modules' => array_map(fn (Menus $m) => [
                'id' => $m->getId(),
                'nom' => $m->getLabel(),
                'parcoursId' => $m->getCategory()?->getId(),
            ], $this->entityManager->getRepository(Menus::class)->findBy([], ['label' => 'ASC'])),
            'niveaux' => array_map(fn (NiveauCours $n) => [
                'id' => $n->getId(),
                'nom' => $n->getName(),
                'ordre' => $n->getOrdre(),
            ], $this->entityManager->getRepository(NiveauCours::class)->findBy([], ['ordre' => 'ASC'])),
        ];
    }

    /**
     * Un parcours reste absent du site tant qu'il ne contient aucun cours publié : le créer ne publie rien.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function createParcours(array $data): array
    {
        $name = $this->requiredName($data, 255);
        $errors = [];
        $existing = $this->entityManager->getRepository(Category::class)->findOneBy(['name' => $name]);
        if ($existing !== null) {
            $errors[] = ['champ' => 'nom', 'message' => 'Ce parcours existe déjà (identifiant '.$existing->getId().').'];
        }
        $level = $this->optionalLevel($data, $errors);
        $rubrique = null;
        if (isset($data['rubriqueId'])) {
            $rubrique = $this->entityManager->find(SuperMenu::class, (int) $data['rubriqueId']);
            if ($rubrique === null) {
                $errors[] = ['champ' => 'rubriqueId', 'message' => 'Rubrique introuvable (voir lister_catalogue).'];
            }
        }
        if ($errors !== []) {
            throw RedactionError::invalid($errors);
        }

        $category = (new Category())
            ->setName($name)
            ->setDescription($this->nullable($data['description'] ?? null))
            ->setNiveauCours($level)
            ->setSuperMenu($rubrique);
        $this->persistValid($category);

        return ['id' => $category->getId(), 'nom' => $category->getName(), 'description' => $category->getDescription(), 'niveauId' => $level?->getId(), 'rubriqueId' => $rubrique?->getId()];
    }

    /**
     * Grande rubrique qui regroupe des parcours (entité SuperMenu). Créer une rubrique ne publie rien.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function createRubrique(array $data): array
    {
        $name = $this->requiredName($data, 255);
        $existing = $this->entityManager->getRepository(SuperMenu::class)->findOneBy(['name' => $name]);
        if ($existing !== null) {
            throw RedactionError::invalid([['champ' => 'nom', 'message' => 'Cette rubrique existe déjà (identifiant '.$existing->getId().').']]);
        }

        if (isset($data['ordre'])) {
            $order = (int) $data['ordre'];
        } else {
            // Par défaut, la nouvelle rubrique se place après les autres
            $last = $this->entityManager->getRepository(SuperMenu::class)->findOneBy([], ['ordre' => 'DESC']);
            $order = (int) $last?->getOrdre() + 1;
        }

        $rubrique = (new SuperMenu())->setName($name)->setOrdre($order);
        $this->persistValid($rubrique);

        return ['id' => $rubrique->getId(), 'nom' => $rubrique->getName(), 'ordre' => $rubrique->getOrdre()];
    }

    /**
     * Fixe l'ordre des rubriques sur l'accueil et dans le menu : le premier identifiant devient le rang 1.
     *
     * @param list<int> $rubriqueIds
     * @return array<string, mixed>
     */
    public function orderRubriques(array $rubriqueIds): array
    {
        $result = ['ordres' => [], 'refus' => []];
        $rank = 0;
        foreach (array_values(array_unique(array_map('intval', $rubriqueIds))) as $id) {
            $rubrique = $this->entityManager->find(SuperMenu::class, $id);
            if ($rubrique === null) {
                $result['refus'][] = ['id' => $id, 'raison' => 'Rubrique introuvable (voir lister_catalogue).'];
                continue;
            }
            $rubrique->setOrdre(++$rank);
            $result['ordres'][] = ['id' => $id, 'nom' => $rubrique->getName(), 'ordre' => $rank];
        }
        $this->entityManager->flush();

        return $result;
    }

    /**
     * Range des parcours dans une rubrique (ou les en retire avec null). Ne change ni les cours, ni les modules,
     * ni le rang des parcours. Les parcours valides sont classés même si d'autres sont refusés.
     *
     * @param list<int> $parcoursIds
     * @return array<string, mixed>
     */
    public function classifyParcours(array $parcoursIds, ?int $rubriqueId): array
    {
        $rubrique = null;
        if ($rubriqueId !== null) {
            $rubrique = $this->entityManager->find(SuperMenu::class, $rubriqueId)
                ?? throw RedactionError::invalid([['champ' => 'rubriqueId', 'message' => 'Rubrique introuvable (voir lister_catalogue).']]);
        }

        $result = ['rubrique' => $rubrique === null ? null : ['id' => $rubrique->getId(), 'nom' => $rubrique->getName()], 'classes' => [], 'refus' => []];
        foreach (array_values(array_unique(array_map('intval', $parcoursIds))) as $id) {
            $category = $this->entityManager->find(Category::class, $id);
            if ($category === null) {
                $result['refus'][] = ['id' => $id, 'raison' => 'Parcours introuvable (voir lister_catalogue).'];
                continue;
            }
            $previous = $category->getSuperMenu();
            $category->setSuperMenu($rubrique);
            $result['classes'][] = ['id' => $id, 'nom' => $category->getName(), 'ancienneRubriqueId' => $previous?->getId(), 'nouvelleRubriqueId' => $rubrique?->getId()];
        }
        $this->entityManager->flush();

        return $result;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function createModule(array $data): array
    {
        $name = $this->requiredName($data, 100);
        $errors = [];
        $category = isset($data['parcoursId']) ? $this->entityManager->find(Category::class, (int) $data['parcoursId']) : null;
        if ($category === null) {
            $errors[] = ['champ' => 'parcoursId', 'message' => 'Parcours introuvable (voir lister_catalogue).'];
        } else {
            $existing = $this->entityManager->getRepository(Menus::class)->findOneBy(['label' => $name, 'category' => $category]);
            if ($existing !== null) {
                $errors[] = ['champ' => 'nom', 'message' => 'Ce module existe déjà dans ce parcours (identifiant '.$existing->getId().').'];
            }
        }
        $level = $this->optionalLevel($data, $errors);
        if ($errors !== []) {
            throw RedactionError::invalid($errors);
        }

        $menu = (new Menus())->setLabel($name)->setCategory($category)->setNiveauCours($level);
        $this->persistValid($menu);

        return ['id' => $menu->getId(), 'nom' => $menu->getLabel(), 'parcoursId' => $category?->getId(), 'niveauId' => $level?->getId()];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function createNiveau(array $data): array
    {
        $name = $this->requiredName($data, 255);
        $repository = $this->entityManager->getRepository(NiveauCours::class);
        $existing = $repository->findOneBy(['name' => $name]);
        if ($existing !== null) {
            throw RedactionError::invalid([['champ' => 'nom', 'message' => 'Ce niveau existe déjà (identifiant '.$existing->getId().').']]);
        }
        if (isset($data['ordre'])) {
            $order = (int) $data['ordre'];
        } else {
            // Par défaut, le nouveau niveau se place après les autres
            $last = $repository->findOneBy([], ['ordre' => 'DESC']);
            $order = $last === null ? 1 : (int) $last->getOrdre() + 1;
        }

        $level = (new NiveauCours())->setName($name)->setOrdre($order);
        $this->persistValid($level);

        return ['id' => $level->getId(), 'nom' => $level->getName(), 'ordre' => $level->getOrdre()];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listDrafts(): array
    {
        $courses = $this->entityManager->getRepository(PageContent::class)->findBy(
            ['status' => [PageContent::STATUS_DRAFT, PageContent::STATUS_IN_REVIEW, PageContent::STATUS_TO_REVISE]],
            ['id' => 'DESC'],
        );

        return array_map(fn (PageContent $c) => $this->summary($c), $courses);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listPublishedCourses(): array
    {
        $courses = $this->entityManager->getRepository(PageContent::class)->findBy(
            ['status' => PageContent::STATUS_PUBLISHED],
            ['id' => 'DESC'],
        );

        return array_map(fn (PageContent $c) => $this->summary($c), $courses);
    }

    /**
     * @return array<string, mixed>
     */
    public function showDraft(int $id): array
    {
        return $this->detail($this->course($id));
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function createDraft(array $data, string $agent): array
    {
        $slug = (string) ($data['slug'] ?? '');
        if (preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug) !== 1 || strlen($slug) > 90) {
            throw RedactionError::invalid([['champ' => 'slug', 'message' => 'Adresse attendue en minuscules, chiffres et tirets, par exemple « ecrire-une-bonne-consigne ».']]);
        }
        if ($this->entityManager->getRepository(Page::class)->findOneBy(['slug' => '/'.$slug]) !== null) {
            throw RedactionError::invalid([['champ' => 'slug', 'message' => 'Cette adresse est déjà utilisée par un autre cours.']]);
        }

        $page = (new Page())->setSlug('/'.$slug);
        $course = (new PageContent())
            ->setStatus(PageContent::STATUS_DRAFT)
            ->setAuthor($agent)
            ->setPage($page);

        $this->apply($course, $data, true);

        $this->entityManager->persist($page);
        $this->entityManager->persist($course);
        $this->entityManager->flush();

        return $this->detail($course);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function updateDraft(int $id, array $data): array
    {
        $course = $this->course($id);
        if (!\in_array($course->getStatus(), self::EDITABLE_STATUSES, true)) {
            throw new RedactionError('Ce cours est en relecture humaine : seul le back-office peut le modifier.', RedactionError::CONFLICT);
        }

        $this->apply($course, $data, false);
        // Le contenu a changé : la relecture précédente ne vaut plus
        $course->clearReview();
        $this->entityManager->flush();

        return $this->detail($course);
    }

    /**
     * @return array{soumissible: bool, controles: list<array<string, mixed>>}
     */
    public function checkDraft(int $id): array
    {
        $course = $this->course($id);
        $issues = $this->checker->check($course);
        if ($course->getMenu() === null) {
            $issues[] = self::missingModule(DraftChecker::WARNING);
        }

        return ['soumissible' => !DraftChecker::hasBlockingIssue($issues), 'controles' => $issues];
    }

    /**
     * @return array<string, mixed>
     */
    public function submitDraft(int $id): array
    {
        $course = $this->course($id);
        $issues = $this->gate($course, false, 'la soumission');

        // Le cours attend maintenant une relecture humaine ; il reste invisible sur le site
        $course->setStatus(PageContent::STATUS_IN_REVIEW);
        $this->entityManager->flush();

        return ['cours' => $this->summary($course), 'controles' => $issues];
    }

    /**
     * Publie le cours si les contrôles bloquants passent et si un autre agent l'a relu avec un avis favorable.
     *
     * @return array<string, mixed>
     */
    public function publishDraft(int $id, bool $withoutModule = false): array
    {
        $course = $this->course($id);
        // Sans module, le cours tomberait dans le chapitre générique « Cours » : il faut le décider explicitement
        $extra = $course->getMenu() === null && !$withoutModule ? [self::missingModule(DraftChecker::BLOCKING)] : [];
        $issues = $this->gate($course, true, 'la publication', $extra);

        $course->setStatus(PageContent::STATUS_PUBLISHED);
        $this->entityManager->flush();

        return ['cours' => $this->summary($course), 'controles' => $issues];
    }

    /**
     * Retire du site un cours, ou tous les cours publiés d'un module ou d'un parcours, en les repassant en brouillon.
     * Un module ou un parcours sans cours publié n'apparaît plus sur le site. Le contenu et la relecture sont
     * gardés : sans modification, publier_cours remet un cours en ligne ; une modification demande une nouvelle relecture.
     *
     * @return array<string, mixed>
     */
    public function unpublish(string $type, int $id): array
    {
        $repository = $this->entityManager->getRepository(PageContent::class);
        $courses = match ($type) {
            'cours' => [$this->course($id)],
            'module' => $repository->findBy([
                'menu' => $this->entityManager->find(Menus::class, $id) ?? throw RedactionError::invalid([['champ' => 'id', 'message' => 'Module introuvable (voir lister_catalogue).']]),
                'status' => PageContent::STATUS_PUBLISHED,
            ]),
            'parcours' => $repository->findBy([
                'category' => $this->entityManager->find(Category::class, $id) ?? throw RedactionError::invalid([['champ' => 'id', 'message' => 'Parcours introuvable (voir lister_catalogue).']]),
                'status' => PageContent::STATUS_PUBLISHED,
            ]),
            default => throw RedactionError::invalid([['champ' => 'type', 'message' => 'Type attendu : cours, module ou parcours.']]),
        };

        if ($type === 'cours' && !$courses[0]->isPublished()) {
            throw new RedactionError("Ce cours n'est pas publié : il n'est déjà pas visible sur le site.", RedactionError::CONFLICT);
        }

        foreach ($courses as $course) {
            $course->setStatus(PageContent::STATUS_DRAFT);
        }
        $this->entityManager->flush();

        return ['type' => $type, 'id' => $id, 'misEnBrouillon' => array_map(fn (PageContent $c) => $this->summary($c), $courses)];
    }

    /**
     * @param list<array<string, mixed>> $extra contrôles propres à l'action
     * @return list<array<string, mixed>>
     */
    private function gate(PageContent $course, bool $favorableOnly, string $action, array $extra = []): array
    {
        if (!\in_array($course->getStatus(), self::UNPUBLISHED_STATUSES, true)) {
            throw new RedactionError('Ce cours est déjà en relecture humaine ou publié.', RedactionError::CONFLICT);
        }

        $issues = [...$this->checker->check($course), ...$extra];
        $review = $course->getReviewReport();
        if ($review === null) {
            $issues[] = ['code' => 'RELECTURE_MANQUANTE', 'regle' => 'RED-02', 'niveau' => DraftChecker::BLOCKING, 'message' => "Faites relire le brouillon par un autre agent (relecture enregistrée) avant $action."];
        } elseif ($review['avis'] === PageContent::REVIEW_UNFAVORABLE) {
            $issues[] = ['code' => 'RELECTURE_DEFAVORABLE', 'regle' => 'RED-02', 'niveau' => DraftChecker::BLOCKING, 'message' => 'La relecture est défavorable : corrigez le brouillon, puis faites-le relire de nouveau.'];
        } elseif ($favorableOnly && $review['avis'] !== PageContent::REVIEW_FAVORABLE) {
            $issues[] = ['code' => 'RELECTURE_AVEC_RESERVES', 'regle' => 'RED-02', 'niveau' => DraftChecker::BLOCKING, 'message' => 'La relecture émet des réserves : corrigez le brouillon, puis faites-le relire de nouveau pour obtenir un avis favorable.'];
        }
        if (DraftChecker::hasBlockingIssue($issues)) {
            throw new RedactionError("Des contrôles bloquants empêchent $action.", RedactionError::BLOCKED, $issues);
        }

        return $issues;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function reviewDraft(int $id, array $data, string $agent): array
    {
        $course = $this->course($id);
        if (!\in_array($course->getStatus(), self::EDITABLE_STATUSES, true)) {
            throw new RedactionError('Ce cours est en relecture humaine : seul le back-office peut le modifier.', RedactionError::CONFLICT);
        }
        if ($course->getAuthor() === $agent) {
            throw new RedactionError("La relecture doit venir d'un autre agent que celui qui a rédigé le brouillon : utilisez l'autre connecteur (par exemple ChatGPT si le brouillon vient de Claude).", RedactionError::CONFLICT);
        }

        [$report, $errors] = ReviewReport::parse($data, PageContent::REVIEW_OPINIONS);
        if ($errors !== []) {
            throw RedactionError::invalid($errors);
        }
        $course->recordReview($report, $agent);
        $this->entityManager->flush();

        return $this->detail($course);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listCaptures(?string $service): array
    {
        $criteria = $service === null || $service === '' ? [] : ['service' => $service];
        $captures = $this->entityManager->getRepository(CourseMedia::class)->findBy($criteria, ['id' => 'DESC'], 200);

        return array_map(fn (CourseMedia $m) => $this->capture($m), $captures);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function captureAutomatically(array $data, string $agent): array
    {
        try {
            $result = $this->automaticCapture->capture($data, $agent);
        } catch (InvalidCaptureRequest $exception) {
            throw RedactionError::invalid(array_map(static fn (string $field, string $message) => ['champ' => $field, 'message' => $message], array_keys($exception->errors), $exception->errors));
        } catch (CaptureFailed $exception) {
            throw new RedactionError($exception->getMessage(), $exception->isUnavailable() ? RedactionError::UNAVAILABLE : RedactionError::BLOCKED, ['code' => $exception->codeName, 'details' => $exception->details]);
        }

        return $this->capture($result['capture']) + ['titrePage' => $result['titrePage'], 'elements' => $result['elements']];
    }

    /**
     * Classement éditorial : range des cours dans un module (ou les en retire avec null), quel que soit leur
     * statut. Ne change ni le contenu, ni le statut, ni la relecture. Les cours valides sont classés même si
     * d'autres sont refusés ; le résultat dit lesquels.
     *
     * @param list<int> $courseIds
     * @return array<string, mixed>
     */
    public function classifyCourses(array $courseIds, ?int $moduleId): array
    {
        $menu = null;
        if ($moduleId !== null) {
            $menu = $this->entityManager->find(Menus::class, $moduleId)
                ?? throw RedactionError::invalid([['champ' => 'moduleId', 'message' => 'Module introuvable (voir lister_catalogue).']]);
        }

        $result = [
            'module' => $menu === null ? null : ['id' => $menu->getId(), 'nom' => $menu->getLabel(), 'parcoursId' => $menu->getCategory()?->getId()],
            'classes' => [],
            'dejaDansCeModule' => [],
            'ordres' => [],
            'refus' => [],
        ];
        $rank = 0;
        foreach (array_values(array_unique(array_map('intval', $courseIds))) as $id) {
            $course = $this->entityManager->find(PageContent::class, $id);
            if ($course === null) {
                $result['refus'][] = ['id' => $id, 'raison' => 'Cours introuvable.'];
                continue;
            }
            if ($menu !== null && $menu->getCategory()?->getId() !== $course->getCategory()?->getId()) {
                $result['refus'][] = ['id' => $id, 'titre' => $course->getTitle(), 'raison' => sprintf('Le module « %s » appartient à un autre parcours que ce cours.', $menu->getLabel())];
                continue;
            }
            // L'ordre des identifiants donne l'ordre des cours dans le module ; sans module, plus de rang
            $course->setOrdre($menu === null ? null : ++$rank);
            $previous = $course->getMenu();
            if ($previous?->getId() === $menu?->getId()) {
                $result['dejaDansCeModule'][] = $id;
                $result['ordres'][$id] = $course->getOrdre();
                continue;
            }
            $course->setMenu($menu);
            $course->getPage()?->setMenus($menu);
            $result['classes'][] = ['id' => $id, 'titre' => $course->getTitle(), 'statut' => $course->getStatus(), 'ancienModuleId' => $previous?->getId(), 'nouveauModuleId' => $menu?->getId(), 'ordre' => $course->getOrdre()];
            $result['ordres'][$id] = $course->getOrdre();
        }
        $this->entityManager->flush();

        return $result;
    }

    /**
     * Fixe l'ordre des parcours dans le catalogue : le premier identifiant devient le rang 1.
     * Seuls les parcours cités sont modifiés ; les autres passent après eux.
     *
     * @param list<int> $parcoursIds
     * @return array<string, mixed>
     */
    public function orderParcours(array $parcoursIds): array
    {
        $result = ['ordres' => [], 'refus' => []];
        $rank = 0;
        foreach (array_values(array_unique(array_map('intval', $parcoursIds))) as $id) {
            $category = $this->entityManager->find(Category::class, $id);
            if ($category === null) {
                $result['refus'][] = ['id' => $id, 'raison' => 'Parcours introuvable (voir lister_catalogue).'];
                continue;
            }
            $category->setOrdre(++$rank);
            $result['ordres'][] = ['id' => $id, 'nom' => $category->getName(), 'ordre' => $rank];
        }
        $this->entityManager->flush();

        return $result;
    }

    /**
     * Associe une illustration existante (dépôt d'image ou back-office) à un parcours, pour sa miniature.
     * Seule une illustration est acceptée : une capture montre une page de service, pas un parcours.
     *
     * @return array<string, mixed>
     */
    public function setParcoursIllustration(int $parcoursId, ?int $illustrationId): array
    {
        $category = $this->entityManager->find(Category::class, $parcoursId)
            ?? throw RedactionError::invalid([['champ' => 'parcoursId', 'message' => 'Parcours introuvable (voir lister_catalogue).']]);

        $media = null;
        if ($illustrationId !== null) {
            $media = $this->entityManager->find(CourseMedia::class, $illustrationId)
                ?? throw RedactionError::invalid([['champ' => 'illustrationId', 'message' => 'Image introuvable (voir lister_captures).']]);
            if (!$media->isIllustration() || $media->getUrl() === null) {
                throw RedactionError::invalid([['champ' => 'illustrationId', 'message' => "Seule une illustration peut servir de miniature : déposez l'image avec preparer_depot_image (type illustration)."]]);
            }
        }

        $category->setIllustration($media);
        $this->entityManager->flush();

        return [
            'parcours' => ['id' => $category->getId(), 'nom' => $category->getName()],
            'illustration' => $media === null ? null : $this->capture($media),
        ];
    }

    /**
     * Remplace une capture par une autre dans un cours, même publié : seuls les marqueurs changent.
     * Refusé si le cours ne contient pas l'ancienne capture ou si le changement ajoute un contrôle bloquant.
     * La relecture enregistrée est gardée : le texte relu ne change pas.
     *
     * @return array<string, mixed>
     */
    public function replaceCapture(int $courseId, int $oldId, int $newId): array
    {
        $course = $this->course($courseId);
        if ($course->getStatus() === PageContent::STATUS_IN_REVIEW) {
            throw new RedactionError('Ce cours est en relecture humaine : seul le back-office peut le modifier.', RedactionError::CONFLICT);
        }
        if ($oldId === $newId) {
            throw RedactionError::invalid([['champ' => 'nouvelleCaptureId', 'message' => "Choisissez une capture différente de l'ancienne."]]);
        }
        $newCapture = $this->entityManager->find(CourseMedia::class, $newId)
            ?? throw RedactionError::invalid([['champ' => 'nouvelleCaptureId', 'message' => 'Capture introuvable (voir lister_captures).']]);

        $before = (string) $course->getCode();
        $count = 0;
        $after = preg_replace('/\[capture:'.$oldId.'\]/', '[capture:'.$newId.']', $before, -1, $count);
        if ($count === 0) {
            throw RedactionError::invalid([['champ' => 'ancienneCaptureId', 'message' => sprintf('Le cours ne contient pas [capture:%d].', $oldId)]]);
        }

        $blockingBefore = array_column(array_filter($this->checker->check($course), static fn (array $i) => $i['niveau'] === DraftChecker::BLOCKING), 'code');
        $course->setCode((string) $after);
        $issues = $this->checker->check($course);
        $newBlocking = array_values(array_filter($issues, static fn (array $i) => $i['niveau'] === DraftChecker::BLOCKING && !\in_array($i['code'], $blockingBefore, true)));
        if ($newBlocking !== []) {
            // Rien n'est enregistré
            $course->setCode($before);
            throw new RedactionError('Ce remplacement ajouterait des contrôles bloquants.', RedactionError::BLOCKED, $newBlocking);
        }
        $this->entityManager->flush();

        $warnings = [];
        if ($newCapture->isDueForReview(new \DateTimeImmutable('today'))) {
            $warnings[] = sprintf('La capture %s est à revérifier depuis le %s.', $newCapture->getMarker(), $newCapture->getNextReviewAt()?->format('Y-m-d'));
        }

        return [
            'cours' => $this->summary($course),
            'remplacements' => $count,
            'ancienneCapture' => '[capture:'.$oldId.']',
            'nouvelleCapture' => $this->capture($newCapture),
            'avertissements' => $warnings,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function missingModule(string $level): array
    {
        return ['code' => 'MODULE_MANQUANT', 'regle' => null, 'niveau' => $level, 'message' => "Rattachez le cours à un module (moduleId, ou classer_cours) : sans module, il apparaît dans un chapitre générique « Cours ». Pour publier sans module, choisissez-le explicitement (sansModule)."];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function prepareImageDeposit(array $data, string $agent, string $baseUrl): array
    {
        try {
            $ticket = $this->imageDeposit->prepare($data, $agent);
        } catch (InvalidCaptureRequest $exception) {
            throw RedactionError::invalid(array_map(static fn (string $field, string $message) => ['champ' => $field, 'message' => $message], array_keys($exception->errors), $exception->errors));
        }
        $url = $baseUrl.'/depot-image/'.$ticket['jeton'];

        return [
            'adresse' => $url,
            'expireLe' => $ticket['expireLe']->format(\DATE_ATOM),
            'commande' => sprintf('curl -sS -F "image=@CHEMIN_DU_FICHIER" %s', $url),
            'consigne' => "Envoyez le fichier (PNG, JPEG ou WebP, 5 Mo au plus) dans le champ « image », une seule fois. La réponse donne le marqueur [capture:ID] à placer dans le brouillon. Un agent sans accès aux fichiers de l'ordinateur ne peut pas faire cet envoi : la personne dépose alors l'image dans le back-office.",
        ];
    }

    private function course(int $id): PageContent
    {
        return $this->entityManager->find(PageContent::class, $id) ?? throw RedactionError::notFound();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function apply(PageContent $course, array $data, bool $creating): void
    {
        $errors = [];

        if ($creating || \array_key_exists('titre', $data)) {
            $course->setTitle(trim((string) ($data['titre'] ?? '')));
            if ($course->getTitle() === '') {
                $errors[] = ['champ' => 'titre', 'message' => 'Le titre est obligatoire.'];
            }
        }
        if ($creating || \array_key_exists('contenu', $data)) {
            $content = (string) ($data['contenu'] ?? '');
            if (mb_strlen($content) > self::MAX_CONTENT_LENGTH) {
                $errors[] = ['champ' => 'contenu', 'message' => 'Contenu trop long.'];
            }
            $course->setCode($content);
        }
        if ($creating || \array_key_exists('parcoursId', $data)) {
            $category = isset($data['parcoursId']) ? $this->entityManager->find(Category::class, (int) $data['parcoursId']) : null;
            if ($category === null) {
                $errors[] = ['champ' => 'parcoursId', 'message' => 'Parcours introuvable (voir lister_catalogue).'];
            }
            $course->setCategory($category);
        }
        if (\array_key_exists('moduleId', $data)) {
            $menu = $data['moduleId'] === null ? null : $this->entityManager->find(Menus::class, (int) $data['moduleId']);
            if ($data['moduleId'] !== null && ($menu === null || $menu->getCategory()?->getId() !== $course->getCategory()?->getId())) {
                $errors[] = ['champ' => 'moduleId', 'message' => 'Module introuvable dans ce parcours.'];
            }
            $course->setMenu($menu);
            $course->getPage()?->setMenus($menu);
        }
        if (\array_key_exists('niveauId', $data)) {
            $level = $data['niveauId'] === null ? null : $this->entityManager->find(NiveauCours::class, (int) $data['niveauId']);
            if ($data['niveauId'] !== null && $level === null) {
                $errors[] = ['champ' => 'niveauId', 'message' => 'Niveau introuvable.'];
            }
            $course->setNiveauCours($level);
        }
        if (\array_key_exists('sourceOfficielle', $data)) {
            $course->setSourceUrl($this->nullable($data['sourceOfficielle']));
        }
        if (\array_key_exists('verifieLe', $data)) {
            $course->setVerifiedAt($this->date($data['verifieLe'], 'verifieLe', $errors));
        }

        if ($errors === []) {
            $errors = $this->violations($this->validator->validate($course));
        }
        // Rien n'est enregistré : l'erreur interrompt la demande avant tout flush
        if ($errors !== []) {
            throw RedactionError::invalid($errors);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(PageContent $course): array
    {
        return [
            'id' => $course->getId(),
            'titre' => $course->getTitle(),
            'statut' => $course->getStatus(),
            'auteur' => $course->getAuthor(),
            'parcoursId' => $course->getCategory()?->getId(),
            'slug' => ltrim((string) $course->getPage()?->getSlug(), '/'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function detail(PageContent $course): array
    {
        return $this->summary($course) + [
            'moduleId' => $course->getMenu()?->getId(),
            'niveauId' => $course->getNiveauCours()?->getId(),
            'contenu' => $course->getCode(),
            'sourceOfficielle' => $course->getSourceUrl(),
            'verifieLe' => $course->getVerifiedAt()?->format('Y-m-d'),
            'relecture' => $course->getReviewReport() === null ? null : $course->getReviewReport() + [
                'par' => $course->getReviewedBy(),
                'le' => $course->getReviewedAt()?->format(\DATE_ATOM),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function capture(CourseMedia $media): array
    {
        return [
            'id' => $media->getId(),
            'marqueur' => $media->getMarker(),
            'type' => $media->getKind(),
            'service' => $media->getService(),
            'pageSource' => $media->getSourceUrl(),
            'dateCapture' => $media->getCapturedAt()?->format('Y-m-d'),
            'legende' => $media->getCaption(),
            'texteAlternatif' => $media->getAltText(),
            'aRevoirLe' => $media->getNextReviewAt()?->format('Y-m-d'),
            'url' => $media->getUrl(),
            'largeur' => $media->getWidth(),
            'hauteur' => $media->getHeight(),
            'auteur' => $media->getAuthor(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requiredName(array $data, int $maxLength): string
    {
        $name = trim((string) ($data['nom'] ?? ''));
        if ($name === '' || mb_strlen($name) > $maxLength) {
            throw RedactionError::invalid([['champ' => 'nom', 'message' => "Nom obligatoire ($maxLength caractères au plus)."]]);
        }

        return $name;
    }

    /**
     * @param array<string, mixed> $data
     * @param list<array{champ: string, message: string}> $errors
     */
    private function optionalLevel(array $data, array &$errors): ?NiveauCours
    {
        if (!isset($data['niveauId'])) {
            return null;
        }
        $level = $this->entityManager->find(NiveauCours::class, (int) $data['niveauId']);
        if ($level === null) {
            $errors[] = ['champ' => 'niveauId', 'message' => 'Niveau introuvable (voir lister_catalogue).'];
        }

        return $level;
    }

    private function persistValid(object $entity): void
    {
        $errors = $this->violations($this->validator->validate($entity));
        if ($errors !== []) {
            throw RedactionError::invalid($errors);
        }
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @param list<array{champ: string, message: string}> $errors
     */
    private function date(mixed $value, string $field, array &$errors): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value);
        if ($date === false || $date->format('Y-m-d') !== (string) $value) {
            $errors[] = ['champ' => $field, 'message' => 'Date attendue au format AAAA-MM-JJ.'];

            return null;
        }

        return $date;
    }

    /**
     * @return list<array{champ: string, message: string}>
     */
    private function violations(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = ['champ' => $violation->getPropertyPath(), 'message' => (string) $violation->getMessage()];
        }

        return $errors;
    }
}
