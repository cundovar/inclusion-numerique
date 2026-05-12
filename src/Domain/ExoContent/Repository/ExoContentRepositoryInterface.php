<?php

declare(strict_types=1);

namespace App\Domain\ExoContent\Repository;

use App\Entity\Category;
use App\Entity\Exo;
use App\Entity\ExoContent;

interface ExoContentRepositoryInterface
{
    public function findById(int $id): ?ExoContent;

    public function countAll(): int;

    /**
     * @return ExoContent[]
     */
    public function findAll(): array;

    /**
     * @return ExoContent[]
     */
    public function findByExo(Exo $exo): array;

    /**
     * @return ExoContent[]
     */
    public function findByCategory(Category $category): array;

    public function save(ExoContent $exoContent): void;

    public function delete(ExoContent $exoContent): void;
}
