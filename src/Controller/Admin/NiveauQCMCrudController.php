<?php

namespace App\Controller\Admin;

use App\Entity\NiveauQCM;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class NiveauQCMCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return NiveauQCM::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('titre'),
            AssociationField::new('qcm')
                ->onlyOnIndex()
        ];
    }
}
