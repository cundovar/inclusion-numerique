<?php

declare(strict_types=1);

namespace App\Domain\LanguageQCM\Repository;

use App\Entity\LanguageQCM;

interface LanguageQCMRepositoryInterface
{
    public function findById(int $id): ?LanguageQCM;
}
