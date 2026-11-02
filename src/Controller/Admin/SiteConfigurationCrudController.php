<?php

namespace App\Controller\Admin;

use App\Entity\SiteConfiguration;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

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

            // Mentions légales et page de confidentialité : un champ vide s'affiche « À compléter » sur le site
            FormField::addFieldset('Informations légales')
                ->setHelp('Affichées dans les mentions légales, la politique de confidentialité et les conditions d\'utilisation. Elles sont publiques.'),
            TextField::new('legalEditeur', "Nom de l'éditeur")
                ->onlyOnForms()
                ->setRequired(false)
                ->setHelp('La structure qui publie le site (association, collectivité…) ou votre nom si vous le publiez à titre personnel.'),
            TextareaField::new('legalAdresse', 'Adresse postale')
                ->onlyOnForms()
                ->setRequired(false)
                ->setNumOfRows(2),
            EmailField::new('legalEmail', 'E-mail de contact')
                ->onlyOnForms()
                ->setRequired(false)
                ->setHelp('Pour les questions sur le site et pour exercer les droits sur les données personnelles (accès, rectification, suppression).'),
            TextField::new('legalDirecteurPublication', 'Directeur·rice de la publication')
                ->onlyOnForms()
                ->setRequired(false)
                ->setHelp('La personne responsable des contenus publiés (souvent le ou la responsable de la structure).'),
            TextField::new('legalIdentifiant', 'SIRET ou RNA (facultatif)')
                ->onlyOnForms()
                ->setRequired(false)
                ->setHelp('Numéro SIRET de la structure, ou numéro RNA pour une association.'),
            DateField::new('legalMiseAJour', 'Date de mise à jour des textes légaux')
                ->onlyOnForms()
                ->setRequired(false)
                ->setFormTypeOption('input', 'datetime_immutable')
                ->setHelp('À changer à chaque modification des mentions légales, de la politique de confidentialité ou des conditions d\'utilisation.'),

            FormField::addFieldset('Paramètres avancés'),
            CodeEditorField::new('settingsJson', 'Paramètres (JSON)')
                ->setLanguage('js')
                ->setNumOfRows(25)
                ->setHelp('Configuration globale du site au format JSON'),

            DateTimeField::new('updatedAt', 'Dernière modification')
                ->hideOnForm(),
        ];
    }
}
