<?php

declare(strict_types=1);

namespace App\Infrastructure\Page\Repository;

use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePageRepository implements PageRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?Page
    {
        return $this->em->find(Page::class, $id);
    }

    public function findAll(): array
    {
        return $this->em->getRepository(Page::class)->findAll();
    }

    public function findBySlug(string $slug): ?Page
    {
        return $this->em->getRepository(Page::class)->findOneBy(['slug' => $slug]);
    }

    public function save(Page $page): void
    {
        $this->em->persist($page);
        $this->em->flush();
    }

    public function delete(Page $page): void
    {
        $this->em->remove($page);
        $this->em->flush();
    }
}
