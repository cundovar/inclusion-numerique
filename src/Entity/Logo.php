<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\LogoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LogoRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
    normalizationContext: ['groups' => ['logo:read']],
    denormalizationContext: ['groups' => ['logo:write']],
    paginationEnabled: false
)]
class Logo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['logo:read', 'doc_de_code:read', 'logo:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['logo:read', 'doc_de_code:read', 'logo:write'])]
    private ?string $logo = null;

    #[ORM\Column(length: 255)]
    #[Groups(['logo:read', 'doc_de_code:read', 'logo:write'])]
    private ?string $titre = null;


    #[ORM\OneToOne(inversedBy: 'logo', cascade: ['persist', 'remove'])]
    private ?DocDeCode $docDeCode = null;

    #[ORM\OneToOne(inversedBy: 'logo', cascade: ['persist', 'remove'])]
    private ?Category $category = null;


        public function __toString(): string
    {
        // Adapte en fonction de ce que tu veux afficher dans EasyAdmin
        // Par exemple, si tu as une propriété "name" ou "filename" :
        return (string) $this->getTitre(); // ou getFilename(), getTitre(), etc.
    }





    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): static
    {
        $this->logo = $logo;

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

    

    public function getDocDeCode(): ?DocDeCode
    {
        return $this->docDeCode;
    }

    public function setDocDeCode(?DocDeCode $docDeCode): static
    {
        $this->docDeCode = $docDeCode;

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
}
