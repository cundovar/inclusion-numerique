<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\SiteConfiguration\Repository\SiteConfigurationRepositoryInterface;
use App\Entity\SiteConfiguration;

/**
 * Informations légales saisies dans le back-office (Configuration du site) : éditeur, contact pour les
 * questions et l'exercice des droits RGPD, directeur·rice de la publication, date des textes.
 * Les pages légales les affichent ; un champ obligatoire vide est signalé « À compléter », jamais inventé.
 */
final class LegalInformation
{
    // Champs exigés par la loi (LCEN, article 6-III ; RGPD, article 13), avec leur libellé
    public const REQUIRED = [
        'editeur' => "Nom de l'éditeur",
        'adresse' => 'Adresse postale',
        'email' => 'E-mail de contact',
        'directeurPublication' => 'Directeur·rice de la publication',
        'miseAJour' => 'Date de mise à jour des textes légaux',
    ];

    // Hébergeur du site (public, OVHcloud)
    public const HOST = [
        'nom' => 'OVH SAS',
        'adresse' => '2 rue Kellermann, 59100 Roubaix, France',
        'site' => 'https://www.ovhcloud.com',
    ];

    public function __construct(private SiteConfigurationRepositoryInterface $siteConfiguration) {}

    /**
     * @return array{editeur: ?string, adresse: ?string, email: ?string, directeurPublication: ?string, identifiant: ?string, miseAJour: ?\DateTimeImmutable, siteName: string, hebergeur: array<string, string>}
     */
    public function all(): array
    {
        $config = $this->siteConfiguration->getDefault();

        return [
            'editeur' => $config->getLegalEditeur(),
            'adresse' => $config->getLegalAdresse(),
            'email' => $config->getLegalEmail(),
            'directeurPublication' => $config->getLegalDirecteurPublication(),
            'identifiant' => $config->getLegalIdentifiant(),
            'miseAJour' => $config->getLegalMiseAJour(),
            'siteName' => (string) ($this->siteConfiguration->getDefaultSettings()['siteName'] ?? ''),
            'hebergeur' => self::HOST,
        ];
    }

    /**
     * Libellés des champs obligatoires encore vides.
     *
     * @return list<string>
     */
    public function missing(): array
    {
        $values = $this->all();

        return array_values(array_map(
            static fn (string $key) => self::REQUIRED[$key],
            array_filter(array_keys(self::REQUIRED), static fn (string $key) => $values[$key] === null),
        ));
    }
}
