<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ChoicesQCMRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ChoicesQCMRepository::class)]
#[ApiResource]
class ChoicesQCM
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['qcm:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['qcm:read'])]
    private ?string $question = null;

    #[ORM\Column]
    #[Groups(['qcm:read'])]
    private ?bool $isCorrect = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['qcm:read'])]
    private ?string $explication = null;

    #[ORM\ManyToOne(inversedBy: 'choicesQCMs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?QCM $qcm = null;

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

    public function getIsCorrect(): ?bool { return $this->isCorrect; }

    public function setIsCorrect(bool $isCorrect): static
    {
        $this->isCorrect = $isCorrect;

        return $this;
    }

    public function getExplication(): ?string
    {
        return $this->explication;
    }

    public function setExplication(string $explication): static
    {
        $this->explication = $explication;

        return $this;
    }

    public function getQcm(): ?QCM
    {
        return $this->qcm;
    }

    public function setQcm(?QCM $qcm): static
    {
        $this->qcm = $qcm;

        return $this;
    }

    public function __toString(): string
    {
        return $this->question ?? '';
    }
}
