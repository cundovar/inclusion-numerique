<?php

declare(strict_types=1);

namespace App\Infrastructure\PageBlock\Repository;

use App\Domain\PageBlock\Repository\PageBlockRepositoryInterface;
use App\Entity\PageBlock;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePageBlockRepository implements PageBlockRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?PageBlock
    {
        return $this->em->getRepository(PageBlock::class)->find($id);
    }

    public function findAll(): array
    {
        return $this->em->getRepository(PageBlock::class)->findAll();
    }

    public function save(PageBlock $pageBlock): void
    {
        $this->em->persist($pageBlock);
        $this->em->flush();
    }

    public function remove(PageBlock $pageBlock): void
    {
        $this->em->remove($pageBlock);
        $this->em->flush();
    }
}
