<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Shared\Persistence\TransactionalExecutorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

final class DoctrineTransactionalExecutor implements TransactionalExecutorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function run(callable $operation): mixed
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        try {
            $result = $operation();
            $connection->commit();

            return $result;
        } catch (Throwable $exception) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }
}
