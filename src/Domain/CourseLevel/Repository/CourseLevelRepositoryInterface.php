<?php

declare(strict_types=1);

namespace App\Domain\CourseLevel\Repository;

use App\Entity\NiveauCours;

interface CourseLevelRepositoryInterface
{
    public function findById(int $id): ?NiveauCours;

    /**
     * @return NiveauCours[]
     */
    public function findAll(): array;

    public function findByName(string $name): ?NiveauCours;

    public function save(NiveauCours $niveauCours): void;

    public function delete(NiveauCours $niveauCours): void;
}
