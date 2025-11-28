<?php

namespace App\Controller\Admin;

use App\Entity\Logo;
use App\Controller\Admin\DocDeCodeCrudController;
use App\Controller\Admin\CategoryCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LogoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Logo::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('titre'),
            TextField::new('logo'),
            AssociationField::new('docDeCode', 'Doc de code')
                ->setCrudController(DocDeCodeCrudController::class)
                ->autocomplete(),
                AssociationField::new('category', 'Catégorie')
                    ->setCrudController(CategoryCrudController::class)
                    ->autocomplete(),
        ];
    }
    
}
