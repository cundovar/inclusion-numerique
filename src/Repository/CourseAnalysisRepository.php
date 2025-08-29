<?php

namespace App\Repository;

use App\Entity\CourseAnalysis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CourseAnalysisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseAnalysis::class);
    }

    public function findByPageContent(int $pageContentId): ?CourseAnalysis
    {
        return $this->createQueryBuilder('ca')
            ->andWhere('ca.pageContent = :pageContentId')
            ->setParameter('pageContentId', $pageContentId)
            ->orderBy('ca.analyzedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findRecentAnalyses(int $limit = 10): array
    {
        return $this->createQueryBuilder('ca')
            ->leftJoin('ca.pageContent', 'pc')
            ->addSelect('pc')
            ->orderBy('ca.analyzedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByQualityScore(float $minScore = 0, float $maxScore = 10): array
    {
        return $this->createQueryBuilder('ca')
            ->andWhere('ca.qualityScore BETWEEN :minScore AND :maxScore')
            ->setParameter('minScore', $minScore)
            ->setParameter('maxScore', $maxScore)
            ->orderBy('ca.qualityScore', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getAverageQualityScoreByCategory(): array
    {
        return $this->createQueryBuilder('ca')
            ->select('cat.name as categoryName, AVG(ca.qualityScore) as avgScore, COUNT(ca.id) as totalAnalyses')
            ->leftJoin('ca.pageContent', 'pc')
            ->leftJoin('pc.category', 'cat')
            ->groupBy('cat.id')
            ->orderBy('avgScore', 'DESC')
            ->getQuery()
            ->getResult();
    }
}