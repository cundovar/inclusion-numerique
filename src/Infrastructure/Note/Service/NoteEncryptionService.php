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
        // Transforme le secret de l'application en clé binaire stable.
        // En test, cela permet d'obtenir un comportement reproductible
        // tant qu'on instancie le service avec le même secret.
        $this->encryptionKey = hash('sha256', $appSecret, true);
    }

    public function encrypt(string $plaintext): string
    {
        // AES-CBC nécessite un vecteur d'initialisation (IV).
        // Cet IV doit changer à chaque chiffrement pour éviter d'obtenir
        // toujours le même résultat avec le même texte en entrée.
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);

        // Le contenu est chiffré en binaire brut.
        // En test, on ne vérifie pas la valeur exacte du résultat car elle change
        // à cause de l'IV aléatoire ; on vérifie plutôt que le résultat n'est pas vide
        // et qu'un decrypt() redonne bien le texte d'origine.
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

        // On concatène l'IV avec le texte chiffré, puis on encode en base64
        // pour obtenir une chaîne facilement stockable en base de données.
        return base64_encode($iv . $encrypted);
    }

    public function decrypt(string $ciphertext): string
    {
        // On reconvertit la chaîne base64 vers sa forme binaire initiale.
        // En test, on peut vérifier qu'une chaîne invalide déclenche bien une erreur.
        $data = base64_decode($ciphertext);

        if ($data === false) {
            throw new \RuntimeException('Erreur lors du décodage');
        }

        // On re-sépare ensuite les deux parties stockées ensemble :
        // l'IV au début, puis le contenu réellement chiffré.
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        // Le déchiffrement doit utiliser exactement le même algorithme,
        // la même clé dérivée et l'IV extrait du message.
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
