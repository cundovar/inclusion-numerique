<?php

declare(strict_types=1);

namespace App\Domain\Page\Repository;

use App\Entity\Page;

interface PageRepositoryInterface
{
    public function findById(int $id): ?Page;

    /**
     * @return Page[]
     */
    public function findAll(): array;

    public function findBySlug(string $slug): ?Page;

    public function save(Page $page): void;

    public function delete(Page $page): void;
}
