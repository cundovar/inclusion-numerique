<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ExoMenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ExoMenuRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['exo_content:read']],
    denormalizationContext: ['groups' => ['exo_content:write']]
)]
class ExoMenu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['exo_content:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['exo_content:read', 'exo_content:write'])]
    private ?string $label = null;

    /**
     * @var Collection<int, Exo>
     */
    #[ORM\OneToMany(targetEntity: Exo::class, mappedBy: 'exoMenu')]
    private Collection $exos;

    #[ORM\ManyToOne(inversedBy: 'exoMenus')]
    private ?Category $category = null;

    /**
     * @var Collection<int, ExoContent>
     */
    #[ORM\OneToMany(targetEntity: ExoContent::class, mappedBy: 'exoMenu')]
    private Collection $exoContents;

    public function __construct()
    {
        $this->exos = new ArrayCollection();
        $this->exoContents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection<int, Exo>
     */
    public function getExos(): Collection
    {
        return $this->exos;
    }

    public function addExo(Exo $exo): static
    {
        if (!$this->exos->contains($exo)) {
            $this->exos->add($exo);
            $exo->setExoMenu($this);
        }

        return $this;
    }

    public function removeExo(Exo $exo): static
    {
        if ($this->exos->removeElement($exo)) {
            if ($exo->getExoMenu() === $this) {
                $exo->setExoMenu(null);
            }
        }

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
            $exoContent->setExoMenu($this);
        }

        return $this;
    }

    public function removeExoContent(ExoContent $exoContent): static
    {
        if ($this->exoContents->removeElement($exoContent)) {
            if ($exoContent->getExoMenu() === $this) {
                $exoContent->setExoMenu(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->label ?? '';
    }
}
