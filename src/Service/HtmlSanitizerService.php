<?php

namespace App\Service;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Service de nettoyage HTML pour prévenir les attaques XSS
 * Utilise HTMLPurifier avec une configuration whitelist stricte
 */
class HtmlSanitizerService
{
    private HTMLPurifier $purifier;

    public function __construct()
    {
        $config = HTMLPurifier_Config::createDefault();

        // ══════════════════════════════════════════════
        // NIVEAU 1 : Tags et attributs autorisés (whitelist)
        // ══════════════════════════════════════════════
        $config->set('HTML.Allowed', implode(',', [
            // Titres
            'h1[class|id]', 'h2[class|id]', 'h3[class|id]', 'h4[class|id]', 'h5[class|id]', 'h6[class|id]',
            // Texte
            'p[class]', 'br', 'hr',
            'strong', 'em', 'u', 's', 'mark', 'small', 'sub', 'sup',
            // Listes
            'ul[class]', 'ol[class]', 'li[class]',
            // Blocs
            'blockquote[class]', 'pre[class]', 'code[class]',
            'span[class]', 'div[class]',
            // Tables
            'table[class]', 'thead', 'tbody', 'tfoot', 'tr', 'th[class|colspan|rowspan]', 'td[class|colspan|rowspan]',
            // Liens
            'a[href|title|target|rel|class]',
            // Interactif
            'details[class]', 'summary[class]',
            // Images
            'img[src|alt|class|width|height]',
            // Styles (balise style pour les classes CSS)
            'style',
        ]));

        // ══════════════════════════════════════════════
        // NIVEAU 2 : Protocoles URL autorisés
        // ══════════════════════════════════════════════
        $config->set('URI.AllowedSchemes', [
            'http'   => true,
            'https'  => true,
            'mailto' => true,
            // Bloqués par défaut : javascript, data, vbscript
        ]);

        // ══════════════════════════════════════════════
        // NIVEAU 3 : Sécurité des liens externes
        // ══════════════════════════════════════════════
        $config->set('HTML.TargetBlank', true);           // Permet target="_blank"
        $config->set('Attr.AllowedRel', [                 // Relations autorisées
            'noopener'    => true,
            'noreferrer'  => true,
            'nofollow'    => true,
        ]);

        // ══════════════════════════════════════════════
        // NIVEAU 4 : Autoriser CSS inline (optionnel)
        // ══════════════════════════════════════════════
        // Pour bloquer tout CSS inline, décommentez :
        // $config->set('CSS.AllowedProperties', []);

        // Pour autoriser certaines propriétés CSS :
        $config->set('CSS.AllowedProperties', [
            'color', 'background-color', 'font-weight', 'font-style',
            'text-align', 'text-decoration', 'margin', 'padding',
            'border', 'width', 'height', 'max-width', 'max-height',
        ]);

        // ══════════════════════════════════════════════
        // NIVEAU 5 : Encodage et cache
        // ══════════════════════════════════════════════
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('Cache.DefinitionImpl', null);       // Désactive le cache fichier

        // ══════════════════════════════════════════════
        // NIVEAU 6 : Autoriser les IDs pour les ancres
        // ══════════════════════════════════════════════
        $config->set('Attr.EnableID', true);

        $this->purifier = new HTMLPurifier($config);
    }

    /**
     * Nettoie le HTML en supprimant tout code potentiellement dangereux
     */
    public function sanitize(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        $cleanHtml = $this->purifier->purify($html);

        // Post-traitement : ajouter rel="noopener noreferrer" aux liens target="_blank"
        $cleanHtml = $this->secureExternalLinks($cleanHtml);

        return $cleanHtml;
    }

    /**
     * Sécurise les liens externes avec target="_blank"
     */
    private function secureExternalLinks(string $html): string
    {
        // Ajoute rel="noopener noreferrer" aux liens avec target="_blank" qui n'ont pas déjà rel
        return preg_replace_callback(
            '/<a\s+([^>]*target=["\']_blank["\'][^>]*)>/i',
            function ($matches) {
                $attributes = $matches[1];

                // Si rel n'existe pas, l'ajouter
                if (!preg_match('/\brel\s*=/i', $attributes)) {
                    return '<a ' . $attributes . ' rel="noopener noreferrer">';
                }

                return $matches[0];
            },
            $html
        );
    }
}
