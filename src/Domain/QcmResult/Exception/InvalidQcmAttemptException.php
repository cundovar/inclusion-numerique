<?php

declare(strict_types=1);

namespace App\Domain\QcmResult\Exception;

class InvalidQcmAttemptException extends \DomainException
{
    public static function unknownQuestion(int $questionId): self
    {
        return new self(sprintf('Question %d inconnue', $questionId));
    }

    public static function questionOutOfScope(int $questionId, string $language, string $level): self
    {
        return new self(sprintf('La question %d n\'appartient pas au QCM %s / %s', $questionId, $language, $level));
    }

    public static function unknownChoice(int $choiceId, int $questionId): self
    {
        return new self(sprintf('Le choix %d n\'appartient pas à la question %d', $choiceId, $questionId));
    }

    public static function duplicateQuestion(int $questionId): self
    {
        return new self(sprintf('La question %d est répondue plusieurs fois', $questionId));
    }

    public static function empty(): self
    {
        return new self('Aucune réponse');
    }
}
