<?php

declare(strict_types=1);

namespace App\Domain\Shared\Persistence;

interface PersistenceFlusherInterface
{
    public function flush(): void;
}
