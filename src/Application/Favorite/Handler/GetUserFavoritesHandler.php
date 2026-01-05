<?php

declare(strict_types=1);

namespace App\Application\Favorite\Handler;

use App\Application\Favorite\DTO\FavoriteDTO;
use App\Application\Favorite\Query\GetUserFavoritesQuery;
use App\Domain\Favorite\Repository\FavoriteRepositoryInterface;

final class GetUserFavoritesHandler
{
    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository
    ) {}

    /**
     * @return FavoriteDTO[]
     */
    public function handle(GetUserFavoritesQuery $query): array
    {
        $favorites = $this->favoriteRepository->findAllByUser($query->user);

        return array_map(
            fn($favorite) => FavoriteDTO::fromEntity($favorite),
            $favorites
        );
    }
}
