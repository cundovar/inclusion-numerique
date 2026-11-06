<?php

declare(strict_types=1);

namespace App\Service\Capture;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Utilise Netcup en production lorsqu'une URL est configurée ; le moteur local
 * reste disponible pour le développement Docker.
 */
final class ConfiguredCaptureRunner implements CaptureRunnerInterface
{
    public function __construct(
        private NodeCaptureRunner $local,
        private RemoteCaptureRunner $remote,
        #[Autowire('%env(CAPTURE_SERVICE_URL)%')] private string $serviceUrl,
    ) {}

    public function run(array $request): array
    {
        return $this->serviceUrl === '' ? $this->local->run($request) : $this->remote->run($request);
    }
}
