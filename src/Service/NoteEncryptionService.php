<?php

namespace App\Service;

class NoteEncryptionService
{
    private string $encryptionKey;
    private string $cipher = 'aes-256-cbc';

    public function __construct(string $appSecret)
    {
        // Utiliser APP_SECRET comme base pour la clé de chiffrement
        // Créer une clé de 32 octets (256 bits) pour AES-256
        $this->encryptionKey = hash('sha256', $appSecret, true);
    }

    /**
     * Chiffre le contenu d'une note
     */
    public function encrypt(string $plaintext): string
    {
        // Générer un vecteur d'initialisation aléatoire
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);

        // Chiffrer les données
        $encrypted = openssl_encrypt(
            $plaintext,
            $this->cipher,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            throw new \RuntimeException('Erreur lors du chiffrement');
        }

        // Combiner IV et données chiffrées, puis encoder en base64
        return base64_encode($iv . $encrypted);
    }

    /**
     * Déchiffre le contenu d'une note
     */
    public function decrypt(string $ciphertext): string
    {
        // Décoder depuis base64 (strict), fallback si contenu non chiffré
        $data = base64_decode($ciphertext, true);
        if ($data === false) {
            return $ciphertext;
        }

        // Extraire IV et données chiffrées
        $ivLength = openssl_cipher_iv_length($this->cipher);
        if ($ivLength === false || strlen($data) <= $ivLength) {
            return $ciphertext;
        }
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        // Déchiffrer
        $decrypted = openssl_decrypt(
            $encrypted,
            $this->cipher,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            return $ciphertext;
        }

        return $decrypted;
    }
}
