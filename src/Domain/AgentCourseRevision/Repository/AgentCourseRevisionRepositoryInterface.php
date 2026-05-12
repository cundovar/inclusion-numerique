<?php

declare(strict_types=1);

namespace App\Domain\AgentCourseRevision\Repository;

use App\Entity\AgentCourseRevision;
use App\Entity\PageContent;

interface AgentCourseRevisionRepositoryInterface
{
    public function findById(int $id): ?AgentCourseRevision;

    /**
     * @return AgentCourseRevision[]
     */
    public function findByCourse(PageContent $course): array;

    /**
     * @return AgentCourseRevision[]
     */
    public function findAll(): array;

    public function save(AgentCourseRevision $revision): void;
}
