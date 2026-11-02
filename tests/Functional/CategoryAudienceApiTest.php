<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Category;
use App\Entity\CourseMedia;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Le public d'un parcours décide de l'entrée de l'accueil où il apparaît.
 */
final class CategoryAudienceApiTest extends WebTestCase
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

    public function testPublicCategoryListExposesTheAudience(): void
    {
        $this->createCategory('messagerie', Category::AUDIENCE_APPRENDRE);

        $this->client->request('GET', '/api/categories', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);

        self::assertResponseIsSuccessful();
        $members = json_decode((string) $this->client->getResponse()->getContent(), true)['member'];
        self::assertSame('apprendre', $members[0]['audience']);
    }

    public function testPublicCategoryListExposesTheThumbnailOfAParcours(): void
    {
        $media = (new CourseMedia())->setKind(CourseMedia::KIND_ILLUSTRATION)->setFilename('miniature.png')->setCaption('Miniature')->setAltText('Un clavier et une souris');
        $this->entityManager->persist($media);
        $withThumbnail = $this->createCategory('messagerie', Category::AUDIENCE_APPRENDRE)->setIllustration($media);
        $this->createCategory('sans-miniature', Category::AUDIENCE_APPRENDRE);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/categories', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);

        self::assertResponseIsSuccessful();
        $members = array_column(json_decode((string) $this->client->getResponse()->getContent(), true)['member'], null, 'name');
        self::assertSame('/uploads/course-media/miniature.png', $members['messagerie']['illustrationUrl']);
        self::assertSame('Un clavier et une souris', $members['messagerie']['illustrationAlt']);
        self::assertNull($members['sans-miniature']['illustrationUrl']);
        self::assertSame($media->getId(), $withThumbnail->getIllustration()?->getId());
    }

    public function testAdminCannotSetAnUnknownAudience(): void
    {
        $category = $this->createCategory('messagerie', Category::AUDIENCE_APPRENDRE);
        $this->client->loginUser($this->createAdmin(), 'main');

        $this->patch($category, ['audience' => 'tout-le-monde']);
        self::assertResponseStatusCodeSame(422);

        $this->patch($category, ['audience' => Category::AUDIENCE_ACCOMPAGNER]);
        self::assertResponseIsSuccessful();
        $this->entityManager->clear();
        self::assertSame('accompagner', $this->entityManager->find(Category::class, $category->getId())?->getAudience());
    }

    /**
     * @param array<string, mixed> $body
     */
    private function patch(Category $category, array $body): void
    {
        $this->client->request('PATCH', sprintf('/api/categories/%d', $category->getId()), [], [], [
            'HTTP_ACCEPT' => 'application/ld+json',
            'CONTENT_TYPE' => 'application/merge-patch+json',
        ], json_encode($body, \JSON_THROW_ON_ERROR));
    }

    private function createCategory(string $name, ?string $audience): Category
    {
        $category = (new Category())->setName($name)->setAudience($audience);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    private function createAdmin(): User
    {
        $admin = (new User())->setUsername('redaction')->setRoles(['ROLE_ADMIN'])->setPassword('inutilise');
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        return $admin;
    }
}
