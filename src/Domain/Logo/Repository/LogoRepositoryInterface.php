<?php

declare(strict_types=1);

namespace App\Domain\Logo\Repository;

use App\Entity\Logo;

interface LogoRepositoryInterface
{
    public function findById(int $id): ?Logo;

    /**
     * @return Logo[]
     */
    public function findAll(): array;

    public function save(Logo $logo): void;

    public function remove(Logo $logo): void;
}
