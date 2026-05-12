<?php

declare(strict_types=1);

namespace App\Infrastructure\NiveauQCM\Repository;

use App\Domain\NiveauQCM\Repository\NiveauQCMRepositoryInterface;
use App\Entity\NiveauQCM;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineNiveauQCMRepository implements NiveauQCMRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?NiveauQCM
    {
        return $this->em->find(NiveauQCM::class, $id);
    }
}
