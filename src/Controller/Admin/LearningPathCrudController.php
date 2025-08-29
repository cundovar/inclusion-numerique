<?php

namespace App\Controller\Admin;

use App\Entity\LearningPath;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class LearningPathCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LearningPath::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Parcours d\'Apprentissage')
            ->setEntityLabelInPlural('Parcours d\'Apprentissage')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('status')->setChoices([
                'Brouillon' => 'draft',
                'Actif' => 'active',
                'Terminé' => 'completed',
                'Archivé' => 'archived'
            ]))
            ->add(ChoiceFilter::new('difficultyLevel')->setChoices([
                'Débutant' => 'beginner',
                'Intermédiaire' => 'intermediate',
                'Avancé' => 'advanced'
            ]));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', 'Titre du parcours')
            ->setRequired(true);

        yield TextareaField::new('description', 'Description')
            ->hideOnIndex()
            ->setHelp('Description détaillée du parcours d\'apprentissage');

        yield AssociationField::new('targetUser', 'Utilisateur cible')
            ->setHelp('Utilisateur spécifique ou laisser vide pour un parcours général')
            ->setRequired(false);

        yield AssociationField::new('category', 'Catégorie')
            ->setCrudController(CategoryCrudController::class)
            ->setRequired(false);

        yield ChoiceField::new('difficultyLevel', 'Niveau de difficulté')
            ->setChoices([
                'Débutant' => 'beginner',
                'Intermédiaire' => 'intermediate',
                'Avancé' => 'advanced'
            ])
            ->setRequired(true)
            ->renderAsBadges([
                'beginner' => 'success',
                'intermediate' => 'warning',
                'advanced' => 'danger'
            ]);

        yield IntegerField::new('estimatedDuration', 'Durée estimée (min)')
            ->setHelp('Durée totale estimée en minutes');

        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'Brouillon' => 'draft',
                'Actif' => 'active',
                'Terminé' => 'completed',
                'Archivé' => 'archived'
            ])
            ->setRequired(true)
            ->renderAsBadges([
                'draft' => 'secondary',
                'active' => 'success',
                'completed' => 'info',
                'archived' => 'dark'
            ]);

        if ($pageName === Crud::PAGE_DETAIL || $pageName === Crud::PAGE_EDIT || $pageName === Crud::PAGE_NEW) {
            yield ArrayField::new('courseSequence', 'Séquence de cours')
                ->hideOnIndex()
                ->setHelp('IDs des PageContent dans l\'ordre du parcours');

            yield ArrayField::new('prerequisites', 'Prérequis')
                ->hideOnIndex()
                ->setHelp('Compétences ou connaissances requises');

            yield ArrayField::new('learningObjectives', 'Objectifs pédagogiques')
                ->hideOnIndex()
                ->setHelp('Objectifs d\'apprentissage de ce parcours');
        }

        yield AssociationField::new('createdBy', 'Créé par')
            ->hideOnForm()
            ->setRequired(false);

        yield DateTimeField::new('createdAt', 'Créé le')
            ->hideOnForm();

        yield DateTimeField::new('updatedAt', 'Modifié le')
            ->hideOnForm()
            ->hideOnIndex();
    }
}