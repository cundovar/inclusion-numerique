<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Shared\Persistence\PersistenceFlusherInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePersistenceFlusher implements PersistenceFlusherInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
