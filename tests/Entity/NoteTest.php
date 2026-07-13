<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Note;

class NoteTest extends TestCase
{
    public function testSetContentUpdatesTimestamp(): void
    {
        $note = new Note();
        $initialUpdatedAt = new \DateTimeImmutable('-1 second');
        $note->setUpdatedAt($initialUpdatedAt);

        $note->setContent('New content');

        $this->assertSame('New content', $note->getContent());
        $this->assertGreaterThan($initialUpdatedAt, $note->getUpdatedAt());
    }
}
