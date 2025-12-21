<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\DocDeCodeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DocDeCodeRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
    normalizationContext: ['groups' => ['doc_de_code:read']],
    denormalizationContext: ['groups' => ['doc_de_code:write']],
    paginationEnabled: false
)]
class DocDeCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['doc_de_code:read', 'doc_de_code:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['doc_de_code:read', 'doc_de_code:write'])]
    private ?string $url = null;

    #[ORM\Column(length: 255)]
    #[Groups(['doc_de_code:read', 'doc_de_code:write'])]
    private ?string $titre = null;

    #[ORM\OneToOne(mappedBy: 'docDeCode', cascade: ['persist', 'remove'])]
    #[Groups(['doc_de_code:read'])]
    private ?Logo $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['doc_de_code:read', 'doc_de_code:write'])]
    private ?string $alt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['doc_de_code:read', 'doc_de_code:write'])]
    private ?string $color = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

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
            $this->logo->setDocDeCode(null);
        }

        // set the owning side of the relation if necessary
        if ($logo !== null && $logo->getDocDeCode() !== $this) {
            $logo->setDocDeCode($this);
        }

        $this->logo = $logo;

        return $this;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(?string $alt): static
    {
        $this->alt = $alt;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function __toString(): string
    {
        return $this->titre ?? '';
    }
}
