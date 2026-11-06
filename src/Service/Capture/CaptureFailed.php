<?php

declare(strict_types=1);

namespace App\Service\Capture;

/**
 * Capture refusée ou impossible : le code (ADRESSE_REFUSEE, ECRAN_CONNEXION…) est renvoyé à l'agent.
 */
final class CaptureFailed extends \RuntimeException
{
    public const UNAVAILABLE = 'CAPTURE_INDISPONIBLE';

    /**
     * @param array<mixed>|null $details
     */
    public function __construct(public readonly string $codeName, string $message, public readonly ?array $details = null)
    {
        parent::__construct($message);
    }

    public function isUnavailable(): bool
    {
        return $this->codeName === self::UNAVAILABLE;
    }
}
