<?php

namespace App\Repository;

use App\Entity\Seo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Seo>
 */
class SeoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seo::class);
    }

    /**
     * Trouve les données SEO pour une page spécifique
     */
    public function findByPage(string $page): ?Seo
    {
        return $this->findOneBy(['page' => $page]);
    }
}
