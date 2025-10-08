<?php

namespace App\Controller\Admin;

use App\Entity\ExoContent;
use App\Form\ExoBlockTypeForm;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

class ExoContentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ExoContent::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('exo'),
            AssociationField::new('category'),
            AssociationField::new('exoMenu'),
            TextField::new('title'),
            CodeEditorField::new('code'),
            CollectionField::new('exoBlocks')
                ->setEntryType(ExoBlockTypeForm::class)
                ->onlyOnForms()
                ->allowAdd()
                ->allowDelete()
        ];
    }
}
