<?php

declare(strict_types=1);

namespace App\Infrastructure\CourseMedia\Repository;

use App\Domain\CourseMedia\Repository\CourseMediaRepositoryInterface;
use App\Entity\CourseMedia;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineCourseMediaRepository implements CourseMediaRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?CourseMedia
    {
        return $this->em->find(CourseMedia::class, $id);
    }

    public function countAll(): int
    {
        return $this->em->getRepository(CourseMedia::class)->count([]);
    }

    public function findDueForReview(\DateTimeImmutable $today): array
    {
        return $this->em->getRepository(CourseMedia::class)->createQueryBuilder('m')
            ->where('m.nextReviewAt IS NOT NULL AND m.nextReviewAt <= :today')
            ->setParameter('today', $today->setTime(0, 0), 'date_immutable')
            ->orderBy('m.nextReviewAt')
            ->getQuery()
            ->getResult();
    }
}
