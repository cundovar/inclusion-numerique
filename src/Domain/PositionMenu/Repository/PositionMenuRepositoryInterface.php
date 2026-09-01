<?php

declare(strict_types=1);

namespace App\Domain\PositionMenu\Repository;

use App\Entity\PositionMenus;

interface PositionMenuRepositoryInterface
{
    public function findById(int $id): ?PositionMenus;

    public function findByPosition(string $position): ?PositionMenus;

    /** @return PositionMenus[] */
    public function findAll(): array;

    public function save(PositionMenus $position): void;

    public function remove(PositionMenus $position): void;
}
