<?php

declare(strict_types=1);

namespace App\Tests\Functional\Mcp;

use App\Entity\CourseMedia;
use App\Service\CaptureFileManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Une image de l'ordinateur arrive en deux temps : le connecteur décrit l'image et obtient une adresse
 * à usage unique, puis le fichier y est envoyé. Elle devient une capture comme celles du back-office.
 */
final class ImageDepositConnectorTest extends McpTestCase
{
    // PNG de 1 × 1 pixel : pas besoin de l'extension GD pour le fabriquer
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    protected function tearDown(): void
    {
        $files = static::getContainer()->get(CaptureFileManager::class);
        foreach ($this->entityManager->getRepository(CourseMedia::class)->findAll() as $media) {
            @unlink($files->path((string) $media->getFilename()));
        }
        parent::tearDown();
    }

    public function testImageIsDepositedOnceAndBecomesACapture(): void
    {
        $address = $this->prepare(['type' => 'illustration', 'legende' => 'Les deux boutons de la souris.', 'texteAlternatif' => 'Souris vue de dessus, bouton gauche repère 1.']);

        $this->send($address, $this->file('png', base64_decode(self::PNG)));
        self::assertResponseStatusCodeSame(201);
        $result = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertMatchesRegularExpression('/^\[capture:\d+\]$/', $result['marqueur']);
        self::assertSame('illustration', $result['type']);

        $capture = $this->tool(self::CLAUDE, 'lister_captures')['data'][0];
        self::assertSame($result['marqueur'], $capture['marqueur']);
        self::assertSame($this->agentLabel(self::CLAUDE), $capture['auteur']);
        self::assertSame(1, $capture['largeur']);
        self::assertFileExists(static::getContainer()->get(CaptureFileManager::class)->path(basename($result['url'])));

        // L'adresse ne sert qu'une fois
        $this->send($address, $this->file('png', base64_decode(self::PNG)));
        self::assertResponseStatusCodeSame(404);
    }

    public function testSheetIsCheckedBeforeAnyFileIsSent(): void
    {
        $missingSource = $this->tool(self::CLAUDE, 'preparer_depot_image', ['type' => 'capture', 'legende' => 'Accueil CAF.', 'texteAlternatif' => 'Page d\'accueil.']);
        self::assertTrue($missingSource['error']);
        self::assertStringContainsString('sourceUrl', $missingSource['text']);

        self::assertTrue($this->tool(self::CLAUDE, 'preparer_depot_image', ['type' => 'illustration', 'legende' => 'x'])['error']);
        self::assertFalse($this->tool(self::CLAUDE, 'preparer_depot_image', [
            'type' => 'capture', 'legende' => 'Accueil CAF.', 'texteAlternatif' => 'Page d\'accueil.', 'pageSource' => 'https://www.caf.fr/', 'dateCapture' => '2026-10-15',
        ])['error']);
    }

    public function testRefusedFileKeepsTheAddressAndAKnownImageIsNotDuplicated(): void
    {
        $sheet = ['type' => 'illustration', 'legende' => 'Clavier.', 'texteAlternatif' => 'Clavier vu de dessus.'];
        $address = $this->prepare($sheet);

        $this->send($address, $this->file('txt', 'pas une image'));
        self::assertResponseStatusCodeSame(422);
        $this->send($address, null);
        self::assertResponseStatusCodeSame(422);

        $this->send($address, $this->file('png', base64_decode(self::PNG)));
        self::assertResponseStatusCodeSame(201);
        $marker = json_decode((string) $this->client->getResponse()->getContent(), true)['marqueur'];

        $this->send($this->prepare($sheet), $this->file('png', base64_decode(self::PNG)));
        self::assertResponseStatusCodeSame(409);
        self::assertSame($marker, json_decode((string) $this->client->getResponse()->getContent(), true)['marqueur']);

        $this->send('/depot-image/inconnu', $this->file('png', base64_decode(self::PNG)));
        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @param array<string, mixed> $sheet
     */
    private function prepare(array $sheet): string
    {
        $result = $this->tool(self::CLAUDE, 'preparer_depot_image', $sheet);
        self::assertFalse($result['error'], $result['text']);
        self::assertStringContainsString('curl', $result['data']['commande']);

        return (string) parse_url($result['data']['adresse'], \PHP_URL_PATH);
    }

    private function send(string $path, ?UploadedFile $file): void
    {
        $this->client->request('POST', $path, [], $file === null ? [] : ['image' => $file]);
    }

    private function file(string $extension, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'depot').'.'.$extension;
        file_put_contents($path, $content);

        return new UploadedFile($path, 'image.'.$extension, null, null, true);
    }
}
