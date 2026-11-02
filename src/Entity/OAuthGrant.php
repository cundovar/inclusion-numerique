<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Accès accordé à un connecteur au nom d'un administrateur : jeton d'accès court et jeton de
 * rafraîchissement, renouvelés ensemble. Seules leurs empreintes SHA-256 sont stockées.
 */
#[ORM\Entity]
#[ORM\Table(name: 'oauth_grant')]
class OAuthGrant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'client_id', nullable: false, onDelete: 'CASCADE')]
    private OAuthClient $client;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    // Code d'autorisation d'origine : un code rejoué révoque l'accès qu'il a ouvert
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'authorization_code_id', nullable: true, onDelete: 'SET NULL')]
    private ?OAuthAuthorizationCode $authorizationCode;

    #[ORM\Column(name: 'access_token_hash', length: 64, unique: true)]
    private string $accessTokenHash;

    #[ORM\Column(name: 'access_expires_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $accessExpiresAt;

    #[ORM\Column(name: 'refresh_token_hash', length: 64, unique: true)]
    private string $refreshTokenHash;

    #[ORM\Column(name: 'refresh_expires_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $refreshExpiresAt;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'revoked_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $revokedAt = null;

    public function __construct(OAuthClient $client, User $user, ?OAuthAuthorizationCode $authorizationCode)
    {
        $this->client = $client;
        $this->user = $user;
        $this->authorizationCode = $authorizationCode;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getClient(): OAuthClient { return $this->client; }

    public function getUser(): User { return $this->user; }

    public function getAuthorizationCode(): ?OAuthAuthorizationCode { return $this->authorizationCode; }

    public function rotate(string $accessTokenHash, \DateTimeImmutable $accessExpiresAt, string $refreshTokenHash, \DateTimeImmutable $refreshExpiresAt): void
    {
        $this->accessTokenHash = $accessTokenHash;
        $this->accessExpiresAt = $accessExpiresAt;
        $this->refreshTokenHash = $refreshTokenHash;
        $this->refreshExpiresAt = $refreshExpiresAt;
    }

    public function isAccessValid(\DateTimeImmutable $now): bool { return $this->revokedAt === null && $now < $this->accessExpiresAt; }

    public function isRefreshValid(\DateTimeImmutable $now): bool { return $this->revokedAt === null && $now < $this->refreshExpiresAt; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getRevokedAt(): ?\DateTimeImmutable { return $this->revokedAt; }

    public function isRevoked(): bool { return $this->revokedAt !== null; }

    public function revoke(): void { $this->revokedAt ??= new \DateTimeImmutable(); }
}
