<?php

declare(strict_types=1);

namespace App\Application\Favorite\Handler;

use App\Application\Favorite\Command\ToggleFavoriteCommand;
use App\Domain\Favorite\Repository\FavoriteRepositoryInterface;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Entity\Favorite;

final class ToggleFavoriteHandler
{
    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository,
        private PageRepositoryInterface $pageRepository
    ) {}

    /**
     * @return array{action: string, message: string, isFavorite: bool, favoriteId?: int}
     */
    public function handle(ToggleFavoriteCommand $command): array
    {
        $page = $this->pageRepository->findById($command->pageId);

        if (!$page) {
            throw new \InvalidArgumentException('Page non trouvée');
        }

        $favorite = $this->favoriteRepository->findByUserAndPage($command->user, $page);

        if ($favorite) {
            // Supprimer le favori
            $this->favoriteRepository->delete($favorite);

            return [
                'action' => 'removed',
                'message' => 'Retiré des favoris',
                'isFavorite' => false
            ];
        }

        // Ajouter le favori
        $favorite = new Favorite();
        $favorite->setUser($command->user);
        $favorite->setPage($page);

        $this->favoriteRepository->save($favorite);

        return [
            'action' => 'added',
            'message' => 'Ajouté aux favoris',
            'isFavorite' => true,
            'favoriteId' => $favorite->getId()
        ];
    }
}
