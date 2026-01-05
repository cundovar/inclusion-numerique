<?php

declare(strict_types=1);

namespace App\Application\Favorite\Query;

use App\Entity\User;

final class CheckFavoriteQuery
{
    public function __construct(
        public readonly User $user,
        public readonly int $pageId
    ) {}
}
