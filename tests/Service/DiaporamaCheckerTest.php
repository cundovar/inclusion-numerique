<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Domain\CourseMedia\Repository\CourseMediaRepositoryInterface;
use App\Entity\Category;
use App\Entity\CourseMedia;
use App\Entity\Diaporama;
use App\Entity\Menus;
use App\Entity\PageContent;
use App\Service\Redaction\DiaporamaChecker;
use App\Service\Redaction\DiaporamaSource;
use App\Service\Redaction\DraftChecker;
use PHPUnit\Framework\TestCase;

/**
 * Contrôles automatiques d'un diaporama (regles/controles.md, regles/08-diaporamas.md).
 * Le diaporama de démonstration du front sert de référence : front et serveur lisent le même format.
 */
final class DiaporamaCheckerTest extends TestCase
{
    private const DEMO = __DIR__.'/../../front/tests/unit/diaporamas/demo.json';
    private const COURS = '<h2>Une consigne claire</h2><p>Une intelligence artificielle répond toujours à la question exacte que la personne lui a posée, sans deviner son intention.</p><p>[capture:42]</p>';

    private DiaporamaChecker $checker;

    protected function setUp(): void
    {
        $captures = [
            42 => (new CourseMedia())->setKind(CourseMedia::KIND_CAPTURE)->setAltText('Conversation fictive'),
            43 => (new CourseMedia())->setKind(CourseMedia::KIND_CAPTURE)->setAltText(''),
        ];
        $repository = $this->createMock(CourseMediaRepositoryInterface::class);
        $repository->method('findById')->willReturnCallback(static fn (int $id) => $captures[$id] ?? null);
        $this->checker = new DiaporamaChecker($repository);
    }

    public function testDemoDiaporamaPassesWithoutIssue(): void
    {
        self::assertSame([], $this->checker->check($this->demo()));
    }

    public function testTargetTitleAndSlideCountAreBlocking(): void
    {
        $diaporama = (new Diaporama('connecteur:Claude#1'))->setDiapositives([['modele' => 'graphique']]);

        self::assertSame(['DIAPO_CIBLE', 'DIAPO_TITRE_MANQUANT', 'DIAPO_MODELE_INCONNU'], $this->codes($diaporama));

        $deux = $this->demo()->setCible($this->cours(), new Menus());
        self::assertContains('DIAPO_CIBLE', $this->codes($deux));

        $court = $this->demo();
        $court->setDiapositives(\array_slice($court->getDiapositives(), 0, 5));
        self::assertSame(['DIAPO_NOMBRE'], $this->codes($court, DraftChecker::BLOCKING));
    }

    /**
     * @return iterable<string, array{0: int, 1: array<string, mixed>, 2: list<string>}>
     */
    public static function slideFields(): iterable
    {
        yield 'trop de points' => [1, ['points' => ['Un', 'Deux', 'Trois', 'Quatre', 'Cinq']], ['DIAPO_TROP_DE_POINTS']];
        yield 'pas assez de points' => [1, ['points' => ['Un seul']], ['DIAPO_CHAMP_MANQUANT']];
        yield 'titre manquant' => [2, ['titre' => ''], ['DIAPO_TITRE_MANQUANT']];
        yield 'titre trop long' => [2, ['titre' => str_repeat('Titre long ', 7)], ['DIAPO_TITRE_LONG']];
        yield 'colonne manquante' => [4, ['colonnes' => [['titre' => 'À faire', 'points' => ['Préciser']]]], ['DIAPO_CHAMP_MANQUANT']];
        yield 'colonne sans titre' => [4, ['colonnes' => [['points' => ['Un']], ['titre' => 'À éviter', 'points' => ['Deux']]]], ['DIAPO_CHAMP_MANQUANT']];
        yield 'capture inconnue' => [5, ['capture' => 99], ['CAPTURE_INCONNUE']];
        yield 'capture sans texte alternatif' => [5, ['capture' => 43], ['CAPTURE_INCONNUE']];
        yield 'consigne vide' => [6, ['consigne' => ' '], ['DIAPO_CHAMP_MANQUANT']];
        yield 'trop de réponses' => [7, ['reponses' => ['A', 'B', 'C', 'D', 'E']], ['DIAPO_TROP_DE_POINTS']];
        yield 'notes manquantes' => [3, ['notes' => ''], ['DIAPO_NOTES_MANQUANTES']];
    }

    /**
     * @dataProvider slideFields
     * @param array<string, mixed> $changes
     * @param list<string> $expected
     */
    public function testSlideFieldsAreChecked(int $index, array $changes, array $expected): void
    {
        self::assertSame($expected, $this->codes($this->demoWith($index, $changes), DraftChecker::BLOCKING));
    }

    public function testWarningsDoNotBlock(): void
    {
        $diaporama = $this->demoWith(2, [
            'points' => ['Une IA ne devine jamais ce que vous pensez sans que vous le lui disiez clairement', 'Court'],
            'notes' => 'Notes trop brèves.',
        ]);

        self::assertSame(['DIAPO_POINT_LONG', 'DIAPO_NOTES_LONGUEUR'], $this->codes($diaporama, DraftChecker::WARNING));
        self::assertSame([], $this->codes($diaporama, DraftChecker::BLOCKING));
    }

    public function testFirstAndLastSlidesNeedNoNotes(): void
    {
        $demo = $this->demo();
        self::assertSame('', $demo->getDiapositives()[0]['notes']);
        self::assertSame('', $demo->getDiapositives()[9]['notes']);
        self::assertNotContains('DIAPO_NOTES_MANQUANTES', $this->codes($demo));
    }

    public function testCopyOfMoreThanEightWordsIsBlocked(): void
    {
        // Neuf mots du cours, avec une autre casse et une autre ponctuation
        $copie = $this->demoWith(2, ['points' => ['Répond toujours à la question exacte que la personne, lui', 'Le contexte change tout']]);
        $issues = $this->checker->check($copie);
        self::assertSame(['DIAPO_COPIE'], array_column($issues, 'code'));
        self::assertStringContainsString('Diapositive 3 (notion)', $issues[0]['message']);

        // Huit mots seulement : une reformulation proche reste permise
        $proche = $this->demoWith(2, ['points' => ['Toujours à la question exacte que la personne', 'Le contexte change tout']]);
        self::assertSame([], $this->codes($proche));
    }

    public function testNotesMayQuoteTheCourse(): void
    {
        $notes = str_pad('Lisez la phrase du cours : une intelligence artificielle répond toujours à la question exacte que la personne lui a posée.', 320, ' Puis commentez.');
        self::assertSame([], $this->codes($this->demoWith(2, ['notes' => $notes])));
    }

    public function testCourseShouldMakeLearnersPractise(): void
    {
        $sansPratique = $this->demoWith(6, ['modele' => 'notion', 'points' => ['Écrire une consigne']]);
        $sansPratique = $this->withSlide($sansPratique, 7, ['modele' => 'notion', 'points' => ['Choisir la bonne']]);
        self::assertSame(['DIAPO_SANS_PRATIQUE'], $this->codes($sansPratique, DraftChecker::WARNING));
    }

    public function testChapterWaitsForAllItsCourses(): void
    {
        $chapitre = new Menus();
        $chapitre->addPageContent($this->cours()->setTitle('Premier cours'));
        $chapitre->addPageContent($this->cours()->setTitle('Deuxième cours')->setStatus(PageContent::STATUS_DRAFT));
        $diaporama = $this->demo()->setCible(null, $chapitre);

        $issues = $this->checker->check($diaporama);
        self::assertSame(['DIAPO_CHAPITRE_INCOMPLET'], array_column($issues, 'code'));
        self::assertStringContainsString('Deuxième cours', $issues[0]['message']);

        $chapitre->getPageContents()->last()->setStatus(PageContent::STATUS_PUBLISHED);
        self::assertSame([], $this->codes($diaporama));
    }

    public function testChapterHasTenToTwentySlides(): void
    {
        $chapitre = (new Menus())->addPageContent($this->cours());
        $diaporama = $this->demo()->setCible(null, $chapitre);
        $diaporama->setDiapositives(\array_slice($diaporama->getDiapositives(), 0, 9));

        self::assertSame(['DIAPO_NOMBRE'], $this->codes($diaporama, DraftChecker::BLOCKING));
    }

    public function testChangedCourseMarksTheDiaporamaOutdated(): void
    {
        $diaporama = $this->demo();
        $diaporama->setEmpreinteSource((new DiaporamaSource())->empreinte($diaporama));
        self::assertSame([], $this->codes($diaporama));

        $diaporama->getCours()?->setCode(self::COURS.'<p>Un nouveau paragraphe.</p>');
        self::assertSame(['DIAPO_SOURCE_PERIMEE'], $this->codes($diaporama, DraftChecker::WARNING));
    }

    public function testPersonalDataIsBlockedEvenInNotes(): void
    {
        $notes = str_pad('Appelez le 06 12 34 56 78 pour toute question.', 320, ' Notes.');

        self::assertSame(['TELEPHONE'], $this->codes($this->demoWith(2, ['notes' => $notes]), DraftChecker::BLOCKING));
    }

    public function testSourceTextFollowsTheCourseOrderWithoutCaptureMarkers(): void
    {
        $chapitre = new Menus();
        $chapitre->addPageContent($this->cours()->setTitle('Second')->setOrdre(2)->setCode('<p>Deux</p>'));
        $chapitre->addPageContent($this->cours()->setTitle('Premier')->setOrdre(1)->setCode('<p>Un&nbsp;[capture:42]</p>'));

        self::assertSame("Premier Un\n\nSecond Deux", (new DiaporamaSource())->texte((new Diaporama('a'))->setCible(null, $chapitre)));
    }

    private function cours(): PageContent
    {
        return (new PageContent())
            ->setTitle('Bien écrire une consigne')
            ->setCategory((new Category())->setName('consignes'))
            ->setStatus(PageContent::STATUS_PUBLISHED)
            ->setCode(self::COURS);
    }

    private function demo(): Diaporama
    {
        /** @var array{titre: string, sousTitre: string, diapositives: list<array<string, mixed>>} $demo */
        $demo = json_decode((string) file_get_contents(self::DEMO), true, flags: \JSON_THROW_ON_ERROR);

        return (new Diaporama('connecteur:Claude#1'))
            ->setCible($this->cours(), null)
            ->setTitre($demo['titre'])
            ->setSousTitre($demo['sousTitre'])
            ->setDiapositives($demo['diapositives']);
    }

    /**
     * @param array<string, mixed> $changes
     */
    private function demoWith(int $index, array $changes): Diaporama
    {
        return $this->withSlide($this->demo(), $index, $changes);
    }

    /**
     * @param array<string, mixed> $changes
     */
    private function withSlide(Diaporama $diaporama, int $index, array $changes): Diaporama
    {
        $diapositives = $diaporama->getDiapositives();
        $diapositives[$index] = array_merge($diapositives[$index], $changes);

        return $diaporama->setDiapositives($diapositives);
    }

    /**
     * @return list<string>
     */
    private function codes(Diaporama $diaporama, ?string $level = null): array
    {
        $issues = $this->checker->check($diaporama);
        if ($level !== null) {
            $issues = array_filter($issues, static fn (array $issue) => $issue['niveau'] === $level);
        }

        return array_values(array_column($issues, 'code'));
    }
}
