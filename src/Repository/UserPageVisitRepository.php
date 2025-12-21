<?php

namespace App\Repository;

use App\Entity\UserPageVisit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserPageVisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPageVisit::class);
    }

    public function findRecentVisitsByUser($userId, $limit = 20)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('v.visitedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getMostVisitedPages($userId, $limit = 10)
    {
        return $this->createQueryBuilder('v')
            ->select('v.pageUrl, v.pageTitle, COUNT(v.id) as visitCount')
            ->andWhere('v.user = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('v.pageUrl, v.pageTitle')
            ->orderBy('visitCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques globales : pages les plus visitées (tous utilisateurs)
     */
    public function getGlobalMostVisitedPages($limit = 20)
    {
        return $this->createQueryBuilder('v')
            ->select('v.pageUrl, v.pageTitle, COUNT(v.id) as visitCount, AVG(v.timeSpent) as avgTimeSpent')
            ->groupBy('v.pageUrl, v.pageTitle')
            ->orderBy('visitCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques par utilisateur : utilisateurs les plus actifs
     */
    public function getMostActiveUsers($limit = 10)
    {
        return $this->createQueryBuilder('v')
            ->select('u.id as userId, u.username, COUNT(v.id) as visitCount, SUM(v.timeSpent) as totalTime')
            ->join('v.user', 'u')
            ->groupBy('u.id, u.username')
            ->orderBy('visitCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques temporelles : visites par jour (7 derniers jours)
     */
    public function getVisitsPerDay($days = 7)
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT DATE(visited_at) as date, COUNT(id) as visitCount
            FROM appy_UserPageVisit
            WHERE visited_at >= :startDate
            GROUP BY DATE(visited_at)
            ORDER BY date ASC
        ';

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['startDate' => $date->format('Y-m-d H:i:s')]);

        return $result->fetchAllAssociative();
    }

    /**
     * Statistiques de durée : pages avec le plus de temps passé
     */
    public function getPagesWithMostTime($limit = 20)
    {
        return $this->createQueryBuilder('v')
            ->select('v.pageUrl, v.pageTitle, AVG(v.timeSpent) as avgTimeSpent, SUM(v.timeSpent) as totalTimeSpent, COUNT(v.id) as visitCount')
            ->where('v.timeSpent IS NOT NULL')
            ->groupBy('v.pageUrl, v.pageTitle')
            ->orderBy('totalTimeSpent', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
