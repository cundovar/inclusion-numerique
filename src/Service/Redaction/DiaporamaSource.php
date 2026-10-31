<?php

declare(strict_types=1);

namespace App\Service\Redaction;

use App\Entity\Diaporama;
use App\Entity\PageContent;

/**
 * Texte source d'un diaporama : le cours, ou tous les cours du chapitre dans l'ordre du parcours.
 * Sert au contrôle de copie (DIA-02), à l'empreinte qui signale un diaporama à mettre à jour,
 * et à la règle « un chapitre attend que tous ses cours soient publiés ».
 */
final class DiaporamaSource
{
    /**
     * @return list<PageContent>
     */
    public function cours(Diaporama $diaporama): array
    {
        if ($diaporama->getCours() !== null) {
            return [$diaporama->getCours()];
        }
        $cours = $diaporama->getChapitre()?->getPageContents()->toArray() ?? [];
        usort($cours, static fn (PageContent $a, PageContent $b) => [$a->getOrdre() ?? \PHP_INT_MAX, $a->getId()] <=> [$b->getOrdre() ?? \PHP_INT_MAX, $b->getId()]);

        return $cours;
    }

    public function texte(Diaporama $diaporama): string
    {
        return implode("\n\n", array_map(self::texteDuCours(...), $this->cours($diaporama)));
    }

    public function empreinte(Diaporama $diaporama): string
    {
        return hash('sha256', $this->texte($diaporama));
    }

    /**
     * Le cours a changé depuis la synthèse : le diaporama est à mettre à jour.
     */
    public function estPerime(Diaporama $diaporama): bool
    {
        return $diaporama->getTypeCible() !== null && $diaporama->getEmpreinteSource() !== null
            && $diaporama->getEmpreinteSource() !== $this->empreinte($diaporama);
    }

    /**
     * Titres des cours du chapitre qui ne sont pas encore publiés.
     *
     * @return list<string>
     */
    public function coursNonPublies(Diaporama $diaporama): array
    {
        if ($diaporama->getChapitre() === null) {
            return [];
        }

        return array_values(array_map(
            static fn (PageContent $cours) => (string) $cours->getTitle(),
            array_filter($this->cours($diaporama), static fn (PageContent $cours) => !$cours->isPublished()),
        ));
    }

    public static function texteDuCours(PageContent $cours): string
    {
        $html = (string) ($cours->getCode() ?: $cours->getContent());
        $texte = html_entity_decode(strip_tags(preg_replace('/\[capture:\d+\]/', ' ', $html) ?? $html), \ENT_QUOTES | \ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', (string) $cours->getTitle().' '.$texte) ?? '');
    }
}
