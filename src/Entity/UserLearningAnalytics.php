<?php

namespace App\Entity;

use App\Repository\UserLearningAnalyticsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserLearningAnalyticsRepository::class)]
class UserLearningAnalytics
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: PageContent::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?PageContent $pageContent = null;

    #[ORM\ManyToOne(targetEntity: LearningPath::class, inversedBy: 'userAnalytics')]
    #[ORM\JoinColumn(nullable: true)]
    private ?LearningPath $learningPath = null;

    #[ORM\Column(length: 50)]
    private ?string $eventType = null; // 'view', 'complete', 'test_passed', 'test_failed', 'bookmark'

    #[ORM\Column]
    private ?int $timeSpent = null; // en secondes

    #[ORM\Column(nullable: true)]
    private ?float $comprehensionScore = null; // 0-100

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $interactionData = null; // données spécifiques selon le type d'événement

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $difficultyConcepts = null; // concepts où l'utilisateur a des difficultés

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $preferredLearningStyle = null; // visual, auditory, kinesthetic, etc.

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $eventDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->eventDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
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

    public function getLearningPath(): ?LearningPath
    {
        return $this->learningPath;
    }

    public function setLearningPath(?LearningPath $learningPath): static
    {
        $this->learningPath = $learningPath;
        return $this;
    }

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): static
    {
        $this->eventType = $eventType;
        return $this;
    }

    public function getTimeSpent(): ?int
    {
        return $this->timeSpent;
    }

    public function setTimeSpent(int $timeSpent): static
    {
        $this->timeSpent = $timeSpent;
        return $this;
    }

    public function getComprehensionScore(): ?float
    {
        return $this->comprehensionScore;
    }

    public function setComprehensionScore(?float $comprehensionScore): static
    {
        $this->comprehensionScore = $comprehensionScore;
        return $this;
    }

    public function getInteractionData(): ?array
    {
        return $this->interactionData;
    }

    public function setInteractionData(?array $interactionData): static
    {
        $this->interactionData = $interactionData;
        return $this;
    }

    public function getDifficultyConcepts(): ?array
    {
        return $this->difficultyConcepts;
    }

    public function setDifficultyConcepts(?array $difficultyConcepts): static
    {
        $this->difficultyConcepts = $difficultyConcepts;
        return $this;
    }

    public function getPreferredLearningStyle(): ?array
    {
        return $this->preferredLearningStyle;
    }

    public function setPreferredLearningStyle(?array $preferredLearningStyle): static
    {
        $this->preferredLearningStyle = $preferredLearningStyle;
        return $this;
    }

    public function getEventDate(): ?\DateTimeInterface
    {
        return $this->eventDate;
    }

    public function setEventDate(\DateTimeInterface $eventDate): static
    {
        $this->eventDate = $eventDate;
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

    public function __toString(): string
    {
        return $this->user?->getUsername() . ' - ' . $this->eventType . ' (' . $this->eventDate?->format('d/m/Y H:i') . ')';
    }
}