<?php

namespace App\Entity;

use App\Repository\LearningPathRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LearningPathRepository::class)]
class LearningPath
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $targetUser = null;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Category $category = null;

    #[ORM\Column(length: 20)]
    private ?string $difficultyLevel = null; // 'beginner', 'intermediate', 'advanced'

    #[ORM\Column]
    private ?int $estimatedDuration = null; // en minutes

    #[ORM\Column(type: Types::JSON)]
    private array $courseSequence = []; // array d'IDs de PageContent dans l'ordre

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $prerequisites = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $learningObjectives = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null; // 'draft', 'active', 'completed', 'archived'

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $createdBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(targetEntity: UserLearningAnalytics::class, mappedBy: 'learningPath')]
    private Collection $userAnalytics;

    public function __construct()
    {
        $this->userAnalytics = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->status = 'draft';
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getTargetUser(): ?User
    {
        return $this->targetUser;
    }

    public function setTargetUser(?User $targetUser): static
    {
        $this->targetUser = $targetUser;
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

    public function getDifficultyLevel(): ?string
    {
        return $this->difficultyLevel;
    }

    public function setDifficultyLevel(string $difficultyLevel): static
    {
        $this->difficultyLevel = $difficultyLevel;
        return $this;
    }

    public function getEstimatedDuration(): ?int
    {
        return $this->estimatedDuration;
    }

    public function setEstimatedDuration(int $estimatedDuration): static
    {
        $this->estimatedDuration = $estimatedDuration;
        return $this;
    }

    public function getCourseSequence(): array
    {
        return $this->courseSequence;
    }

    public function setCourseSequence(array $courseSequence): static
    {
        $this->courseSequence = $courseSequence;
        return $this;
    }

    public function getPrerequisites(): ?array
    {
        return $this->prerequisites;
    }

    public function setPrerequisites(?array $prerequisites): static
    {
        $this->prerequisites = $prerequisites;
        return $this;
    }

    public function getLearningObjectives(): ?array
    {
        return $this->learningObjectives;
    }

    public function setLearningObjectives(?array $learningObjectives): static
    {
        $this->learningObjectives = $learningObjectives;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;
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

    /**
     * @return Collection<int, UserLearningAnalytics>
     */
    public function getUserAnalytics(): Collection
    {
        return $this->userAnalytics;
    }

    public function addUserAnalytic(UserLearningAnalytics $userAnalytic): static
    {
        if (!$this->userAnalytics->contains($userAnalytic)) {
            $this->userAnalytics->add($userAnalytic);
            $userAnalytic->setLearningPath($this);
        }

        return $this;
    }

    public function removeUserAnalytic(UserLearningAnalytics $userAnalytic): static
    {
        if ($this->userAnalytics->removeElement($userAnalytic)) {
            if ($userAnalytic->getLearningPath() === $this) {
                $userAnalytic->setLearningPath(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? 'Parcours #' . $this->id;
    }
}