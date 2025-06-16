<?php

namespace App\Entity;

use App\Repository\LessonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
class Lesson
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $codeSnippets = null;

    #[ORM\ManyToOne(inversedBy: 'lessons')]
    private ?Module $module = null;

    #[ORM\OneToMany(mappedBy: 'lesson', targetEntity: Menu::class)]
    private Collection $menus;

    public function __construct()
    {
        $this->menus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getTitle(): ?string
    {
        return $this->title;
    }
    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }
    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
    public function setContent(?string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getCodeSnippets(): ?array
    {
        return $this->codeSnippets;
    }
    public function setCodeSnippets(?array $codeSnippets): static
    {
        $this->codeSnippets = $codeSnippets;
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

    public function getMenus(): Collection
    {
        return $this->menus;
    }
    public function addMenu(Menu $menu): static
    {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
            $menu->setLesson($this);
        }
        return $this;
    }
    public function removeMenu(Menu $menu): static
    {
        if ($this->menus->removeElement($menu) && $menu->getLesson() === $this) {
            $menu->setLesson(null);
        }
        return $this;
    }
}
