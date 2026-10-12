<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Entity\Page;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Contrôles d'accès HTTP : back-office EasyAdmin et écritures API Platform.
 * Base SQLite dédiée aux tests (DATABASE_URL forcé dans phpunit.xml.dist).
 */
final class AccessControlTest extends WebTestCase
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

    /**
     * @dataProvider adminCrudPaths
     */
    public function testAdminCrudRequiresLogin(string $path): void
    {
        $this->client->request('GET', $path);

        self::assertResponseRedirects('/login');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function adminCrudPaths(): iterable
    {
        yield 'tableau de bord' => ['/admin'];
        yield 'création de catégorie' => ['/admin/category/new'];
        yield 'liste des utilisateurs' => ['/admin/user'];
    }

    public function testAdminCrudIsNotExposedOutsideAdminPrefix(): void
    {
        $this->client->request('GET', '/category/new');

        self::assertResponseStatusCodeSame(404);
    }

    public function testAdminCrudIsReachableForAdmin(): void
    {
        $this->client->loginUser($this->createUser('admin', ['ROLE_ADMIN']), 'main');
        $this->client->request('GET', '/admin/category/new');

        self::assertResponseIsSuccessful();
    }

    public function testAdminCrudIsForbiddenForRegularUser(): void
    {
        $this->client->loginUser($this->createUser('alice'), 'main');
        $this->client->request('GET', '/admin/category/new');

        self::assertResponseStatusCodeSame(403);
    }

    public function testPublicReadOfApiResourceStaysOpen(): void
    {
        $this->createPage('flexbox');

        $this->jsonRequest('GET', '/api/pages');

        self::assertResponseIsSuccessful();
    }

    /**
     * @dataProvider anonymousWrites
     */
    public function testAnonymousWriteOnApiResourceIsRejected(string $method, string $path): void
    {
        $page = $this->createPage('flexbox');

        $this->jsonRequest($method, sprintf($path, $page->getId()), ['slug' => 'pirate']);

        self::assertResponseStatusCodeSame(401);
        $this->entityManager->clear();
        self::assertSame('flexbox', $this->entityManager->find(Page::class, $page->getId())?->getSlug());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function anonymousWrites(): iterable
    {
        yield 'création' => ['POST', '/api/pages'];
        yield 'modification' => ['PATCH', '/api/pages/%d'];
        yield 'suppression' => ['DELETE', '/api/pages/%d'];
    }

    public function testRegularUserCannotWriteApiResource(): void
    {
        $page = $this->createPage('flexbox');
        $this->client->loginUser($this->createUser('alice'), 'main');

        $this->jsonRequest('DELETE', sprintf('/api/pages/%d', $page->getId()));

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminCanWriteApiResource(): void
    {
        $page = $this->createPage('flexbox');
        $this->client->loginUser($this->createUser('admin', ['ROLE_ADMIN']), 'main');

        $this->jsonRequest('DELETE', sprintf('/api/pages/%d', $page->getId()));

        self::assertResponseStatusCodeSame(204);
    }

    public function testAnonymousCannotReadUsers(): void
    {
        $this->createUser('alice');

        $this->jsonRequest('GET', '/api/users');

        self::assertResponseStatusCodeSame(401);
    }

    public function testAnonymousCannotPatchUser(): void
    {
        $alice = $this->createUser('alice');

        $this->jsonRequest('PATCH', sprintf('/api/users/%d', $alice->getId()), ['roles' => ['ROLE_ADMIN']]);

        self::assertResponseStatusCodeSame(401);
    }

    public function testUserCannotPatchAnotherUser(): void
    {
        $alice = $this->createUser('alice');
        $this->client->loginUser($this->createUser('bob'), 'main');

        $this->jsonRequest('PATCH', sprintf('/api/users/%d', $alice->getId()), ['username' => 'pirate']);

        self::assertResponseStatusCodeSame(403);
    }

    public function testUserCanRenameHimselfButNotChangeRoles(): void
    {
        $alice = $this->createUser('alice');
        $this->client->loginUser($alice, 'main');

        $this->jsonRequest('PATCH', sprintf('/api/users/%d', $alice->getId()), [
            'username' => 'alice2',
            'roles' => ['ROLE_ADMIN'],
            'password' => 'changed',
        ]);

        self::assertResponseIsSuccessful();
        $this->entityManager->clear();
        $reloaded = $this->entityManager->find(User::class, $alice->getId());
        self::assertSame('alice2', $reloaded?->getUsername());
        self::assertSame(['ROLE_USER'], $reloaded->getRoles());
        self::assertSame('hashed', $reloaded->getPassword());
    }

    /**
     * @param array<string, mixed>|null $body
     */
    private function jsonRequest(string $method, string $path, ?array $body = null): void
    {
        $contentType = 'PATCH' === $method ? 'application/merge-patch+json' : 'application/ld+json';

        $this->client->request($method, $path, [], [], [
            'HTTP_ACCEPT' => 'application/ld+json',
            'CONTENT_TYPE' => $contentType,
        ], null === $body ? null : json_encode($body, \JSON_THROW_ON_ERROR));
    }

    /**
     * @param list<string> $roles
     */
    private function createUser(string $username, array $roles = ['ROLE_USER']): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setPassword('hashed');
        $user->setRoles($roles);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createPage(string $slug): Page
    {
        $page = new Page();
        $page->setSlug($slug);

        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }
}
