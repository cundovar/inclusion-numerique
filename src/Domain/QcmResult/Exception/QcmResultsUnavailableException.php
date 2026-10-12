<?php

declare(strict_types=1);

namespace App\Domain\QcmResult\Exception;

/**
 * Le service des résultats de QCM ne répond pas ou répond en erreur : la fonctionnalité est indisponible,
 * le reste du site continue de fonctionner.
 */
class QcmResultsUnavailableException extends \RuntimeException
{
    public static function because(string $reason, ?\Throwable $previous = null): self
    {
        return new self(sprintf('Service des résultats de QCM indisponible : %s', $reason), 0, $previous);
    }
}
