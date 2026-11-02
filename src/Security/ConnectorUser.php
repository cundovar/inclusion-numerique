<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\OAuthGrant;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Connecteur (claude.ai, ChatGPT…) authentifié par un jeton OAuth, au nom d'un administrateur.
 * Il ne peut que préparer des brouillons : son seul rôle est ROLE_AGENT_REDACTION.
 */
final class ConnectorUser implements UserInterface
{
    public const ROLE = 'ROLE_AGENT_REDACTION';

    public function __construct(private OAuthGrant $grant) {}

    public function getGrant(): OAuthGrant
    {
        return $this->grant;
    }

    /**
     * Identité de l'agent enregistrée comme auteur ou relecteur : un connecteur par outil d'IA.
     */
    public function getAgentLabel(): string
    {
        return $this->grant->getClient()->getAgentLabel();
    }

    public function getRoles(): array
    {
        return [self::ROLE];
    }

    public function eraseCredentials(): void {}

    public function getUserIdentifier(): string
    {
        return $this->getAgentLabel();
    }
}
