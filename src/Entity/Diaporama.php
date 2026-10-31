<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Diaporama d'un cours ou d'un chapitre (docs/PLAN_DIAPORAMAS.md) : une synthèse structurée, rédigée par
 * l'assistant de rédaction, relue par un autre connecteur, puis publiée. Le site lui applique un thème fixe
 * et construit le .pptx et la version PDF dans le navigateur.
 *
 * Il vise exactement un cours ou un chapitre. Les diapositives sont gardées en JSON : liste d'objets
 * {modele, titre, points, notes…} décrits dans front/src/diaporamas/modeles.js.
 *
 * API publique en lecture seule : GET /api/diaporamas?cours=ID ou ?chapitre[]=ID. PublishedContentExtension
 * ne sert que les diaporamas publiés dont la cible est publiée et accessible.
 */
#[ORM\Entity]
#[ORM\Table(name: 'diaporama')]
#[ApiResource(
    operations: [new GetCollection(), new Get()],
    normalizationContext: ['groups' => ['diaporama:read']],
    paginationItemsPerPage: 50,
)]
#[ApiFilter(SearchFilter::class, properties: ['cours' => 'exact', 'chapitre' => 'exact'])]
class Diaporama
{
    public const STATUS_DRAFT = 'brouillon';
    public const STATUS_TO_FIX = 'a_corriger';
    public const STATUS_PUBLISHED = 'publie';

    public const STATUS_LABELS = [
        'Brouillon' => self::STATUS_DRAFT,
        'À corriger' => self::STATUS_TO_FIX,
        'Publié' => self::STATUS_PUBLISHED,
    ];

    public const TARGET_COURSE = 'cours';
    public const TARGET_CHAPTER = 'chapitre';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['diaporama:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'cours_id', onDelete: 'CASCADE')]
    private ?PageContent $cours = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'chapitre_id', onDelete: 'CASCADE')]
    private ?Menus $chapitre = null;

    #[ORM\Column(length: 255)]
    #[Groups(['diaporama:read'])]
    private string $titre = '';

    #[ORM\Column(name: 'sous_titre', length: 255)]
    #[Groups(['diaporama:read'])]
    private string $sousTitre = '';

    // JSON fourni par l'agent : sa forme est vérifiée par DiaporamaChecker
    /** @var list<mixed> */
    #[ORM\Column(type: Types::JSON)]
    #[Groups(['diaporama:read'])]
    private array $diapositives = [];

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_DRAFT;

    #[ORM\Column(length: 100)]
    private string $author = '';

    // Empreinte du texte du cours ou du chapitre au moment de la synthèse : signale un diaporama à mettre à jour
    #[ORM\Column(name: 'empreinte_source', length: 64, nullable: true)]
    private ?string $empreinteSource = null;

    /** @var array{avis: string, resume: string, points: list<array{regle: ?string, constat: string, suggestion: ?string}>}|null */
    #[ORM\Column(name: 'review_report', type: Types::JSON, nullable: true)]
    private ?array $reviewReport = null;

    #[ORM\Column(name: 'reviewed_by', length: 100, nullable: true)]
    private ?string $reviewedBy = null;

    #[ORM\Column(name: 'reviewed_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $reviewedAt = null;

    #[ORM\Column(name: 'published_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $author)
    {
        $this->author = $author;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCours(): ?PageContent
    {
        return $this->cours;
    }

    public function getChapitre(): ?Menus
    {
        return $this->chapitre;
    }

    /**
     * Un diaporama vise un cours ou un chapitre, jamais les deux.
     */
    public function setCible(?PageContent $cours, ?Menus $chapitre): static
    {
        $this->cours = $cours;
        $this->chapitre = $chapitre;

        return $this;
    }

    /**
     * @return self::TARGET_COURSE|self::TARGET_CHAPTER|null null si la cible est absente ou double
     */
    public function getTypeCible(): ?string
    {
        if (($this->cours === null) === ($this->chapitre === null)) {
            return null;
        }

        return $this->cours !== null ? self::TARGET_COURSE : self::TARGET_CHAPTER;
    }

    /**
     * Libellé de la cible pour le back-office.
     */
    public function getLibelleCible(): string
    {
        return $this->cours !== null
            ? 'Cours : '.$this->cours->getTitle()
            : 'Chapitre : '.$this->chapitre?->getLabel();
    }

    #[Groups(['diaporama:read'])]
    #[SerializedName('cours')]
    public function getCoursId(): ?int
    {
        return $this->cours?->getId();
    }

    #[Groups(['diaporama:read'])]
    #[SerializedName('chapitre')]
    public function getChapitreId(): ?int
    {
        return $this->chapitre?->getId();
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getSousTitre(): string
    {
        return $this->sousTitre;
    }

    public function setSousTitre(string $sousTitre): static
    {
        $this->sousTitre = $sousTitre;

        return $this;
    }

    /**
     * @return list<mixed>
     */
    public function getDiapositives(): array
    {
        return $this->diapositives;
    }

    /**
     * @param list<mixed> $diapositives
     */
    public function setDiapositives(array $diapositives): static
    {
        $this->diapositives = $diapositives;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getEmpreinteSource(): ?string
    {
        return $this->empreinteSource;
    }

    public function setEmpreinteSource(?string $empreinteSource): static
    {
        $this->empreinteSource = $empreinteSource;

        return $this;
    }

    /**
     * @param array{avis: string, resume: string, points: list<array{regle: ?string, constat: string, suggestion: ?string}>} $report
     */
    public function recordReview(array $report, string $reviewer): static
    {
        $this->reviewReport = $report;
        $this->reviewedBy = $reviewer;
        $this->reviewedAt = new \DateTimeImmutable();

        return $this;
    }

    public function clearReview(): static
    {
        $this->reviewReport = null;
        $this->reviewedBy = null;
        $this->reviewedAt = null;

        return $this;
    }

    /**
     * @return array{avis: string, resume: string, points: list<array{regle: ?string, constat: string, suggestion: ?string}>}|null
     */
    public function getReviewReport(): ?array
    {
        return $this->reviewReport;
    }

    public function getReviewedBy(): ?string
    {
        return $this->reviewedBy;
    }

    public function getReviewedAt(): ?\DateTimeImmutable
    {
        return $this->reviewedAt;
    }

    public function markPublished(): static
    {
        $this->status = self::STATUS_PUBLISHED;
        $this->publishedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): static
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
