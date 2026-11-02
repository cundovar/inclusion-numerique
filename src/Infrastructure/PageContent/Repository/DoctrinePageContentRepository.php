<?php

declare(strict_types=1);

namespace App\Infrastructure\PageContent\Repository;

use App\Domain\PageContent\Repository\PageContentRepositoryInterface;
use App\Entity\Category;
use App\Entity\Page;
use App\Entity\PageContent;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePageContentRepository implements PageContentRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?PageContent
    {
        return $this->em->find(PageContent::class, $id);
    }

    public function countAll(): int
    {
        return $this->em->getRepository(PageContent::class)->count([]);
    }

    public function findAll(): array
    {
        return $this->em->getRepository(PageContent::class)->findAll();
    }

    public function findByPage(Page $page): array
    {
        return $this->em->getRepository(PageContent::class)->findBy(['page' => $page]);
    }

    public function findByCategory(Category $category): array
    {
        return $this->em->getRepository(PageContent::class)->findBy(['category' => $category]);
    }

    public function findByCriteria(array $criteria, array $orderBy = []): array
    {
        return $this->em->getRepository(PageContent::class)->findBy($criteria, $orderBy);
    }

    public function findUsingCapture(int $captureId): array
    {
        $marker = '%'.addcslashes(sprintf('[capture:%d]', $captureId), '%_').'%';

        return $this->em->getRepository(PageContent::class)->createQueryBuilder('c')
            ->where('c.code LIKE :marker OR c.content LIKE :marker')
            ->setParameter('marker', $marker)
            ->orderBy('c.title')
            ->getQuery()
            ->getResult();
    }

    public function findByStatuses(array $statuses): array
    {
        return $this->em->getRepository(PageContent::class)->findBy(['status' => $statuses], ['title' => 'ASC']);
    }

    public function save(PageContent $pageContent): void
    {
        $this->em->persist($pageContent);
        $this->em->flush();
    }

    public function delete(PageContent $pageContent): void
    {
        $this->em->remove($pageContent);
        $this->em->flush();
    }
}
