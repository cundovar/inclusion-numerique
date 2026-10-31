<?php

declare(strict_types=1);

namespace App\Service\Capture;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\RuntimeException as ProcessRuntimeException;
use Symfony\Component\Process\Process;

/**
 * Lance le moteur Node (Playwright) du dossier capture/ : paramètres en JSON sur l'entrée, résultat en JSON.
 */
final class NodeCaptureRunner implements CaptureRunnerInterface
{
    private const TIMEOUT_SECONDS = 90;

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
        #[Autowire('%env(CAPTURE_NODE_BINARY)%')] private string $nodeBinary,
        #[Autowire('%env(CAPTURE_CHROMIUM_PATH)%')] private string $chromiumPath,
        #[Autowire('%env(bool:CAPTURE_NO_SANDBOX)%')] private bool $noSandbox,
        #[Autowire('%env(CAPTURE_HOME)%')] private string $home = '',
    ) {}

    public function run(array $request): array
    {
        $script = $this->projectDir.'/capture/src/cli.js';
        if (!is_file($script) || !is_dir($this->projectDir.'/capture/node_modules/playwright-core')) {
            throw new CaptureFailed(CaptureFailed::UNAVAILABLE, 'Le moteur de capture n\'est pas installé sur ce serveur (npm ci --prefix capture).');
        }

        // Le navigateur a besoin d'un dossier personnel (profil, certificats) ; php-fpm et le serveur web n'en fournissent pas toujours
        $home = $this->home !== '' ? $this->home : (getenv('HOME') ?: $this->projectDir.'/var/capture-home');
        if (!is_dir($home)) {
            mkdir($home, 0700, true);
        }

        $process = new Process([$this->nodeBinary, $script], $this->projectDir.'/capture', [
            'CAPTURE_CHROMIUM_PATH' => $this->chromiumPath,
            'CAPTURE_NO_SANDBOX' => $this->noSandbox ? '1' : '0',
            'HOME' => $home,
        ], json_encode($request, \JSON_THROW_ON_ERROR), self::TIMEOUT_SECONDS);

        try {
            $process->run();
        } catch (ProcessTimedOutException) {
            throw new CaptureFailed('DELAI_DEPASSE', 'La page a mis trop de temps à répondre.');
        } catch (ProcessRuntimeException $exception) {
            throw new CaptureFailed(CaptureFailed::UNAVAILABLE, 'Node.js est introuvable sur ce serveur : '.$exception->getMessage());
        }

        $result = json_decode($process->getOutput(), true);
        if (!\is_array($result)) {
            // Node absent (code 127) ou moteur en échec avant de répondre
            $reason = $process->getExitCode() === 127 ? 'Node.js est introuvable sur ce serveur.' : (trim($process->getErrorOutput()) ?: 'Réponse illisible du moteur de capture.');
            throw new CaptureFailed(CaptureFailed::UNAVAILABLE, mb_strimwidth($reason, 0, 300, '…'));
        }
        if (($result['ok'] ?? false) !== true) {
            $code = (string) ($result['code'] ?? 'ERREUR_TECHNIQUE');
            if ($code === 'ERREUR_TECHNIQUE' && str_contains((string) ($result['message'] ?? ''), 'Executable')) {
                $code = CaptureFailed::UNAVAILABLE;
            }
            throw new CaptureFailed($code, (string) ($result['message'] ?? 'Capture impossible.'), $result['details'] ?? null);
        }
        unset($result['ok']);

        return $result;
    }
}
