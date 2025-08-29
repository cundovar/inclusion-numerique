<?php

namespace App\Repository;

use App\Entity\LearningPath;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LearningPathRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LearningPath::class);
    }

    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('lp')
            ->andWhere('lp.targetUser = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('lp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveByCategory(int $categoryId): array
    {
        return $this->createQueryBuilder('lp')
            ->andWhere('lp.category = :categoryId')
            ->andWhere('lp.status = :status')
            ->setParameter('categoryId', $categoryId)
            ->setParameter('status', 'active')
            ->orderBy('lp.difficultyLevel', 'ASC')
            ->addOrderBy('lp.estimatedDuration', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByDifficultyLevel(string $level): array
    {
        return $this->createQueryBuilder('lp')
            ->andWhere('lp.difficultyLevel = :level')
            ->andWhere('lp.status = :status')
            ->setParameter('level', $level)
            ->setParameter('status', 'active')
            ->orderBy('lp.estimatedDuration', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getStatsByCreator(int $userId): array
    {
        return $this->createQueryBuilder('lp')
            ->select('lp.status, COUNT(lp.id) as count')
            ->andWhere('lp.createdBy = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('lp.status')
            ->getQuery()
            ->getResult();
    }
}