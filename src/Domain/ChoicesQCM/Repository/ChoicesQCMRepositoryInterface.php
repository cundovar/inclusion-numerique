<?php

declare(strict_types=1);

namespace App\Domain\ChoicesQCM\Repository;

use App\Entity\ChoicesQCM;

interface ChoicesQCMRepositoryInterface
{
    public function findById(int $id): ?ChoicesQCM;
}
