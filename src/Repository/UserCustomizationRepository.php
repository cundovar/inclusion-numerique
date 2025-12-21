<?php

namespace App\Repository;

use App\Entity\UserCustomization;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserCustomization>
 */
class UserCustomizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCustomization::class);
    }

    /**
     * Récupérer la personnalisation d'un utilisateur
     */
    public function findByUser(User $user): ?UserCustomization
    {
        return $this->findOneBy(['user' => $user]);
    }

    /**
     * Créer ou récupérer la personnalisation d'un utilisateur
     */
    public function findOrCreateForUser(User $user): UserCustomization
    {
        $customization = $this->findByUser($user);

        if (!$customization) {
            $customization = new UserCustomization();
            $customization->setUser($user);
        }

        return $customization;
    }
}
