<?php

declare(strict_types=1);

namespace App\Infrastructure\Favorite\Repository;

use App\Domain\Favorite\Repository\FavoriteRepositoryInterface;
use App\Entity\Favorite;
use App\Entity\Page;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineFavoriteRepository implements FavoriteRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?Favorite
    {
        return $this->em->find(Favorite::class, $id);
    }

    public function findByUserAndPage(User $user, Page $page): ?Favorite
    {
        return $this->em->getRepository(Favorite::class)
            ->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->andWhere('f.page = :page')
            ->setParameter('user', $user)
            ->setParameter('page', $page)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Favorite[]
     */
    public function findAllByUser(User $user): array
    {
        return $this->em->getRepository(Favorite::class)
            ->createQueryBuilder('f')
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

    public function isPageFavorite(User $user, Page $page): bool
    {
        return $this->findByUserAndPage($user, $page) !== null;
    }

    public function save(Favorite $favorite): void
    {
        $this->em->persist($favorite);
        $this->em->flush();
    }

    public function delete(Favorite $favorite): void
    {
        $this->em->remove($favorite);
        $this->em->flush();
    }
}
