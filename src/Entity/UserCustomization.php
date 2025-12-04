<?php

namespace App\Entity;

use App\Repository\UserCustomizationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserCustomizationRepository::class)]
class UserCustomization
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'customization', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::JSON)]
    private array $settings = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->settings = $this->getDefaultSettings();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): static
    {
        $this->settings = $settings;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Paramètres par défaut
     */
    private function getDefaultSettings(): array
    {
        return [
            'siteName' => 'DevDoc',
            'header' => [
                'bgColor' => '',
                'textColor' => '',
                'hoverColor' => '',
            ],
            'body' => [
                'bgColor' => '',
                'textColor' => '',
            ],
            'menuGauche' => [
                'categoryBgColor' => 'bg-blue-300',
                'categoryTextColor' => 'text-gray-800',
                'categoryTextSize' => 'text-2xl',
                'categoryHoverColor' => 'hover:bg-blue-400',
                'menuItemBgColor' => 'bg-gray-100',
                'menuItemTextColor' => 'text-blue-500',
                'menuItemHoverBgColor' => 'hover:bg-gray-300',
            ],
            'menuDroit' => [
                'categoryBgColor' => 'bg-blue-300',
                'categoryTextColor' => 'text-gray-600',
                'categoryTextSize' => 'text-xl',
                'categoryHoverColor' => 'hover:bg-blue-400',
            ],
        ];
    }

    /**
     * Réinitialiser aux valeurs par défaut
     */
    public function resetToDefaults(): static
    {
        $this->settings = $this->getDefaultSettings();
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
