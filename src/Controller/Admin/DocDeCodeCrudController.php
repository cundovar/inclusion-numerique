<?php

namespace App\Controller\Admin;

use App\Entity\DocDeCode;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class DocDeCodeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DocDeCode::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
           
            TextField::new('titre'),
            TextField::new('url'),
            TextField::new('alt'),
            TextField::new('color'),
            AssociationField::new('logo', 'Logo')
                ->setCrudController(LogoCrudController::class)
                ->autocomplete(),
        ];
    }

}
