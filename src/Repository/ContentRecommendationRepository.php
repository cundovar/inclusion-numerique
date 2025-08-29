<?php

namespace App\Repository;

use App\Entity\ContentRecommendation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ContentRecommendationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContentRecommendation::class);
    }

    public function findByPageContent(int $pageContentId, ?string $status = null): array
    {
        $qb = $this->createQueryBuilder('cr')
            ->andWhere('cr.pageContent = :pageContentId')
            ->setParameter('pageContentId', $pageContentId);

        if ($status) {
            $qb->andWhere('cr.status = :status')
               ->setParameter('status', $status);
        }

        return $qb->orderBy('cr.priority', 'ASC')
                  ->addOrderBy('cr.createdAt', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    public function findHighPriorityPending(int $limit = 20): array
    {
        return $this->createQueryBuilder('cr')
            ->andWhere('cr.status = :status')
            ->andWhere('cr.priority = :priority')
            ->setParameter('status', 'pending')
            ->setParameter('priority', 'high')
            ->leftJoin('cr.pageContent', 'pc')
            ->addSelect('pc')
            ->orderBy('cr.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByStatus(): array
    {
        return $this->createQueryBuilder('cr')
            ->select('cr.status, COUNT(cr.id) as count')
            ->groupBy('cr.status')
            ->getQuery()
            ->getResult();
    }

    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('cr')
            ->andWhere('cr.type = :type')
            ->setParameter('type', $type)
            ->orderBy('cr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}