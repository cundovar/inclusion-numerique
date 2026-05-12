<?php

declare(strict_types=1);

namespace App\Application\AgentCourse\DTO;

final class AgentCourseRevisionDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $courseId,
        public readonly string $typeRevision,
        public readonly string $commentaire,
        public readonly string $ancienCode,
        public readonly string $nouveauCode,
        public readonly string $dateRevision,
        public readonly bool $appliquee
    ) {}
}
