<?php

declare(strict_types=1);

namespace App\Service\OAuth;

/**
 * Erreur OAuth au format des RFC 6749 et 7591 (`error`, `error_description`).
 */
final class OAuthError extends \RuntimeException
{
    public function __construct(public readonly string $error, string $description, public readonly int $status = 400)
    {
        parent::__construct($description);
    }

    /**
     * @return array{error: string, error_description: string}
     */
    public function toArray(): array
    {
        return ['error' => $this->error, 'error_description' => $this->getMessage()];
    }
}
