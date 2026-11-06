<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\QcmDraft;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** Read-only back-office listing for AI-authored QCM drafts. */
final class QcmDraftCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return QcmDraft::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Brouillon QCM')
            ->setEntityLabelInPlural('Brouillons du connecteur')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::NEW, Action::EDIT, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('question', 'Question');
        yield AssociationField::new('theme', 'Thème');
        yield AssociationField::new('level', 'Niveau');
        yield TextField::new('status', 'Statut');
        yield TextField::new('author', 'Auteur')->hideOnIndex();
        yield DateTimeField::new('createdAt', 'Créé le')->hideOnIndex();
        yield TextareaField::new('answersForAdmin', 'Réponses')->onlyOnDetail();
        yield TextareaField::new('explanation', 'Explication')->onlyOnDetail();
        yield TextareaField::new('reviewSummaryForAdmin', 'Relecture')->onlyOnDetail();
        yield AssociationField::new('publishedQcm', 'QCM publié')->onlyOnDetail();
    }
}
