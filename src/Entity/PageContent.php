<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['page_content:read']],
    denormalizationContext: ['groups' => ['page_content:write']],
    paginationEnabled: false // pour forcer api plateforme à ne pas paginer et de proposer que 30 éléments par page

)]
#[ApiFilter(SearchFilter::class, properties: ['page.slug' => 'exact'])]
class PageContent
{
    use AccesReserve;

    public const STATUS_DRAFT = 'brouillon';
    public const STATUS_IN_REVIEW = 'en_revue';
    public const STATUS_PUBLISHED = 'publie';
    public const STATUS_TO_REVISE = 'a_reviser';
    public const STATUSES = [self::STATUS_DRAFT, self::STATUS_IN_REVIEW, self::STATUS_PUBLISHED, self::STATUS_TO_REVISE];

    // Libellés du back-office
    public const STATUS_LABELS = [
        'Brouillon' => self::STATUS_DRAFT,
        'En revue' => self::STATUS_IN_REVIEW,
        'Publié' => self::STATUS_PUBLISHED,
        'À réviser' => self::STATUS_TO_REVISE,
    ];

    public const REVIEW_FAVORABLE = 'favorable';
    public const REVIEW_RESERVES = 'reserves';
    public const REVIEW_UNFAVORABLE = 'defavorable';
    public const REVIEW_OPINIONS = [self::REVIEW_FAVORABLE, self::REVIEW_RESERVES, self::REVIEW_UNFAVORABLE];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['page_content:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?string $title = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'pageContents')]
    #[ApiProperty(readableLink: true)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?Page $page = null;

    #[ORM\ManyToOne(inversedBy: 'pageContents')]
    #[ApiProperty(readableLink: true)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'pageContents')]
    #[ApiProperty(readableLink: true)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?Menus $menu = null;

    #[ORM\Column(nullable: true, type: Types::TEXT)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?string $code = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?string $duration = null;

    #[ORM\OneToMany(
        mappedBy: 'pageContent',
        targetEntity: PageBlock::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    #[ApiProperty(readableLink: true)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private Collection $pageBlocks;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['default' => true])]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?bool $visible = true;

    // Cycle éditorial des connecteurs MCP ; `visible` suit le statut (vrai seulement si « publié »).
    // Un cours créé dans le back-office est publié, comme avant ; le connecteur crée des brouillons.
    #[ORM\Column(length: 20, options: ['default' => self::STATUS_PUBLISHED])]
    #[Assert\Choice(choices: self::STATUSES)]
    #[Groups(['page_content:read'])]
    private string $status = self::STATUS_PUBLISHED;

    // Documentation officielle de référence (Symfony, Vue…)
    #[ORM\Column(name: 'source_url', length: 500, nullable: true)]
    #[Assert\Url]
    #[Assert\Length(max: 500)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?string $sourceUrl = null;

    // Qui a préparé le cours : « connecteur:<nom> » pour l'assistant de rédaction (suivi interne, non publié)
    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $author = null;

    // Dernière vérification du contenu auprès de la documentation officielle
    #[ORM\Column(name: 'verified_at', type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    private ?\DateTimeImmutable $verifiedAt = null;

    // Relecture par un autre connecteur (ou une personne) : avis, résumé et constats rattachés aux règles.
    // Effacée à chaque modification du contenu ; exigée pour publier par le connecteur.
    #[ORM\Column(name: 'review_report', type: Types::JSON, nullable: true)]
    private ?array $reviewReport = null;

    #[ORM\Column(name: 'reviewed_by', length: 100, nullable: true)]
    private ?string $reviewedBy = null;

    #[ORM\Column(name: 'reviewed_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $reviewedAt = null;

    // Rang du cours dans son module (1, 2, 3…) ; sans rang, le cours passe après les autres, par ancienneté
    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?int $ordre = null;

    #[ORM\ManyToOne(inversedBy: 'pageContents')]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?NiveauCours $niveauCours = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'acces_cours')]
    #[ORM\JoinColumn(name: 'page_content_id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'user_id', onDelete: 'CASCADE')]
    private Collection $destinataires;

    public function __construct()
    {
        $this->pageBlocks = new ArrayCollection();
        $this->destinataires = new ArrayCollection();
        $this->visible = true;
    }

    public function getId(): ?int { return $this->id; }

    public function getTitle(): ?string { return $this->title; }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getType(): ?string { return $this->type; }

    public function setType(?string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getContent(): ?string { return $this->content; }

    public function setContent(?string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getPage(): ?Page { return $this->page; }

    public function setPage(?Page $page): static
    {
        $this->page = $page;
        return $this;
    }

    public function getCategory(): ?Category { return $this->category; }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getMenu(): ?Menus { return $this->menu; }

    public function setMenu(?Menus $menu): static
    {
        $this->menu = $menu;
        return $this;
    }

    public function getDuration(): ?string { return $this->duration; }

    public function setDuration(?string $duration): static
    {
        $this->duration = $duration;
        return $this;
    }

    public function getCode(): ?string { return $this->code; }

    public function setCode(?string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? 'PageContent';
    }

    /**
     * @return Collection<int, PageBlock>
     */
    public function getPageBlocks(): Collection
    {
        return $this->pageBlocks;
    }

    public function addPageBlock(PageBlock $pageBlock): static
    {
        if (!$this->pageBlocks->contains($pageBlock)) {
            $this->pageBlocks->add($pageBlock);
            $pageBlock->setPageContent($this);
        }
        return $this;
    }

    public function removePageBlock(PageBlock $pageBlock): static
    {
        if ($this->pageBlocks->removeElement($pageBlock)) {
            if ($pageBlock->getPageContent() === $this) {
                $pageBlock->setPageContent(null);
            }
        }
        return $this;
    }

    public function isVisible(): ?bool
    {
        return $this->visible ?? true;
    }

    // Bascule du back-office : visible publie, masqué repasse en brouillon
    public function setVisible(?bool $visible): static
    {
        $this->visible = $visible;
        if ($visible === false && $this->status === self::STATUS_PUBLISHED) {
            $this->status = self::STATUS_DRAFT;
        } elseif ($visible !== false) {
            $this->status = self::STATUS_PUBLISHED;
        }
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        $this->visible = $status === self::STATUS_PUBLISHED;
        return $this;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    // Publié et ouvert à tous : ni le cours ni son parcours ne sont réservés à certains comptes
    public function isPublic(): bool
    {
        return $this->isPublished() && !$this->isReserve() && !($this->category?->isReserve() ?? false);
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): static
    {
        $this->author = $author;
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

    // Relecture mise en forme pour le back-office
    public function getReviewSummary(): string
    {
        if ($this->reviewReport === null) {
            return 'Aucune relecture.';
        }
        $lines = [sprintf('Avis : %s — par %s le %s', $this->reviewReport['avis'], $this->reviewedBy ?? '?', $this->reviewedAt?->format('d/m/Y H:i') ?? '?'), $this->reviewReport['resume']];
        foreach ($this->reviewReport['points'] as $point) {
            $lines[] = sprintf('• %s%s%s', $point['regle'] !== null ? '['.$point['regle'].'] ' : '', $point['constat'], $point['suggestion'] !== null ? ' → '.$point['suggestion'] : '');
        }
        return implode("\n", $lines);
    }

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }

    public function setSourceUrl(?string $sourceUrl): static
    {
        $this->sourceUrl = $sourceUrl;
        return $this;
    }

    public function getVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->verifiedAt;
    }

    public function setVerifiedAt(?\DateTimeImmutable $verifiedAt): static
    {
        $this->verifiedAt = $verifiedAt;
        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(?int $ordre): static
    {
        $this->ordre = $ordre;
        return $this;
    }

    public function getNiveauCours(): ?NiveauCours
    {
        return $this->niveauCours;
    }

    public function setNiveauCours(?NiveauCours $niveauCours): static
    {
        $this->niveauCours = $niveauCours;

        return $this;
    }
}
