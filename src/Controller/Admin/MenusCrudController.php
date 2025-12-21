<?php

namespace App\Controller\Admin;

use App\Entity\Menus;
use App\Controller\Admin\PositionMenusCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class MenusCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Menus::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('label'),

            AssociationField::new('positionMenus', 'Position du menu')
                ->setCrudController(PositionMenusCrudController::class)
                ->autocomplete(),
        ];
    }
    
}
