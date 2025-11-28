<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use App\Controller\Admin\PositionMenusCrudController;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom de la catégorie'),
            TextField::new('couleur', 'Couleur bg de la catégorie'),
            TextEditorField::new('description', 'Description '),

            AssociationField::new('positionMenus', 'Position du menu')
                ->setCrudController(PositionMenusCrudController::class)
                ->autocomplete(),

            AssociationField::new('logo', 'Logo')
                ->setCrudController(LogoCrudController::class)
                ->autocomplete(),    
        ];
    }
    
}
