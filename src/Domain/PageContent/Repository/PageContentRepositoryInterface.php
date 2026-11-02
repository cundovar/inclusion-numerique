<?php

declare(strict_types=1);

namespace App\Domain\PageContent\Repository;

use App\Entity\Category;
use App\Entity\Page;
use App\Entity\PageContent;

interface PageContentRepositoryInterface
{
    public function findById(int $id): ?PageContent;

    public function countAll(): int;

    /**
     * @return PageContent[]
     */
    public function findAll(): array;

    /**
     * @return PageContent[]
     */
    public function findByPage(Page $page): array;

    /**
     * @return PageContent[]
     */
    public function findByCategory(Category $category): array;

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, string> $orderBy
     * @return PageContent[]
     */
    public function findByCriteria(array $criteria, array $orderBy = []): array;

    /**
     * Cours dont le contenu contient le marqueur [capture:ID].
     *
     * @return PageContent[]
     */
    public function findUsingCapture(int $captureId): array;

    /**
     * @param list<string> $statuses
     * @return PageContent[]
     */
    public function findByStatuses(array $statuses): array;

    public function save(PageContent $pageContent): void;

    public function delete(PageContent $pageContent): void;
}
