<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\Redaction\PersonalDataChecker;
use PHPUnit\Framework\TestCase;

/**
 * Données personnelles repérées dans un cours ou un QCM (DON-04).
 */
final class PersonalDataCheckerTest extends TestCase
{
    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function realData(): iterable
    {
        yield 'sécurité sociale' => ['Votre numéro : 1 85 05 78 006 084 36.', 'NUMERO_SECURITE_SOCIALE'];
        yield 'IBAN' => ['IBAN FR76 3000 6000 0112 3456 7890 189.', 'IBAN'];
        yield 'téléphone' => ['Appelez le 06 12 34 56 78.', 'TELEPHONE'];
        yield 'téléphone international' => ['Appelez le +33 6 12 34 56 78.', 'TELEPHONE'];
        yield 'courriel' => ['Écrivez à jean.dupont@gmail.com.', 'COURRIEL_REEL'];
    }

    /**
     * @dataProvider realData
     */
    public function testRealDataIsFound(string $text, string $code): void
    {
        self::assertContains($code, array_column((new PersonalDataChecker())->find($text), 0));
    }

    public function testFictionalDataIsAllowed(): void
    {
        $text = 'Mme Exemple écrit à mme.exemple@example.com, à contact@mairie.example ou appelle le 06 XX XX XX XX. Numéro : 1 XX XX XX XXX XXX XX.';

        self::assertSame([], (new PersonalDataChecker())->find($text));
    }
}
