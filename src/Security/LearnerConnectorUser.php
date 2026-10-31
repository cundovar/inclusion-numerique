<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\OAuthGrant;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

/** Connecteur apprenant : il lit les contenus accessibles au seul compte qui l'a autorisé. */
final class LearnerConnectorUser implements UserInterface
{
    public const ROLE = 'ROLE_AGENT_APPRENANT';

    public function __construct(private OAuthGrant $grant) {}

    public function getGrant(): OAuthGrant { return $this->grant; }

    public function getLearner(): User { return $this->grant->getUser(); }

    public function getRoles(): array { return [self::ROLE]; }

    public function eraseCredentials(): void {}

    public function getUserIdentifier(): string { return 'apprenant-'.$this->grant->getUser()->getId(); }
}
