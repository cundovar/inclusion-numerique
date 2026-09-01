<?php

declare(strict_types=1);

namespace App\Infrastructure\SuperMenu\Repository;

use App\Domain\SuperMenu\Repository\SuperMenuRepositoryInterface;
use App\Entity\SuperMenu;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineSuperMenuRepository implements SuperMenuRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?SuperMenu
    {
        return $this->em->getRepository(SuperMenu::class)->find($id);
    }

    public function findByName(string $name): ?SuperMenu
    {
        return $this->em->getRepository(SuperMenu::class)->findOneBy(['name' => $name]);
    }

    public function findAll(): array
    {
        return $this->em->getRepository(SuperMenu::class)->findAll();
    }

    public function save(SuperMenu $superMenu): void
    {
        $this->em->persist($superMenu);
        $this->em->flush();
    }

    public function remove(SuperMenu $superMenu): void
    {
        $this->em->remove($superMenu);
        $this->em->flush();
    }
}
