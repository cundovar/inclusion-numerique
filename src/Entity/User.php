<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: '`appy_User`')] // Pour éviter les conflits avec les mots-clés SQL
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', columns: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'Ce pseudo est déjà pris : choisissez-en un autre.')]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cette adresse : connectez-vous.')]
// Seul le nom d'utilisateur est exposé et modifiable (par son propriétaire ou un admin) :
// rôles et mot de passe ne passent jamais par l'API.
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Get(security: "is_granted('ROLE_ADMIN') or object == user"),
        new Patch(security: "is_granted('ROLE_ADMIN') or object == user"),
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    // Adresse de connexion d'un apprenant (les comptes du back-office peuvent ne pas en avoir).
    // Elle ne sort ni par l'API ni par le connecteur : le pseudo suffit pour attribuer un contenu.
    #[ORM\Column(length: 180, unique: true, nullable: true)]
    private ?string $email = null;






    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email === null ? null : mb_strtolower(trim($email));
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // $this->plainPassword = null;
    }











    public function __toString(): string
    {
        return $this->username ?? 'User #' . $this->id;
    }
}
