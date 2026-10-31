<?php

namespace App\Controller\Admin;

use App\Entity\PageContent;
use App\Form\ContentBlockTypeForm;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;


class PageContentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PageContent::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id' => 'DESC']);
    }

    // « En revue » : cours soumis par un connecteur MCP, à relire puis publier ici
    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(ChoiceFilter::new('status', 'Statut')->setChoices(PageContent::STATUS_LABELS));
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('page'),
            AssociationField::new('category'),
            AssociationField::new('menu'),
            AssociationField::new('niveauCours'),

            TextField::new('title'),

            ChoiceField::new('status', 'Statut')
                ->setChoices(PageContent::STATUS_LABELS)
                ->renderAsBadges([
                    PageContent::STATUS_DRAFT => 'secondary',
                    PageContent::STATUS_IN_REVIEW => 'info',
                    PageContent::STATUS_PUBLISHED => 'success',
                    PageContent::STATUS_TO_REVISE => 'warning',
                ])
                ->setHelp('Seul un cours « Publié » est affiché sur le site.'),
            TextField::new('author', 'Préparé par')
                ->setHelp('« connecteur:… » pour un brouillon préparé par un connecteur MCP.')
                ->hideOnIndex(),
            UrlField::new('sourceUrl', 'Documentation de référence')->hideOnIndex(),
            DateField::new('verifiedAt', 'Vérifié le')->hideOnIndex(),
            TextareaField::new('reviewSummary', 'Relecture par un connecteur')->onlyOnDetail(),

            CodeEditorField::new('code'),

           CollectionField::new('pageBlocks')
              ->setEntryType(ContentBlockTypeForm::class)
              ->onlyOnForms()
              ->allowAdd()
              ->allowDelete()
        ];

    }
    
}
