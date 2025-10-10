<?php

namespace App\Controller\Admin;

use App\Entity\QCM;
use App\Form\ChoicesQCMTypeForm;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

class QCMCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return QCM::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('titre', 'Question'),
            AssociationField::new('languageQCM', 'Langage'),
            AssociationField::new('niveauQCM', 'Niveau'),
            TextareaField::new('solution', 'Explication de la solution')
                ->setHelp('Explication détaillée de la bonne réponse'),
            CollectionField::new('choicesQCMs', 'Choix de réponses')
                ->setEntryType(ChoicesQCMTypeForm::class)
                ->onlyOnForms()
                ->allowAdd()
                ->allowDelete()
                ->setHelp('Ajoutez au moins 2 choix, dont au moins 1 correct')
        ];
    }
}
