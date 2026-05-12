<?php

declare(strict_types=1);

namespace App\Domain\PositionMenu\Repository;

use App\Entity\PositionMenus;

interface PositionMenuRepositoryInterface
{
    public function findById(int $id): ?PositionMenus;
}
