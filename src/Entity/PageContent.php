<?php

namespace App\Entity;

use App\Repository\PageContentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;

#[ORM\Entity(repositoryClass: PageContentRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['page_content:read']],
    denormalizationContext: ['groups' => ['page_content:write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['page.slug' => 'exact'])]
class PageContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['page_content:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?string $title = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'pageContents')]
    #[ApiProperty(readableLink: true)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?Page $page = null;

    #[ORM\ManyToOne(inversedBy: 'pageContents')]
    #[ApiProperty(readableLink: true)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'pageContents')]
    #[ApiProperty(readableLink: true)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?Menus $menu = null;

    #[ORM\Column(nullable: true, type: Types::TEXT)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private ?string $code = null;

    #[ORM\OneToMany(
        mappedBy: 'pageContent',
        targetEntity: PageBlock::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    #[ApiProperty(readableLink: true)]
    #[Groups(['page_content:read', 'page_content:write'])]
    private Collection $pageBlocks;

    public function __construct()
    {
        $this->pageBlocks = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getTitle(): ?string { return $this->title; }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getType(): ?string { return $this->type; }

    public function setType(?string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getContent(): ?string { return $this->content; }

    public function setContent(?string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getPage(): ?Page { return $this->page; }

    public function setPage(?Page $page): static
    {
        $this->page = $page;
        return $this;
    }

    public function getCategory(): ?Category { return $this->category; }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getMenu(): ?Menus { return $this->menu; }

    public function setMenu(?Menus $menu): static
    {
        $this->menu = $menu;
        return $this;
    }

    public function getCode(): ?string { return $this->code; }

    public function setCode(?string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? 'PageContent';
    }

    /**
     * @return Collection<int, PageBlock>
     */
    public function getPageBlocks(): Collection
    {
        return $this->pageBlocks;
    }

    public function addPageBlock(PageBlock $pageBlock): static
    {
        if (!$this->pageBlocks->contains($pageBlock)) {
            $this->pageBlocks->add($pageBlock);
            $pageBlock->setPageContent($this);
        }
        return $this;
    }

    public function removePageBlock(PageBlock $pageBlock): static
    {
        if ($this->pageBlocks->removeElement($pageBlock)) {
            if ($pageBlock->getPageContent() === $this) {
                $pageBlock->setPageContent(null);
            }
        }
        return $this;
    }
}
