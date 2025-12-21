<?php

namespace App\Controller\Admin;

use App\Entity\LanguageQCM;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class LanguageQCMCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LanguageQCM::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            AssociationField::new('qcm')
                ->onlyOnIndex()
        ];
    }
}
