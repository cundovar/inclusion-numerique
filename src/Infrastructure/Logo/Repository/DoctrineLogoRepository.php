<?php

declare(strict_types=1);

namespace App\Infrastructure\Logo\Repository;

use App\Domain\Logo\Repository\LogoRepositoryInterface;
use App\Entity\Logo;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineLogoRepository implements LogoRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?Logo
    {
        return $this->em->getRepository(Logo::class)->find($id);
    }

    public function findAll(): array
    {
        return $this->em->getRepository(Logo::class)->findAll();
    }

    public function save(Logo $logo): void
    {
        $this->em->persist($logo);
        $this->em->flush();
    }

    public function remove(Logo $logo): void
    {
        $this->em->remove($logo);
        $this->em->flush();
    }
}
