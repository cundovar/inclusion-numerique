<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Repository\PageBlockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageBlockRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['page_block:read']],
    denormalizationContext: ['groups' => ['page_block:write']]
)]
class PageBlock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['page_content:read'])]
   
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['page_block:read', 'page_block:write'])]
    private ?string $content = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['page_block:read', 'page_block:write'])]
    private ?string $code = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['page_block:read', 'page_block:write'])]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'pageBlocks')]
    #[Groups(['page_block:read'])] // ⛔️ Ne pas exposer en lecture pour éviter la boucle
    private ?PageContent $pageContent = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPageContent(): ?PageContent
    {
        return $this->pageContent;
    }

    public function setPageContent(?PageContent $pageContent): static
    {
        $this->pageContent = $pageContent;

        return $this;
    }

    public function __toString(): string
    {
        return $this->type ?? 'Block';
    }
}
