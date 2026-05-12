<?php

declare(strict_types=1);

namespace App\Domain\Seo\Repository;

use App\Entity\Seo;

interface SeoRepositoryInterface
{
    public function findByPage(string $page): ?Seo;
}
