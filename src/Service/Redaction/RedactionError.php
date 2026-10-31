<?php

declare(strict_types=1);

namespace App\Service\Redaction;

/**
 * Refus d'une demande de l'assistant de rédaction : message lisible, détails par champ ou contrôles bloquants.
 */
final class RedactionError extends \RuntimeException
{
    public const NOT_FOUND = 'introuvable';
    public const CONFLICT = 'conflit';
    public const INVALID = 'invalide';
    public const BLOCKED = 'bloque';
    public const UNAVAILABLE = 'indisponible';

    /**
     * @param list<array<string, mixed>>|array<string, mixed>|null $details
     */
    public function __construct(string $message, public readonly string $kind, public readonly ?array $details = null)
    {
        parent::__construct($message);
    }

    /**
     * @param list<array{champ: string, message: string}> $errors
     */
    public static function invalid(array $errors): self
    {
        return new self('Données invalides.', self::INVALID, $errors);
    }

    public static function notFound(): self
    {
        return new self('Cours introuvable.', self::NOT_FOUND);
    }
}
