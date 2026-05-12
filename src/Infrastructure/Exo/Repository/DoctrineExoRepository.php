<?php

declare(strict_types=1);

namespace App\Infrastructure\Exo\Repository;

use App\Domain\Exo\Repository\ExoRepositoryInterface;
use App\Entity\Exo;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineExoRepository implements ExoRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?Exo
    {
        return $this->em->find(Exo::class, $id);
    }
}
