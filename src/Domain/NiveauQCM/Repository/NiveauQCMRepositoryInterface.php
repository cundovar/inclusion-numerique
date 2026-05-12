<?php

declare(strict_types=1);

namespace App\Domain\NiveauQCM\Repository;

use App\Entity\NiveauQCM;

interface NiveauQCMRepositoryInterface
{
    public function findById(int $id): ?NiveauQCM;
}
