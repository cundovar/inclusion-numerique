<?php

namespace App\Repository;

use App\Entity\Favorite;
use App\Entity\User;
use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Favorite>
 */
class FavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favorite::class);
    }

    /**
     * Trouve tous les favoris d'un utilisateur
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->leftJoin('f.page', 'p')
            ->leftJoin('p.pageContents', 'pc')
            ->leftJoin('pc.category', 'c')
            ->addSelect('p', 'pc', 'c')
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si une page est en favori pour un utilisateur
     */
    public function isPageFavorite(User $user, Page $page): bool
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->andWhere('f.page = :page')
            ->setParameter('user', $user)
            ->setParameter('page', $page)
            ->getQuery()
            ->getOneOrNullResult() !== null;
    }

    /**
     * Trouve un favori spécifique par utilisateur et page
     */
    public function findByUserAndPage(User $user, Page $page): ?Favorite
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->andWhere('f.page = :page')
            ->setParameter('user', $user)
            ->setParameter('page', $page)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Compte le nombre de favoris d'un utilisateur
     */
    public function countByUser(User $user): int
    {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les favoris par catégorie pour un utilisateur
     */
    public function findByUserGroupedByCategory(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->select('c.name as category_name, COUNT(f.id) as count')
            ->leftJoin('f.page', 'p')
            ->leftJoin('p.pageContents', 'pc')
            ->leftJoin('pc.category', 'c')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->groupBy('c.id')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function save(Favorite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Favorite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}