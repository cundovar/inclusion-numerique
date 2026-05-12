<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Entity\User;

interface UserRepositoryInterface
{
    public function findByUsername(string $username): ?User;

    public function countAll(): int;

    /**
     * @return User[]
     */
    public function findLatest(int $limit): array;

    public function save(User $user): void;
}
