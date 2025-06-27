<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['page_content:read']],
    denormalizationContext: ['groups' => ['page_content:write']]
)]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['page_content:read'])]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'pages')]
    private ?Menus $menus = null;

    /**
     * @var Collection<int, PageContent>
     */
    #[ORM\OneToMany(targetEntity: PageContent::class, mappedBy: 'page')]
    private Collection $pageContents;

    public function __construct()
    {
        $this->pageContents = new ArrayCollection();
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

    public function getMenus(): ?Menus
    {
        return $this->menus;
    }

    public function setMenus(?Menus $menus): static
    {
        $this->menus = $menus;

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
            $pageContent->setPage($this);
        }

        return $this;
    }

    public function removePageContent(PageContent $pageContent): static
    {
        if ($this->pageContents->removeElement($pageContent)) {
            // set the owning side to null (unless already changed)
            if ($pageContent->getPage() === $this) {
                $pageContent->setPage(null);
            }
        }

        return $this;
    }
    public function __toString(): string
    {
        return $this->slug;
    }
}
