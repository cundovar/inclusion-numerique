<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Seo;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class SeoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Seo::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('SEO')
            ->setEntityLabelInPlural('SEO')
            ->setPageTitle('index', 'Gestion du SEO')
            ->setPageTitle('new', 'Créer un nouveau SEO')
            ->setPageTitle('edit', 'Modifier le SEO')
            ->setPageTitle('detail', 'Détails du SEO')
            ->setDefaultSort(['page' => 'ASC'])
            ->setPaginatorPageSize(30);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();

        yield AssociationField::new('category', 'Catégorie')
            ->setHelp('Sélectionnez une catégorie pour lier le SEO (optionnel si vous utilisez "Page")')
            ->autocomplete();

        yield TextField::new('page', 'Identifiant de la page')
            ->setHelp('Ex: home, about, contact (laissez vide si vous avez sélectionné une catégorie)')
            ->setRequired(false);

        yield TextField::new('title', 'Titre SEO')
            ->setHelp('Titre affiché dans les résultats de recherche (max 60 caractères)')
            ->setRequired(true);

        yield TextareaField::new('metaDescription', 'Meta Description')
            ->setHelp('Description affichée dans les résultats de recherche (max 160 caractères)')
            ->setRequired(true);

        yield TextareaField::new('metaKeywords', 'Mots-clés')
            ->setHelp('Mots-clés séparés par des virgules')
            ->hideOnIndex();

        yield TextField::new('ogTitle', 'Open Graph Title')
            ->setHelp('Titre pour les partages sur les réseaux sociaux')
            ->hideOnIndex();

        yield TextareaField::new('ogDescription', 'Open Graph Description')
            ->setHelp('Description pour les partages sur les réseaux sociaux (max 300 caractères)')
            ->hideOnIndex();

        yield UrlField::new('ogImage', 'Open Graph Image')
            ->setHelp('URL de l\'image pour les partages (recommandé: 1200x630px)')
            ->hideOnIndex();

        yield UrlField::new('canonicalUrl', 'URL Canonique')
            ->setHelp('URL canonique de la page')
            ->hideOnIndex();

        yield BooleanField::new('noIndex', 'No Index')
            ->setHelp('Empêcher l\'indexation par les moteurs de recherche')
            ->hideOnIndex();

        yield BooleanField::new('noFollow', 'No Follow')
            ->setHelp('Empêcher le suivi des liens par les moteurs de recherche')
            ->hideOnIndex();

        yield DateTimeField::new('createdAt', 'Créé le')
            ->hideOnForm();

        yield DateTimeField::new('updatedAt', 'Modifié le')
            ->hideOnForm();
    }
}
