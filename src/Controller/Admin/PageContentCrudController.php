<?php

namespace App\Controller\Admin;

use App\Entity\PageContent;
use App\Form\ContentBlockTypeForm;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class PageContentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PageContent::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Cours')
            ->setEntityLabelInPlural('Cours')
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('status', 'Statut')->setChoices(PageContent::STATUS_LABELS))
            ->add(EntityFilter::new('category', 'Parcours'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Titre'),
            ChoiceField::new('status', 'Statut')
                ->setChoices(PageContent::STATUS_LABELS)
                ->renderAsBadges([
                    PageContent::STATUS_DRAFT => 'secondary',
                    PageContent::STATUS_IN_REVIEW => 'info',
                    PageContent::STATUS_PUBLISHED => 'success',
                    PageContent::STATUS_TO_REVISE => 'warning',
                ])
                ->setHelp('Seul un cours « Publié » apparaît sur le site.'),

            TextField::new('author', 'Préparé par')
                ->setHelp('« agent:… » pour un brouillon préparé par l\'assistant de rédaction.')
                ->hideOnIndex(),
            AssociationField::new('page')->setHelp("Adresse du cours (slug)."),
            AssociationField::new('category', 'Parcours'),
            AssociationField::new('menu', 'Module'),
            IntegerField::new('ordre', 'Rang dans le module')
                ->setHelp('1 pour le premier cours du module. Sans rang, le cours passe après les autres.')
                ->hideOnIndex(),
            AssociationField::new('niveauCours', 'Niveau'),

            UrlField::new('sourceUrl', 'Page officielle de référence')
                ->setHelp("Pour un cours sur un service (CAF, France Travail…), la page officielle d'où viennent les informations.")
                ->hideOnIndex(),
            DateField::new('verifiedAt', 'Vérifié le')
                ->setHelp('Date de la dernière comparaison avec la source officielle.'),

            TextareaField::new('reviewSummary', 'Relecture par agent')
                ->onlyOnDetail(),
            CodeEditorField::new('code', 'Contenu (HTML)')
                ->setLanguage('xml')
                ->setHelp('Pour insérer une capture, écrivez son marqueur sur une ligne seule, par exemple [capture:12] (voir le menu Captures).')
                ->hideOnIndex(),

            CollectionField::new('pageBlocks')
                ->setEntryType(ContentBlockTypeForm::class)
                ->onlyOnForms()
                ->allowAdd()
                ->allowDelete(),
        ];
    }
}
