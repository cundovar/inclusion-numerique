<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Connecteur (claude.ai, ChatGPT…) enregistré dynamiquement auprès du serveur OAuth du site.
 * Chaque connecteur est un agent distinct : un brouillon est relu par un autre connecteur que son auteur.
 */
#[ORM\Entity]
#[ORM\Table(name: 'oauth_client')]
class OAuthClient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'client_id', length: 64, unique: true)]
    private string $clientId;

    // Nom annoncé par le connecteur à l'enregistrement (« Claude », « ChatGPT »…)
    #[ORM\Column(length: 60)]
    private string $name;

    /** @var list<string> */
    #[ORM\Column(name: 'redirect_uris', type: Types::JSON)]
    private array $redirectUris;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'last_used_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastUsedAt = null;

    /**
     * @param list<string> $redirectUris
     */
    public function __construct(string $clientId, string $name, array $redirectUris)
    {
        $this->clientId = $clientId;
        $this->name = $name;
        $this->redirectUris = $redirectUris;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getClientId(): string { return $this->clientId; }

    public function getName(): string { return $this->name; }

    /** @return list<string> */
    public function getRedirectUris(): array { return $this->redirectUris; }

    public function hasRedirectUri(string $uri): bool { return \in_array($uri, $this->redirectUris, true); }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getLastUsedAt(): ?\DateTimeImmutable { return $this->lastUsedAt; }

    public function markUsed(): void { $this->lastUsedAt = new \DateTimeImmutable(); }

    /**
     * Identité de l'agent, enregistrée comme auteur ou relecteur des brouillons : nom et début de l'identifiant,
     * pour distinguer deux connecteurs de même nom.
     */
    public function getAgentLabel(): string
    {
        return sprintf('connecteur:%s#%s', $this->name, substr($this->clientId, 0, 8));
    }
}
