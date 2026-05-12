<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'revision')]
class AgentCourseRevision
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'course_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?PageContent $course = null;

    #[ORM\Column(name: 'type_revision', length: 50)]
    private ?string $typeRevision = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(name: 'ancien_code', type: Types::TEXT, nullable: true)]
    private ?string $ancienCode = null;

    #[ORM\Column(name: 'nouveau_code', type: Types::TEXT, nullable: true)]
    private ?string $nouveauCode = null;

    #[ORM\Column(name: 'date_revision', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateRevision = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $appliquee = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourse(): ?PageContent
    {
        return $this->course;
    }

    public function setCourse(?PageContent $course): static
    {
        $this->course = $course;

        return $this;
    }

    public function getTypeRevision(): ?string
    {
        return $this->typeRevision;
    }

    public function setTypeRevision(string $typeRevision): static
    {
        $this->typeRevision = $typeRevision;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getAncienCode(): ?string
    {
        return $this->ancienCode;
    }

    public function setAncienCode(?string $ancienCode): static
    {
        $this->ancienCode = $ancienCode;

        return $this;
    }

    public function getNouveauCode(): ?string
    {
        return $this->nouveauCode;
    }

    public function setNouveauCode(?string $nouveauCode): static
    {
        $this->nouveauCode = $nouveauCode;

        return $this;
    }

    public function getDateRevision(): ?\DateTimeInterface
    {
        return $this->dateRevision;
    }

    public function setDateRevision(\DateTimeInterface $dateRevision): static
    {
        $this->dateRevision = $dateRevision;

        return $this;
    }

    public function isAppliquee(): bool
    {
        return $this->appliquee;
    }

    public function setAppliquee(bool $appliquee): static
    {
        $this->appliquee = $appliquee;

        return $this;
    }
}
