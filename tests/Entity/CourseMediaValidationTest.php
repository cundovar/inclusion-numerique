<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\CourseMedia;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Chaque image d'un cours a une légende et un texte alternatif ; une capture réelle a aussi
 * une source et une date. Une illustration fictive n'a pas de page officielle à citer.
 */
final class CourseMediaValidationTest extends KernelTestCase
{
    public function testCompleteCaptureIsValid(): void
    {
        self::assertSame([], $this->violations($this->capture()));
    }

    public function testCaptureNeedsItsSourceAndDate(): void
    {
        $capture = $this->capture()->setSourceUrl(null)->setCapturedAt(null);

        self::assertSame(['capturedAt', 'sourceUrl'], $this->violations($capture));
    }

    public function testIllustrationDoesNotNeedASource(): void
    {
        $illustration = $this->capture()->setKind(CourseMedia::KIND_ILLUSTRATION)->setSourceUrl(null)->setCapturedAt(null);

        self::assertSame([], $this->violations($illustration));
    }

    public function testEveryImageNeedsAnAlternativeTextACaptionAndAFile(): void
    {
        $capture = $this->capture()->setAltText('')->setCaption(' ')->setFilename(null);

        self::assertSame(['altText', 'caption', 'filename'], $this->violations($capture));
    }

    public function testReviewDateIsDueOnTheDayItself(): void
    {
        $capture = $this->capture()->setNextReviewAt(new \DateTimeImmutable('2027-01-15'));

        self::assertFalse($capture->isDueForReview(new \DateTimeImmutable('2027-01-14')));
        self::assertTrue($capture->isDueForReview(new \DateTimeImmutable('2027-01-15')));
    }

    private function capture(): CourseMedia
    {
        return (new CourseMedia())
            ->setService('CAF')
            ->setSourceUrl('https://www.caf.fr/')
            ->setCapturedAt(new \DateTimeImmutable('2026-10-15'))
            ->setAltText("Page d'accueil de caf.fr")
            ->setCaption('Le bouton « Mon Compte »')
            ->setFilename('a1.png');
    }

    /**
     * @return list<string>
     */
    private function violations(CourseMedia $media): array
    {
        self::bootKernel();
        $paths = [];
        foreach (static::getContainer()->get(ValidatorInterface::class)->validate($media) as $violation) {
            $paths[] = $violation->getPropertyPath();
        }
        sort($paths);

        return array_values(array_unique($paths));
    }
}
