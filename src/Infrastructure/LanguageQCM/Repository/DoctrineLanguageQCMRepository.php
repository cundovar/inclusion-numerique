<?php

declare(strict_types=1);

namespace App\Infrastructure\LanguageQCM\Repository;

use App\Domain\LanguageQCM\Repository\LanguageQCMRepositoryInterface;
use App\Entity\LanguageQCM;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineLanguageQCMRepository implements LanguageQCMRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?LanguageQCM
    {
        return $this->em->find(LanguageQCM::class, $id);
    }
}
