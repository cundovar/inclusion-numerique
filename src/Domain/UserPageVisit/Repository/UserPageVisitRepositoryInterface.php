<?php

declare(strict_types=1);

namespace App\Domain\UserPageVisit\Repository;

use App\Entity\User;
use App\Entity\UserPageVisit;

interface UserPageVisitRepositoryInterface
{
    public function findById(int $id): ?UserPageVisit;

    public function save(UserPageVisit $visit): void;

    public function delete(UserPageVisit $visit): void;

    /**
     * @return UserPageVisit[]
     */
    public function findRecentVisitsByUser(User $user, int $limit = 20): array;

    /**
     * @return UserPageVisit[]
     */
    public function findLatest(int $limit): array;

    /**
     * @return UserPageVisit[]
     */
    public function findOldest(int $limit): array;

    public function clearByUser(User $user): int;

    public function countAll(): int;

    public function countByUser(User $user): int;

    public function countDistinctUsers(): int;

    /**
     * @return array<int, array{pageUrl: string, pageTitle: ?string, visitCount: numeric-string|int}>
     */
    public function getMostVisitedPagesByUser(User $user, int $limit = 10): array;

    /**
     * @return array<int, array{pageUrl: string, pageTitle: ?string, visitCount: numeric-string|int, avgTimeSpent: mixed}>
     */
    public function getGlobalMostVisitedPages(int $limit = 20): array;

    /**
     * @return array<int, array{userId: mixed, username: mixed, visitCount: mixed, totalTime: mixed}>
     */
    public function getMostActiveUsers(int $limit = 10): array;

    /**
     * @return array<int, array{date: mixed, visitCount: mixed}>
     */
    public function getVisitsPerDay(int $days = 7): array;

    /**
     * @return array<int, array{pageUrl: string, pageTitle: ?string, avgTimeSpent: mixed, totalTimeSpent: mixed, visitCount: mixed}>
     */
    public function getPagesWithMostTime(int $limit = 20): array;
}
