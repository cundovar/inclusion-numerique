<?php

declare(strict_types=1);

namespace App\Domain\PropositionIA\Repository;

use App\Entity\PropositionIA;

interface PropositionIARepositoryInterface
{
    /**
     * @return PropositionIA[]
     */
    public function findAll(): array;

    public function save(PropositionIA $propositionIA): void;

    public function delete(PropositionIA $propositionIA): void;
}
