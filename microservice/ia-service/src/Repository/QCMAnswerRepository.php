<?php

namespace App\Repository;

use App\Entity\QCMAnswer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QCMAnswer>
 */
class QCMAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QCMAnswer::class);
    }

    public function findByUser(int $userId, int $limit = 50): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.question', 'q')
            ->leftJoin('q.session', 's')
            ->addSelect('q', 's')
            ->where('a.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('a.answeredAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getUserCorrectAnswersCount(int $userId): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.userId = :userId')
            ->andWhere('a.isCorrect = :correct')
            ->setParameter('userId', $userId)
            ->setParameter('correct', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getUserAnswersBySession(int $userId, int $sessionId): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.question', 'q')
            ->addSelect('q')
            ->where('a.userId = :userId')
            ->andWhere('q.session = :sessionId')
            ->setParameter('userId', $userId)
            ->setParameter('sessionId', $sessionId)
            ->orderBy('a.answeredAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getAnswerStatsByQuestion(int $questionId): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.selectedAnswer')
            ->addSelect('COUNT(a.id) as count')
            ->addSelect('SUM(CASE WHEN a.isCorrect = 1 THEN 1 ELSE 0 END) as correctCount')
            ->where('a.question = :questionId')
            ->setParameter('questionId', $questionId)
            ->groupBy('a.selectedAnswer')
            ->getQuery()
            ->getResult();
    }

    public function getAverageTimeByUser(int $userId): ?float
    {
        $result = $this->createQueryBuilder('a')
            ->select('AVG(a.timeSpent)')
            ->where('a.userId = :userId')
            ->andWhere('a.timeSpent IS NOT NULL')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? round($result, 2) : null;
    }

    public function getUserPerformanceByDifficulty(int $userId): array
    {
        return $this->createQueryBuilder('a')
            ->select('q.difficulty')
            ->addSelect('COUNT(a.id) as totalAnswers')
            ->addSelect('SUM(CASE WHEN a.isCorrect = 1 THEN 1 ELSE 0 END) as correctAnswers')
            ->addSelect('(SUM(CASE WHEN a.isCorrect = 1 THEN 1 ELSE 0 END) / COUNT(a.id) * 100) as successRate')
            ->leftJoin('a.question', 'q')
            ->where('a.userId = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('q.difficulty')
            ->getQuery()
            ->getResult();
    }
}