<?php

namespace App\Controller\Admin;

use App\Entity\ExoMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ExoMenuCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ExoMenu::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('label'),
            AssociationField::new('category'),
        ];
    }
}
