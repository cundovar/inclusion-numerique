<?php

declare(strict_types=1);

namespace App\Infrastructure\PropositionIA\Repository;

use App\Domain\PropositionIA\Repository\PropositionIARepositoryInterface;
use App\Entity\PropositionIA;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePropositionIARepository implements PropositionIARepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findAll(): array
    {
        return $this->em->getRepository(PropositionIA::class)->findAll();
    }

    public function save(PropositionIA $propositionIA): void
    {
        $this->em->persist($propositionIA);
        $this->em->flush();
    }

    public function delete(PropositionIA $propositionIA): void
    {
        $this->em->remove($propositionIA);
        $this->em->flush();
    }
}
