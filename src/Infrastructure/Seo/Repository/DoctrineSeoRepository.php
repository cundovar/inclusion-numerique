<?php

declare(strict_types=1);

namespace App\Infrastructure\Seo\Repository;

use App\Domain\Seo\Repository\SeoRepositoryInterface;
use App\Entity\Seo;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineSeoRepository implements SeoRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findByPage(string $page): ?Seo
    {
        return $this->em->getRepository(Seo::class)->findOneBy(['page' => $page]);
    }
}
