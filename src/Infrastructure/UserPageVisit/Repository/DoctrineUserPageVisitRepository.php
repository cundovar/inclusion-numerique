<?php

declare(strict_types=1);

namespace App\Infrastructure\UserPageVisit\Repository;

use App\Domain\UserPageVisit\Repository\UserPageVisitRepositoryInterface;
use App\Entity\User;
use App\Entity\UserPageVisit;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserPageVisitRepository implements UserPageVisitRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?UserPageVisit
    {
        return $this->em->find(UserPageVisit::class, $id);
    }

    public function save(UserPageVisit $visit): void
    {
        $this->em->persist($visit);
        $this->em->flush();
    }

    public function delete(UserPageVisit $visit): void
    {
        $this->em->remove($visit);
        $this->em->flush();
    }

    public function findRecentVisitsByUser(User $user, int $limit = 20): array
    {
        return $this->em->getRepository(UserPageVisit::class)
            ->createQueryBuilder('v')
            ->andWhere('v.user = :user')
            ->setParameter('user', $user)
            ->orderBy('v.visitedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLatest(int $limit): array
    {
        return $this->em->getRepository(UserPageVisit::class)
            ->createQueryBuilder('v')
            ->orderBy('v.visitedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findOldest(int $limit): array
    {
        return $this->em->getRepository(UserPageVisit::class)
            ->createQueryBuilder('v')
            ->orderBy('v.visitedAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function clearByUser(User $user): int
    {
        return $this->em->createQuery(
            'DELETE FROM App\Entity\UserPageVisit v WHERE v.user = :user'
        )
            ->setParameter('user', $user)
            ->execute();
    }

    public function countAll(): int
    {
        return $this->em->getRepository(UserPageVisit::class)->count([]);
    }

    public function countByUser(User $user): int
    {
        return $this->em->getRepository(UserPageVisit::class)->count(['user' => $user]);
    }

    public function countDistinctUsers(): int
    {
        return (int) $this->em->getRepository(UserPageVisit::class)
            ->createQueryBuilder('v')
            ->select('COUNT(DISTINCT u.id)')
            ->join('v.user', 'u')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getMostVisitedPagesByUser(User $user, int $limit = 10): array
    {
        return $this->em->getRepository(UserPageVisit::class)
            ->createQueryBuilder('v')
            ->select('v.pageUrl, v.pageTitle, COUNT(v.id) as visitCount')
            ->andWhere('v.user = :user')
            ->setParameter('user', $user)
            ->groupBy('v.pageUrl, v.pageTitle')
            ->orderBy('visitCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getGlobalMostVisitedPages(int $limit = 20): array
    {
        return $this->em->getRepository(UserPageVisit::class)
            ->createQueryBuilder('v')
            ->select('v.pageUrl, v.pageTitle, COUNT(v.id) as visitCount, AVG(v.timeSpent) as avgTimeSpent')
            ->groupBy('v.pageUrl, v.pageTitle')
            ->orderBy('visitCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getMostActiveUsers(int $limit = 10): array
    {
        return $this->em->getRepository(UserPageVisit::class)
            ->createQueryBuilder('v')
            ->select('u.id as userId, u.username, COUNT(v.id) as visitCount, SUM(v.timeSpent) as totalTime')
            ->join('v.user', 'u')
            ->groupBy('u.id, u.username')
            ->orderBy('visitCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getVisitsPerDay(int $days = 7): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        $conn = $this->em->getConnection();
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

    public function getPagesWithMostTime(int $limit = 20): array
    {
        return $this->em->getRepository(UserPageVisit::class)
            ->createQueryBuilder('v')
            ->select('v.pageUrl, v.pageTitle, AVG(v.timeSpent) as avgTimeSpent, SUM(v.timeSpent) as totalTimeSpent, COUNT(v.id) as visitCount')
            ->where('v.timeSpent IS NOT NULL')
            ->groupBy('v.pageUrl, v.pageTitle')
            ->orderBy('totalTimeSpent', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
