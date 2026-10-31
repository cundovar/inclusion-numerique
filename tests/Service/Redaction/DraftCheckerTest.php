<?php

declare(strict_types=1);

namespace App\Tests\Service\Redaction;

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
        // Capture 12 : une vraie page d'outil ; capture 13 : une illustration fictive
        $captures = [
            12 => (new CourseMedia())->setKind(CourseMedia::KIND_CAPTURE),
            13 => (new CourseMedia())->setKind(CourseMedia::KIND_ILLUSTRATION),
        ];
        $repository = $this->createMock(CourseMediaRepositoryInterface::class);
        $repository->method('findById')->willReturnCallback(static fn (int $id) => $captures[$id] ?? null);
        $this->checker = new DraftChecker($repository);
    }

    public function testCompleteCoursePassesWithoutIssue(): void
    {
        $course = $this->course(<<<'HTML'
            <h2>Ce que vous allez apprendre</h2>
            <p>Créer un contrôleur qui renvoie du JSON.</p>
            <pre><code class="language-php">return $this->json(['contact' => 'dev@example.com']);</code></pre>
            <h2>À vous</h2>
            <p>Ajoutez une route qui renvoie votre nom.</p>
            <p>Voir la <a href="https://symfony.com/doc/6.4/controller.html">documentation</a>.</p>
            HTML)->setSourceUrl('https://symfony.com/doc/6.4/controller.html');

        self::assertSame([], $this->checker->check($course));
    }

    public function testCourseWithoutTitleParcoursOrContentIsBlocked(): void
    {
        $course = (new PageContent())->setTitle(' ')->setCode('');

        self::assertSame(['TITRE_MANQUANT', 'PARCOURS_MANQUANT', 'CONTENU_VIDE', 'SOURCE_MANQUANTE', 'SANS_EXERCICE'], $this->codes($course));
        self::assertTrue(DraftChecker::hasBlockingIssue($this->checker->check($course)));
    }

    public function testSourceWithoutLinkToItsSiteIsBlocked(): void
    {
        $course = $this->course('<h2>Exercice</h2><p>Lien vers <a href="https://example.com/">un autre site</a>.</p>')
            ->setSourceUrl('https://vuejs.org/guide/introduction.html');

        self::assertSame(['LIEN_OFFICIEL_MANQUANT'], $this->codes($course));
    }

    public function testSecretInCodeBlockIsBlocked(): void
    {
        $course = $this->course('<h2>Exercice</h2><pre><code class="language-bash">export OPENAI_API_KEY=REDACTED;

        self::assertContains('CLE_API', $this->codes($course));
        self::assertTrue(DraftChecker::hasBlockingIssue($this->checker->check($course)));
    }

    public function testCodeBlockWithoutLanguageIsAWarning(): void
    {
        $issues = $this->checker->check($this->course('<h2>À vous</h2><pre><code>echo 1;</code></pre>'));

        self::assertContains('CODE_SANS_LANGAGE', array_column($issues, 'code'));
        self::assertFalse(DraftChecker::hasBlockingIssue($issues));
    }

    public function testLongSentenceOutsideCodeIsAWarning(): void
    {
        $sentence = str_repeat('mot ', 30);
        $codes = $this->codes($this->course('<h2>À vous</h2><p>'.$sentence.'.</p><pre><code class="language-php">'.$sentence.'</code></pre>'));

        self::assertSame(1, array_count_values($codes)['PHRASE_LONGUE'] ?? 0);
    }

    public function testUnknownCaptureAndRawImageAreBlocked(): void
    {
        $course = $this->course('<h2>À vous</h2><p>[capture:99]</p><img src="/x.png" alt="">')->setSourceUrl('https://symfony.com/doc/current/setup.html');
        $course->setCode($course->getCode().'<p><a href="https://symfony.com/doc/current/setup.html">Documentation</a></p>');

        self::assertSame(['CAPTURE_INCONNUE', 'IMAGE_DIRECTE'], $this->codes($course));
    }

    public function testRealCaptureRequiresTheOfficialSource(): void
    {
        $withoutSource = $this->checker->check($this->course('<h2>À vous</h2><p>[capture:12]</p>'));
        self::assertSame(['SOURCE_MANQUANTE'], array_column($withoutSource, 'code'));
        self::assertTrue(DraftChecker::hasBlockingIssue($withoutSource));

        // Une illustration fictive ne cite aucune page : la source manquante reste un avertissement
        $illustrationOnly = $this->checker->check($this->course('<h2>À vous</h2><p>[capture:13]</p>'));
        self::assertSame(['SOURCE_MANQUANTE'], array_column($illustrationOnly, 'code'));
        self::assertFalse(DraftChecker::hasBlockingIssue($illustrationOnly));
    }

    private function course(string $html): PageContent
    {
        return (new PageContent())->setTitle('Premier contrôleur')->setCategory((new Category())->setName('Symfony'))->setCode($html);
    }

    /**
     * @return list<string>
     */
    private function codes(PageContent $course): array
    {
        return array_column($this->checker->check($course), 'code');
    }
}
