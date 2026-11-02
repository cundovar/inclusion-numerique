<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use App\Controller\Admin\PositionMenusCrudController;
use App\Controller\Admin\SuperMenuCrudController;
use App\Controller\Admin\LogoCrudController;
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
            ChoiceField::new('audience', 'Public')
                ->setChoices([
                    "J'apprends le numérique" => Category::AUDIENCE_APPRENDRE,
                    "J'accompagne des personnes" => Category::AUDIENCE_ACCOMPAGNER,
                ])
                ->setHelp("Entrée de l'accueil où ce parcours apparaît. Sans public, il n'apparaît dans aucune des deux."),
            IntegerField::new('ordre', 'Rang dans le catalogue')
                ->setHelp('1 pour le premier parcours de son public. Sans rang, le parcours passe après les autres, par ordre alphabétique.'),
            TextField::new('couleur', 'Couleur bg de la catégorie'),
            TextEditorField::new('description', 'Description '),
            BooleanField::new('visible', 'Visible')
                ->setHelp('Si décoché, la catégorie ne sera pas affichée dans les menus'),

            AssociationField::new('superMenu', 'Super Menu')
                ->setCrudController(SuperMenuCrudController::class)
                ->autocomplete(),

            AssociationField::new('positionMenus', 'Position du menu')
                ->setCrudController(PositionMenusCrudController::class)
                ->autocomplete(),
                AssociationField::new('niveauCours', 'Niveau de cours')
                ->setCrudController(NiveauCoursCrudController::class)
                ->autocomplete(),


            AssociationField::new('illustration', 'Miniature du parcours')
                ->setCrudController(CourseMediaCrudController::class)
                ->autocomplete()
                ->setHelp("Illustration affichée sur la carte du parcours (menu Captures, type « illustration »). Sans miniature, la carte garde une place réservée."),

            AssociationField::new('logo', 'Logo')
                ->setCrudController(LogoCrudController::class)
                ->autocomplete(),
        ];
    }
    
}
