<?php

declare(strict_types=1);

namespace App\Domain\Note\Exception;

class UnauthorizedNoteAccessException extends \DomainException
{
    public static function forNote(int $noteId, int $userId): self
    {
        return new self(sprintf(
            'L\'utilisateur %d n\'est pas autorisé à accéder à la note %d',
            $userId,
            $noteId
        ));
    }
}
