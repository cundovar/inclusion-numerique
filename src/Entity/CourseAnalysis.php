<?php

namespace App\Entity;

use App\Repository\CourseAnalysisRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CourseAnalysisRepository::class)]
class CourseAnalysis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PageContent::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?PageContent $pageContent = null;

    #[ORM\Column(type: Types::JSON)]
    private array $analysis = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column]
    private ?float $qualityScore = null;

    #[ORM\Column(length: 20)]
    private ?string $difficultyLevel = null;

    #[ORM\Column]
    private ?int $estimatedReadingTime = null;

    #[ORM\Column(type: Types::JSON)]
    private array $suggestions = [];

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $analyzedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->analyzedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPageContent(): ?PageContent
    {
        return $this->pageContent;
    }

    public function setPageContent(?PageContent $pageContent): static
    {
        $this->pageContent = $pageContent;
        return $this;
    }

    public function getAnalysis(): array
    {
        return $this->analysis;
    }

    public function setAnalysis(array $analysis): static
    {
        $this->analysis = $analysis;
        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;
        return $this;
    }

    public function getQualityScore(): ?float
    {
        return $this->qualityScore;
    }

    public function setQualityScore(float $qualityScore): static
    {
        $this->qualityScore = $qualityScore;
        return $this;
    }

    public function getDifficultyLevel(): ?string
    {
        return $this->difficultyLevel;
    }

    public function setDifficultyLevel(string $difficultyLevel): static
    {
        $this->difficultyLevel = $difficultyLevel;
        return $this;
    }

    public function getEstimatedReadingTime(): ?int
    {
        return $this->estimatedReadingTime;
    }

    public function setEstimatedReadingTime(int $estimatedReadingTime): static
    {
        $this->estimatedReadingTime = $estimatedReadingTime;
        return $this;
    }

    public function getSuggestions(): array
    {
        return $this->suggestions;
    }

    public function setSuggestions(array $suggestions): static
    {
        $this->suggestions = $suggestions;
        return $this;
    }

    public function getAnalyzedAt(): ?\DateTimeInterface
    {
        return $this->analyzedAt;
    }

    public function setAnalyzedAt(\DateTimeInterface $analyzedAt): static
    {
        $this->analyzedAt = $analyzedAt;
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

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function __toString(): string
    {
        return $this->pageContent?->getTitle() . ' - Analyse du ' . $this->analyzedAt?->format('d/m/Y');
    }
}