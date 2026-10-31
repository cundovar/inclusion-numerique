<?php

declare(strict_types=1);

namespace App\Service\OAuth;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

/**
 * Adresse publique du site, annoncée aux connecteurs (émetteur OAuth, ressource /mcp).
 * `MCP_PUBLIC_URL` la fixe en production, où un proxy peut masquer le schéma https ; sinon elle vient de la requête.
 */
final class PublicUrl
{
    public function __construct(#[Autowire('%env(MCP_PUBLIC_URL)%')] private string $configured) {}

    public function base(Request $request): string
    {
        $configured = rtrim(trim($this->configured), '/');

        return $configured !== '' ? $configured : $request->getSchemeAndHttpHost();
    }

    public function resource(Request $request, string $path = '/mcp'): string
    {
        return $this->base($request).$path;
    }

    public function host(Request $request): string
    {
        return (string) parse_url($this->base($request), \PHP_URL_HOST);
    }
}
