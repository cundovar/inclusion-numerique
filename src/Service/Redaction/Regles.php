<?php

declare(strict_types=1);

namespace App\Service\Redaction;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Référentiel `regles/` : fichiers, règles indexées par identifiant (POS-01…) et gabarits de cours.
 */
final class Regles
{
    private const RULE_HEADING = '/^## ([A-Z]{3}-\d{2}) — (.+)$/mu';

    /** @var array<string, string>|null */
    private ?array $files = null;
    /** @var array<string, array{id: string, titre: string, fichier: string, texte: string}> */
    private array $rules = [];
    /** @var array<string, string> */
    private array $modeles = [];

    public function __construct(#[Autowire('%kernel.project_dir%/regles')] private string $dir) {}

    /**
     * @return array<string, string> contenu par nom de fichier
     */
    public function files(): array
    {
        $this->load();

        return $this->files ?? [];
    }

    /**
     * @return array<string, array{id: string, titre: string, fichier: string, texte: string}>
     */
    public function rules(): array
    {
        $this->load();

        return $this->rules;
    }

    /**
     * @return array<string, string> gabarit par nom (cours-apprendre…)
     */
    public function modeles(): array
    {
        $this->load();

        return $this->modeles;
    }

    public function statut(): string
    {
        return preg_match('/\*\*Statut : (.+?)\*\*/u', $this->files()['README.md'] ?? '', $m) === 1 ? $m[1] : 'inconnu';
    }

    public function ruleTitle(?string $id): ?string
    {
        return $id === null ? null : ($this->rules()[$id]['titre'] ?? null);
    }

    private function load(): void
    {
        if ($this->files !== null) {
            return;
        }

        $this->files = [];
        $names = glob($this->dir.'/*.md') ?: [];
        sort($names);
        foreach ($names as $path) {
            $this->files[basename($path)] = (string) file_get_contents($path);
        }

        foreach ($this->files as $file => $text) {
            preg_match_all(self::RULE_HEADING, $text, $matches, \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE);
            foreach ($matches as $index => $match) {
                $start = $match[0][1];
                $end = $matches[$index + 1][0][1] ?? \strlen($text);
                $this->rules[$match[1][0]] = [
                    'id' => $match[1][0],
                    'titre' => trim($match[2][0]),
                    'fichier' => $file,
                    'texte' => trim(substr($text, $start, $end - $start)),
                ];
            }
        }

        $modeles = glob($this->dir.'/modeles/*.md') ?: [];
        sort($modeles);
        foreach ($modeles as $path) {
            $this->modeles[basename($path, '.md')] = (string) file_get_contents($path);
        }
    }
}
