<?php

namespace App\Entity;

use App\Repository\QCMAnswerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: QCMAnswerRepository::class)]
class QCMAnswer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['qcm:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?string $selectedAnswer = null;

    #[ORM\Column]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?bool $isCorrect = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['qcm:read'])]
    private ?\DateTimeInterface $answeredAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?int $timeSpent = null;

    #[ORM\ManyToOne(inversedBy: 'userAnswers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?QCMQuestion $question = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?int $userId = null;

    public function __construct()
    {
        $this->answeredAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSelectedAnswer(): ?string
    {
        return $this->selectedAnswer;
    }

    public function setSelectedAnswer(string $selectedAnswer): static
    {
        $this->selectedAnswer = $selectedAnswer;
        return $this;
    }

    public function isCorrect(): ?bool
    {
        return $this->isCorrect;
    }

    public function setCorrect(bool $isCorrect): static
    {
        $this->isCorrect = $isCorrect;
        return $this;
    }

    public function getAnsweredAt(): ?\DateTimeInterface
    {
        return $this->answeredAt;
    }

    public function setAnsweredAt(\DateTimeInterface $answeredAt): static
    {
        $this->answeredAt = $answeredAt;
        return $this;
    }

    public function getTimeSpent(): ?int
    {
        return $this->timeSpent;
    }

    public function setTimeSpent(?int $timeSpent): static
    {
        $this->timeSpent = $timeSpent;
        return $this;
    }

    public function getQuestion(): ?QCMQuestion
    {
        return $this->question;
    }

    public function setQuestion(?QCMQuestion $question): static
    {
        $this->question = $question;
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

    public function __toString(): string
    {
        return $this->selectedAnswer ?? 'Answer';
    }
}