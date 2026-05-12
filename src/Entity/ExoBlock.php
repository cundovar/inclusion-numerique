<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['exo_block:read']],
    denormalizationContext: ['groups' => ['exo_block:write']]
)]
class ExoBlock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['exo_content:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['exo_block:read', 'exo_block:write'])]
    private ?string $content = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['exo_block:read', 'exo_block:write'])]
    private ?string $code = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['exo_block:read', 'exo_block:write'])]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'exoBlocks')]
    #[Groups(['exo_block:read'])]
    private ?ExoContent $exoContent = null;

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

    public function getExoContent(): ?ExoContent
    {
        return $this->exoContent;
    }

    public function setExoContent(?ExoContent $exoContent): static
    {
        $this->exoContent = $exoContent;

        return $this;
    }

    public function __toString(): string
    {
        return $this->type ?? 'Block';
    }
}
