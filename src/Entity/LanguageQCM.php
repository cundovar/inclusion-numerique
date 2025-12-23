<?php

namespace App\Entity;

use App\Repository\LanguageQCMRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiResource;

#[ORM\Entity(repositoryClass: LanguageQCMRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['qcm:read']],
    denormalizationContext: ['groups' => ['qcm:write']]
)]
class LanguageQCM
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['qcm:read', 'qcm:write'])]
    private ?string $name = null;

    /**
     * @var Collection<int, QCM>
     */
    #[ORM\OneToMany(targetEntity: QCM::class, mappedBy: 'languageQCM')]
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
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
            $qcm->setLanguageQCM($this);
        }

        return $this;
    }

    public function removeQcm(QCM $qcm): static
    {
        if ($this->qcm->removeElement($qcm)) {
            // set the owning side to null (unless already changed)
            if ($qcm->getLanguageQCM() === $this) {
                $qcm->setLanguageQCM(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
