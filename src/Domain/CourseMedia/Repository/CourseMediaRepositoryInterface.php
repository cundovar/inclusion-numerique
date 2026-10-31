<?php

declare(strict_types=1);

namespace App\Domain\CourseMedia\Repository;

use App\Entity\CourseMedia;

interface CourseMediaRepositoryInterface
{
    public function findById(int $id): ?CourseMedia;

    public function countAll(): int;

    /**
     * Captures dont la date de prochaine vérification est atteinte.
     *
     * @return CourseMedia[]
     */
    public function findDueForReview(\DateTimeImmutable $today): array;
}
