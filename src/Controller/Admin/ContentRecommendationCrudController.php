<?php

namespace App\Controller\Admin;

use App\Entity\ContentRecommendation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\CourseModificationService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class ContentRecommendationCrudController extends AbstractCrudController
{
    private CourseModificationService $modificationService;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(CourseModificationService $modificationService, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->modificationService = $modificationService;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }
    public static function getEntityFqcn(): string
    {
        return ContentRecommendation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Recommandation')
            ->setEntityLabelInPlural('Recommandations de Contenu')
            ->setDefaultSort(['priority' => 'ASC', 'createdAt' => 'DESC'])
            ->setPaginatorPageSize(25);
    }

    public function configureActions(Actions $actions): Actions
    {
        // Action personnalisée pour appliquer les recommandations
        $applyAction = Action::new('apply', '✅ Appliquer au cours', 'fa fa-check')
            ->linkToCrudAction('applyToCourse')
            ->displayIf(fn ($entity) => $entity->getStatus() === 'pending')
            ->addCssClass('btn btn-success');

        $previewAction = Action::new('preview', '👁️ Prévisualiser', 'fa fa-eye')
            ->linkToCrudAction('previewModification')
            ->addCssClass('btn btn-info');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $applyAction)
            ->add(Crud::PAGE_DETAIL, $previewAction)
            ->disable(Action::NEW); // Les recommandations sont créées par l'IA
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('status')->setChoices([
                'En attente' => 'pending',
                'Appliquée' => 'applied',
                'Rejetée' => 'dismissed'
            ]))
            ->add(ChoiceFilter::new('priority')->setChoices([
                'Haute' => 'high',
                'Moyenne' => 'medium',
                'Basse' => 'low'
            ]))
            ->add(ChoiceFilter::new('type')->setChoices([
                'Structure' => 'structure',
                'Contenu' => 'content',
                'Exemples' => 'examples',
                'Exercices' => 'exercises',
                'Amélioration code' => 'code_improvement',
                'Enhancement CSS' => 'css_enhancement',
                'SEO' => 'seo_enhancement',
                'Exemples réels' => 'real_world_examples',
                'Compatibilité' => 'browser_compatibility'
            ]));
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('pageContent', 'Contenu concerné')
            ->setCrudController(PageContentCrudController::class)
            ->setRequired(true);

        yield TextField::new('title', 'Titre de la recommandation')
            ->setRequired(true);

        yield ChoiceField::new('type', 'Type')
            ->setChoices([
                'Structure' => 'structure',
                'Contenu' => 'content',
                'Exemples' => 'examples',
                'Exercices' => 'exercises'
            ])
            ->setRequired(true);

        yield ChoiceField::new('priority', 'Priorité')
            ->setChoices([
                'Haute' => 'high',
                'Moyenne' => 'medium',
                'Basse' => 'low'
            ])
            ->setRequired(true)
            ->renderAsBadges([
                'high' => 'danger',
                'medium' => 'warning',
                'low' => 'success'
            ]);

        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'En attente' => 'pending',
                'Appliquée' => 'applied',
                'Rejetée' => 'dismissed'
            ])
            ->setRequired(true)
            ->renderAsBadges([
                'pending' => 'warning',
                'applied' => 'success',
                'dismissed' => 'secondary'
            ]);

        yield TextareaField::new('description', 'Description')
            ->hideOnIndex()
            ->setRequired(true);

        if ($pageName === Crud::PAGE_DETAIL || $pageName === Crud::PAGE_EDIT) {
            yield CodeEditorField::new('suggestedContent', 'Code suggéré')
                ->setLanguage('xml')
                ->hideOnIndex()
                ->setHelp('Code HTML/CSS proposé par l\'IA - Copier/coller dans votre cours');
        }

        yield AssociationField::new('appliedBy', 'Appliquée par')
            ->hideOnIndex()
            ->setRequired(false);

        yield DateTimeField::new('appliedAt', 'Appliquée le')
            ->hideOnIndex()
            ->hideOnForm();

        yield DateTimeField::new('createdAt', 'Créée le')
            ->hideOnForm();
    }

    /**
     * Action pour appliquer une recommandation au cours
     */
    public function applyToCourse(AdminContext $context): RedirectResponse
    {
        /** @var ContentRecommendation $recommendation */
        $recommendation = $context->getEntity()->getInstance();
        
        if ($this->modificationService->applyRecommendation($recommendation)) {
            $this->addFlash('success', 'Recommandation appliquée avec succès au cours !');
        } else {
            $this->addFlash('error', 'Erreur lors de l\'application de la recommandation.');
        }

        $url = $this->adminUrlGenerator
            ->setController(ContentRecommendationCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($recommendation->getId())
            ->generateUrl();

        return new RedirectResponse($url);
    }

    /**
     * Action pour prévisualiser les modifications
     */
    public function previewModification(AdminContext $context): RedirectResponse
    {
        /** @var ContentRecommendation $recommendation */
        $recommendation = $context->getEntity()->getInstance();
        
        $preview = $this->modificationService->previewModification($recommendation);
        
        // Stocker la prévisualisation en session pour l'afficher
        $context->getRequest()->getSession()->set('modification_preview', $preview);
        $this->addFlash('info', 'Prévisualisation générée. Consultez les détails ci-dessous.');

        $url = $this->adminUrlGenerator
            ->setController(ContentRecommendationCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($recommendation->getId())
            ->generateUrl();

        return new RedirectResponse($url);
    }
}