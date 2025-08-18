<?php

namespace App\Entity;

use App\Repository\QCMSessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: QCMSessionRepository::class)]
class QCMSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['qcm:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['qcm:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?int $userId = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?int $categoryId = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?string $difficulty = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?int $questionsCount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?int $score = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?int $totalQuestions = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?\DateTimeInterface $completedAt = null;

    #[ORM\Column(length: 20)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?string $status = 'pending';

    /**
     * @var Collection<int, QCMQuestion>
     */
    #[ORM\OneToMany(targetEntity: QCMQuestion::class, mappedBy: 'session', cascade: ['persist', 'remove'])]
    #[Groups(['qcm:read'])]
    private Collection $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->status = 'pending';
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function setCategoryId(?int $categoryId): static
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(?string $difficulty): static
    {
        $this->difficulty = $difficulty;
        return $this;
    }

    public function getQuestionsCount(): ?int
    {
        return $this->questionsCount;
    }

    public function setQuestionsCount(?int $questionsCount): static
    {
        $this->questionsCount = $questionsCount;
        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): static
    {
        $this->score = $score;
        return $this;
    }

    public function getTotalQuestions(): ?int
    {
        return $this->totalQuestions;
    }

    public function setTotalQuestions(?int $totalQuestions): static
    {
        $this->totalQuestions = $totalQuestions;
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): static
    {
        $this->completedAt = $completedAt;
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

    /**
     * @return Collection<int, QCMQuestion>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(QCMQuestion $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setSession($this);
        }
        return $this;
    }

    public function removeQuestion(QCMQuestion $question): static
    {
        if ($this->questions->removeElement($question)) {
            if ($question->getSession() === $this) {
                $question->setSession(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? 'QCM Session';
    }
}