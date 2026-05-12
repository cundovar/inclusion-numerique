<?php

declare(strict_types=1);

namespace App\Domain\QCM\Repository;

use App\Entity\QCM;

interface QCMRepositoryInterface
{
    public function findById(int $id): ?QCM;

    public function countAll(): int;

    /**
     * @return QCM[]
     */
    public function findAll(): array;

    /**
     * @return QCM[]
     */
    public function findByLanguage(string $language): array;

    /**
     * @return QCM[]
     */
    public function findByDifficulty(string $difficulty): array;

    /**
     * @return QCM[]
     */
    public function findByLanguageAndDifficulty(string $language, string $difficulty): array;
}
