<?php

declare(strict_types=1);

namespace App\Service\Pdf;

/**
 * PDF prêt à envoyer : fichier sur le disque, empreinte du contenu (ETag) et nom proposé au téléchargement.
 */
final class PdfDocument
{
    public function __construct(
        public readonly string $path,
        public readonly string $fingerprint,
        public readonly string $filename,
    ) {}
}
