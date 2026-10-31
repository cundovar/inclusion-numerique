<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\Capture\CaptureFailed;
use App\Service\Capture\NodeCaptureRunner;
use PHPUnit\Framework\TestCase;

/**
 * Liaison réelle entre PHP et le moteur Node (sans réseau : le moteur refuse avant d'ouvrir une page).
 */
final class NodeCaptureRunnerTest extends TestCase
{
    private string $projectDir;

    protected function setUp(): void
    {
        $this->projectDir = \dirname(__DIR__, 2);
        if (!is_dir($this->projectDir.'/capture/node_modules/playwright-core')) {
            self::markTestSkipped('Moteur de capture non installé (npm ci --prefix capture).');
        }
    }

    public function testInsecureAddressIsRefusedByEngine(): void
    {
        $runner = new NodeCaptureRunner($this->projectDir, 'node', '', true);

        try {
            $runner->run(['url' => 'http://example.org/', 'elements' => [], 'format' => 'ordinateur', 'outDir' => sys_get_temp_dir()]);
            self::fail('La capture aurait dû être refusée.');
        } catch (CaptureFailed $failure) {
            self::assertSame('ADRESSE_REFUSEE', $failure->codeName);
            self::assertFalse($failure->isUnavailable());
        }
    }

    public function testMissingNodeIsReportedAsUnavailable(): void
    {
        $runner = new NodeCaptureRunner($this->projectDir, '/chemin/inexistant/node', '', true);

        try {
            $runner->run(['url' => 'https://www.caf.fr/', 'elements' => [], 'format' => 'ordinateur', 'outDir' => sys_get_temp_dir()]);
            self::fail('Le moteur aurait dû être indisponible.');
        } catch (CaptureFailed $failure) {
            self::assertTrue($failure->isUnavailable());
        }
    }
}
