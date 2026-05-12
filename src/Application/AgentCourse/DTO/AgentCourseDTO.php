<?php

declare(strict_types=1);

namespace App\Application\AgentCourse\DTO;

final class AgentCourseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $pageId,
        public readonly int $menuId,
        public readonly string $slug,
        public readonly string $title,
        public readonly string $technology,
        public readonly string $level,
        public readonly string $status,
        public readonly bool $visible
    ) {}
}
