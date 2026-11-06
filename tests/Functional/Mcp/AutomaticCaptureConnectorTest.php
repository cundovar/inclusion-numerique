<?php

declare(strict_types=1);

namespace App\Tests\Functional\Mcp;

use App\Entity\CourseMedia;
use App\Service\Capture\CaptureFailed;
use App\Service\Capture\CaptureRunnerInterface;
use App\Service\CaptureFileManager;

/**
 * Capture automatique demandée par le connecteur : tout domaine public en HTTPS, fiche remplie par le site.
 * Le moteur Playwright est remplacé par un double ; il est testé à part (capture/test, NodeCaptureRunnerTest).
 */
final class AutomaticCaptureConnectorTest extends McpTestCase
{
    private FakeCaptureRunner $runner;
    /** @var list<string> */
    private array $createdFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->runner = new FakeCaptureRunner();
        static::getContainer()->set(CaptureRunnerInterface::class, $this->runner);
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $path) {
            @unlink($path);
        }
        parent::tearDown();
    }

    /**
     * @dataProvider refusedUrls
     */
    public function testHttpAndCredentialUrlsAreRejected(string $url): void
    {
        $result = $this->capture(['url' => $url]);

        self::assertTrue($result['error']);
        self::assertStringContainsString('ADRESSE_REFUSEE', $result['text']);
        self::assertSame([], $this->runner->requests);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function refusedUrls(): iterable
    {
        yield 'http' => ['http://www.caf.fr/'];
        yield 'identifiants dans l\'adresse' => ['https://user:mdp@www.caf.fr/'];
    }

    public function testCaptureFillsTheSheetFromThePageActuallyCaptured(): void
    {
        $result = $this->capture();
        self::assertFalse($result['error'], $result['text']);
        $data = $result['data'];

        self::assertSame([
            'url' => 'https://www.caf.fr/',
            'elements' => [['repere' => 1, 'texte' => 'Mon Compte']],
            'format' => 'mobile',
        ], array_diff_key($this->runner->requests[0], ['outDir' => true]));
        self::assertSame('https://www.caf.fr/allocataires', $data['pageSource']);
        self::assertSame((new \DateTimeImmutable('today'))->format('Y-m-d'), $data['dateCapture']);
        self::assertSame((new \DateTimeImmutable('+3 months'))->format('Y-m-d'), $data['aRevoirLe']);
        self::assertSame([$this->agentLabel(self::CLAUDE), 'capture', 'CAF'], [$data['auteur'], $data['type'], $data['service']]);
        self::assertSame([['repere' => 1, 'texte' => 'Mon Compte']], $data['elements']);
        self::assertSame(sprintf('[capture:%d]', $data['id']), $data['marqueur']);

        $path = static::getContainer()->get(CaptureFileManager::class)->path(basename((string) $data['url']));
        $this->createdFiles[] = $path;
        self::assertFileExists($path);
        self::assertDirectoryDoesNotExist($this->runner->requests[0]['outDir'], 'Le dossier de travail est supprimé');
    }

    public function testAnyPublicHttpsDomainCanBeCaptured(): void
    {
        $result = $this->capture(['url' => 'https://www.ameli.fr/']);

        self::assertFalse($result['error'], $result['text']);
        self::assertSame('https://www.ameli.fr/', $this->runner->requests[0]['url']);
        self::assertArrayNotHasKey('allowedHosts', $this->runner->requests[0]);
    }

    public function testInvalidRequestIsRejectedBeforeCapturing(): void
    {
        // Format inconnu et repère hors limites : refusés par le schéma de l'outil
        self::assertTrue($this->capture(['format' => 'tablette'])['error']);
        self::assertTrue($this->capture(['elements' => [['repere' => 12, 'texte' => 'C']]])['error']);

        // Texte et sélecteur à la fois : refusé par le site
        $both = $this->capture(['elements' => [['repere' => 1, 'texte' => 'A', 'selecteur' => '#b']]]);
        self::assertTrue($both['error']);
        self::assertStringContainsString('elements[0]', $both['text']);
        self::assertSame([], $this->runner->requests);
    }

    public function testMissingCaptionOrAlternativeTextLeavesNothingBehind(): void
    {
        $result = $this->capture(['texteAlternatif' => ' ']);

        self::assertTrue($result['error']);
        self::assertStringContainsString('altText', $result['text']);
        self::assertSame(0, $this->entityManager->getRepository(CourseMedia::class)->count([]));
        self::assertDirectoryDoesNotExist($this->runner->requests[0]['outDir']);
    }

    public function testLoginScreenAndAntiBotProtectionAreRefusedWithTheirReason(): void
    {
        foreach (['ECRAN_CONNEXION', 'PROTECTION_ANTI_ROBOT'] as $code) {
            $this->runner->failure = new CaptureFailed($code, 'Refus du moteur.');

            $result = $this->capture();

            self::assertTrue($result['error']);
            self::assertStringContainsString($code, $result['text']);
        }
        self::assertSame(0, $this->entityManager->getRepository(CourseMedia::class)->count([]));
    }

    public function testUnavailableEngineIsReportedAsSuch(): void
    {
        $this->runner->failure = new CaptureFailed(CaptureFailed::UNAVAILABLE, 'Node.js est introuvable sur ce serveur.');

        $result = $this->capture();

        self::assertTrue($result['error']);
        self::assertStringContainsString('CAPTURE_INDISPONIBLE', $result['text']);
    }

    /**
     * @param array<string, mixed> $override
     * @return array{error: bool, text: string, data: mixed}
     */
    private function capture(array $override = []): array
    {
        return $this->tool(self::CLAUDE, 'capturer_page', $override + [
            'url' => 'https://www.caf.fr/',
            'format' => 'mobile',
            'elements' => [['repere' => 1, 'texte' => 'Mon Compte']],
            'service' => 'CAF',
            'legende' => 'Le bouton « Mon Compte » (repère 1).',
            'texteAlternatif' => "Page d'accueil de caf.fr, bouton « Mon Compte » entouré, repère 1",
        ]);
    }
}
