<?php

declare(strict_types=1);

namespace App\Application\Favorite\Handler;

use App\Application\Favorite\DTO\FavoriteDTO;
use App\Application\Favorite\Query\GetFavoriteByIdQuery;
use App\Domain\Favorite\Exception\FavoriteNotFoundException;
use App\Domain\Favorite\Exception\UnauthorizedFavoriteAccessException;
use App\Domain\Favorite\Repository\FavoriteRepositoryInterface;

final class GetFavoriteByIdHandler
{
    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository
    ) {}

    public function handle(GetFavoriteByIdQuery $query): FavoriteDTO
    {
        $favorite = $this->favoriteRepository->findById($query->favoriteId);

        if (!$favorite) {
            throw FavoriteNotFoundException::withId($query->favoriteId);
        }

        if ($favorite->getUser() !== $query->user) {
            throw UnauthorizedFavoriteAccessException::forFavorite(
                $query->favoriteId,
                $query->user->getId()
            );
        }

        return FavoriteDTO::fromEntity($favorite);
    }
}
