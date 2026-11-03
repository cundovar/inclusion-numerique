<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Une réponse de l'API destinée à un administrateur contient des brouillons : le service worker
 * ne doit jamais la garder (voir front/pwa/apiCache.js). Sinon, une ancienne version
 * resterait affichée, et un visiteur suivant la retrouverait hors ligne sur le même poste.
 */
#[AsEventListener(event: KernelEvents::RESPONSE)]
final class ApiOfflineCacheListener
{
    public const HEADER = 'X-Offline-Cache';

    public function __construct(private Security $security) {}

    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || !str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $event->getResponse()->headers->set(self::HEADER, 'none');
        }
    }
}
