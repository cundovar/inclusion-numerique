<?php

declare(strict_types=1);

namespace App\Infrastructure\CourseLevel\Repository;

use App\Domain\CourseLevel\Repository\CourseLevelRepositoryInterface;
use App\Entity\NiveauCours;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineCourseLevelRepository implements CourseLevelRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?NiveauCours
    {
        return $this->em->find(NiveauCours::class, $id);
    }

    public function findAll(): array
    {
        return $this->em->getRepository(NiveauCours::class)->findBy([], ['ordre' => 'ASC', 'id' => 'ASC']);
    }

    public function findByName(string $name): ?NiveauCours
    {
        return $this->em->getRepository(NiveauCours::class)->findOneBy(['name' => $name]);
    }

    public function save(NiveauCours $niveauCours): void
    {
        $this->em->persist($niveauCours);
        $this->em->flush();
    }

    public function delete(NiveauCours $niveauCours): void
    {
        $this->em->remove($niveauCours);
        $this->em->flush();
    }
}
