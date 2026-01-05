<?php

declare(strict_types=1);

namespace App\Domain\Note\Exception;

class NoteNotFoundException extends \DomainException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Note avec l\'id %d non trouvée', $id));
    }

    public static function forUserAndPage(int $userId, int $pageId): self
    {
        return new self(sprintf(
            'Note non trouvée pour l\'utilisateur %d et la page %d',
            $userId,
            $pageId
        ));
    }
}
