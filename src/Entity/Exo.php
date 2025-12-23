<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ExoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ExoRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['exo:read']],
    denormalizationContext: ['groups' => ['exo:write']]
)]
class Exo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['exo:read', 'exo:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['exo:read', 'exo:write'])]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'exos')]
    #[Groups(['exo:read', 'exo:write'])]
    private ?ExoMenu $exoMenu = null;

    /**
     * @var Collection<int, ExoContent>
     */
    #[ORM\OneToMany(targetEntity: ExoContent::class, mappedBy: 'exo')]
    private Collection $exoContents;

    public function __construct()
    {
        $this->exoContents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getExoMenu(): ?ExoMenu
    {
        return $this->exoMenu;
    }

    public function setExoMenu(?ExoMenu $exoMenu): static
    {
        $this->exoMenu = $exoMenu;

        return $this;
    }

    /**
     * @return Collection<int, ExoContent>
     */
    public function getExoContents(): Collection
    {
        return $this->exoContents;
    }

    public function addExoContent(ExoContent $exoContent): static
    {
        if (!$this->exoContents->contains($exoContent)) {
            $this->exoContents->add($exoContent);
            $exoContent->setExo($this);
        }

        return $this;
    }

    public function removeExoContent(ExoContent $exoContent): static
    {
        if ($this->exoContents->removeElement($exoContent)) {
            if ($exoContent->getExo() === $this) {
                $exoContent->setExo(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->slug ?? '';
    }
}
