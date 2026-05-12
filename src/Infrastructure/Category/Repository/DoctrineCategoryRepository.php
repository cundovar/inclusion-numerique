<?php

declare(strict_types=1);

namespace App\Infrastructure\Category\Repository;

use App\Domain\Category\Repository\CategoryRepositoryInterface;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineCategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?Category
    {
        return $this->em->find(Category::class, $id);
    }

    public function findAll(): array
    {
        return $this->em->getRepository(Category::class)->findAll();
    }

    public function findByName(string $name): ?Category
    {
        return $this->em->getRepository(Category::class)->findOneBy(['name' => $name]);
    }

    public function save(Category $category): void
    {
        $this->em->persist($category);
        $this->em->flush();
    }

    public function delete(Category $category): void
    {
        $this->em->remove($category);
        $this->em->flush();
    }
}
