<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\PageContent;
use PHPUnit\Framework\TestCase;

/**
 * `visible`, lu par le front, suit le statut éditorial des connecteurs MCP.
 */
final class PageContentStatusTest extends TestCase
{
    public function testNewCourseIsPublishedAsBefore(): void
    {
        $course = new PageContent();

        self::assertSame(PageContent::STATUS_PUBLISHED, $course->getStatus());
        self::assertTrue($course->isVisible());
    }

    public function testOnlyAPublishedCourseIsVisible(): void
    {
        $course = new PageContent();
        foreach ([PageContent::STATUS_DRAFT, PageContent::STATUS_IN_REVIEW, PageContent::STATUS_TO_REVISE] as $status) {
            self::assertFalse($course->setStatus($status)->isVisible(), $status);
        }

        self::assertTrue($course->setStatus(PageContent::STATUS_PUBLISHED)->isVisible());
    }

    public function testHidingACoursePutsItBackInDraft(): void
    {
        $course = (new PageContent())->setVisible(false);
        self::assertSame(PageContent::STATUS_DRAFT, $course->getStatus());

        self::assertSame(PageContent::STATUS_PUBLISHED, $course->setVisible(true)->getStatus());
    }
}
