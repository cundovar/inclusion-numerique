<?php

declare(strict_types=1);

namespace App\Application\AgentCourse\Command;

final class CreateAgentCourseCommand
{
    public function __construct(
        public readonly string $title,
        public readonly string $technology,
        public readonly string $level,
        public readonly string $duration,
        public readonly string $codeHtml,
        public readonly ?string $description = null,
        public readonly ?string $objectives = null,
        public readonly ?int $menuId = null,
        public readonly ?string $newMenuLabel = null,
        public readonly string $status = 'brouillon'
    ) {}
}
