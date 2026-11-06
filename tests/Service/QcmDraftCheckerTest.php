<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\LanguageQCM;
use App\Entity\NiveauQCM;
use App\Entity\QcmDraft;
use App\Service\Redaction\DraftChecker;
use App\Service\Redaction\QcmDraftChecker;
use PHPUnit\Framework\TestCase;

/**
 * Contrôles automatiques d'un brouillon de QCM (regles/controles.md, regles/06-qcm.md).
 */
final class QcmDraftCheckerTest extends TestCase
{
    public function testCompleteQcmPassesWithoutIssue(): void
    {
        self::assertSame([], (new QcmDraftChecker())->check($this->draft()));
    }

    public function testMissingPartsAreBlocking(): void
    {
        $draft = (new QcmDraft('connecteur:Claude#1'))->setAnswers([]);

        $codes = $this->codes($draft, DraftChecker::BLOCKING);

        self::assertSame(['QUESTION_MANQUANTE', 'EXPLICATION_MANQUANTE', 'THEME_MANQUANT', 'NIVEAU_MANQUANT', 'NOMBRE_REPONSES', 'BONNE_REPONSE_UNIQUE'], $codes);
    }

    /**
     * @return iterable<string, array{0: int, 1: int, 2: list<string>}>
     */
    public static function answerCounts(): iterable
    {
        yield 'une seule réponse' => [1, 1, ['NOMBRE_REPONSES']];
        yield 'deux réponses' => [2, 1, []];
        yield 'six réponses' => [6, 1, []];
        yield 'sept réponses' => [7, 1, ['NOMBRE_REPONSES']];
        yield 'aucune bonne réponse' => [3, 0, ['BONNE_REPONSE_UNIQUE']];
        yield 'deux bonnes réponses' => [3, 2, ['BONNE_REPONSE_UNIQUE']];
    }

    /**
     * @dataProvider answerCounts
     * @param list<string> $expected
     */
    public function testAnswerCountAndSingleCorrectAnswer(int $count, int $correct, array $expected): void
    {
        $answers = [];
        for ($i = 0; $i < $count; ++$i) {
            $answers[] = ['texte' => 'Réponse '.$i, 'correcte' => $i < $correct, 'explication' => 'Parce que.'];
        }

        self::assertSame($expected, $this->codes($this->draft()->setAnswers($answers), DraftChecker::BLOCKING));
    }

    public function testLengthIsMeasuredOnTheHtmlShownBySite(): void
    {
        // 250 caractères saisis, mais « & » devient « &amp; » dans le HTML public : la colonne de 255 déborderait
        $question = str_repeat('&', 2).str_repeat('a', 248);
        self::assertSame(['QUESTION_TROP_LONGUE'], $this->codes($this->draft()->setQuestion($question), DraftChecker::BLOCKING));
        self::assertSame('&lt;b&gt; l\'écran', QcmDraftChecker::html("<b> l'écran"));
    }

    public function testDuplicateAndEmptyAnswersAreBlocking(): void
    {
        $draft = $this->draft()->setAnswers([
            ['texte' => 'Un lien', 'correcte' => true, 'explication' => null],
            ['texte' => 'un  LIEN', 'correcte' => false, 'explication' => 'Non.'],
            ['texte' => ' ', 'correcte' => false, 'explication' => 'Non.'],
        ]);

        self::assertSame(['REPONSES_IDENTIQUES', 'REPONSE_VIDE'], $this->codes($draft, DraftChecker::BLOCKING));
    }

    public function testPersonalDataInAnyFieldIsBlocking(): void
    {
        $draft = $this->draft()->setAnswers([
            ['texte' => 'Répondre au message', 'correcte' => false, 'explication' => 'Écrivez plutôt à jean.dupont@gmail.com.'],
            ['texte' => 'Supprimer le message', 'correcte' => true, 'explication' => null],
        ]);

        self::assertSame(['COURRIEL_REEL'], $this->codes($draft, DraftChecker::BLOCKING));
    }

    public function testWarningsPointToTricksLongQuestionsAndMissingExplanations(): void
    {
        $draft = $this->draft()
            ->setQuestion(str_repeat('mot ', 26).'?')
            ->setAnswers([
                ['texte' => 'Supprimer le message', 'correcte' => true, 'explication' => null],
                ['texte' => 'Toutes les réponses', 'correcte' => false, 'explication' => null],
            ]);

        self::assertSame([], $this->codes($draft, DraftChecker::BLOCKING));
        self::assertSame(['QUESTION_LONGUE', 'REPONSE_PIEGE', 'EXPLICATION_REPONSE_MANQUANTE'], $this->codes($draft, DraftChecker::WARNING));
    }

    private function draft(): QcmDraft
    {
        return (new QcmDraft('connecteur:Claude#1'))
            ->setQuestion('Un message vous demande de payer un colis. Que faites-vous ?')
            ->setExplanation('Un vrai transporteur ne demande pas de payer par un lien.')
            ->setTheme((new LanguageQCM())->setName('Sécurité'))
            ->setLevel((new NiveauQCM())->setTitre('Débutant'))
            ->setAnswers([
                ['texte' => 'Je clique sur le lien', 'correcte' => false, 'explication' => 'Le lien peut mener à un faux site.'],
                ['texte' => 'Je supprime le message', 'correcte' => true, 'explication' => null],
                ['texte' => 'Je réponds au message', 'correcte' => false, 'explication' => 'Répondre confirme que votre numéro existe.'],
            ]);
    }

    /**
     * @return list<string>
     */
    private function codes(QcmDraft $draft, string $level): array
    {
        $issues = array_filter((new QcmDraftChecker())->check($draft), static fn (array $i) => $i['niveau'] === $level);

        return array_values(array_column($issues, 'code'));
    }
}
