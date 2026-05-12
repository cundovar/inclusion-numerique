<?php

declare(strict_types=1);

namespace App\Infrastructure\ExoBlock\Repository;

use App\Domain\ExoBlock\Repository\ExoBlockRepositoryInterface;
use App\Entity\ExoBlock;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineExoBlockRepository implements ExoBlockRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?ExoBlock
    {
        return $this->em->getRepository(ExoBlock::class)->find($id);
    }

    public function findAll(): array
    {
        return $this->em->getRepository(ExoBlock::class)->findAll();
    }

    public function save(ExoBlock $exoBlock): void
    {
        $this->em->persist($exoBlock);
        $this->em->flush();
    }

    public function remove(ExoBlock $exoBlock): void
    {
        $this->em->remove($exoBlock);
        $this->em->flush();
    }
}
