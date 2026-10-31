<?php

declare(strict_types=1);

namespace App\Service\Capture;

interface CaptureRunnerInterface
{
    /**
     * Lance le moteur de capture (capture/src/cli.js).
     *
     * @param array{url: string, elements: list<array<string, mixed>>, format: string, outDir: string} $request
     * @return array{urlFinale: string, titre: string, format: string, largeur: int, hauteur: int, elements: list<array{repere: int, texte: string}>, fichier: string}
     *
     * @throws CaptureFailed
     */
    public function run(array $request): array;
}
