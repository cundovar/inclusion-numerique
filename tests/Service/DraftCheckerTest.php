<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Domain\CourseMedia\Repository\CourseMediaRepositoryInterface;
use App\Entity\Category;
use App\Entity\CourseMedia;
use App\Entity\PageContent;
use App\Service\Redaction\DraftChecker;
use PHPUnit\Framework\TestCase;

/**
 * Contrôles automatiques d'un brouillon (regles/controles.md).
 */
final class DraftCheckerTest extends TestCase
{
    private DraftChecker $checker;

    protected function setUp(): void
    {
        $captures = [
            12 => (new CourseMedia())->setKind(CourseMedia::KIND_CAPTURE),
            13 => (new CourseMedia())->setKind(CourseMedia::KIND_ILLUSTRATION),
        ];
        $repository = $this->createMock(CourseMediaRepositoryInterface::class);
        $repository->method('findById')->willReturnCallback(static fn (int $id) => $captures[$id] ?? null);
        $this->checker = new DraftChecker($repository);
    }

    public function testCompleteServiceSheetPassesWithoutIssue(): void
    {
        $course = $this->course(Category::AUDIENCE_APPRENDRE, <<<'HTML'
            <h2>À quoi sert ce site</h2>
            <p>Pour faire votre démarche, allez sur <a href="https://www.caf.fr/allocataires">caf.fr</a>.</p>
            <h2>Étape 1 : trouver le bouton</h2>
            <p>[capture:12]</p>
            <p>Écrivez à mme.exemple@example.com ou appelez le 06 XX XX XX XX.</p>
            <h2>Si ça ne marche pas</h2>
            <p>Allez dans une France Services.</p>
            <h2>À vous</h2>
            <p>Retrouvez le bouton seul.</p>
            HTML)->setSourceUrl('https://www.caf.fr/');

        self::assertSame([], $this->checker->check($course));
    }

    public function testCourseOutsideAnyAudienceAndWithoutTitleIsBlocked(): void
    {
        $course = (new PageContent())->setTitle(' ')->setCode('<p>Texte</p>');

        self::assertSame(['TITRE_MANQUANT', 'PARCOURS_SANS_PUBLIC'], $this->codes($course));
        self::assertTrue(DraftChecker::hasBlockingIssue($this->checker->check($course)));
    }

    public function testUnknownCaptureAndRawImageAreBlocked(): void
    {
        $codes = $this->codes($this->course(Category::AUDIENCE_ACCOMPAGNER, '<h2>Quand orienter</h2><p>[capture:99]</p><img src="/x.png" alt="">'));

        self::assertSame(['CAPTURE_INCONNUE', 'IMAGE_DIRECTE'], $codes);
    }

    public function testRealCaptureRequiresTheOfficialSourceAndALinkToIt(): void
    {
        $withoutSource = $this->course(Category::AUDIENCE_ACCOMPAGNER, '<h2>Quand orienter</h2><p>[capture:12]</p>');
        self::assertSame(['SOURCE_MANQUANTE'], $this->codes($withoutSource));

        $illustrationOnly = $this->course(Category::AUDIENCE_ACCOMPAGNER, '<h2>Quand orienter</h2><p>[capture:13]</p>');
        self::assertSame([], $this->codes($illustrationOnly));

        $withoutLink = $this->course(Category::AUDIENCE_ACCOMPAGNER, '<h2>Quand orienter</h2><p>[capture:12]</p><p><a href="https://caf.fr.example.net/">piège</a></p>')
            ->setSourceUrl('https://www.caf.fr/');
        self::assertSame(['LIEN_OFFICIEL_MANQUANT'], $this->codes($withoutLink));
    }

    /**
     * @dataProvider personalData
     */
    public function testRealLookingPersonalDataIsBlocked(string $text, string $code): void
    {
        self::assertContains($code, $this->codes($this->course(Category::AUDIENCE_ACCOMPAGNER, '<h2>Quand orienter</h2><p>'.$text.'</p>')));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function personalData(): iterable
    {
        yield 'numéro de sécurité sociale' => ['Numéro : 1 85 05 78 006 084 36', 'NUMERO_SECURITE_SOCIALE'];
        yield 'IBAN' => ['IBAN FR76 3000 6000 0112 3456 7890 189', 'IBAN'];
        yield 'téléphone' => ['Appelez le 06 12 34 56 78.', 'TELEPHONE'];
        yield 'téléphone international' => ['Appelez le +33 6 12 34 56 78.', 'TELEPHONE'];
        yield 'courriel réel' => ['Écrivez à jean.dupont@gmail.com.', 'COURRIEL_REEL'];
    }

    public function testFictionalExamplesAreAccepted(): void
    {
        $text = '<h2>Quand orienter</h2><p>Mme Exemple, 1 XX XX XX XXX XXX XX, écrit à contact@service.example et appelle le 06 XX XX XX XX.</p>';

        self::assertSame([], $this->codes($this->course(Category::AUDIENCE_ACCOMPAGNER, $text)));
    }

    public function testWarningsDoNotBlock(): void
    {
        $long = str_repeat('mot ', 30);
        $issues = $this->checker->check($this->course(Category::AUDIENCE_APPRENDRE, '<h2>Étape</h2><p>'.$long.'</p>'));

        self::assertSame(['PHRASE_LONGUE', 'SANS_EXERCICE'], array_column($issues, 'code'));
        self::assertSame(['avertissement', 'avertissement'], array_column($issues, 'niveau'));
        self::assertFalse(DraftChecker::hasBlockingIssue($issues));
    }

    public function testAccompanyingCourseShouldSayWhenToRefer(): void
    {
        self::assertSame(['SANS_ORIENTATION'], $this->codes($this->course(Category::AUDIENCE_ACCOMPAGNER, '<h2>Objectif</h2><p>Court.</p>')));
    }

    private function course(string $audience, string $html): PageContent
    {
        return (new PageContent())
            ->setTitle('Trouver « Mon Compte »')
            ->setCategory((new Category())->setName('démarches')->setAudience($audience))
            ->setCode($html);
    }

    /**
     * @return list<string>
     */
    private function codes(PageContent $course): array
    {
        return array_column($this->checker->check($course), 'code');
    }
}
