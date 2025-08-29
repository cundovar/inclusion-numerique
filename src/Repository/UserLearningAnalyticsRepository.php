<?php

namespace App\Repository;

use App\Entity\UserLearningAnalytics;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserLearningAnalyticsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserLearningAnalytics::class);
    }

    public function findByUser(int $userId, ?string $eventType = null): array
    {
        $qb = $this->createQueryBuilder('ula')
            ->andWhere('ula.user = :userId')
            ->setParameter('userId', $userId);

        if ($eventType) {
            $qb->andWhere('ula.eventType = :eventType')
               ->setParameter('eventType', $eventType);
        }

        return $qb->orderBy('ula.eventDate', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    public function getUserLearningStats(int $userId): array
    {
        return $this->createQueryBuilder('ula')
            ->select('
                COUNT(ula.id) as totalEvents,
                SUM(ula.timeSpent) as totalTimeSpent,
                AVG(ula.comprehensionScore) as avgComprehension,
                ula.eventType,
                COUNT(DISTINCT ula.pageContent) as uniqueContentViewed
            ')
            ->andWhere('ula.user = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('ula.eventType')
            ->getQuery()
            ->getResult();
    }

    public function findLearningDifficulties(int $userId): array
    {
        return $this->createQueryBuilder('ula')
            ->select('ula.difficultyConcepts, pc.title, COUNT(ula.id) as frequency')
            ->leftJoin('ula.pageContent', 'pc')
            ->andWhere('ula.user = :userId')
            ->andWhere('ula.difficultyConcepts IS NOT NULL')
            ->setParameter('userId', $userId)
            ->groupBy('pc.id')
            ->orderBy('frequency', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getContentPopularity(): array
    {
        return $this->createQueryBuilder('ula')
            ->select('
                pc.title,
                COUNT(ula.id) as viewCount,
                AVG(ula.timeSpent) as avgTimeSpent,
                AVG(ula.comprehensionScore) as avgComprehension
            ')
            ->leftJoin('ula.pageContent', 'pc')
            ->andWhere('ula.eventType = :eventType')
            ->setParameter('eventType', 'view')
            ->groupBy('pc.id')
            ->orderBy('viewCount', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    public function getLearningPathProgress(int $learningPathId): array
    {
        return $this->createQueryBuilder('ula')
            ->select('
                u.username,
                COUNT(CASE WHEN ula.eventType = \'complete\' THEN 1 END) as completedLessons,
                AVG(ula.comprehensionScore) as avgScore,
                SUM(ula.timeSpent) as totalTime
            ')
            ->leftJoin('ula.user', 'u')
            ->andWhere('ula.learningPath = :pathId')
            ->setParameter('pathId', $learningPathId)
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
    }
}