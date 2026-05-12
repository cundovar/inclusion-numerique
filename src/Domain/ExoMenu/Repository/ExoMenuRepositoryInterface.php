<?php

declare(strict_types=1);

namespace App\Domain\ExoMenu\Repository;

use App\Entity\ExoMenu;

interface ExoMenuRepositoryInterface
{
    public function findById(int $id): ?ExoMenu;
}
