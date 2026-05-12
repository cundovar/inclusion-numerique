<?php

declare(strict_types=1);

namespace App\Infrastructure\ExoMenu\Repository;

use App\Domain\ExoMenu\Repository\ExoMenuRepositoryInterface;
use App\Entity\ExoMenu;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineExoMenuRepository implements ExoMenuRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?ExoMenu
    {
        return $this->em->find(ExoMenu::class, $id);
    }
}
