<?php

declare(strict_types=1);

namespace App\Application\Favorite\Command;

use App\Entity\User;

final class ToggleFavoriteCommand
{
    public function __construct(
        public readonly User $user,
        public readonly int $pageId
    ) {}
}
