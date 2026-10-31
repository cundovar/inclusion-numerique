<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Domain\PageContent\Repository\PageContentRepositoryInterface;
use App\Entity\CourseMedia;
use App\Service\CaptureFileManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Validator\Constraints\Image;

/**
 * Captures d'écran et illustrations des cours. Un fichier remplacé met à jour tous les cours
 * qui contiennent son marqueur [capture:ID].
 */
class CourseMediaCrudController extends AbstractCrudController
{
    private const KIND_LABELS = [
        "Capture d'une page publique" => CourseMedia::KIND_CAPTURE,
        'Illustration fictive (écran personnel)' => CourseMedia::KIND_ILLUSTRATION,
    ];

    public function __construct(
        private CaptureFileManager $files,
        private PageContentRepositoryInterface $courses,
    ) {}

    public static function getEntityFqcn(): string
    {
        return CourseMedia::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Capture')
            ->setEntityLabelInPlural('Captures')
            ->setDefaultSort(['id' => 'DESC'])
            ->setHelp('index', 'Pour afficher une capture dans un cours, copiez son marqueur (par exemple [capture:12]) sur une ligne seule du contenu.');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('service', 'Service'))
            ->add(ChoiceFilter::new('kind', 'Type')->setChoices(self::KIND_LABELS))
            ->add(DateTimeFilter::new('nextReviewAt', 'À revérifier le'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('marker', 'Marqueur à insérer')->hideOnForm();
        yield ImageField::new('filename', 'Image')
            ->setBasePath(CourseMedia::PUBLIC_DIR)
            ->setUploadDir(CaptureFileManager::UPLOAD_DIR)
            // Nom tiré du contenu : un remplacement change l'adresse, donc aucun cache ne garde l'ancienne image
            ->setUploadedFileNamePattern('[contenthash].[extension]')
            ->setFileConstraints(new Image(
                maxSize: CaptureFileManager::MAX_SIZE,
                mimeTypes: CaptureFileManager::MIME_TYPES,
                mimeTypesMessage: 'Formats acceptés : PNG, JPEG ou WebP.',
            ))
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->setHelp('PNG, JPEG ou WebP, 5 Mo au plus. Déposer un nouveau fichier remplace l’image dans tous les cours.');

        yield FormField::addFieldset('Description');
        yield ChoiceField::new('kind', 'Type')
            ->setChoices(self::KIND_LABELS)
            ->setHelp("Les écrans d'un compte personnel ne sont jamais capturés : utilisez une illustration fictive, signalée comme telle sur le site.");
        yield TextField::new('service', 'Service')->setHelp('Par exemple : ChatGPT, Claude, Le Chat, Gemini.');
        yield TextareaField::new('caption', 'Légende')->setHelp('Ce que la personne doit repérer, en une ou deux phrases.');
        yield TextareaField::new('altText', 'Texte alternatif')
            ->setHelp("Décrit l'image pour les personnes qui ne la voient pas : où se trouve l'élément à repérer et ce qu'il indique.")
            ->hideOnIndex();

        yield FormField::addFieldset('Source et suivi');
        yield UrlField::new('sourceUrl', 'Page officielle capturée')->hideOnIndex();
        yield DateField::new('capturedAt', 'Capturée le');
        yield TextField::new('author', 'Auteur de la capture')->hideOnIndex();
        yield DateField::new('nextReviewAt', 'À revérifier le')
            ->setHelp('Date à laquelle comparer de nouveau la capture avec le site officiel.');

        if ($pageName === Crud::PAGE_DETAIL) {
            // Calculé à partir des marqueurs présents dans les cours. Adossé au marqueur, une chaîne toujours lisible :
            // une propriété absente s'affiche « Inaccessible », et un TextField refuse l'identifiant entier.
            yield TextField::new('marker', 'Utilisée dans')
                ->formatValue(fn ($value, CourseMedia $media) => $this->describeUsage($media));
        }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->files->describe($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->files->describe($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $filename = $entityInstance->getFilename();
        parent::deleteEntity($entityManager, $entityInstance);
        $this->files->remove($filename);
    }

    private function describeUsage(CourseMedia $media): string
    {
        $titles = array_map(
            // EasyAdmin affiche le texte calculé comme du HTML : échapper les titres
            static fn ($course) => htmlspecialchars((string) $course->getTitle(), \ENT_QUOTES),
            $media->getId() === null ? [] : $this->courses->findUsingCapture($media->getId()),
        );

        return $titles === [] ? 'Aucun cours' : implode(' · ', $titles);
    }
}
