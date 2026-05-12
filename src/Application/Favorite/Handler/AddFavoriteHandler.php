<?php

declare(strict_types=1);

namespace App\Application\Favorite\Handler;

use App\Application\Favorite\Command\AddFavoriteCommand;
use App\Application\Favorite\DTO\FavoriteDTO;
use App\Domain\Favorite\Exception\FavoriteAlreadyExistsException;
use App\Domain\Favorite\Repository\FavoriteRepositoryInterface;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Entity\Favorite;

final class AddFavoriteHandler
{
    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository,
        private PageRepositoryInterface $pageRepository
    ) {}

    public function handle(AddFavoriteCommand $command): FavoriteDTO
    {
        $page = $this->pageRepository->findById($command->pageId);

        if (!$page) {
            throw new \InvalidArgumentException('Page non trouvée');
        }

        // Vérifier si le favori existe déjà
        $existing = $this->favoriteRepository->findByUserAndPage($command->user, $page);
        if ($existing) {
            throw FavoriteAlreadyExistsException::forUserAndPage(
                $command->user->getId(),
                $command->pageId
            );
        }

        $favorite = new Favorite();
        $favorite->setUser($command->user);
        $favorite->setPage($page);

        $this->favoriteRepository->save($favorite);

        return FavoriteDTO::fromEntity($favorite);
    }
}
