<?php

declare(strict_types=1);

namespace App\Service\Capture;

/**
 * Demande de capture incomplète ou invalide : liste champ => message.
 */
final class InvalidCaptureRequest extends \InvalidArgumentException
{
    /**
     * @param array<string, string> $errors
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct('Demande de capture invalide.');
    }
}
