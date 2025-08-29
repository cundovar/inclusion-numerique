<?php

namespace App\Controller\Admin;

use App\Entity\UserLearningAnalytics;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class UserLearningAnalyticsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserLearningAnalytics::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Analytics Apprenant')
            ->setEntityLabelInPlural('Analytics des Apprenants')
            ->setDefaultSort(['eventDate' => 'DESC'])
            ->setPaginatorPageSize(30);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::EDIT); // Les analytics sont créées automatiquement
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('user'))
            ->add(ChoiceFilter::new('eventType')->setChoices([
                'Vue' => 'view',
                'Complété' => 'complete',
                'Test réussi' => 'test_passed',
                'Test échoué' => 'test_failed',
                'Favori' => 'bookmark'
            ]));
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('user', 'Utilisateur')
            ->setRequired(true);

        yield AssociationField::new('pageContent', 'Contenu')
            ->setCrudController(PageContentCrudController::class)
            ->setRequired(false);

        yield AssociationField::new('learningPath', 'Parcours d\'apprentissage')
            ->setCrudController(LearningPathCrudController::class)
            ->setRequired(false)
            ->hideOnIndex();

        yield ChoiceField::new('eventType', 'Type d\'événement')
            ->setChoices([
                'Vue' => 'view',
                'Complété' => 'complete',
                'Test réussi' => 'test_passed',
                'Test échoué' => 'test_failed',
                'Favori' => 'bookmark'
            ])
            ->setRequired(true)
            ->renderAsBadges([
                'view' => 'info',
                'complete' => 'success',
                'test_passed' => 'success',
                'test_failed' => 'danger',
                'bookmark' => 'warning'
            ]);

        yield IntegerField::new('timeSpent', 'Temps passé (sec)')
            ->setHelp('Temps passé en secondes');

        yield NumberField::new('comprehensionScore', 'Score de compréhension')
            ->setNumDecimals(1)
            ->setHelp('Score de 0 à 100')
            ->hideOnIndex();

        if ($pageName === Crud::PAGE_DETAIL) {
            yield ArrayField::new('interactionData', 'Données d\'interaction')
                ->hideOnIndex()
                ->setHelp('Données spécifiques à l\'événement');

            yield ArrayField::new('difficultyConcepts', 'Concepts difficiles')
                ->hideOnIndex()
                ->setHelp('Concepts où l\'utilisateur a des difficultés');

            yield ArrayField::new('preferredLearningStyle', 'Style d\'apprentissage')
                ->hideOnIndex()
                ->setHelp('Style d\'apprentissage préféré');
        }

        yield DateTimeField::new('eventDate', 'Date de l\'événement')
            ->setRequired(true);

        yield DateTimeField::new('createdAt', 'Créé le')
            ->hideOnForm()
            ->hideOnIndex();
    }
}