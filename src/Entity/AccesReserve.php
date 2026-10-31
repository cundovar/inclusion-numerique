<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Contenu personnalisé : un cours, un parcours ou un QCM réservé à certains comptes.
 * Réservé, il n'est servi qu'à ses destinataires (et aux administrateurs) ; sans destinataire, il n'est servi à personne.
 * La classe qui utilise ce trait déclare la relation `$destinataires` (ManyToMany vers User, avec sa table de liaison).
 *
 * @property Collection<int, User> $destinataires
 */
trait AccesReserve
{
    #[ORM\Column(options: ['default' => false])]
    #[Groups(['page_content:read', 'qcm:read'])]
    private bool $reserve = false;

    public function isReserve(): bool
    {
        return $this->reserve;
    }

    public function setReserve(bool $reserve): static
    {
        $this->reserve = $reserve;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getDestinataires(): Collection
    {
        return $this->destinataires;
    }

    public function addDestinataire(User $user): static
    {
        if (!$this->destinataires->contains($user)) {
            $this->destinataires->add($user);
        }

        return $this;
    }

    public function removeDestinataire(User $user): static
    {
        $this->destinataires->removeElement($user);

        return $this;
    }

    public function isAccessibleTo(?User $user): bool
    {
        return !$this->reserve || ($user !== null && $this->destinataires->contains($user));
    }
}
