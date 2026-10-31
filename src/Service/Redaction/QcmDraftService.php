<?php

declare(strict_types=1);

namespace App\Service\Redaction;

use App\Entity\ChoicesQCM;
use App\Entity\LanguageQCM;
use App\Entity\NiveauQCM;
use App\Entity\QCM;
use App\Entity\User;
use App\Entity\QcmDraft;
use Doctrine\ORM\EntityManagerInterface;

/**
 * QCM préparés par l'assistant de rédaction : brouillon hors de l'API publique, relecture par un autre agent,
 * puis publication dans le modèle public (QCM et ChoicesQCM). Aucune tentative ni note n'est enregistrée.
 *
 * `$agent` est l'identité du connecteur, enregistrée comme auteur ou relecteur.
 */
final class QcmDraftService
{
    private const MAX_EXPLANATION_LENGTH = 5000;
    private const MAX_ANSWER_EXPLANATION_LENGTH = 1000;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private QcmDraftChecker $checker,
    ) {}

    /**
     * Thèmes et niveaux du parcours QCM public : un QCM publié apparaît dans la série de son thème et de son niveau.
     *
     * @return array{themes: list<array<string, mixed>>, niveaux: list<array<string, mixed>>}
     */
    public function catalogue(): array
    {
        return [
            'themes' => array_map(static fn (LanguageQCM $t) => [
                'id' => $t->getId(),
                'nom' => $t->getName(),
                'qcmPublies' => $t->getQcm()->count(),
            ], $this->entityManager->getRepository(LanguageQCM::class)->findBy([], ['name' => 'ASC'])),
            'niveaux' => array_map(static fn (NiveauQCM $n) => [
                'id' => $n->getId(),
                'nom' => $n->getTitre(),
                'qcmPublies' => $n->getQcm()->count(),
            ], $this->entityManager->getRepository(NiveauQCM::class)->findBy([], ['titre' => 'ASC'])),
        ];
    }

    /** @return array{id: int|null, nom: string, cree: bool} */
    public function createTheme(string $name): array
    {
        $name = $this->catalogueName($name, 100, 'theme');
        $repository = $this->entityManager->getRepository(LanguageQCM::class);
        $existing = $repository->findOneBy(['name' => $name]);
        if ($existing instanceof LanguageQCM) {
            return ['id' => $existing->getId(), 'nom' => (string) $existing->getName(), 'cree' => false];
        }

        $theme = (new LanguageQCM())->setName($name);
        $this->entityManager->persist($theme);
        $this->entityManager->flush();

        return ['id' => $theme->getId(), 'nom' => (string) $theme->getName(), 'cree' => true];
    }

    /** @return array{id: int|null, nom: string, cree: bool} */
    public function createLevel(string $name): array
    {
        $name = $this->catalogueName($name, 255, 'niveau');
        $repository = $this->entityManager->getRepository(NiveauQCM::class);
        $existing = $repository->findOneBy(['titre' => $name]);
        if ($existing instanceof NiveauQCM) {
            return ['id' => $existing->getId(), 'nom' => (string) $existing->getTitre(), 'cree' => false];
        }

        $level = (new NiveauQCM())->setTitre($name);
        $this->entityManager->persist($level);
        $this->entityManager->flush();

        return ['id' => $level->getId(), 'nom' => (string) $level->getTitre(), 'cree' => true];
    }

    private function catalogueName(string $name, int $maxLength, string $field): string
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > $maxLength) {
            throw RedactionError::invalid([['champ' => 'nom', 'message' => sprintf('Nom de %s obligatoire (%d caractères au plus).', $field, $maxLength)]]);
        }

        return $name;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(): array
    {
        $drafts = $this->entityManager->getRepository(QcmDraft::class)->findBy([], ['id' => 'DESC'], 200);

        return array_map(fn (QcmDraft $d) => $this->summary($d), $drafts);
    }

    /**
     * @return array<string, mixed>
     */
    public function show(int $id): array
    {
        return $this->detail($this->draft($id));
    }

    /**
     * Enregistre le brouillon s'il ne déclenche aucun contrôle bloquant ; sinon rien n'est enregistré.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(array $data, string $agent): array
    {
        $draft = new QcmDraft($agent);
        $this->apply($draft, $data, true);
        $issues = $this->blockUnlessValid($draft);

        $this->entityManager->persist($draft);
        $this->entityManager->flush();

        return $this->detail($draft) + ['controles' => $issues];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(int $id, array $data): array
    {
        $draft = $this->draft($id);
        $this->assertNotPublished($draft);

        try {
            $this->apply($draft, $data, false);
            $issues = $this->blockUnlessValid($draft);
        } catch (RedactionError $error) {
            // Le brouillon enregistré reste tel quel
            $this->entityManager->refresh($draft);
            throw $error;
        }

        // Le contenu a changé : la relecture précédente ne vaut plus
        $draft->clearReview()->setStatus(QcmDraft::STATUS_DRAFT);
        $this->entityManager->flush();

        return $this->detail($draft) + ['controles' => $issues];
    }

    /**
     * @return array{sansControleBloquant: bool, publiable: bool, controles: list<array<string, mixed>>}
     */
    public function check(int $id): array
    {
        $draft = $this->draft($id);
        $issues = $this->checker->check($draft);
        $all = [...$issues, ...$this->reviewIssues($draft)];

        return [
            'sansControleBloquant' => !DraftChecker::hasBlockingIssue($issues),
            'publiable' => $draft->getStatus() !== QcmDraft::STATUS_PUBLISHED && !DraftChecker::hasBlockingIssue($all),
            'controles' => $all,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function review(int $id, array $data, string $agent): array
    {
        $draft = $this->draft($id);
        $this->assertNotPublished($draft);
        if ($draft->getAuthor() === $agent) {
            throw new RedactionError("La relecture doit venir d'un autre agent que celui qui a rédigé le QCM : utilisez l'autre connecteur (par exemple ChatGPT si le QCM vient de Claude).", RedactionError::CONFLICT);
        }

        [$report, $errors] = ReviewReport::parse($data);
        if ($errors !== []) {
            throw RedactionError::invalid($errors);
        }
        $draft->recordReview($report, $agent);
        $draft->setStatus($report['avis'] === ReviewReport::FAVORABLE ? QcmDraft::STATUS_DRAFT : QcmDraft::STATUS_TO_FIX);
        $this->entityManager->flush();

        return $this->detail($draft);
    }

    /**
     * Publie le QCM si aucun contrôle ne bloque et si un autre agent l'a relu avec un avis favorable.
     * Le QCM et toutes ses réponses sont créés ensemble, ou rien n'est créé.
     * Avec des destinataires, le QCM est publié directement réservé à ces comptes (QCM personnalisé) :
     * il n'est jamais servi à tous, même un instant.
     *
     * @param list<User> $destinataires
     * @return array<string, mixed>
     */
    public function publish(int $id, array $destinataires = []): array
    {
        $draft = $this->draft($id);
        $this->assertNotPublished($draft);
        $issues = [...$this->checker->check($draft), ...$this->reviewIssues($draft)];
        if (DraftChecker::hasBlockingIssue($issues)) {
            throw new RedactionError('Des contrôles bloquants empêchent la publication.', RedactionError::BLOCKED, $issues);
        }

        $qcm = $this->entityManager->wrapInTransaction(function () use ($draft, $destinataires): QCM {
            $qcm = (new QCM())
                ->setTitre(QcmDraftChecker::html(trim($draft->getQuestion())))
                ->setSolution(self::paragraphs($draft->getExplanation()))
                ->setLanguageQCM($draft->getTheme())
                ->setNiveauQCM($draft->getLevel());
            foreach ($draft->getAnswers() as $answer) {
                $choice = (new ChoicesQCM())
                    ->setQuestion(QcmDraftChecker::html(trim($answer['texte'])))
                    ->setIsCorrect($answer['correcte']);
                if ($answer['explication'] !== null && trim($answer['explication']) !== '') {
                    $choice->setExplication(self::paragraphs($answer['explication']));
                }
                $qcm->addChoicesQCM($choice);
            }
            if ($destinataires !== []) {
                $qcm->setReserve(true);
                foreach ($destinataires as $destinataire) {
                    $qcm->addDestinataire($destinataire);
                }
            }
            $this->entityManager->persist($qcm);
            $draft->markPublished($qcm);
            $this->entityManager->flush();

            return $qcm;
        });

        return [
            'brouillon' => $this->summary($draft),
            'qcm' => [
                'id' => $qcm->getId(),
                'theme' => $qcm->getLanguageQCM()?->getName(),
                'niveau' => $qcm->getNiveauQCM()?->getTitre(),
                'reponses' => $qcm->getChoicesQCMs()->count(),
                'reserve' => $qcm->isReserve(),
                'destinataires' => array_map(static fn (User $user) => ['id' => $user->getId(), 'pseudo' => $user->getUsername()], $destinataires),
            ],
            'controles' => $issues,
        ];
    }

    /**
     * @return list<array{code: string, regle: ?string, niveau: string, message: string}>
     */
    private function reviewIssues(QcmDraft $draft): array
    {
        $review = $draft->getReviewReport();
        if ($review === null) {
            return [['code' => 'RELECTURE_MANQUANTE', 'regle' => 'RED-02', 'niveau' => DraftChecker::BLOCKING, 'message' => 'Faites relire le QCM par un autre agent (relire_qcm, puis enregistrer_relecture_qcm) avant la publication.']];
        }

        return match ($review['avis']) {
            ReviewReport::FAVORABLE => [],
            ReviewReport::UNFAVORABLE => [['code' => 'RELECTURE_DEFAVORABLE', 'regle' => 'RED-02', 'niveau' => DraftChecker::BLOCKING, 'message' => 'La relecture est défavorable : corrigez le QCM, puis faites-le relire de nouveau.']],
            default => [['code' => 'RELECTURE_AVEC_RESERVES', 'regle' => 'RED-02', 'niveau' => DraftChecker::BLOCKING, 'message' => 'La relecture émet des réserves : corrigez le QCM, puis faites-le relire de nouveau pour obtenir un avis favorable.']],
        };
    }

    /**
     * @return list<array{code: string, regle: ?string, niveau: string, message: string}> avertissements restants
     */
    private function blockUnlessValid(QcmDraft $draft): array
    {
        $issues = $this->checker->check($draft);
        if (DraftChecker::hasBlockingIssue($issues)) {
            throw new RedactionError('Le QCM ne peut pas être enregistré : corrigez les contrôles bloquants.', RedactionError::BLOCKED, $issues);
        }

        return $issues;
    }

    private function assertNotPublished(QcmDraft $draft): void
    {
        if ($draft->getStatus() === QcmDraft::STATUS_PUBLISHED) {
            throw new RedactionError('Ce QCM est déjà publié : il se modifie dans le back-office.', RedactionError::CONFLICT);
        }
    }

    private function draft(int $id): QcmDraft
    {
        return $this->entityManager->find(QcmDraft::class, $id) ?? throw new RedactionError('Brouillon de QCM introuvable (voir lister_brouillons_qcm).', RedactionError::NOT_FOUND);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function apply(QcmDraft $draft, array $data, bool $creating): void
    {
        $errors = [];

        if ($creating || \array_key_exists('question', $data)) {
            $draft->setQuestion(trim((string) ($data['question'] ?? '')));
        }
        if ($creating || \array_key_exists('explication', $data)) {
            $explanation = trim((string) ($data['explication'] ?? ''));
            if (mb_strlen($explanation) > self::MAX_EXPLANATION_LENGTH) {
                $errors[] = ['champ' => 'explication', 'message' => sprintf('Explication trop longue (%d caractères au plus).', self::MAX_EXPLANATION_LENGTH)];
            }
            $draft->setExplanation($explanation);
        }
        if ($creating || \array_key_exists('reponses', $data)) {
            $draft->setAnswers($this->answers($data['reponses'] ?? null, $errors));
        }
        if ($creating || \array_key_exists('themeId', $data)) {
            $theme = isset($data['themeId']) ? $this->entityManager->find(LanguageQCM::class, (int) $data['themeId']) : null;
            if (isset($data['themeId']) && $theme === null) {
                $errors[] = ['champ' => 'themeId', 'message' => 'Thème introuvable (voir lister_catalogue_qcm).'];
            }
            $draft->setTheme($theme);
        }
        if ($creating || \array_key_exists('niveauId', $data)) {
            $level = isset($data['niveauId']) ? $this->entityManager->find(NiveauQCM::class, (int) $data['niveauId']) : null;
            if (isset($data['niveauId']) && $level === null) {
                $errors[] = ['champ' => 'niveauId', 'message' => 'Niveau introuvable (voir lister_catalogue_qcm).'];
            }
            $draft->setLevel($level);
        }

        if ($errors !== []) {
            throw RedactionError::invalid($errors);
        }
    }

    /**
     * @param list<array{champ: string, message: string}> $errors
     * @return list<array{texte: string, correcte: bool, explication: ?string}>
     */
    private function answers(mixed $raw, array &$errors): array
    {
        if (!\is_array($raw) || !array_is_list($raw)) {
            $errors[] = ['champ' => 'reponses', 'message' => 'Liste de réponses attendue.'];

            return [];
        }

        $answers = [];
        foreach ($raw as $index => $answer) {
            if (!\is_array($answer) || !\is_string($answer['texte'] ?? null) || !\is_bool($answer['correcte'] ?? null)) {
                $errors[] = ['champ' => "reponses[$index]", 'message' => 'Réponse attendue : {texte, correcte, explication}.'];
                continue;
            }
            $explanation = isset($answer['explication']) ? trim((string) $answer['explication']) : '';
            if (mb_strlen($explanation) > self::MAX_ANSWER_EXPLANATION_LENGTH) {
                $errors[] = ['champ' => "reponses[$index].explication", 'message' => sprintf('Explication trop longue (%d caractères au plus).', self::MAX_ANSWER_EXPLANATION_LENGTH)];
            }
            $answers[] = ['texte' => trim($answer['texte']), 'correcte' => $answer['correcte'], 'explication' => $explanation === '' ? null : $explanation];
        }

        return $answers;
    }

    /**
     * Texte en paragraphes HTML : une ligne vide sépare deux paragraphes.
     */
    private static function paragraphs(string $text): string
    {
        $parts = preg_split('/\R\s*\R/u', trim($text), -1, \PREG_SPLIT_NO_EMPTY) ?: [];

        return implode('', array_map(static fn (string $p) => '<p>'.nl2br(QcmDraftChecker::html(trim($p)), false).'</p>', $parts));
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(QcmDraft $draft): array
    {
        return [
            'id' => $draft->getId(),
            'question' => $draft->getQuestion(),
            'statut' => $draft->getStatus(),
            'auteur' => $draft->getAuthor(),
            'theme' => $draft->getTheme()?->getName(),
            'niveau' => $draft->getLevel()?->getTitre(),
            'qcmPublieId' => $draft->getPublishedQcm()?->getId(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function detail(QcmDraft $draft): array
    {
        return $this->summary($draft) + [
            'explication' => $draft->getExplanation(),
            'reponses' => $draft->getAnswers(),
            'themeId' => $draft->getTheme()?->getId(),
            'niveauId' => $draft->getLevel()?->getId(),
            'creeLe' => $draft->getCreatedAt()->format(\DATE_ATOM),
            'publieLe' => $draft->getPublishedAt()?->format(\DATE_ATOM),
            'relecture' => $draft->getReviewReport() === null ? null : $draft->getReviewReport() + [
                'par' => $draft->getReviewedBy(),
                'le' => $draft->getReviewedAt()?->format(\DATE_ATOM),
            ],
        ];
    }
}
