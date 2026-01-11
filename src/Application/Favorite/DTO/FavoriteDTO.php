<?php

declare(strict_types=1);

namespace App\Application\Favorite\DTO;

use App\Entity\Favorite;

final class FavoriteDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly array $category,
        public readonly array $page,
        public readonly string $createdAt
    ) {}

    public static function fromEntity(Favorite $favorite): self
    {
        $pageContent = $favorite->getPageContent();

        return new self(
            id: $favorite->getId(),
            title: $pageContent?->getTitle() ?? '',
            category: [
                'id' => $pageContent?->getCategory()?->getId(),
                'name' => $pageContent?->getCategory()?->getName()
            ],
            page: [
                'id' => $favorite->getPage()->getId(),
                'slug' => $favorite->getPage()->getSlug()
            ],
            createdAt: $favorite->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category,
            'page' => $this->page,
            'createdAt' => $this->createdAt
        ];
    }
}
