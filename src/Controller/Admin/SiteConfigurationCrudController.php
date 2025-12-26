<?php

namespace App\Controller\Admin;

use App\Entity\SiteConfiguration;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Doctrine\ORM\EntityManagerInterface;

class SiteConfigurationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SiteConfiguration::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Configuration du site')
            ->setEntityLabelInPlural('Configurations du site')
            ->setPageTitle('index', 'Configuration globale du site')
            ->setPageTitle('edit', 'Modifier la configuration');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            TextField::new('configKey', 'Clé de configuration')
                ->setDisabled()
                ->setHelp('Identifiant unique de la configuration'),

            CodeEditorField::new('settingsJson', 'Paramètres (JSON)')
                ->setLanguage('js')
                ->setNumOfRows(25)
                ->setHelp('Configuration globale du site au format JSON'),

            DateTimeField::new('updatedAt', 'Dernière modification')
                ->hideOnForm(),
        ];
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Force la mise à jour du timestamp
        $entityInstance->setSettings($entityInstance->getSettings());

        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }
}
