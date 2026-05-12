<?php

declare(strict_types=1);

namespace App\Domain\Exo\Repository;

use App\Entity\Exo;

interface ExoRepositoryInterface
{
    public function findById(int $id): ?Exo;
}
