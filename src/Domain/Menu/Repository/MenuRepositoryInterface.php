<?php

declare(strict_types=1);

namespace App\Domain\Menu\Repository;

use App\Entity\Menus;

interface MenuRepositoryInterface
{
    public function findById(int $id): ?Menus;

    /**
     * @return Menus[]
     */
    public function findAll(): array;

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, string> $orderBy
     *
     * @return Menus[]
     */
    public function findByCriteria(array $criteria, array $orderBy = []): array;

    public function save(Menus $menu): void;

    public function delete(Menus $menu): void;
}
