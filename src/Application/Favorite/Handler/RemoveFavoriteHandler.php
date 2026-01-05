<?php

declare(strict_types=1);

namespace App\Application\Favorite\Handler;

use App\Application\Favorite\Command\RemoveFavoriteCommand;
use App\Domain\Favorite\Exception\FavoriteNotFoundException;
use App\Domain\Favorite\Exception\UnauthorizedFavoriteAccessException;
use App\Domain\Favorite\Repository\FavoriteRepositoryInterface;

final class RemoveFavoriteHandler
{
    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository
    ) {}

    public function handle(RemoveFavoriteCommand $command): void
    {
        $favorite = $this->favoriteRepository->findById($command->favoriteId);

        if (!$favorite) {
            throw FavoriteNotFoundException::withId($command->favoriteId);
        }

        if ($favorite->getUser() !== $command->user) {
            throw UnauthorizedFavoriteAccessException::forFavorite(
                $command->favoriteId,
                $command->user->getId()
            );
        }

        $this->favoriteRepository->delete($favorite);
    }
}
