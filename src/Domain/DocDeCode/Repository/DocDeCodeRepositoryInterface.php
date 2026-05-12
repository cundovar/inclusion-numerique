<?php

declare(strict_types=1);

namespace App\Domain\DocDeCode\Repository;

use App\Entity\DocDeCode;

interface DocDeCodeRepositoryInterface
{
    public function findById(int $id): ?DocDeCode;

    /**
     * @return DocDeCode[]
     */
    public function findAll(): array;

    public function save(DocDeCode $docDeCode): void;

    public function remove(DocDeCode $docDeCode): void;
}
