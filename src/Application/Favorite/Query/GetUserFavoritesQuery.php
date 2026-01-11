<?php

declare(strict_types=1);

namespace App\Application\Favorite\Query;

use App\Entity\User;

final class GetUserFavoritesQuery
{
    public function __construct(
        public readonly User $user
    ) {}
}
