<?php

declare(strict_types=1);

namespace App\Service\Redaction;

use App\Domain\CourseMedia\Repository\CourseMediaRepositoryInterface;
use App\Entity\Category;
use App\Entity\CourseMedia;
use App\Entity\PageContent;

/**
 * Contrôles automatiques d'un brouillon avant relecture (voir regles/controles.md).
 * Ils couvrent seulement ce qui se vérifie sans jugement ; la relecture humaine reste nécessaire.
 */
final class DraftChecker
{
    public const BLOCKING = 'bloquant';
    public const WARNING = 'avertissement';

    private const LONG_SENTENCE_WORDS = 25;

    public function __construct(
        private CourseMediaRepositoryInterface $captures,
        private PersonalDataChecker $personalData = new PersonalDataChecker(),
    ) {}

    /**
     * @return list<array{code: string, regle: ?string, niveau: string, message: string}>
     */
    public function check(PageContent $course): array
    {
        $issues = [];
        $html = (string) $course->getCode();
        $text = $this->plainText($html);
        $audience = $course->getCategory()?->getAudience();

        if (trim((string) $course->getTitle()) === '') {
            $issues[] = $this->issue('TITRE_MANQUANT', null, self::BLOCKING, 'Le cours n\'a pas de titre.');
        }
        if ($audience === null) {
            $issues[] = $this->issue('PARCOURS_SANS_PUBLIC', null, self::BLOCKING, 'Rattachez le cours à un parcours qui a un public (apprendre ou accompagner).');
        }

        $hasRealCapture = false;
        preg_match_all('/\[capture:(\d+)\]/', $html, $markers);
        foreach (array_unique(array_map('intval', $markers[1])) as $id) {
            $capture = $this->captures->findById($id);
            if ($capture === null) {
                $issues[] = $this->issue('CAPTURE_INCONNUE', 'CAP-02', self::BLOCKING, sprintf('La capture [capture:%d] n\'existe pas.', $id));
                continue;
            }
            $hasRealCapture = $hasRealCapture || $capture->getKind() === CourseMedia::KIND_CAPTURE;
        }

        if (preg_match('/<img\b/i', $html) === 1) {
            $issues[] = $this->issue('IMAGE_DIRECTE', 'CAP-02', self::BLOCKING, 'Les images passent par les captures du back-office : remplacez la balise <img> par un marqueur [capture:ID].');
        }

        $sourceUrl = $course->getSourceUrl();
        if ($hasRealCapture && ($sourceUrl === null || trim($sourceUrl) === '')) {
            $issues[] = $this->issue('SOURCE_MANQUANTE', 'CAP-04', self::BLOCKING, 'Le cours montre une capture d\'un service : indiquez la page officielle de référence.');
        }
        if ($sourceUrl !== null && trim($sourceUrl) !== '' && !$this->linksToHost($html, $sourceUrl)) {
            $issues[] = $this->issue('LIEN_OFFICIEL_MANQUANT', 'CAP-04', self::BLOCKING, sprintf('Ajoutez dans le cours un lien vers le site officiel (%s) pour faire la démarche.', parse_url($sourceUrl, \PHP_URL_HOST)));
        }

        foreach ($this->personalData->find($text) as [$code, $message]) {
            $issues[] = $this->issue($code, 'DON-04', self::BLOCKING, $message);
        }

        foreach ($this->sentences($text) as $sentence) {
            $words = count(preg_split('/\s+/u', $sentence, -1, \PREG_SPLIT_NO_EMPTY) ?: []);
            if ($words > self::LONG_SENTENCE_WORDS) {
                $issues[] = $this->issue('PHRASE_LONGUE', 'RED-01', self::WARNING, sprintf('Phrase de %d mots : « %s ».', $words, mb_strimwidth($sentence, 0, 80, '…')));
            }
        }

        if ($audience === Category::AUDIENCE_APPRENDRE && !$this->hasHeading($html, ['à vous'])) {
            $issues[] = $this->issue('SANS_EXERCICE', 'POS-06', self::WARNING, 'Terminez par une partie « À vous » où la personne refait le geste seule.');
        }
        if ($audience === Category::AUDIENCE_ACCOMPAGNER && !$this->hasHeading($html, ['quand orienter'])) {
            $issues[] = $this->issue('SANS_ORIENTATION', 'LIM-05', self::WARNING, 'Ajoutez une partie « Quand orienter ».');
        }
        if ($sourceUrl !== null && trim($sourceUrl) !== '' && $audience !== Category::AUDIENCE_ACCOMPAGNER && !$this->hasHeading($html, ['si ça ne marche pas', 'où se faire aider'])) {
            $issues[] = $this->issue('SANS_ORIENTATION', 'LIM-05', self::WARNING, 'Ajoutez une partie « Si ça ne marche pas » qui indique où se faire aider.');
        }

        return $issues;
    }

    /**
     * @param list<array{niveau: string}> $issues
     */
    public static function hasBlockingIssue(array $issues): bool
    {
        foreach ($issues as $issue) {
            if ($issue['niveau'] === self::BLOCKING) {
                return true;
            }
        }

        return false;
    }

    private function linksToHost(string $html, string $sourceUrl): bool
    {
        $host = strtolower((string) parse_url($sourceUrl, \PHP_URL_HOST));
        if ($host === '') {
            return false;
        }
        $baseHost = preg_replace('/^www\./', '', $host);
        preg_match_all('/<a\b[^>]*\bhref\s*=\s*["\']([^"\']+)["\']/i', $html, $links);
        foreach ($links[1] as $href) {
            $linkHost = preg_replace('/^www\./', '', strtolower((string) parse_url(html_entity_decode($href), \PHP_URL_HOST)));
            if ($linkHost !== '' && ($linkHost === $baseHost || str_ends_with($linkHost, '.'.$baseHost))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $needles
     */
    private function hasHeading(string $html, array $needles): bool
    {
        preg_match_all('/<h[1-6]\b[^>]*>(.*?)<\/h[1-6]>/is', $html, $headings);
        foreach ($headings[1] as $heading) {
            $heading = mb_strtolower($this->plainText($heading));
            foreach ($needles as $needle) {
                if (str_contains($heading, $needle)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function plainText(string $html): string
    {
        // Les fins de bloc deviennent des fins de phrase pour ne pas coller titres et paragraphes
        $html = preg_replace('/<\/(p|h[1-6]|li|td|th|div|blockquote)>|<br\s*\/?>/i', ".\n", $html) ?? $html;

        return trim(html_entity_decode(strip_tags($html), \ENT_QUOTES | \ENT_HTML5, 'UTF-8'));
    }

    /**
     * @return list<string>
     */
    private function sentences(string $text): array
    {
        $parts = preg_split('/(?<=[.!?…:;])\s+|\n+/u', $text) ?: [];

        return array_values(array_filter(array_map('trim', $parts), static fn (string $part) => $part !== '' && $part !== '.'));
    }

    /**
     * @return array{code: string, regle: ?string, niveau: string, message: string}
     */
    private function issue(string $code, ?string $rule, string $level, string $message): array
    {
        return ['code' => $code, 'regle' => $rule, 'niveau' => $level, 'message' => $message];
    }
}
