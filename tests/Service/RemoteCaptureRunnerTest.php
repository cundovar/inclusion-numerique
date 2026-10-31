<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\Capture\CaptureFailed;
use App\Service\Capture\RemoteCaptureRunner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class RemoteCaptureRunnerTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/remote-capture-'.bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        if (is_dir($this->directory)) {
            array_map('unlink', glob($this->directory.'/*') ?: []);
            rmdir($this->directory);
        }
    }

    public function testItStoresTheAuthenticatedServiceImageInTheLocalWorkDirectory(): void
    {
        $response = new MockResponse(json_encode([
            'ok' => true,
            'urlFinale' => 'https://www.service-public.fr/',
            'titre' => 'Service Public',
            'format' => 'ordinateur',
            'largeur' => 1280,
            'hauteur' => 800,
            'elements' => [['repere' => 1, 'texte' => 'Accueil']],
            'image' => base64_encode("\xFF\xD8\xFF\xD9"),
        ], \JSON_THROW_ON_ERROR), ['http_code' => 200]);
        $runner = new RemoteCaptureRunner(new MockHttpClient($response), 'https://capture.example.test', 'jeton-de-test');

        $result = $runner->run(['url' => 'https://www.service-public.fr/', 'elements' => [], 'format' => 'ordinateur', 'outDir' => $this->directory]);

        self::assertSame('https://www.service-public.fr/', $result['urlFinale']);
        self::assertSame("\xFF\xD8\xFF\xD9", file_get_contents($result['fichier']));
    }

    public function testItKeepsCaptureEngineRefusals(): void
    {
        $response = new MockResponse(json_encode(['ok' => false, 'code' => 'ADRESSE_REFUSEE', 'message' => 'Adresse refusée.'], \JSON_THROW_ON_ERROR), ['http_code' => 422]);
        $runner = new RemoteCaptureRunner(new MockHttpClient($response), 'https://capture.example.test', 'jeton-de-test');

        $this->expectException(CaptureFailed::class);
        $this->expectExceptionMessage('Adresse refusée.');
        try {
            $runner->run(['url' => 'https://127.0.0.1/', 'elements' => [], 'format' => 'ordinateur', 'outDir' => $this->directory]);
        } catch (CaptureFailed $failure) {
            self::assertSame('ADRESSE_REFUSEE', $failure->codeName);
            throw $failure;
        }
    }

    public function testItReportsMalformedRemoteResponsesAsUnavailable(): void
    {
        $runner = new RemoteCaptureRunner(new MockHttpClient(new MockResponse('{}', ['http_code' => 200])), 'https://capture.example.test', 'jeton-de-test');

        $this->expectException(CaptureFailed::class);
        try {
            $runner->run(['url' => 'https://www.service-public.fr/', 'elements' => [], 'format' => 'ordinateur', 'outDir' => $this->directory]);
        } catch (CaptureFailed $failure) {
            self::assertSame(CaptureFailed::UNAVAILABLE, $failure->codeName);
            throw $failure;
        }
    }
}
