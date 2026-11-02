<?php

declare(strict_types=1);

namespace App\Service\Redaction;

/**
 * Repère dans un texte les données qui pourraient être celles d'une personne réelle (règle DON-04).
 * Partagé par les contrôles des cours et des QCM.
 */
final class PersonalDataChecker
{
    // Domaines réservés aux exemples (RFC 2606)
    private const EXAMPLE_EMAIL_DOMAIN = '/@([a-z0-9-]+\.)*(example\.(com|org|net)|[a-z0-9-]+\.example)$/i';

    /**
     * @return list<array{0: string, 1: string}> code du contrôle et message
     */
    public function find(string $text): array
    {
        $found = [];
        // Numéro de sécurité sociale : sexe, année, mois, département, commune, ordre, clé (espaces facultatifs)
        if (preg_match('/\b[12]\s?\d{2}\s?(0[1-9]|1[0-2]|[2-9]\d)\s?(\d{2}|2A|2B)\s?\d{3}\s?\d{3}(\s?\d{2})?\b/i', $text) === 1) {
            $found[] = ['NUMERO_SECURITE_SOCIALE', 'Le contenu contient un numéro au format du numéro de sécurité sociale. Utilisez un exemple écrit avec des X.'];
        }
        if (preg_match('/\bFR\d{2}(\s?[0-9A-Z]{4}){5}\s?[0-9A-Z]{3}\b/i', $text) === 1) {
            $found[] = ['IBAN', 'Le contenu contient un IBAN. Utilisez un exemple écrit avec des X.'];
        }
        if (preg_match('/(?<!\d)(\+33\s?|0)[1-9]([\s.-]?\d{2}){4}(?!\d)/', $text) === 1) {
            $found[] = ['TELEPHONE', 'Le contenu contient un numéro de téléphone. Écrivez un exemple avec des X (06 XX XX XX XX).'];
        }
        preg_match_all('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $text, $emails);
        foreach ($emails[0] as $email) {
            if (preg_match(self::EXAMPLE_EMAIL_DOMAIN, $email) !== 1) {
                $found[] = ['COURRIEL_REEL', sprintf('L\'adresse « %s » pourrait être réelle. Utilisez une adresse en @example.com.', $email)];
            }
        }

        return $found;
    }
}
