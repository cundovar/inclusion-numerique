<?php

namespace App\Controller\Admin;

use App\Entity\UserCustomization;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class UserCustomizationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserCustomization::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Personnalisation')
            ->setEntityLabelInPlural('Personnalisations')
            ->setSearchFields(['user.username']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            AssociationField::new('user', 'Utilisateur')
                ->setRequired(true),

            CodeEditorField::new('settingsJson', 'Paramètres (JSON)')
                ->setLanguage('js')
                ->setNumOfRows(20)
                ->setHelp('Paramètres de personnalisation au format JSON'),

            DateTimeField::new('createdAt', 'Créé le')
                ->hideOnForm(),

            DateTimeField::new('updatedAt', 'Modifié le')
                ->hideOnForm(),
        ];
    }
}
