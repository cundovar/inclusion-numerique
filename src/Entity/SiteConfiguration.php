<?php

namespace App\Entity;

use App\Repository\SiteConfigurationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiteConfigurationRepository::class)]
class SiteConfiguration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $configKey = 'default';

    #[ORM\Column(type: Types::JSON)]
    private array $settings = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
        $this->settings = [
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
                'categoryBgColor' => 'bg-blue-100',
                'categoryTextColor' => 'text-gray-800',
                'categoryTextSize' => 'text-2xl',
                'categoryHoverColor' => 'hover:bg-blue-400',
                'menuItemBgColor' => 'bg-gray-100',
                'menuItemTextColor' => 'text-blue-500',
                'menuItemHoverBgColor' => 'hover:bg-gray-300',
            ],
            'menuDroit' => [
                'categoryBgColor' => 'bg-blue-100',
                'categoryTextColor' => 'text-gray-600',
                'categoryTextSize' => 'text-xl',
                'categoryHoverColor' => 'hover:bg-blue-400',
            ],
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConfigKey(): string
    {
        return $this->configKey;
    }

    public function setConfigKey(string $configKey): static
    {
        $this->configKey = $configKey;
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

    public function getSettingsJson(): string
    {
        return json_encode($this->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function setSettingsJson(string $json): static
    {
        $decoded = json_decode($json, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $this->settings = $decoded;
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function __toString(): string
    {
        return $this->configKey;
    }
}
