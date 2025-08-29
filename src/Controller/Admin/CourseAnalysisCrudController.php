<?php

namespace App\Controller\Admin;

use App\Entity\CourseAnalysis;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class CourseAnalysisCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CourseAnalysis::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Analyse de Cours')
            ->setEntityLabelInPlural('Analyses de Cours')
            ->setDefaultSort(['analyzedAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
            // Boutons NEW et EDIT activés pour gestion manuelle
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('pageContent', 'Contenu du cours')
            ->setCrudController(PageContentCrudController::class)
            ->setRequired(true);
            
        yield NumberField::new('qualityScore', 'Score de qualité')
            ->setNumDecimals(1)
            ->setHelp('Score de qualité pédagogique (0-10)');
            
        yield TextField::new('difficultyLevel', 'Niveau de difficulté')
            ->setHelp('Niveau évalué par l\'IA');
            
        yield NumberField::new('estimatedReadingTime', 'Temps de lecture (min)')
            ->setHelp('Temps estimé en minutes');

        yield TextareaField::new('summary', 'Résumé de l\'analyse')
            ->hideOnForm();

        if ($pageName === Crud::PAGE_DETAIL) {
            yield ArrayField::new('analysis', 'Analyse détaillée')
                ->setHelp('Données complètes de l\'analyse IA');
                
            yield ArrayField::new('suggestions', 'Suggestions d\'amélioration avec code')
                ->setHelp('Suggestions concrètes avec exemples de code à implémenter');
        }

        yield DateTimeField::new('analyzedAt', 'Analysé le')
            ->hideOnForm();
            
        yield DateTimeField::new('createdAt', 'Créé le')
            ->hideOnForm()
            ->hideOnIndex();
    }
}