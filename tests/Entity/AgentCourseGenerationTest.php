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

    public function testFailDoesNotCountTheLastReportTwice(): void
    {
        $generation = new AgentCourseGeneration('batch-1', 'course-1', ['title' => 'Cours test']);
        $report = ['approved' => false, 'issues' => [['severity' => 'blocking']]];

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $generation->update('verifying', null, $report, null);
        }
        $generation->fail($report, 'Trois vérifications refusées');

        self::assertSame('failed', $generation->getStatus());
        self::assertSame(3, $generation->getVerificationAttempts());
        self::assertSame($report, $generation->getVerificationReport());
        self::assertNotNull($generation->getFinishedAt());
    }
    public function testItCanBecomeReadyAndReplaceItsPayload(): void
    {
        $generation = new AgentCourseGeneration('batch-1', 'course-1', ['menuId' => 92]);

        $generation->update(
            'ready',
            ['codeHTML' => '<main class="principal"></main>'],
            ['approved' => true],
            null,
            ['menuId' => 93]
        );

        self::assertSame('ready', $generation->getStatus());
        self::assertSame(['menuId' => 93], $generation->getPayload());
        self::assertSame(1, $generation->getVerificationAttempts());
    }

}
