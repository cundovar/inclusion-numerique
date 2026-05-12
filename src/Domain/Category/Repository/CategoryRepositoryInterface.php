<?php

declare(strict_types=1);

namespace App\Domain\Category\Repository;

use App\Entity\Category;

interface CategoryRepositoryInterface
{
    public function findById(int $id): ?Category;

    /**
     * @return Category[]
     */
    public function findAll(): array;

    public function findByName(string $name): ?Category;

    public function save(Category $category): void;

    public function delete(Category $category): void;
}
