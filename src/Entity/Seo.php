<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\SeoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SeoRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
    paginationEnabled: false
)]
class Seo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    private ?string $metaDescription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $metaKeywords = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $ogTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 300)]
    private ?string $ogDescription = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url]
    private ?string $ogImage = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url]
    private ?string $canonicalUrl = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $noIndex = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $noFollow = false;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $page = null;

    #[ORM\OneToOne(targetEntity: Category::class, inversedBy: 'seo')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Category $category = null;

    #[ORM\OneToOne(targetEntity: Page::class, mappedBy: 'seo')]
    private ?Page $pageEntity = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $structuredData = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(string $metaDescription): static
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?string $metaKeywords): static
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    public function getOgTitle(): ?string
    {
        return $this->ogTitle;
    }

    public function setOgTitle(?string $ogTitle): static
    {
        $this->ogTitle = $ogTitle;

        return $this;
    }

    public function getOgDescription(): ?string
    {
        return $this->ogDescription;
    }

    public function setOgDescription(?string $ogDescription): static
    {
        $this->ogDescription = $ogDescription;

        return $this;
    }

    public function getOgImage(): ?string
    {
        return $this->ogImage;
    }

    public function setOgImage(?string $ogImage): static
    {
        $this->ogImage = $ogImage;

        return $this;
    }

    public function getCanonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    public function setCanonicalUrl(?string $canonicalUrl): static
    {
        $this->canonicalUrl = $canonicalUrl;

        return $this;
    }

    public function isNoIndex(): ?bool
    {
        return $this->noIndex;
    }

    public function setNoIndex(bool $noIndex): static
    {
        $this->noIndex = $noIndex;

        return $this;
    }

    public function isNoFollow(): ?bool
    {
        return $this->noFollow;
    }

    public function setNoFollow(bool $noFollow): static
    {
        $this->noFollow = $noFollow;

        return $this;
    }

    public function getPage(): ?string
    {
        return $this->page;
    }

    public function setPage(?string $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPageEntity(): ?Page
    {
        return $this->pageEntity;
    }

    public function setPageEntity(?Page $pageEntity): static
    {
        $this->pageEntity = $pageEntity;
        return $this;
    }

    public function getStructuredData(): ?string
    {
        return $this->structuredData;
    }

    public function setStructuredData(?string $structuredData): static
    {
        $this->structuredData = $structuredData;
        return $this;
    }

    public function __toString(): string
    {
        if ($this->category) {
            return $this->category->getName() . ' - ' . $this->title;
        }
        return ($this->page ?? 'SEO') . ' - ' . $this->title;
    }
}
