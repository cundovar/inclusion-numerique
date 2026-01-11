<?php

declare(strict_types=1);

namespace App\Domain\Favorite\Repository;

use App\Entity\Favorite;
use App\Entity\Page;
use App\Entity\User;

interface FavoriteRepositoryInterface
{
    public function findById(int $id): ?Favorite;

    public function findByUserAndPage(User $user, Page $page): ?Favorite;

    /**
     * @return Favorite[]
     */
    public function findAllByUser(User $user): array;

    public function isPageFavorite(User $user, Page $page): bool;

    public function save(Favorite $favorite): void;

    public function delete(Favorite $favorite): void;
}
