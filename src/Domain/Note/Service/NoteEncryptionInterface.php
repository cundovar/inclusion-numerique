<?php

declare(strict_types=1);

namespace App\Domain\Note\Service;

interface NoteEncryptionInterface
{
    /**
     * Chiffre le contenu d'une note
     */
    public function encrypt(string $plaintext): string;

    /**
     * Déchiffre le contenu d'une note
     */
    public function decrypt(string $ciphertext): string;
}
