<?php

declare(strict_types=1);

namespace App\Infrastructure\ExoContent\Repository;

use App\Domain\ExoContent\Repository\ExoContentRepositoryInterface;
use App\Entity\Category;
use App\Entity\Exo;
use App\Entity\ExoContent;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineExoContentRepository implements ExoContentRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?ExoContent
    {
        return $this->em->find(ExoContent::class, $id);
    }

    public function countAll(): int
    {
        return $this->em->getRepository(ExoContent::class)->count([]);
    }

    public function findAll(): array
    {
        return $this->em->getRepository(ExoContent::class)->findAll();
    }

    public function findByExo(Exo $exo): array
    {
        return $this->em->getRepository(ExoContent::class)->findBy(['exo' => $exo]);
    }

    public function findByCategory(Category $category): array
    {
        return $this->em->getRepository(ExoContent::class)->findBy(['category' => $category]);
    }

    public function save(ExoContent $exoContent): void
    {
        $this->em->persist($exoContent);
        $this->em->flush();
    }

    public function delete(ExoContent $exoContent): void
    {
        $this->em->remove($exoContent);
        $this->em->flush();
    }
}
