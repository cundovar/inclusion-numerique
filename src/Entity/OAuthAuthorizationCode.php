<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Code d'autorisation à usage unique, délivré après le consentement d'un administrateur (OAuth 2.1, PKCE S256).
 * Seule l'empreinte SHA-256 du code est stockée.
 */
#[ORM\Entity]
#[ORM\Table(name: 'oauth_authorization_code')]
class OAuthAuthorizationCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'code_hash', length: 64, unique: true)]
    private string $codeHash;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'client_id', nullable: false, onDelete: 'CASCADE')]
    private OAuthClient $client;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(name: 'redirect_uri', length: 500)]
    private string $redirectUri;

    #[ORM\Column(name: 'code_challenge', length: 128)]
    private string $codeChallenge;

    #[ORM\Column(length: 500)]
    private string $resource;

    #[ORM\Column(length: 64)]
    private string $scope;

    #[ORM\Column(name: 'expires_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(name: 'used_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $usedAt = null;

    public function __construct(string $codeHash, OAuthClient $client, User $user, string $redirectUri, string $codeChallenge, \DateTimeImmutable $expiresAt, string $resource = '/mcp', string $scope = 'redaction')
    {
        $this->codeHash = $codeHash;
        $this->client = $client;
        $this->user = $user;
        $this->redirectUri = $redirectUri;
        $this->codeChallenge = $codeChallenge;
        $this->expiresAt = $expiresAt;
        $this->resource = $resource;
        $this->scope = $scope;
    }

    public function getId(): ?int { return $this->id; }

    public function getClient(): OAuthClient { return $this->client; }

    public function getUser(): User { return $this->user; }

    public function getRedirectUri(): string { return $this->redirectUri; }

    public function getCodeChallenge(): string { return $this->codeChallenge; }

    public function getResource(): string { return $this->resource; }

    public function getScope(): string { return $this->scope; }

    public function isExpired(\DateTimeImmutable $now): bool { return $now >= $this->expiresAt; }

    public function isUsed(): bool { return $this->usedAt !== null; }

    public function markUsed(): void { $this->usedAt = new \DateTimeImmutable(); }
}
