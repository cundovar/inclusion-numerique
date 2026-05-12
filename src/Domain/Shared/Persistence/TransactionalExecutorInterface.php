<?php

declare(strict_types=1);

namespace App\Domain\Shared\Persistence;

interface TransactionalExecutorInterface
{
    public function run(callable $operation): mixed;
}
