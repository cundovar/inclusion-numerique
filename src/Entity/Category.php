<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['page_content:read']],
    denormalizationContext: ['groups' => ['page_content:write']]
)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['page_content:read', 'page_content:write', 'exo_content:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['page_content:read', 'page_content:write', 'exo_content:read'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Menus>
     */
    #[ORM\OneToMany(targetEntity: Menus::class, mappedBy: 'category')]
    private Collection $menus;

    /**
     * @var Collection<int, PageContent>
     */
    #[ORM\OneToMany(targetEntity: PageContent::class, mappedBy: 'category')]
    private Collection $pageContents;

    /**
     * @var Collection<int, ExoMenu>
     */
    #[ORM\OneToMany(targetEntity: ExoMenu::class, mappedBy: 'category')]
    private Collection $exoMenus;

    /**
     * @var Collection<int, ExoContent>
     */
    #[ORM\OneToMany(targetEntity: ExoContent::class, mappedBy: 'category')]
    private Collection $exoContents;

    #[ORM\ManyToOne(inversedBy: 'categorie')]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?PositionMenus $positionMenus = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?string $couleur = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?string $description = null;

    #[ORM\OneToOne(mappedBy: 'category', cascade: ['persist', 'remove'])]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?Logo $logo = null;

    #[ORM\OneToOne(mappedBy: 'category', cascade: ['persist', 'remove'])]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?Seo $seo = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['default' => true])]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?bool $visible = true;

    #[ORM\ManyToOne(inversedBy: 'category')]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?SuperMenu $superMenu = null;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?NiveauCours $niveauCours = null;

    public function __construct()
    {
        $this->menus = new ArrayCollection();
        $this->pageContents = new ArrayCollection();
        $this->exoMenus = new ArrayCollection();
        $this->exoContents = new ArrayCollection();
        $this->visible = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Menus>
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menus $menu): static
    {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
            $menu->setCategory($this);
        }

        return $this;
    }

    public function removeMenu(Menus $menu): static
    {
        if ($this->menus->removeElement($menu)) {
            // set the owning side to null (unless already changed)
            if ($menu->getCategory() === $this) {
                $menu->setCategory(null);
            }
        }

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
            $pageContent->setCategory($this);
        }

        return $this;
    }

    public function removePageContent(PageContent $pageContent): static
    {
        if ($this->pageContents->removeElement($pageContent)) {
            // set the owning side to null (unless already changed)
            if ($pageContent->getCategory() === $this) {
                $pageContent->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ExoMenu>
     */
    public function getExoMenus(): Collection
    {
        return $this->exoMenus;
    }

    public function addExoMenu(ExoMenu $exoMenu): static
    {
        if (!$this->exoMenus->contains($exoMenu)) {
            $this->exoMenus->add($exoMenu);
            $exoMenu->setCategory($this);
        }

        return $this;
    }

    public function removeExoMenu(ExoMenu $exoMenu): static
    {
        if ($this->exoMenus->removeElement($exoMenu)) {
            if ($exoMenu->getCategory() === $this) {
                $exoMenu->setCategory(null);
            }
        }

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
            $exoContent->setCategory($this);
        }

        return $this;
    }

    public function removeExoContent(ExoContent $exoContent): static
    {
        if ($this->exoContents->removeElement($exoContent)) {
            if ($exoContent->getCategory() === $this) {
                $exoContent->setCategory(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
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

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(?string $couleur): static
    {
        $this->couleur = $couleur;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLogo(): ?Logo
    {
        return $this->logo;
    }

    public function setLogo(?Logo $logo): static
    {
        // unset the owning side of the relation if necessary
        if ($logo === null && $this->logo !== null) {
            $this->logo->setCategory(null);
        }

        // set the owning side of the relation if necessary
        if ($logo !== null && $logo->getCategory() !== $this) {
            $logo->setCategory($this);
        }

        $this->logo = $logo;

        return $this;
    }

    public function getSeo(): ?Seo
    {
        return $this->seo;
    }

    public function setSeo(?Seo $seo): static
    {
        // unset the owning side of the relation if necessary
        if ($seo === null && $this->seo !== null) {
            $this->seo->setCategory(null);
        }

        // set the owning side of the relation if necessary
        if ($seo !== null && $seo->getCategory() !== $this) {
            $seo->setCategory($this);
        }

        $this->seo = $seo;

        return $this;
    }

    public function isVisible(): ?bool
    {
        return $this->visible ?? true;
    }

    public function setVisible(?bool $visible): static
    {
        $this->visible = $visible;

        return $this;
    }

    public function getSuperMenu(): ?SuperMenu
    {
        return $this->superMenu;
    }

    public function setSuperMenu(?SuperMenu $superMenu): static
    {
        $this->superMenu = $superMenu;

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
