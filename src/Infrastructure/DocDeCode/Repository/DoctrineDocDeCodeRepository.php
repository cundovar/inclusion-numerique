<?php

declare(strict_types=1);

namespace App\Infrastructure\DocDeCode\Repository;

use App\Domain\DocDeCode\Repository\DocDeCodeRepositoryInterface;
use App\Entity\DocDeCode;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineDocDeCodeRepository implements DocDeCodeRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?DocDeCode
    {
        return $this->em->getRepository(DocDeCode::class)->find($id);
    }

    public function findAll(): array
    {
        return $this->em->getRepository(DocDeCode::class)->findAll();
    }

    public function save(DocDeCode $docDeCode): void
    {
        $this->em->persist($docDeCode);
        $this->em->flush();
    }

    public function remove(DocDeCode $docDeCode): void
    {
        $this->em->remove($docDeCode);
        $this->em->flush();
    }
}
