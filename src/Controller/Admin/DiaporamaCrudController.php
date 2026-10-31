<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Diaporama;
use App\Service\Redaction\DiaporamaChecker;
use App\Service\Redaction\DiaporamaService;
use App\Service\Redaction\DiaporamaSource;
use App\Service\Redaction\DraftChecker;
use App\Service\Redaction\RedactionError;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Diaporamas rédigés par le connecteur (docs/PLAN_DIAPORAMAS.md, D7) : liste, aperçu, correction d'un point
 * ou d'une note, retrait de la publication et mention « à mettre à jour » quand le cours a changé.
 * La création et la publication restent au connecteur, avec la relecture par un autre agent.
 */
final class DiaporamaCrudController extends AbstractCrudController
{
    // Formulaire POST avec un jeton CSRF : EasyAdmin n'en ajoute pas aux actions personnalisées
    private const POST_ACTION_TEMPLATE = 'admin/diaporama/action_post.html.twig';

    public function __construct(
        private DiaporamaService $diaporamas,
        private DiaporamaChecker $checker,
        private AdminUrlGenerator $adminUrlGenerator,
        private DiaporamaSource $source = new DiaporamaSource(),
    ) {}

    public static function getEntityFqcn(): string
    {
        return Diaporama::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Diaporama')
            ->setEntityLabelInPlural('Diaporamas')
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setHelp('index', 'Les diaporamas sont rédigés et publiés par l\'assistant de rédaction, après la relecture d\'un autre connecteur. Ici, vous pouvez les relire, corriger un point ou une note, ou retirer un diaporama du site.');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(ChoiceFilter::new('status', 'Statut')->setChoices(Diaporama::STATUS_LABELS));
    }

    public function configureActions(Actions $actions): Actions
    {
        $corriger = Action::new('corriger', 'Corriger', 'fa fa-pen')->linkToCrudAction('corriger');
        $retirer = Action::new('retirer', 'Retirer du site', 'fa fa-eye-slash')
            ->linkToCrudAction('retirer')
            ->setTemplatePath(self::POST_ACTION_TEMPLATE)
            ->displayIf(static fn (Diaporama $diaporama) => $diaporama->isPublished());
        $aJour = Action::new('marquerAJour', 'Marquer comme à jour', 'fa fa-check')
            ->linkToCrudAction('marquerAJour')
            ->setTemplatePath(self::POST_ACTION_TEMPLATE)
            ->displayIf(fn (Diaporama $diaporama) => $this->source->estPerime($diaporama));

        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $corriger)
            ->add(Crud::PAGE_DETAIL, $corriger)
            ->add(Crud::PAGE_DETAIL, $retirer)
            ->add(Crud::PAGE_DETAIL, $aJour);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id');
        yield TextField::new('titre', 'Titre');
        yield TextField::new('libelleCible', 'Cible');
        yield ChoiceField::new('status', 'Statut')
            ->setChoices(Diaporama::STATUS_LABELS)
            ->renderAsBadges([
                Diaporama::STATUS_DRAFT => 'secondary',
                Diaporama::STATUS_TO_FIX => 'warning',
                Diaporama::STATUS_PUBLISHED => 'success',
            ]);
        // Calculé à partir du texte actuel du cours ; adossé au titre, toujours renseigné
        yield TextField::new('titre', 'Suivi du cours')
            ->formatValue(fn ($value, Diaporama $diaporama) => $this->source->estPerime($diaporama) ? 'À mettre à jour' : 'À jour');
        yield DateTimeField::new('updatedAt', 'Modifié le');

        if ($pageName === Crud::PAGE_DETAIL) {
            yield TextField::new('sousTitre', 'Sous-titre');
            yield TextField::new('author', 'Rédigé par');
            yield DateTimeField::new('publishedAt', 'Publié le');
            yield TextField::new('titre', 'Contrôles')
                ->formatValue(fn ($value, Diaporama $diaporama) => $this->describeIssues($this->checker->check($diaporama)));
            yield ArrayField::new('reviewReport', 'Relecture')->setTemplatePath('admin/diaporama/relecture.html.twig');
            yield ArrayField::new('diapositives', 'Aperçu')->setTemplatePath('admin/diaporama/apercu.html.twig');
        }
    }

    /**
     * Correction du titre, du sous-titre et des diapositives (en YAML, plus lisible que le JSON enregistré).
     */
    #[AdminAction(routePath: '/{entityId}/corriger', routeName: 'corriger', methods: ['GET', 'POST'])]
    public function corriger(AdminContext $context, Request $request): Response
    {
        $diaporama = $this->instance($context);
        $form = $this->createFormBuilder([
            'titre' => $diaporama->getTitre(),
            'sousTitre' => $diaporama->getSousTitre(),
            'diapositives' => Yaml::dump($diaporama->getDiapositives(), 4, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK),
        ])
            ->add('titre', TextType::class, ['label' => 'Titre'])
            ->add('sousTitre', TextType::class, ['label' => 'Sous-titre', 'required' => false])
            ->add('diapositives', TextareaType::class, [
                'label' => 'Diapositives',
                'attr' => ['rows' => 30, 'spellcheck' => 'true', 'style' => 'font-family: monospace'],
                'help' => 'Chaque diapositive commence par « - modele: ». Gardez les espaces en début de ligne et le tiret devant chaque point. Les notes de présentation suivent « notes: | », décalées de quelques espaces.',
            ])
            ->getForm();

        $form->handleRequest($request);
        $errors = [];
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{titre: ?string, sousTitre: ?string, diapositives: ?string} $data */
            $data = $form->getData();
            try {
                $warnings = $this->diaporamas->correct($diaporama, [
                    'titre' => $data['titre'],
                    'sousTitre' => $data['sousTitre'],
                    'diapositives' => Yaml::parse((string) $data['diapositives']),
                ]);
                $this->addFlash('success', 'Diaporama corrigé.'.($warnings === [] ? '' : sprintf(' %d avertissement(s) restent, voir « Contrôles ».', \count($warnings))));

                return $this->redirectToDetail($diaporama);
            } catch (ParseException $exception) {
                $errors[] = sprintf('Diapositives illisibles, ligne %d : vérifiez les espaces en début de ligne et les tirets.', $exception->getParsedLine());
            } catch (RedactionError $error) {
                $errors = $this->errorMessages($error);
            }
        }

        return $this->render('admin/diaporama/corriger.html.twig', [
            'diaporama' => $diaporama,
            'form' => $form,
            'errors' => $errors,
            'detailUrl' => $this->detailUrl($diaporama),
        ]);
    }

    #[AdminAction(routePath: '/{entityId}/retirer', routeName: 'retirer', methods: ['POST'])]
    public function retirer(AdminContext $context, Request $request): RedirectResponse
    {
        $diaporama = $this->instance($context);
        if (!$this->validToken($request, 'retirer', $diaporama)) {
            return $this->redirectToDetail($diaporama);
        }
        $this->diaporamas->unpublish($diaporama);
        $this->addFlash('success', 'Diaporama retiré du site. Pour le republier, l\'assistant de rédaction devra obtenir une nouvelle relecture.');

        return $this->redirectToDetail($diaporama);
    }

    #[AdminAction(routePath: '/{entityId}/marquer-a-jour', routeName: 'marquer_a_jour', methods: ['POST'])]
    public function marquerAJour(AdminContext $context, Request $request): RedirectResponse
    {
        $diaporama = $this->instance($context);
        if (!$this->validToken($request, 'marquerAJour', $diaporama)) {
            return $this->redirectToDetail($diaporama);
        }
        $this->diaporamas->markUpToDate($diaporama);
        $this->addFlash('success', 'Diaporama marqué comme à jour.');

        return $this->redirectToDetail($diaporama);
    }

    private function instance(AdminContext $context): Diaporama
    {
        $diaporama = $context->getEntity()->getInstance();
        if (!$diaporama instanceof Diaporama) {
            throw $this->createNotFoundException();
        }

        return $diaporama;
    }

    private function validToken(Request $request, string $action, Diaporama $diaporama): bool
    {
        if ($this->isCsrfTokenValid(sprintf('diaporama-%s-%d', $action, $diaporama->getId()), (string) $request->request->get('_token'))) {
            return true;
        }
        $this->addFlash('danger', 'Action refusée : la page a expiré. Rechargez-la puis recommencez.');

        return false;
    }

    private function detailUrl(Diaporama $diaporama): string
    {
        return $this->adminUrlGenerator->unsetAll()->setController(self::class)->setAction(Action::DETAIL)->setEntityId($diaporama->getId())->generateUrl();
    }

    private function redirectToDetail(Diaporama $diaporama): RedirectResponse
    {
        return $this->redirect($this->detailUrl($diaporama));
    }

    /**
     * @return list<string>
     */
    private function errorMessages(RedactionError $error): array
    {
        $messages = [];
        foreach ($error->details ?? [] as $detail) {
            if (!\is_array($detail) || ($detail['niveau'] ?? DraftChecker::BLOCKING) !== DraftChecker::BLOCKING) {
                continue;
            }
            $messages[] = sprintf('%s : %s', $detail['code'] ?? $detail['champ'] ?? '', $detail['message'] ?? '');
        }

        return $messages === [] ? [$error->getMessage()] : $messages;
    }

    /**
     * @param list<array{code: string, regle: ?string, niveau: string, message: string}> $issues
     */
    private function describeIssues(array $issues): string
    {
        if ($issues === []) {
            return 'Aucun contrôle signalé.';
        }

        // EasyAdmin affiche le texte calculé comme du HTML : échapper les messages
        return implode('<br>', array_map(
            static fn (array $issue) => htmlspecialchars(sprintf('%s %s : %s', $issue['niveau'] === DraftChecker::BLOCKING ? '⛔' : '⚠️', $issue['code'], $issue['message']), \ENT_QUOTES),
            $issues,
        ));
    }
}
