<?php 
namespace App\Entity;

use App\Repository\ModuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModuleRepository::class)]
class Module
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
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'module', targetEntity: Lesson::class)]
    private Collection $lessons;

    #[ORM\OneToMany(mappedBy: 'module', targetEntity: Menu::class)]
    private Collection $menus;

    public function __construct()
    {
        $this->lessons = new ArrayCollection();
        $this->menus = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $title): static { $this->title = $title; return $this; }

    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(?string $slug): static { $this->slug = $slug; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getLessons(): Collection { return $this->lessons; }
    public function addLesson(Lesson $lesson): static {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
            $lesson->setModule($this);
        }
        return $this;
    }
    public function removeLesson(Lesson $lesson): static {
        if ($this->lessons->removeElement($lesson) && $lesson->getModule() === $this) {
            $lesson->setModule(null);
        }
        return $this;
    }

    public function getMenus(): Collection { return $this->menus; }
    public function addMenu(Menu $menu): static {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
            $menu->setModule($this);
        }
        return $this;
    }
    public function removeMenu(Menu $menu): static {
        if ($this->menus->removeElement($menu) && $menu->getModule() === $this) {
            $menu->setModule(null);
        }
        return $this;
    }
}
