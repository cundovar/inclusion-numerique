<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

/**
 * Adresse publique du site, pour les liens envoyés par e-mail (réinitialisation du mot de passe).
 * `MCP_PUBLIC_URL` la fixe en production, où un proxy peut masquer le schéma https ; sinon elle vient de la requête.
 * La variable garde son nom d'origine (connecteur MCP, retiré de ce site) pour ne pas changer le .env.
 */
final class PublicUrl
{
    public function __construct(#[Autowire('%env(MCP_PUBLIC_URL)%')] private string $configured) {}

    public function base(Request $request): string
    {
        $configured = rtrim(trim($this->configured), '/');

        return $configured !== '' ? $configured : $request->getSchemeAndHttpHost();
    }
}
