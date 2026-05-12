<?php

declare(strict_types=1);

namespace App\Infrastructure\Menu\Repository;

use App\Domain\Menu\Repository\MenuRepositoryInterface;
use App\Entity\Menus;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineMenuRepository implements MenuRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?Menus
    {
        return $this->em->find(Menus::class, $id);
    }

    public function findAll(): array
    {
        return $this->em->getRepository(Menus::class)->findAll();
    }

    public function findByCriteria(array $criteria, array $orderBy = []): array
    {
        return $this->em->getRepository(Menus::class)->findBy($criteria, $orderBy);
    }

    public function save(Menus $menu): void
    {
        $this->em->persist($menu);
        $this->em->flush();
    }

    public function delete(Menus $menu): void
    {
        $this->em->remove($menu);
        $this->em->flush();
    }
}
