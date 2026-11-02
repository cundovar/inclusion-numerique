<?php

declare(strict_types=1);

namespace App\Service\Redaction;

use App\Entity\QcmDraft;

/**
 * Contrôles automatiques d'un brouillon de QCM (voir regles/controles.md et regles/06-qcm.md).
 * Ils couvrent la structure et les données personnelles ; la qualité pédagogique relève de la relecture.
 */
final class QcmDraftChecker
{
    public const MIN_ANSWERS = 2;
    public const MAX_ANSWERS = 6;
    // Taille des colonnes publiques QCM.titre et ChoicesQCM.question, une fois le texte converti en HTML
    public const MAX_TEXT_LENGTH = 255;

    private const LONG_QUESTION_WORDS = 25;
    // Réponses qui transforment la question en devinette (QCM-02)
    private const TRICK_ANSWER = '/\b(toutes les réponses|aucune des réponses|aucune de ces réponses|toutes ces réponses|tout ce qui précède)\b/iu';

    public function __construct(
        private PersonalDataChecker $personalData = new PersonalDataChecker(),
    ) {}

    /**
     * Texte saisi par l'agent → HTML affiché par le site public (le front interprète ces champs comme du HTML).
     */
    public static function html(string $text): string
    {
        return htmlspecialchars($text, \ENT_NOQUOTES | \ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * @return list<array{code: string, regle: ?string, niveau: string, message: string}>
     */
    public function check(QcmDraft $draft): array
    {
        $issues = [];
        $question = trim($draft->getQuestion());
        $answers = $draft->getAnswers();

        if ($question === '') {
            $issues[] = $this->issue('QUESTION_MANQUANTE', 'QCM-01', DraftChecker::BLOCKING, 'Écrivez la question.');
        } elseif (mb_strlen(self::html($question)) > self::MAX_TEXT_LENGTH) {
            $issues[] = $this->issue('QUESTION_TROP_LONGUE', 'QCM-03', DraftChecker::BLOCKING, sprintf('La question dépasse %d caractères : raccourcissez-la.', self::MAX_TEXT_LENGTH));
        } elseif (\count(preg_split('/\s+/u', $question, -1, \PREG_SPLIT_NO_EMPTY) ?: []) > self::LONG_QUESTION_WORDS) {
            $issues[] = $this->issue('QUESTION_LONGUE', 'RED-01', DraftChecker::WARNING, sprintf('La question dépasse %d mots : visez une phrase courte.', self::LONG_QUESTION_WORDS));
        }

        if (trim($draft->getExplanation()) === '') {
            $issues[] = $this->issue('EXPLICATION_MANQUANTE', 'QCM-04', DraftChecker::BLOCKING, 'Ajoutez l\'explication affichée après la réponse.');
        }

        if ($draft->getTheme() === null) {
            $issues[] = $this->issue('THEME_MANQUANT', null, DraftChecker::BLOCKING, 'Choisissez un thème (lister_catalogue_qcm) : sans thème, le QCM n\'apparaît dans aucune série.');
        }
        if ($draft->getLevel() === null) {
            $issues[] = $this->issue('NIVEAU_MANQUANT', null, DraftChecker::BLOCKING, 'Choisissez un niveau (lister_catalogue_qcm) : sans niveau, le QCM n\'apparaît dans aucune série.');
        }

        $count = \count($answers);
        if ($count < self::MIN_ANSWERS || $count > self::MAX_ANSWERS) {
            $issues[] = $this->issue('NOMBRE_REPONSES', 'QCM-01', DraftChecker::BLOCKING, sprintf('Proposez de %d à %d réponses (%d actuellement).', self::MIN_ANSWERS, self::MAX_ANSWERS, $count));
        }
        $correct = \count(array_filter($answers, static fn (array $a) => $a['correcte']));
        if ($correct !== 1) {
            $issues[] = $this->issue('BONNE_REPONSE_UNIQUE', 'QCM-01', DraftChecker::BLOCKING, sprintf('Une seule réponse doit être correcte (%d actuellement).', $correct));
        }

        $seen = [];
        foreach ($answers as $index => $answer) {
            $number = $index + 1;
            $text = trim($answer['texte']);
            if ($text === '') {
                $issues[] = $this->issue('REPONSE_VIDE', 'QCM-01', DraftChecker::BLOCKING, sprintf('La réponse %d est vide.', $number));
                continue;
            }
            if (mb_strlen(self::html($text)) > self::MAX_TEXT_LENGTH) {
                $issues[] = $this->issue('REPONSE_TROP_LONGUE', 'QCM-03', DraftChecker::BLOCKING, sprintf('La réponse %d dépasse %d caractères.', $number, self::MAX_TEXT_LENGTH));
            }
            $key = mb_strtolower(preg_replace('/\s+/u', ' ', $text) ?? $text);
            if (isset($seen[$key])) {
                $issues[] = $this->issue('REPONSES_IDENTIQUES', 'QCM-02', DraftChecker::BLOCKING, sprintf('Les réponses %d et %d sont identiques.', $seen[$key], $number));
            }
            $seen[$key] ??= $number;
            if (preg_match(self::TRICK_ANSWER, $text) === 1) {
                $issues[] = $this->issue('REPONSE_PIEGE', 'QCM-02', DraftChecker::WARNING, sprintf('La réponse %d (« %s ») renvoie aux autres réponses : proposez plutôt une réponse précise.', $number, mb_strimwidth($text, 0, 60, '…')));
            }
            if (!$answer['correcte'] && trim((string) $answer['explication']) === '') {
                $issues[] = $this->issue('EXPLICATION_REPONSE_MANQUANTE', 'QCM-04', DraftChecker::WARNING, sprintf('Expliquez pourquoi la réponse %d ne convient pas : la personne qui la choisit comprend son erreur.', $number));
            }
        }

        $texts = [$question, $draft->getExplanation()];
        foreach ($answers as $answer) {
            $texts[] = $answer['texte'];
            $texts[] = (string) $answer['explication'];
        }
        foreach ($this->personalData->find(implode("\n", $texts)) as [$code, $message]) {
            $issues[] = $this->issue($code, 'DON-04', DraftChecker::BLOCKING, $message);
        }

        return $issues;
    }

    /**
     * @return array{code: string, regle: ?string, niveau: string, message: string}
     */
    private function issue(string $code, ?string $rule, string $level, string $message): array
    {
        return ['code' => $code, 'regle' => $rule, 'niveau' => $level, 'message' => $message];
    }
}
