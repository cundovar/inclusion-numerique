<?php

declare(strict_types=1);

namespace App\Application\QcmResult\Command;

use App\Entity\User;

final class RecordQcmAttemptCommand
{
    /**
     * @param array<int, int> $answers identifiant de question => identifiant du choix retenu
     */
    public function __construct(
        public readonly User $user,
        public readonly string $language,
        public readonly string $level,
        public readonly array $answers
    ) {}
}
