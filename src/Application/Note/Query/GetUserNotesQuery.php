<?php

declare(strict_types=1);

namespace App\Application\Note\Query;

use App\Entity\User;

final class GetUserNotesQuery
{
    public function __construct(
        public readonly User $user
    ) {}
}
