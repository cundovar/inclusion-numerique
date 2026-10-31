<?php

declare(strict_types=1);

namespace App\Service\OAuth;

/**
 * Erreur d'une demande d'autorisation dont le client et l'adresse de retour sont valides :
 * elle est renvoyée au connecteur sur cette adresse (RFC 6749, 4.1.2.1).
 */
final class RedirectableOAuthError extends \RuntimeException
{
    public function __construct(public readonly string $error, string $description)
    {
        parent::__construct($description);
    }
}
