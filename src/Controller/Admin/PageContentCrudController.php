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
           
            TextField::new('title'),
            TextEditorField::new('content'),
            CodeEditorField::new('code'),

           CollectionField::new('pageBlocks')
              ->setEntryType(ContentBlockTypeForm::class)
              ->onlyOnForms()
              ->allowAdd()
              ->allowDelete()
        ];
   
    }
    
}
