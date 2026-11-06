<?php

declare(strict_types=1);

namespace App\Service\Redaction;

/**
 * Rapport de relecture envoyé par l'agent relecteur (cours ou QCM) : avis, résumé et constats rattachés aux règles.
 */
final class ReviewReport
{
    public const FAVORABLE = 'favorable';
    public const RESERVES = 'reserves';
    public const UNFAVORABLE = 'defavorable';
    public const OPINIONS = [self::FAVORABLE, self::RESERVES, self::UNFAVORABLE];

    /**
     * @param array<string, mixed> $data
     * @param list<string> $opinions avis acceptés
     * @return array{0: array{avis: string, resume: string, points: list<array{regle: ?string, constat: string, suggestion: ?string}>}, 1: list<array{champ: string, message: string}>}
     */
    public static function parse(array $data, array $opinions = self::OPINIONS): array
    {
        $errors = [];
        $opinion = (string) ($data['avis'] ?? '');
        if (!\in_array($opinion, $opinions, true)) {
            $errors[] = ['champ' => 'avis', 'message' => 'Avis attendu : favorable, reserves ou defavorable.'];
        }
        $summary = trim((string) ($data['resume'] ?? ''));
        if ($summary === '' || mb_strlen($summary) > 2000) {
            $errors[] = ['champ' => 'resume', 'message' => 'Résumé de la relecture attendu (2 000 caractères au plus).'];
        }
        $points = [];
        $rawPoints = $data['points'] ?? [];
        if (!\is_array($rawPoints) || \count($rawPoints) > 50) {
            $errors[] = ['champ' => 'points', 'message' => 'Liste de 50 constats au plus attendue.'];
            $rawPoints = [];
        }
        foreach (array_values($rawPoints) as $index => $point) {
            $rule = \is_array($point) ? ($point['regle'] ?? null) : null;
            $finding = \is_array($point) ? trim((string) ($point['constat'] ?? '')) : '';
            $suggestion = \is_array($point) ? trim((string) ($point['suggestion'] ?? '')) : '';
            if ($rule !== null && preg_match('/^[A-Z]{3}-\d{2}$/', (string) $rule) !== 1) {
                $errors[] = ['champ' => "points[$index].regle", 'message' => 'Identifiant de règle attendu, par exemple POS-01.'];
            }
            if ($finding === '' || mb_strlen($finding) > 1000 || mb_strlen($suggestion) > 1000) {
                $errors[] = ['champ' => "points[$index].constat", 'message' => 'Constat attendu (1 000 caractères au plus).'];
            }
            $points[] = ['regle' => $rule === null ? null : (string) $rule, 'constat' => $finding, 'suggestion' => $suggestion === '' ? null : $suggestion];
        }

        return [['avis' => $opinion, 'resume' => $summary, 'points' => $points], $errors];
    }
}
