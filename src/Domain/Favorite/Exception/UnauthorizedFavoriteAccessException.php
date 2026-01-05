<?php

declare(strict_types=1);

namespace App\Domain\Favorite\Exception;

class UnauthorizedFavoriteAccessException extends \DomainException
{
    public static function forFavorite(int $favoriteId, int $userId): self
    {
        return new self(sprintf(
            'L\'utilisateur %d n\'est pas autorisé à accéder au favori %d',
            $userId,
            $favoriteId
        ));
    }
}
