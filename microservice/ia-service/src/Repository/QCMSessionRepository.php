<?php

namespace App\Repository;

use App\Entity\QCMSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QCMSession>
 */
class QCMSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QCMSession::class);
    }

    public function findByUser(int $userId, int $limit = 10): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByCategory(int $categoryId, int $limit = 10): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.categoryId = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findCompletedSessions(?int $userId = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.status = :status')
            ->setParameter('status', 'completed')
            ->orderBy('s.completedAt', 'DESC');

        if ($userId !== null) {
            $qb->andWhere('s.userId = :userId')
               ->setParameter('userId', $userId);
        }

        return $qb->getQuery()->getResult();
    }

    public function getAverageScoreByCategory(int $categoryId): ?float
    {
        $result = $this->createQueryBuilder('s')
            ->select('AVG(s.score / s.totalQuestions * 100) as avgScore')
            ->where('s.categoryId = :categoryId')
            ->andWhere('s.status = :status')
            ->setParameter('categoryId', $categoryId)
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? round($result, 2) : null;
    }

    public function getUserStats(int $userId): array
    {
        $completedSessions = $this->createQueryBuilder('s')
            ->select('COUNT(s.id) as totalSessions')
            ->addSelect('AVG(s.score / s.totalQuestions * 100) as avgScore')
            ->addSelect('MAX(s.score / s.totalQuestions * 100) as bestScore')
            ->where('s.userId = :userId')
            ->andWhere('s.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleResult();

        return [
            'totalSessions' => (int) $completedSessions['totalSessions'],
            'avgScore' => $completedSessions['avgScore'] ? round($completedSessions['avgScore'], 2) : 0,
            'bestScore' => $completedSessions['bestScore'] ? round($completedSessions['bestScore'], 2) : 0
        ];
    }
}