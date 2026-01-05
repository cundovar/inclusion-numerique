<?php

declare(strict_types=1);

namespace App\Domain\Favorite\Exception;

class FavoriteNotFoundException extends \DomainException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Favori avec l\'id %d non trouvé', $id));
    }
}
