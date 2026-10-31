<?php

declare(strict_types=1);

namespace App\Mcp;

use Mcp\Server\ClientGateway;
use Mcp\Server\Handler\ToolHandlerInterface;

/**
 * Outil du connecteur : une fonction qui reçoit seulement ses arguments, sans les entrées internes du SDK MCP.
 */
final class CallbackTool implements ToolHandlerInterface
{
    /** @param \Closure(array<string, mixed>): mixed $callback */
    public function __construct(private \Closure $callback) {}

    public function execute(array $arguments, ClientGateway $gateway): mixed
    {
        return ($this->callback)(self::clean($arguments));
    }

    /**
     * @param array<string, mixed> $arguments
     * @return array<string, mixed>
     */
    public static function clean(array $arguments): array
    {
        return array_filter($arguments, static fn ($key) => !str_starts_with((string) $key, '_'), \ARRAY_FILTER_USE_KEY);
    }
}
