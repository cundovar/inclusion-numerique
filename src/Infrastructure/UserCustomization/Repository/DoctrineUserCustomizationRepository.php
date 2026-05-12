<?php

declare(strict_types=1);

namespace App\Infrastructure\UserCustomization\Repository;

use App\Domain\UserCustomization\Repository\UserCustomizationRepositoryInterface;
use App\Entity\User;
use App\Entity\UserCustomization;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserCustomizationRepository implements UserCustomizationRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findByUser(User $user): ?UserCustomization
    {
        return $this->em->getRepository(UserCustomization::class)->findOneBy(['user' => $user]);
    }

    public function findOrCreateForUser(User $user): UserCustomization
    {
        $customization = $this->findByUser($user);

        if (!$customization) {
            $customization = new UserCustomization();
            $customization->setUser($user);
        }

        return $customization;
    }

    public function save(UserCustomization $customization): void
    {
        $this->em->persist($customization);
        $this->em->flush();
    }
}
