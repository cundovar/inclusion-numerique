<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['supermenu:read']],
    denormalizationContext: ['groups' => ['supermenu:write']]
)]
class SuperMenu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['supermenu:read', 'page_content:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['supermenu:read', 'supermenu:write', 'page_content:read'])]
    private ?string $name = null;

    // Rang de la rubrique sur l'accueil et dans le menu (1, 2, 3…) ; sans rang, elle passe après les autres, par nom
    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['supermenu:read', 'supermenu:write', 'page_content:read'])]
    private ?int $ordre = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'superMenu')]
    private Collection $category;

    public function __construct()
    {
        $this->category = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
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

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(?int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->category->contains($category)) {
            $this->category->add($category);
            $category->setSuperMenu($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->category->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getSuperMenu() === $this) {
                $category->setSuperMenu(null);
            }
        }

        return $this;
    }
}
