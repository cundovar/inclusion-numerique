<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\CourseMedia;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Le site lit les captures pour les afficher dans les cours ; l'API est en lecture seule
 * et ne montre pas le suivi éditorial interne.
 */
final class CaptureApiTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testVisitorReadsTheRequestedCapturesWithTheirSource(): void
    {
        $caf = $this->createCapture('CAF', 'a1.png');
        $this->createCapture('Ameli', 'b2.png');

        $this->client->request('GET', sprintf('/api/captures?id[]=%d', $caf->getId()), [], [], ['HTTP_ACCEPT' => 'application/ld+json']);

        self::assertResponseIsSuccessful();
        $members = json_decode((string) $this->client->getResponse()->getContent(), true)['member'];
        self::assertCount(1, $members);
        self::assertSame([
            'id' => $caf->getId(),
            'kind' => 'capture',
            'service' => 'CAF',
            'sourceUrl' => 'https://www.caf.fr/',
            'capturedAt' => '2026-10-15',
            'altText' => "Page d'accueil de caf.fr",
            'caption' => 'Le bouton « Mon Compte »',
            'width' => 1280,
            'height' => 800,
            'url' => '/uploads/course-media/a1.png',
        ], array_diff_key($members[0], ['@id' => 1, '@type' => 1]));
    }

    public function testCapturesCannotBeWrittenThroughTheApi(): void
    {
        $capture = $this->createCapture('CAF', 'a1.png');
        $admin = (new User())->setUsername('redaction')->setRoles(['ROLE_ADMIN'])->setPassword('inutilise');
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->client->loginUser($admin, 'main');

        // Aucune route d'écriture : même un administrateur passe par le back-office
        $this->client->request('DELETE', sprintf('/api/captures/%d', $capture->getId()));
        self::assertResponseStatusCodeSame(405);
        $this->client->request('POST', '/api/captures', [], [], ['CONTENT_TYPE' => 'application/ld+json'], '{}');
        self::assertResponseStatusCodeSame(405);

        $this->entityManager->clear();
        self::assertNotNull($this->entityManager->find(CourseMedia::class, $capture->getId()));
    }

    private function createCapture(string $service, string $filename): CourseMedia
    {
        $capture = (new CourseMedia())
            ->setService($service)
            ->setSourceUrl('https://www.caf.fr/')
            ->setCapturedAt(new \DateTimeImmutable('2026-10-15'))
            ->setAuthor('Agent de rédaction')
            ->setNextReviewAt(new \DateTimeImmutable('2027-01-15'))
            ->setAltText("Page d'accueil de caf.fr")
            ->setCaption('Le bouton « Mon Compte »')
            ->setFilename($filename)
            ->describeFile('image/png', 1280, 800, str_repeat('a', 64));
        $this->entityManager->persist($capture);
        $this->entityManager->flush();

        return $capture;
    }
}
