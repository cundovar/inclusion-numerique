<?php

declare(strict_types=1);

namespace App\Infrastructure\Note\Service;

use App\Domain\Note\Service\NoteEncryptionInterface;

final class NoteEncryptionService implements NoteEncryptionInterface
{
    private string $encryptionKey;
    private string $cipher = 'aes-256-cbc';

    public function __construct(string $appSecret)
    {
        $this->encryptionKey = hash('sha256', $appSecret, true);
    }

    public function encrypt(string $plaintext): string
    {
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);

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

        return base64_encode($iv . $encrypted);
    }

    public function decrypt(string $ciphertext): string
    {
        $data = base64_decode($ciphertext);

        if ($data === false) {
            throw new \RuntimeException('Erreur lors du décodage');
        }

        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        $decrypted = openssl_decrypt(
            $encrypted,
            $this->cipher,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            throw new \RuntimeException('Erreur lors du déchiffrement');
        }

        return $decrypted;
    }
}
