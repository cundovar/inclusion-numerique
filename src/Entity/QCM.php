<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\QCMRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: QCMRepository::class)]
#[ApiResource]
class QCM
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['qcm:read'])]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['qcm:read'])]
    private ?string $solution = null;

    #[ORM\ManyToOne(inversedBy: 'qcm')]
    #[Groups(['qcm:read'])]
    private ?LanguageQCM $languageQCM = null;

    #[ORM\ManyToOne(inversedBy: 'qcm')]
    #[Groups(['qcm:read'])]
    private ?NiveauQCM $niveauQCM = null;

    /**
     * @var Collection<int, ChoicesQCM>
     */
    #[ORM\OneToMany(targetEntity: ChoicesQCM::class, mappedBy: 'qcm', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['qcm:read'])]
    private Collection $choicesQCMs;

    public function __construct()
    {
        $this->choicesQCMs = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getSolution(): ?string
    {
        return $this->solution;
    }

    public function setSolution(string $solution): static
    {
        $this->solution = $solution;

        return $this;
    }

    public function getLanguageQCM(): ?LanguageQCM
    {
        return $this->languageQCM;
    }

    public function setLanguageQCM(?LanguageQCM $languageQCM): static
    {
        $this->languageQCM = $languageQCM;

        return $this;
    }

    public function getNiveauQCM(): ?NiveauQCM
    {
        return $this->niveauQCM;
    }

    public function setNiveauQCM(?NiveauQCM $niveauQCM): static
    {
        $this->niveauQCM = $niveauQCM;

        return $this;
    }

    /**
     * @return Collection<int, ChoicesQCM>
     */
    public function getChoicesQCMs(): Collection
    {
        return $this->choicesQCMs;
    }

    public function addChoicesQCM(ChoicesQCM $choicesQCM): static
    {
        if (!$this->choicesQCMs->contains($choicesQCM)) {
            $this->choicesQCMs->add($choicesQCM);
            $choicesQCM->setQcm($this);
        }

        return $this;
    }

    public function removeChoicesQCM(ChoicesQCM $choicesQCM): static
    {
        if ($this->choicesQCMs->removeElement($choicesQCM)) {
            // set the owning side to null (unless already changed)
            if ($choicesQCM->getQcm() === $this) {
                $choicesQCM->setQcm(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->titre ?? '';
    }
}