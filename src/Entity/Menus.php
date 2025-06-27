<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\MenusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MenusRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['page_content:read']],
    denormalizationContext: ['groups' => ['page_content:write']]
)]
class Menus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['page_content:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['page_content:read'])]
    private ?string $label = null;

    /**
     * @var Collection<int, Page>
     */
    #[ORM\OneToMany(targetEntity: Page::class, mappedBy: 'menus')]
    private Collection $pages;

    #[ORM\ManyToOne(inversedBy: 'menus')]
    private ?Category $category = null;

    /**
     * @var Collection<int, PageContent>
     */
    #[ORM\OneToMany(targetEntity: PageContent::class, mappedBy: 'menu')]
    private Collection $pageContents;

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
        return $this->label;
    }
}
