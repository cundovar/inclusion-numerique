<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SiteConfiguration
{
    public const LEGAL = 'legal';

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
            'siteName' => 'Médiation numérique',
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

    // Les informations légales ont leurs propres champs dans le back-office : elles ne passent pas par le JSON,
    // pour qu'une modification de l'un n'efface jamais l'autre
    public function getSettingsJson(): string
    {
        $settings = $this->settings;
        unset($settings[self::LEGAL]);

        return json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function setSettingsJson(string $json): static
    {
        $decoded = json_decode($json, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            unset($decoded[self::LEGAL]);
            if (isset($this->settings[self::LEGAL])) {
                $decoded[self::LEGAL] = $this->settings[self::LEGAL];
            }
            $this->settings = $decoded;
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    /**
     * Informations légales du site (mentions légales, contact RGPD), rangées dans les paramètres.
     *
     * @return array<string, string>
     */
    public function getLegal(): array
    {
        $legal = $this->settings[self::LEGAL] ?? [];

        return \is_array($legal) ? array_map('strval', $legal) : [];
    }

    private function legalValue(string $key): ?string
    {
        $value = trim($this->getLegal()[$key] ?? '');

        return $value === '' ? null : $value;
    }

    private function setLegalValue(string $key, ?string $value): static
    {
        $legal = $this->getLegal();
        $legal[$key] = trim((string) $value);
        $this->settings[self::LEGAL] = $legal;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getLegalEditeur(): ?string { return $this->legalValue('editeur'); }

    public function setLegalEditeur(?string $value): static { return $this->setLegalValue('editeur', $value); }

    public function getLegalAdresse(): ?string { return $this->legalValue('adresse'); }

    public function setLegalAdresse(?string $value): static { return $this->setLegalValue('adresse', $value); }

    public function getLegalEmail(): ?string { return $this->legalValue('email'); }

    public function setLegalEmail(?string $value): static { return $this->setLegalValue('email', $value); }

    public function getLegalDirecteurPublication(): ?string { return $this->legalValue('directeurPublication'); }

    public function setLegalDirecteurPublication(?string $value): static { return $this->setLegalValue('directeurPublication', $value); }

    public function getLegalIdentifiant(): ?string { return $this->legalValue('identifiant'); }

    public function setLegalIdentifiant(?string $value): static { return $this->setLegalValue('identifiant', $value); }

    public function getLegalMiseAJour(): ?\DateTimeImmutable
    {
        $value = $this->legalValue('miseAJour');
        $date = $value === null ? false : \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date === false ? null : $date;
    }

    public function setLegalMiseAJour(?\DateTimeInterface $value): static
    {
        return $this->setLegalValue('miseAJour', $value?->format('Y-m-d'));
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
