<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\AgentCourseGeneration;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AgentCourseGenerationTest extends TestCase
{
    public function testItTracksVerificationAttemptsAndRejectsUnknownStates(): void
    {
        $generation = new AgentCourseGeneration('batch-1', 'course-1', ['title' => 'Cours test']);

        $generation->update('verifying', ['codeHTML' => '<main class="principal"></main>'], ['approved' => false], null);

        self::assertSame('verifying', $generation->getStatus());
        self::assertSame(1, $generation->getVerificationAttempts());
        self::assertFalse($generation->getVerificationReport()['approved']);

        $this->expectException(InvalidArgumentException::class);
        $generation->update('unknown', null, null, null);
    }
}
