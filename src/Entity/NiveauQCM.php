<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\NiveauQCMRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NiveauQCMRepository::class)]
#[ApiResource]
class NiveauQCM
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['qcm:read'])]
    private ?string $titre = null;

    /**
     * @var Collection<int, QCM>
     */
    #[ORM\OneToMany(targetEntity: QCM::class, mappedBy: 'niveauQCM')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Collection $qcm;

    public function __construct()
    {
        $this->qcm = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * @return Collection<int, QCM>
     */
    public function getQcm(): Collection
    {
        return $this->qcm;
    }

    public function addQcm(QCM $qcm): static
    {
        if (!$this->qcm->contains($qcm)) {
            $this->qcm->add($qcm);
            $qcm->setNiveauQCM($this);
        }

        return $this;
    }

    public function removeQcm(QCM $qcm): static
    {
        if ($this->qcm->removeElement($qcm)) {
            // set the owning side to null (unless already changed)
            if ($qcm->getNiveauQCM() === $this) {
                $qcm->setNiveauQCM(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->titre ?? '';
    }
}
