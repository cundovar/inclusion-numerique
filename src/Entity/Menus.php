<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiProperty;
  use ApiPlatform\Metadata\GetCollection;
  use ApiPlatform\Metadata\Post;
  use ApiPlatform\Metadata\Get;
  use ApiPlatform\Metadata\Put;
  use ApiPlatform\Metadata\Patch;
  use ApiPlatform\Metadata\Delete;


#[ORM\Entity]
#[ApiResource(
  operations: [
          new GetCollection(),
          new Post(),
          new Get(),
          new Put(),    // ← Ajouter
          new Patch(),  // ← Ajouter
          new Delete(),
      ],
    normalizationContext: ['groups' => ['page_content:read']],
    denormalizationContext: ['groups' => ['page_content:write']]
)]
class Menus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['page_content:read', 'menu:read', 'menu:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['page_content:read', 'page_content:write', 'menu:read', 'menu:write', 'menu:list'])]
    private ?string $label = null;

    /**
     * @var Collection<int, Page>
     */
    #[ORM\OneToMany(targetEntity: Page::class, mappedBy: 'menus')]
   
    private Collection $pages;

    #[ORM\ManyToOne(inversedBy: 'menus')]
    #[Groups(['page_content:read', 'page_content:write', 'menu:read', 'menu:write'])] 
    private ?Category $category = null;

    /**
     * @var Collection<int, PageContent>
     */
    #[ORM\OneToMany(targetEntity: PageContent::class, mappedBy: 'menu')]
   
    private Collection $pageContents;



    #[ORM\ManyToOne(inversedBy: 'menus')]
    #[Groups(['page_content:read', 'page_content:write', 'menu:read', 'menu:write'])] 
    private ?PositionMenus $positionMenus = null;

    #[ORM\ManyToOne(inversedBy: 'menus')]
    #[ApiProperty(readableLink: true)]
    #[Groups(['page_content:read', 'page_content:write', 'menu:read', 'menu:write'])]
    private ?NiveauCours $niveauCours = null;

    public function __construct()
    {
        $this->pages = new ArrayCollection();
        $this->pageContents = new ArrayCollection();
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
     * @return Collection<int, Page>
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): static
    {
        if (!$this->pages->contains($page)) {
            $this->pages->add($page);
            $page->setMenus($this);
        }

        return $this;
    }

    public function removePage(Page $page): static
    {
        if ($this->pages->removeElement($page)) {
            // set the owning side to null (unless already changed)
            if ($page->getMenus() === $this) {
                $page->setMenus(null);
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
     * @return Collection<int, PageContent>
     */
    public function getPageContents(): Collection
    {
        return $this->pageContents;
    }

    public function addPageContent(PageContent $pageContent): static
    {
        if (!$this->pageContents->contains($pageContent)) {
            $this->pageContents->add($pageContent);
            $pageContent->setMenu($this);
        }

        return $this;
    }

    public function removePageContent(PageContent $pageContent): static
    {
        if ($this->pageContents->removeElement($pageContent)) {
            // set the owning side to null (unless already changed)
            if ($pageContent->getMenu() === $this) {
                $pageContent->setMenu(null);
            }
        }

        return $this;
    }
    public function __toString(): string
    {
        return $this->label ?? '';
    }

    public function getPositionMenus(): ?PositionMenus
    {
        return $this->positionMenus;
    }

    public function setPositionMenus(?PositionMenus $positionMenus): static
    {
        $this->positionMenus = $positionMenus;

        return $this;
    }

    public function getNiveauCours(): ?NiveauCours
    {
        return $this->niveauCours;
    }

    public function setNiveauCours(?NiveauCours $niveauCours): static
    {
        $this->niveauCours = $niveauCours;

        return $this;
    }
}
