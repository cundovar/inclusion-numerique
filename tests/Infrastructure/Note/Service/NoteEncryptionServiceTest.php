<?php

declare(strict_types=1);

use App\Infrastructure\Note\Service\NoteEncryptionService;
use PHPUnit\Framework\TestCase;

final class NoteEncryptionServiceTest extends TestCase
{
    public function testEncryptDecryptRoundTrip(): void
    {
        // On instancie le service avec un secret fixe pour garder un contexte de test stable.
        $service = new NoteEncryptionService('test-secret');
        $plaintext = 'contenu sensible';

        // On chiffre d'abord le texte brut.
        $ciphertext = $service->encrypt($plaintext);

        // Le résultat chiffré ne doit pas être identique au texte d'origine.
        $this->assertNotSame($plaintext, $ciphertext);

        // Le test le plus important : après chiffrement puis déchiffrement,
        // on doit retrouver exactement la valeur de départ.
        $this->assertSame($plaintext, $service->decrypt($ciphertext));
    }
}
