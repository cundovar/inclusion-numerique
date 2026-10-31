<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Category;
use App\Entity\Menus;
use App\Entity\Page;
use App\Entity\PageContent;
use App\Entity\QCM;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Contenus personnalisés : un cours, un parcours ou un QCM réservé n'est servi qu'à ses destinataires.
 * Les autres visiteurs, connectés ou non, ne le voient ni dans les listes, ni par son identifiant, ni dans le plan du site.
 */
final class PersonalizedContentApiTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private User $alice;
    private User $bruno;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $this->alice = $this->user('alice');
        $this->bruno = $this->user('bruno');
        $this->entityManager->flush();
    }

    public function testReservedCourseIsOnlyServedToItsLearners(): void
    {
        $this->course('public');
        $reserved = $this->course('pour-alice')->setReserve(true)->addDestinataire($this->alice);
        $this->course('reserve-sans-destinataire')->setReserve(true);
        $this->entityManager->flush();

        self::assertSame(['public'], $this->titles('/api/page_contents'));
        $this->assertNotFound('/api/page_contents/'.$reserved->getId());

        $this->client->loginUser($this->bruno, 'main');
        self::assertSame(['public'], $this->titles('/api/page_contents'));
        $this->assertNotFound('/api/page_contents/'.$reserved->getId());

        $this->client->loginUser($this->alice, 'main');
        self::assertSame(['pour-alice', 'public'], $this->titles('/api/page_contents'));
        $item = $this->get('/api/page_contents/'.$reserved->getId());
        self::assertTrue($item['reserve']);
        self::assertArrayNotHasKey('destinataires', $item, 'La liste des destinataires ne sort jamais par l\'API');
    }

    public function testReservedParcoursHidesItsModulesAndCourses(): void
    {
        $open = (new Category())->setName('Ouvert');
        $private = (new Category())->setName('Atelier Alice')->setReserve(true)->addDestinataire($this->alice);
        $this->entityManager->persist($open);
        $this->entityManager->persist($private);
        $this->entityManager->persist((new Menus())->setLabel('Module ouvert')->setCategory($open));
        $this->entityManager->persist((new Menus())->setLabel('Module Alice')->setCategory($private));
        $this->course('cours-ouvert')->setCategory($open);
        $this->course('cours-atelier')->setCategory($private);
        // Un cours public par lui-même reste masqué s'il est rangé dans un parcours réservé à une autre personne
        $this->course('cours-bruno-chez-alice')->setCategory($private)->setReserve(true)->addDestinataire($this->bruno);
        $this->entityManager->flush();

        self::assertSame(['Ouvert'], $this->names('/api/categories', 'name'));
        self::assertSame(['Module ouvert'], $this->names('/api/menuses', 'label'));
        self::assertSame(['cours-ouvert'], $this->titles('/api/page_contents'));

        $this->client->loginUser($this->bruno, 'main');
        self::assertSame(['cours-ouvert'], $this->titles('/api/page_contents'));

        $this->client->loginUser($this->alice, 'main');
        self::assertSame(['Atelier Alice', 'Ouvert'], $this->names('/api/categories', 'name'));
        self::assertSame(['Module Alice', 'Module ouvert'], $this->names('/api/menuses', 'label'));
        self::assertSame(['cours-atelier', 'cours-ouvert'], $this->titles('/api/page_contents'));
    }

    public function testReservedQcmIsAPersonalExercise(): void
    {
        $this->entityManager->persist((new QCM())->setTitre('Question publique')->setSolution('s'));
        $this->entityManager->persist((new QCM())->setTitre('Exercice pour Alice')->setSolution('s')->setReserve(true)->addDestinataire($this->alice));
        $this->entityManager->flush();

        self::assertSame(['Question publique'], $this->names('/api/q_c_ms', 'titre'));
        $this->client->loginUser($this->bruno, 'main');
        self::assertSame(['Question publique'], $this->names('/api/q_c_ms', 'titre'));
        $this->client->loginUser($this->alice, 'main');
        self::assertSame(['Exercice pour Alice', 'Question publique'], $this->names('/api/q_c_ms', 'titre'));
    }

    public function testAdministratorSeesEveryReservedContent(): void
    {
        $this->course('pour-alice')->setReserve(true)->addDestinataire($this->alice);
        $admin = $this->user('redaction', ['ROLE_ADMIN']);
        $this->entityManager->flush();

        $this->client->loginUser($admin, 'main');
        self::assertSame(['pour-alice'], $this->titles('/api/page_contents'));
    }

    public function testReservedContentStaysOutOfTheSitemapAndCourseCounts(): void
    {
        $private = (new Category())->setName('atelier-alice')->setReserve(true)->addDestinataire($this->alice);
        $menu = (new Menus())->setLabel('Module');
        $this->entityManager->persist($private);
        $this->entityManager->persist($menu);
        $this->course('cours-public')->setMenu($menu);
        $this->course('cours-perso')->setMenu($menu)->setReserve(true)->addDestinataire($this->alice);
        $this->course('cours-du-parcours-reserve')->setCategory($private);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->client->request('GET', '/sitemap.xml');
        $xml = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('/pages/cours-public', $xml);
        self::assertStringNotContainsString('cours-perso', $xml);
        self::assertStringNotContainsString('cours-du-parcours-reserve', $xml);
        self::assertStringNotContainsString('atelier-alice', $xml);

        self::assertSame(1, $this->entityManager->getRepository(Menus::class)->findOneBy(['label' => 'Module'])?->getCoursCount());
    }

    private function user(string $name, array $roles = []): User
    {
        $user = (new User())->setUsername($name)->setRoles($roles)->setPassword('inutilise');
        $this->entityManager->persist($user);

        return $user;
    }

    private function course(string $title): PageContent
    {
        $page = (new Page())->setSlug('/'.$title);
        $course = (new PageContent())->setTitle($title)->setStatus(PageContent::STATUS_PUBLISHED)->setPage($page);
        $this->entityManager->persist($page);
        $this->entityManager->persist($course);

        return $course;
    }

    /**
     * @return list<string>
     */
    private function titles(string $url): array
    {
        return $this->names($url, 'title');
    }

    /**
     * @return list<string>
     */
    private function names(string $url, string $field): array
    {
        $names = array_column($this->get($url)['member'], $field);
        sort($names);

        return $names;
    }

    /**
     * @return array<string, mixed>
     */
    private function get(string $url): array
    {
        $this->client->request('GET', $url, [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        self::assertResponseIsSuccessful();

        return json_decode((string) $this->client->getResponse()->getContent(), true);
    }

    private function assertNotFound(string $url): void
    {
        $this->client->request('GET', $url, [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        self::assertResponseStatusCodeSame(404);
    }
}
