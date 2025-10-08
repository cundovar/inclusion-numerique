<?php

namespace App\Controller\Admin;

use App\Entity\Exo;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ExoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Exo::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('slug'),
            AssociationField::new('exoMenu'),
        ];
    }
}
