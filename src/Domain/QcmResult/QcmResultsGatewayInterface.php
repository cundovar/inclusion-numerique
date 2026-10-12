<?php

declare(strict_types=1);

namespace App\Domain\QcmResult;

use App\Domain\QcmResult\Exception\QcmResultsUnavailableException;

/**
 * Port vers le stockage des résultats de QCM (micro-service Node + MongoDB).
 *
 * @phpstan-type Answer array{questionId: int, choiceId: int, correct: bool}
 */
interface QcmResultsGatewayInterface
{
    /**
     * @param list<Answer> $answers
     *
     * @return array<string, mixed> la tentative enregistrée (id, score, total, createdAt…)
     *
     * @throws QcmResultsUnavailableException
     */
    public function recordAttempt(int $userId, string $language, string $level, array $answers): array;

    /**
     * @return list<array<string, mixed>> tentatives, la plus récente d'abord, sans le détail des réponses
     *
     * @throws QcmResultsUnavailableException
     */
    public function findUserAttempts(int $userId, int $limit = 20): array;

    /**
     * @return list<array<string, mixed>> progression par langage et niveau
     *
     * @throws QcmResultsUnavailableException
     */
    public function getUserProgress(int $userId): array;

    /**
     * @throws QcmResultsUnavailableException
     */
    public function deleteUserAttempts(int $userId): int;

    /**
     * @return list<array{questionId: int, answered: int, correct: int, successPercent: int}>
     *
     * @throws QcmResultsUnavailableException
     */
    public function getQuestionStats(?string $language = null, ?string $level = null, int $limit = 20): array;
}
