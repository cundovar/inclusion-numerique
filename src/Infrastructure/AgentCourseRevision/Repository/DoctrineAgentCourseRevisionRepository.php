<?php

declare(strict_types=1);

namespace App\Infrastructure\AgentCourseRevision\Repository;

use App\Domain\AgentCourseRevision\Repository\AgentCourseRevisionRepositoryInterface;
use App\Entity\AgentCourseRevision;
use App\Entity\PageContent;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineAgentCourseRevisionRepository implements AgentCourseRevisionRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?AgentCourseRevision
    {
        return $this->em->find(AgentCourseRevision::class, $id);
    }

    public function findByCourse(PageContent $course): array
    {
        return $this->em->getRepository(AgentCourseRevision::class)->findBy(
            ['course' => $course],
            ['dateRevision' => 'DESC', 'id' => 'DESC']
        );
    }

    public function findAll(): array
    {
        return $this->em->getRepository(AgentCourseRevision::class)->findBy([], ['dateRevision' => 'DESC', 'id' => 'DESC']);
    }

    public function save(AgentCourseRevision $revision): void
    {
        $this->em->persist($revision);
        $this->em->flush();
    }
}
