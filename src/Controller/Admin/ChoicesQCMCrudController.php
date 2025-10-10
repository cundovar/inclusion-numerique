<?php

namespace App\Controller\Admin;

use App\Entity\ChoicesQCM;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ChoicesQCMCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ChoicesQCM::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('qcm', 'QCM')
                ->hideOnForm(),
            TextField::new('question', 'Choix'),
            BooleanField::new('isCorrect', 'Correct ?'),
            TextareaField::new('explication', 'Explication')
        ];
    }
}
