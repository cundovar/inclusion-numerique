<?php

declare(strict_types=1);

namespace App\Application\Note\Command;

use App\Entity\User;

final class DeleteNoteCommand
{
    public function __construct(
        public readonly User $user,
        public readonly int $noteId
    ) {}
}
