<?php

declare(strict_types=1);

namespace App\Domain\SuperMenu\Repository;

use App\Entity\SuperMenu;

interface SuperMenuRepositoryInterface
{
    public function findById(int $id): ?SuperMenu;

    /**
     * @return SuperMenu[]
     */
    public function findAll(): array;

    public function save(SuperMenu $superMenu): void;

    public function remove(SuperMenu $superMenu): void;
}
