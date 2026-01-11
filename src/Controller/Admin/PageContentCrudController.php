<?php

namespace App\Controller\Admin;

use App\Entity\PageContent;
use App\Form\ContentBlockTypeForm;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;


class PageContentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PageContent::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('page'),
            AssociationField::new('category'),
            AssociationField::new('menu'),
            AssociationField::new('niveauCours'),

            TextField::new('title'),

            BooleanField::new('visible', 'Visible')
                ->setHelp('Si décoché, le contenu ne sera pas affiché sur le site'),

            CodeEditorField::new('code'),

           CollectionField::new('pageBlocks')
              ->setEntryType(ContentBlockTypeForm::class)
              ->onlyOnForms()
              ->allowAdd()
              ->allowDelete()
        ];

    }
    
}
