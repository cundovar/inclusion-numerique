<?php

declare(strict_types=1);

namespace App\Service\Redaction;

use App\Domain\CourseMedia\Repository\CourseMediaRepositoryInterface;
use App\Entity\Diaporama;

/**
 * Contrôles automatiques d'un diaporama (regles/controles.md, regles/08-diaporamas.md).
 * Ils couvrent la structure, la copie du cours et les données personnelles ; la qualité de la synthèse
 * relève de la relecture par un autre connecteur.
 */
final class DiaporamaChecker
{
    /**
     * Modèles de diapositive et leurs champs (garder aligné avec front/src/diaporamas/modeles.js) :
     * nombre de points ou de réponses [min, max] et textes obligatoires.
     */
    public const MODELES = [
        'titre' => [],
        'objectifs' => ['points' => [2, 4]],
        'notion' => ['points' => [1, 4]],
        'etapes' => ['points' => [2, 5]],
        'comparaison' => ['colonnes' => true],
        'capture' => ['points' => [0, 2], 'capture' => true],
        'exercice' => ['points' => [0, 4], 'textes' => ['consigne']],
        'question' => ['reponses' => [2, 4], 'textes' => ['question']],
        'a_retenir' => ['points' => [2, 5]],
        'fin' => [],
    ];

    // Nombre de diapositives par cible (DIA-01)
    public const NOMBRE = [Diaporama::TARGET_COURSE => [6, 12], Diaporama::TARGET_CHAPTER => [10, 20]];
    public const MAX_TITLE_LENGTH = 60;
    public const MAX_POINTS = 5;
    public const MAX_POINT_WORDS = 12;
    public const NOTES_LENGTH = [300, 900];
    // DIA-02 : aucun passage de plus de 8 mots repris à l'identique
    public const COPY_WORDS = 9;

    // Modèles dont le titre vient du diaporama ou du thème
    private const TITRE_FACULTATIF = ['titre', 'fin'];
    private const PRATIQUE = ['exercice', 'question'];

    public function __construct(
        private CourseMediaRepositoryInterface $captures,
        private DiaporamaSource $source = new DiaporamaSource(),
        private PersonalDataChecker $personalData = new PersonalDataChecker(),
    ) {}

    /**
     * @return list<array{code: string, regle: ?string, niveau: string, message: string}>
     */
    public function check(Diaporama $diaporama): array
    {
        $issues = [];
        $cible = $diaporama->getTypeCible();
        $diapositives = $diaporama->getDiapositives();

        if ($cible === null) {
            $issues[] = $this->issue('DIAPO_CIBLE', null, DraftChecker::BLOCKING, 'Le diaporama vise un cours ou un chapitre, et un seul.');
        }
        if (trim($diaporama->getTitre()) === '') {
            $issues[] = $this->issue('DIAPO_TITRE_MANQUANT', 'DIA-05', DraftChecker::BLOCKING, 'Le diaporama n\'a pas de titre.');
        }
        if ($cible !== null) {
            [$min, $max] = self::NOMBRE[$cible];
            if (\count($diapositives) < $min || \count($diapositives) > $max) {
                $issues[] = $this->issue('DIAPO_NOMBRE', 'DIA-01', DraftChecker::BLOCKING, sprintf('Un diaporama de %s compte de %d à %d diapositives (%d actuellement).', $cible, $min, $max, \count($diapositives)));
            }
        }
        $nonPublies = $this->source->coursNonPublies($diaporama);
        if ($nonPublies !== []) {
            $issues[] = $this->issue('DIAPO_CHAPITRE_INCOMPLET', null, DraftChecker::BLOCKING, sprintf('Le diaporama d\'un chapitre attend que tous ses cours soient publiés. Pas encore publiés : %s.', implode(', ', $nonPublies)));
        }

        $sourceShingles = $this->shingles($this->source->texte($diaporama));
        $last = \count($diapositives) - 1;
        foreach ($diapositives as $index => $diapo) {
            array_push($issues, ...$this->checkSlide($diapo, $index + 1, $index === 0 || $index === $last, $sourceShingles));
        }

        if ($cible === Diaporama::TARGET_COURSE
            && array_filter($diapositives, static fn ($d) => \is_array($d) && \in_array($d['modele'] ?? null, self::PRATIQUE, true)) === []) {
            $issues[] = $this->issue('DIAPO_SANS_PRATIQUE', 'DIA-04', DraftChecker::WARNING, 'Ajoutez une diapositive « exercice » ou « question » : le cours fait pratiquer.');
        }
        if ($this->source->estPerime($diaporama)) {
            $issues[] = $this->issue('DIAPO_SOURCE_PERIMEE', null, DraftChecker::WARNING, 'Le cours a changé depuis la synthèse : relisez le diaporama et mettez-le à jour.');
        }

        $textes = [$diaporama->getTitre(), $diaporama->getSousTitre()];
        array_walk_recursive($diapositives, static function ($valeur) use (&$textes) {
            if (\is_string($valeur)) {
                $textes[] = $valeur;
            }
        });
        foreach ($this->personalData->find(implode("\n", $textes)) as [$code, $message]) {
            $issues[] = $this->issue($code, 'DON-01', DraftChecker::BLOCKING, $message);
        }

        return $issues;
    }

    /**
     * @param array<string, true> $sourceShingles
     * @return list<array{code: string, regle: ?string, niveau: string, message: string}>
     */
    private function checkSlide(mixed $diapo, int $numero, bool $extremite, array $sourceShingles): array
    {
        $modele = \is_array($diapo) ? ($diapo['modele'] ?? null) : null;
        if (!\is_string($modele) || !isset(self::MODELES[$modele])) {
            return [$this->issue('DIAPO_MODELE_INCONNU', null, DraftChecker::BLOCKING, sprintf('Diapositive %d : modèle inconnu. Modèles possibles : %s.', $numero, implode(', ', array_keys(self::MODELES))))];
        }
        $spec = self::MODELES[$modele];
        $issues = [];
        $prefixe = sprintf('Diapositive %d (%s) : ', $numero, $modele);

        $titre = self::texte($diapo['titre'] ?? null);
        if ($titre === '' && !\in_array($modele, self::TITRE_FACULTATIF, true)) {
            $issues[] = $this->issue('DIAPO_TITRE_MANQUANT', 'DIA-05', DraftChecker::BLOCKING, $prefixe.'ajoutez un titre.');
        } elseif (mb_strlen($titre) > self::MAX_TITLE_LENGTH) {
            $issues[] = $this->issue('DIAPO_TITRE_LONG', 'DIA-01', DraftChecker::BLOCKING, $prefixe.sprintf('le titre dépasse %d caractères.', self::MAX_TITLE_LENGTH));
        }

        $listes = [];
        if (isset($spec['points'])) {
            $listes['points'] = [self::liste($diapo['points'] ?? null), $spec['points']];
        }
        if (isset($spec['reponses'])) {
            $listes['réponses'] = [self::liste($diapo['reponses'] ?? null), $spec['reponses']];
        }
        if (isset($spec['colonnes'])) {
            $colonnes = \is_array($diapo['colonnes'] ?? null) ? array_values($diapo['colonnes']) : [];
            if (\count($colonnes) !== 2) {
                $issues[] = $this->issue('DIAPO_CHAMP_MANQUANT', null, DraftChecker::BLOCKING, $prefixe.'une comparaison a exactement deux colonnes {titre, points}.');
            }
            foreach (array_slice($colonnes, 0, 2) as $i => $colonne) {
                if (self::texte(\is_array($colonne) ? ($colonne['titre'] ?? null) : null) === '') {
                    $issues[] = $this->issue('DIAPO_CHAMP_MANQUANT', null, DraftChecker::BLOCKING, $prefixe.sprintf('la colonne %d n\'a pas de titre.', $i + 1));
                }
                $listes[sprintf('points de la colonne %d', $i + 1)] = [self::liste(\is_array($colonne) ? ($colonne['points'] ?? null) : null), [1, 4]];
            }
        }
        foreach ($listes as $nom => [$elements, [$min, $max]]) {
            if (\count($elements) > min($max, self::MAX_POINTS)) {
                $issues[] = $this->issue('DIAPO_TROP_DE_POINTS', 'DIA-01', DraftChecker::BLOCKING, $prefixe.sprintf('%d %s au plus (%d actuellement).', min($max, self::MAX_POINTS), $nom, \count($elements)));
            } elseif (\count($elements) < $min) {
                $issues[] = $this->issue('DIAPO_CHAMP_MANQUANT', null, DraftChecker::BLOCKING, $prefixe.sprintf('%d %s au moins (%d actuellement).', $min, $nom, \count($elements)));
            }
            foreach ($elements as $element) {
                if (self::mots($element) > self::MAX_POINT_WORDS) {
                    $issues[] = $this->issue('DIAPO_POINT_LONG', 'DIA-01', DraftChecker::WARNING, $prefixe.sprintf('« %s » dépasse %d mots : gardez le détail pour les notes.', mb_strimwidth($element, 0, 60, '…'), self::MAX_POINT_WORDS));
                }
            }
        }
        foreach ($spec['textes'] ?? [] as $champ) {
            if (self::texte($diapo[$champ] ?? null) === '') {
                $issues[] = $this->issue('DIAPO_CHAMP_MANQUANT', null, DraftChecker::BLOCKING, $prefixe.sprintf('le champ « %s » est vide.', $champ));
            }
        }
        if (isset($spec['capture'])) {
            array_push($issues, ...$this->checkCapture($diapo['capture'] ?? null, $prefixe));
        }

        $notes = self::texte($diapo['notes'] ?? null);
        if (!$extremite && $notes === '') {
            $issues[] = $this->issue('DIAPO_NOTES_MANQUANTES', 'DIA-03', DraftChecker::BLOCKING, $prefixe.'ajoutez les notes de présentation : c\'est là que va le détail du cours.');
        } elseif ($notes !== '' && (mb_strlen($notes) < self::NOTES_LENGTH[0] || mb_strlen($notes) > self::NOTES_LENGTH[1])) {
            $issues[] = $this->issue('DIAPO_NOTES_LONGUEUR', 'DIA-03', DraftChecker::WARNING, $prefixe.sprintf('les notes font %d caractères ; visez de %d à %d.', mb_strlen($notes), ...self::NOTES_LENGTH));
        }

        $copie = $this->passageCopie(self::texteVisible($diapo), $sourceShingles);
        if ($copie !== null) {
            $issues[] = $this->issue('DIAPO_COPIE', 'DIA-02', DraftChecker::BLOCKING, $prefixe.sprintf('« %s… » reprend le cours mot pour mot : reformulez.', $copie));
        }

        return $issues;
    }

    /**
     * @return list<array{code: string, regle: ?string, niveau: string, message: string}>
     */
    private function checkCapture(mixed $id, string $prefixe): array
    {
        if (!\is_int($id) && !(\is_string($id) && ctype_digit($id))) {
            return [$this->issue('DIAPO_CHAMP_MANQUANT', null, DraftChecker::BLOCKING, $prefixe.'indiquez l\'identifiant de la capture (lister_captures).')];
        }
        $capture = $this->captures->findById((int) $id);
        if ($capture === null) {
            return [$this->issue('CAPTURE_INCONNUE', 'CAP-02', DraftChecker::BLOCKING, $prefixe.sprintf('la capture %d n\'existe pas.', (int) $id))];
        }
        if (trim($capture->getAltText()) === '') {
            return [$this->issue('CAPTURE_INCONNUE', 'DIA-05', DraftChecker::BLOCKING, $prefixe.sprintf('la capture %d n\'a pas de texte alternatif : complétez-la dans le back-office.', (int) $id))];
        }

        return [];
    }

    /**
     * Texte projeté d'une diapositive (sans les notes, qui peuvent reprendre le cours à l'oral).
     *
     * @param array<string, mixed> $diapo
     */
    private static function texteVisible(array $diapo): string
    {
        $textes = [self::texte($diapo['titre'] ?? null), self::texte($diapo['consigne'] ?? null), self::texte($diapo['question'] ?? null)];
        array_push($textes, ...self::liste($diapo['points'] ?? null), ...self::liste($diapo['reponses'] ?? null));
        foreach (\is_array($diapo['colonnes'] ?? null) ? $diapo['colonnes'] : [] as $colonne) {
            if (\is_array($colonne)) {
                array_push($textes, self::texte($colonne['titre'] ?? null), ...self::liste($colonne['points'] ?? null));
            }
        }

        // Chaque texte est comparé séparément : un passage ne chevauche pas deux points
        return implode(' | ', array_filter($textes));
    }

    /**
     * @param array<string, true> $sourceShingles
     */
    private function passageCopie(string $texte, array $sourceShingles): ?string
    {
        foreach (explode(' | ', $texte) as $morceau) {
            $mots = self::motsNormalises($morceau);
            for ($i = 0; $i + self::COPY_WORDS <= \count($mots); ++$i) {
                $passage = implode(' ', \array_slice($mots, $i, self::COPY_WORDS));
                if (isset($sourceShingles[$passage])) {
                    return $passage;
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, true>
     */
    private function shingles(string $texte): array
    {
        $mots = self::motsNormalises($texte);
        $shingles = [];
        for ($i = 0; $i + self::COPY_WORDS <= \count($mots); ++$i) {
            $shingles[implode(' ', \array_slice($mots, $i, self::COPY_WORDS))] = true;
        }

        return $shingles;
    }

    /**
     * Mots en minuscules, sans ponctuation : la comparaison ignore la casse et les signes.
     *
     * @return list<string>
     */
    private static function motsNormalises(string $texte): array
    {
        return preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($texte), -1, \PREG_SPLIT_NO_EMPTY) ?: [];
    }

    private static function mots(string $texte): int
    {
        return \count(preg_split('/\s+/u', $texte, -1, \PREG_SPLIT_NO_EMPTY) ?: []);
    }

    private static function texte(mixed $valeur): string
    {
        return \is_string($valeur) ? trim($valeur) : '';
    }

    /**
     * @return list<string>
     */
    private static function liste(mixed $valeur): array
    {
        return \is_array($valeur) ? array_values(array_filter(array_map(self::texte(...), $valeur), static fn (string $t) => $t !== '')) : [];
    }

    /**
     * @return array{code: string, regle: ?string, niveau: string, message: string}
     */
    private function issue(string $code, ?string $rule, string $level, string $message): array
    {
        return ['code' => $code, 'regle' => $rule, 'niveau' => $level, 'message' => $message];
    }
}
