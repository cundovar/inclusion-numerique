<?php

namespace App\Entity;

use App\Repository\ExoContentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;

#[ORM\Entity(repositoryClass: ExoContentRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['exo_content:read']],
    denormalizationContext: ['groups' => ['exo_content:write']],
    paginationEnabled: false
)]
#[ApiFilter(SearchFilter::class, properties: ['exo.slug' => 'exact'])]
class ExoContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['exo_content:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['exo_content:read', 'exo_content:write'])]
    private ?string $title = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['exo_content:read', 'exo_content:write'])]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['exo_content:read', 'exo_content:write'])]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'exoContents')]
    #[ApiProperty(readableLink: true)]
    #[Groups(['exo_content:read', 'exo_content:write'])]
    private ?Exo $exo = null;

    #[ORM\ManyToOne(inversedBy: 'exoContents')]
    #[ApiProperty(readableLink: true)]
    #[Groups(['exo_content:read', 'exo_content:write'])]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'exoContents')]
    #[ApiProperty(readableLink: true)]
    #[Groups(['exo_content:read', 'exo_content:write'])]
    private ?ExoMenu $exoMenu = null;

    #[ORM\Column(nullable: true, type: Types::TEXT)]
    #[Groups(['exo_content:read', 'exo_content:write'])]
    private ?string $code = null;

    #[ORM\OneToMany(
        mappedBy: 'exoContent',
        targetEntity: ExoBlock::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    #[ApiProperty(readableLink: true)]
    #[Groups(['exo_content:read', 'exo_content:write'])]
    private Collection $exoBlocks;

    public function __construct()
    {
        $this->exoBlocks = new ArrayCollection();
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

    public function getExo(): ?Exo { return $this->exo; }

    public function setExo(?Exo $exo): static
    {
        $this->exo = $exo;
        return $this;
    }

    public function getCategory(): ?Category { return $this->category; }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getExoMenu(): ?ExoMenu { return $this->exoMenu; }

    public function setExoMenu(?ExoMenu $exoMenu): static
    {
        $this->exoMenu = $exoMenu;
        return $this;
    }

    public function getCode(): ?string { return $this->code; }

    public function setCode(?string $code): static
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return Collection<int, ExoBlock>
     */
    public function getExoBlocks(): Collection
    {
        return $this->exoBlocks;
    }

    public function addExoBlock(ExoBlock $exoBlock): static
    {
        if (!$this->exoBlocks->contains($exoBlock)) {
            $this->exoBlocks->add($exoBlock);
            $exoBlock->setExoContent($this);
        }
        return $this;
    }

    public function removeExoBlock(ExoBlock $exoBlock): static
    {
        if ($this->exoBlocks->removeElement($exoBlock)) {
            if ($exoBlock->getExoContent() === $this) {
                $exoBlock->setExoContent(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? 'ExoContent';
    }
}
