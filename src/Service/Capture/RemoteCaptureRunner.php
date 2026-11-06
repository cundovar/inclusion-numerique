<?php

declare(strict_types=1);

namespace App\Service\Capture;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Appelle le moteur Playwright isolé sur Netcup. Le JPEG reste privé : il est
 * renvoyé dans la réponse authentifiée puis enregistré par le back-office OVH.
 */
final class RemoteCaptureRunner implements CaptureRunnerInterface
{
    private const MAX_IMAGE_BYTES = 10_000_000;

    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire('%env(CAPTURE_SERVICE_URL)%')] private string $serviceUrl,
        #[Autowire('%env(CAPTURE_SERVICE_TOKEN)%')] private string $token,
    ) {}

    public function run(array $request): array
    {
        if ($this->serviceUrl === '' || $this->token === '') {
            throw new CaptureFailed(CaptureFailed::UNAVAILABLE, 'Le service de capture distant n’est pas configuré.');
        }

        try {
            $response = $this->httpClient->request('POST', rtrim($this->serviceUrl, '/').'/captures', [
                'auth_bearer' => $this->token,
                'json' => [
                    'url' => $request['url'],
                    'elements' => $request['elements'],
                    'format' => $request['format'],
                ],
                'timeout' => 90,
                'max_duration' => 95,
            ]);
            $status = $response->getStatusCode();
            $result = $response->toArray(false);
        } catch (\Throwable) {
            throw new CaptureFailed(CaptureFailed::UNAVAILABLE, 'Le service de capture distant est indisponible.');
        }

        if ($status !== 200) {
            if (($result['ok'] ?? null) === false && isset($result['code'], $result['message']) && \is_string($result['code']) && \is_string($result['message'])) {
                throw new CaptureFailed($result['code'], $result['message'], $result['details'] ?? null);
            }

            throw new CaptureFailed(CaptureFailed::UNAVAILABLE, 'Le service de capture distant a renvoyé une réponse invalide.');
        }

        return $this->responseToResult($result, $request['outDir']);
    }

    /**
     * @param array<string, mixed> $result
     * @return array{urlFinale: string, titre: string, format: string, largeur: int, hauteur: int, elements: list<array{repere: int, texte: string}>, fichier: string}
     */
    private function responseToResult(array $result, string $outDir): array
    {
        $image = $result['image'] ?? null;
        if (($result['ok'] ?? false) !== true || !\is_string($result['urlFinale'] ?? null) || !\is_string($result['titre'] ?? null)
            || !\is_string($result['format'] ?? null) || !\is_int($result['largeur'] ?? null) || !\is_int($result['hauteur'] ?? null)
            || !\is_array($result['elements'] ?? null) || !\is_string($image) || $outDir === '') {
            throw new CaptureFailed(CaptureFailed::UNAVAILABLE, 'Le service de capture distant a renvoyé une réponse invalide.');
        }

        $binary = base64_decode($image, true);
        if ($binary === false || $binary === '' || \strlen($binary) > self::MAX_IMAGE_BYTES || !str_starts_with($binary, "\xFF\xD8\xFF")) {
            throw new CaptureFailed(CaptureFailed::UNAVAILABLE, 'Le service de capture distant a renvoyé une image invalide.');
        }

        if (!is_dir($outDir) && !mkdir($outDir, 0700, true) && !is_dir($outDir)) {
            throw new CaptureFailed('ERREUR_TECHNIQUE', 'Le dossier temporaire de capture ne peut pas être créé.');
        }
        $file = rtrim($outDir, '/').'/capture.jpg';
        if (file_put_contents($file, $binary) === false) {
            throw new CaptureFailed('ERREUR_TECHNIQUE', 'L’image de capture ne peut pas être enregistrée.');
        }

        return [
            'urlFinale' => $result['urlFinale'],
            'titre' => $result['titre'],
            'format' => $result['format'],
            'largeur' => $result['largeur'],
            'hauteur' => $result['hauteur'],
            'elements' => $result['elements'],
            'fichier' => $file,
        ];
    }
}
