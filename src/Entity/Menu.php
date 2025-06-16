<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customRoute = null;

    #[ORM\ManyToOne(inversedBy: 'menus')]
    private ?Module $module = null;

    #[ORM\ManyToOne(inversedBy: 'menus')]
    private ?Lesson $lesson = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $children;

    #[ORM\Column(type: 'boolean')]
    private bool $isVisible = true;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    public function __construct()
    {
        $this->children = new ArrayCollection();
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

    public function getCustomRoute(): ?string
    {
        return $this->customRoute;
    }
    public function setCustomRoute(?string $customRoute): static
    {
        $this->customRoute = $customRoute;
        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }
    public function setModule(?Module $module): static
    {
        $this->module = $module;
        return $this;
    }

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }
    public function setLesson(?Lesson $lesson): static
    {
        $this->lesson = $lesson;
        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }
    public function setParent(?self $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }
    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }
        return $this;
    }
    public function removeChild(self $child): static
    {
        if ($this->children->removeElement($child) && $child->getParent() === $this) {
            $child->setParent(null);
        }
        return $this;
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }
    public function setIsVisible(bool $isVisible): static
    {
        $this->isVisible = $isVisible;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }
    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getRoute(): string
    {
        if ($this->lesson) {
            return '/lessons/' . $this->lesson->getSlug();
        }

        if ($this->module) {
            return '/modules/' . $this->module->getSlug();
        }

        return $this->customRoute ?? '#';
    }
}
