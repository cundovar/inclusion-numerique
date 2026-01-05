<?php

declare(strict_types=1);

namespace App\Domain\Favorite\Exception;

class FavoriteAlreadyExistsException extends \DomainException
{
    public static function forUserAndPage(int $userId, int $pageId): self
    {
        return new self(sprintf(
            'Un favori existe déjà pour l\'utilisateur %d et la page %d',
            $userId,
            $pageId
        ));
    }
}
