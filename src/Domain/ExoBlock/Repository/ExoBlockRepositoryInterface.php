<?php

declare(strict_types=1);

namespace App\Domain\ExoBlock\Repository;

use App\Entity\ExoBlock;

interface ExoBlockRepositoryInterface
{
    public function findById(int $id): ?ExoBlock;

    /**
     * @return ExoBlock[]
     */
    public function findAll(): array;

    public function save(ExoBlock $exoBlock): void;

    public function remove(ExoBlock $exoBlock): void;
}
