<?php

declare(strict_types=1);

namespace App\Application\Favorite\Handler;

use App\Application\Favorite\Query\CheckFavoriteQuery;
use App\Domain\Favorite\Repository\FavoriteRepositoryInterface;
use App\Repository\PageRepository;

final class CheckFavoriteHandler
{
    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository,
        private PageRepository $pageRepository
    ) {}

    /**
     * @return array{isFavorite: bool, pageId: int}
     */
    public function handle(CheckFavoriteQuery $query): array
    {
        $page = $this->pageRepository->find($query->pageId);

        if (!$page) {
            throw new \InvalidArgumentException('Page non trouvée');
        }

        $isFavorite = $this->favoriteRepository->isPageFavorite($query->user, $page);

        return [
            'isFavorite' => $isFavorite,
            'pageId' => $query->pageId
        ];
    }
}
