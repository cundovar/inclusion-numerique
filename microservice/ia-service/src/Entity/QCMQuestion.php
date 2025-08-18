<?php

namespace App\Entity;

use App\Repository\QCMQuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: QCMQuestionRepository::class)]
class QCMQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['qcm:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?string $question = null;

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private array $options = [];

    #[ORM\Column(length: 10)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?string $correctAnswer = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?string $explanation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?int $pageContentId = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?string $difficulty = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?string $topic = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?QCMSession $session = null;

    /**
     * @var Collection<int, QCMAnswer>
     */
    #[ORM\OneToMany(targetEntity: QCMAnswer::class, mappedBy: 'question', cascade: ['persist', 'remove'])]
    #[Groups(['qcm:read'])]
    private Collection $userAnswers;

    public function __construct()
    {
        $this->userAnswers = new ArrayCollection();
        $this->options = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function getCorrectAnswer(): ?string
    {
        return $this->correctAnswer;
    }

    public function setCorrectAnswer(string $correctAnswer): static
    {
        $this->correctAnswer = $correctAnswer;
        return $this;
    }

    public function getExplanation(): ?string
    {
        return $this->explanation;
    }

    public function setExplanation(?string $explanation): static
    {
        $this->explanation = $explanation;
        return $this;
    }

    public function getPageContentId(): ?int
    {
        return $this->pageContentId;
    }

    public function setPageContentId(?int $pageContentId): static
    {
        $this->pageContentId = $pageContentId;
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

    public function getTopic(): ?string
    {
        return $this->topic;
    }

    public function setTopic(?string $topic): static
    {
        $this->topic = $topic;
        return $this;
    }

    public function getSession(): ?QCMSession
    {
        return $this->session;
    }

    public function setSession(?QCMSession $session): static
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return Collection<int, QCMAnswer>
     */
    public function getUserAnswers(): Collection
    {
        return $this->userAnswers;
    }

    public function addUserAnswer(QCMAnswer $userAnswer): static
    {
        if (!$this->userAnswers->contains($userAnswer)) {
            $this->userAnswers->add($userAnswer);
            $userAnswer->setQuestion($this);
        }
        return $this;
    }

    public function removeUserAnswer(QCMAnswer $userAnswer): static
    {
        if ($this->userAnswers->removeElement($userAnswer)) {
            if ($userAnswer->getQuestion() === $this) {
                $userAnswer->setQuestion(null);
            }
        }
        return $this;
    }

    public function isAnswerCorrect(string $answer): bool
    {
        return $this->correctAnswer === $answer;
    }

    public function __toString(): string
    {
        return substr($this->question ?? 'Question', 0, 50) . '...';
    }
}