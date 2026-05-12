<?php

declare(strict_types=1);

namespace App\Domain\PageBlock\Repository;

use App\Entity\PageBlock;

interface PageBlockRepositoryInterface
{
    public function findById(int $id): ?PageBlock;

    /**
     * @return PageBlock[]
     */
    public function findAll(): array;

    public function save(PageBlock $pageBlock): void;

    public function remove(PageBlock $pageBlock): void;
}
