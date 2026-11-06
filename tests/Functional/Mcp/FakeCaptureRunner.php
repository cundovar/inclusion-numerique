<?php

declare(strict_types=1);

namespace App\Tests\Functional\Mcp;

use App\Service\Capture\CaptureFailed;
use App\Service\Capture\CaptureRunnerInterface;

/**
 * Double du moteur : écrit une vraie image JPEG dans le dossier de travail, comme le moteur Node.
 */
final class FakeCaptureRunner implements CaptureRunnerInterface
{
    /** @var list<array<string, mixed>> */
    public array $requests = [];
    public ?CaptureFailed $failure = null;

    public function run(array $request): array
    {
        $this->requests[] = $request;
        if ($this->failure !== null) {
            throw $this->failure;
        }
        mkdir($request['outDir'], 0775, true);
        $file = $request['outDir'].'/capture-'.$request['format'].'.jpg';
        $image = imagecreatetruecolor(390, 844);
        imagefill($image, 0, 0, (int) imagecolorallocate($image, random_int(0, 255), random_int(0, 255), random_int(0, 255)));
        imagejpeg($image, $file);

        return [
            'urlFinale' => 'https://www.caf.fr/allocataires',
            'titre' => 'Allocataires',
            'format' => $request['format'],
            'largeur' => 390,
            'hauteur' => 844,
            'elements' => [['repere' => 1, 'texte' => 'Mon Compte']],
            'fichier' => $file,
        ];
    }
}
