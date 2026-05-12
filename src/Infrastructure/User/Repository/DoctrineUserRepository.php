<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Repository;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findByUsername(string $username): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
    }

    public function countAll(): int
    {
        return $this->em->getRepository(User::class)->count([]);
    }

    public function findLatest(int $limit): array
    {
        return $this->em->getRepository(User::class)->findBy([], ['id' => 'DESC'], $limit);
    }

    public function save(User $user): void
    {
        $this->em->persist($user);
        $this->em->flush();
    }
}
