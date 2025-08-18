<?php

namespace App\Repository;

use App\Entity\QCMQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QCMQuestion>
 */
class QCMQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QCMQuestion::class);
    }

    public function findBySession(int $sessionId): array
    {
        return $this->createQueryBuilder('q')
            ->leftJoin('q.userAnswers', 'a')
            ->addSelect('a')
            ->where('q.session = :sessionId')
            ->setParameter('sessionId', $sessionId)
            ->orderBy('q.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByDifficulty(string $difficulty, ?int $categoryId = null, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('q')
            ->where('q.difficulty = :difficulty')
            ->setParameter('difficulty', $difficulty)
            ->setMaxResults($limit);

        if ($categoryId !== null) {
            $qb->leftJoin('q.session', 's')
               ->andWhere('s.categoryId = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByTopic(string $topic, int $limit = 10): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.topic LIKE :topic')
            ->setParameter('topic', '%' . $topic . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findRandomQuestionsByContent(array $pageContentIds, int $limit = 10): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.pageContentId IN (:pageContentIds)')
            ->setParameter('pageContentIds', $pageContentIds)
            ->orderBy('RAND()')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getQuestionStats(): array
    {
        return $this->createQueryBuilder('q')
            ->select('q.difficulty')
            ->addSelect('COUNT(q.id) as count')
            ->groupBy('q.difficulty')
            ->getQuery()
            ->getResult();
    }

    public function getMostMissedQuestions(int $limit = 10): array
    {
        return $this->createQueryBuilder('q')
            ->select('q')
            ->addSelect('COUNT(a.id) as totalAnswers')
            ->addSelect('SUM(CASE WHEN a.isCorrect = 0 THEN 1 ELSE 0 END) as wrongAnswers')
            ->addSelect('(SUM(CASE WHEN a.isCorrect = 0 THEN 1 ELSE 0 END) / COUNT(a.id) * 100) as errorRate')
            ->leftJoin('q.userAnswers', 'a')
            ->groupBy('q.id')
            ->having('COUNT(a.id) > 0')
            ->orderBy('errorRate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}