<?php

declare(strict_types=1);

namespace App\Infrastructure\ChoicesQCM\Repository;

use App\Domain\ChoicesQCM\Repository\ChoicesQCMRepositoryInterface;
use App\Entity\ChoicesQCM;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineChoicesQCMRepository implements ChoicesQCMRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?ChoicesQCM
    {
        return $this->em->find(ChoicesQCM::class, $id);
    }
}
