<?php

declare(strict_types=1);

namespace App\Infrastructure\PositionMenu\Repository;

use App\Domain\PositionMenu\Repository\PositionMenuRepositoryInterface;
use App\Entity\PositionMenus;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePositionMenuRepository implements PositionMenuRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?PositionMenus
    {
        return $this->em->find(PositionMenus::class, $id);
    }

    public function findByPosition(string $position): ?PositionMenus
    {
        return $this->em->getRepository(PositionMenus::class)->findOneBy(['position' => $position]);
    }

    public function findAll(): array
    {
        return $this->em->getRepository(PositionMenus::class)->findAll();
    }

    public function save(PositionMenus $position): void
    {
        $this->em->persist($position);
        $this->em->flush();
    }

    public function remove(PositionMenus $position): void
    {
        $this->em->remove($position);
        $this->em->flush();
    }
}
