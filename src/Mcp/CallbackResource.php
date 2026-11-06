<?php

declare(strict_types=1);

namespace App\Mcp;

use Mcp\Server\ClientGateway;
use Mcp\Server\Handler\ResourceHandlerInterface;

/**
 * Ressource du connecteur : une fonction qui reçoit l'adresse demandée.
 */
final class CallbackResource implements ResourceHandlerInterface
{
    /** @param \Closure(string): mixed $callback */
    public function __construct(private \Closure $callback) {}

    public function read(string $uri, ClientGateway $gateway): mixed
    {
        return ($this->callback)($uri);
    }
}
