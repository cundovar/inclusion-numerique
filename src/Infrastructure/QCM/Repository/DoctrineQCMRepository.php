<?php

declare(strict_types=1);

namespace App\Infrastructure\QCM\Repository;

use App\Domain\QCM\Repository\QCMRepositoryInterface;
use App\Entity\QCM;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineQCMRepository implements QCMRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?QCM
    {
        return $this->em->find(QCM::class, $id);
    }

    public function countAll(): int
    {
        return $this->em->getRepository(QCM::class)->count([]);
    }

    public function findAll(): array
    {
        return $this->em->getRepository(QCM::class)->findAll();
    }

    public function findByLanguage(string $language): array
    {
        return $this->em->getRepository(QCM::class)
            ->createQueryBuilder('q')
            ->join('q.languageQCM', 'l')
            ->where('l.name = :language')
            ->setParameter('language', $language)
            ->getQuery()
            ->getResult();
    }

    public function findByDifficulty(string $difficulty): array
    {
        return $this->em->getRepository(QCM::class)
            ->createQueryBuilder('q')
            ->join('q.niveauQCM', 'n')
            ->where('n.titre = :difficulty')
            ->setParameter('difficulty', $difficulty)
            ->getQuery()
            ->getResult();
    }

    public function findByLanguageAndDifficulty(string $language, string $difficulty): array
    {
        return $this->em->getRepository(QCM::class)
            ->createQueryBuilder('q')
            ->join('q.languageQCM', 'l')
            ->join('q.niveauQCM', 'n')
            ->where('l.name = :language')
            ->andWhere('n.titre = :difficulty')
            ->setParameter('language', $language)
            ->setParameter('difficulty', $difficulty)
            ->getQuery()
            ->getResult();
    }
}
