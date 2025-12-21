<?php

namespace App\Controller\Admin;

use App\Entity\UserPageVisit;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class UserPageVisitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserPageVisit::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Visite de page')
            ->setEntityLabelInPlural('Historique des visites')
            ->setDefaultSort(['visitedAt' => 'DESC'])
            ->setPaginatorPageSize(50)
            ->setDateTimeFormat('dd/MM/yyyy HH:mm');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('user')
            ->add('pageUrl')
            ->add('visitedAt');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('id', 'ID')
                ->onlyOnDetail(),
            AssociationField::new('user', 'Utilisateur')
                ->setCssClass('font-weight-bold'),
            TextField::new('pageTitle', 'Titre de la page')
                ->setRequired(false),
            TextField::new('pageUrl', 'URL')
                ->hideOnIndex(),
            DateTimeField::new('visitedAt', 'Date de visite')
                ->setFormat('dd/MM/yyyy HH:mm:ss'),
            IntegerField::new('timeSpent', 'Durée (secondes)')
                ->setHelp('Temps passé sur la page en secondes')
                ->formatValue(function ($value) {
                    if (!$value) return 'N/A';
                    $minutes = floor($value / 60);
                    $seconds = $value % 60;
                    return $minutes > 0 ? "{$minutes}m {$seconds}s" : "{$seconds}s";
                }),
            TextField::new('ipAddress', 'Adresse IP')
                ->hideOnIndex(),
            TextField::new('userAgent', 'Navigateur')
                ->hideOnIndex()
                ->formatValue(function ($value) {
                    if (!$value) return 'N/A';
                    // Extraire juste le nom du navigateur
                    if (preg_match('/(Chrome|Firefox|Safari|Edge|Opera)\/[\d\.]+/', $value, $matches)) {
                        return $matches[1];
                    }
                    return substr($value, 0, 50) . '...';
                })
        ];
    }
}
