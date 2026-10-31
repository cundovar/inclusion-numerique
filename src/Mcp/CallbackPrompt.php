<?php

declare(strict_types=1);

namespace App\Mcp;

use Mcp\Server\ClientGateway;
use Mcp\Server\Handler\PromptHandlerInterface;

/**
 * Consigne du connecteur : une fonction qui reçoit seulement ses arguments.
 */
final class CallbackPrompt implements PromptHandlerInterface
{
    /** @param \Closure(array<string, mixed>): mixed $callback */
    public function __construct(private \Closure $callback) {}

    public function get(array $arguments, ClientGateway $gateway): mixed
    {
        return ($this->callback)(CallbackTool::clean($arguments));
    }
}
