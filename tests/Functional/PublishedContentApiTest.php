<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Page;
use App\Entity\PageContent;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Seuls les cours publiés sont exposés au public : API, plan du site, nombre de cours.
 */
final class PublishedContentApiTest extends WebTestCase
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

    public function testVisitorOnlyReceivesPublishedCourses(): void
    {
        $this->createCourse('publie', PageContent::STATUS_PUBLISHED);
        foreach ([PageContent::STATUS_DRAFT, PageContent::STATUS_IN_REVIEW, PageContent::STATUS_TO_REVISE] as $status) {
            $this->createCourse($status, $status);
        }

        self::assertSame(['publie'], $this->titles('/api/page_contents'));
    }

    public function testCoursesAreServedInRankOrderThenByAge(): void
    {
        $sansRang = $this->createCourse('sans-rang', PageContent::STATUS_PUBLISHED);
        $troisieme = $this->createCourse('troisieme', PageContent::STATUS_PUBLISHED)->setOrdre(3);
        $premier = $this->createCourse('premier', PageContent::STATUS_PUBLISHED)->setOrdre(1);
        $deuxieme = $this->createCourse('deuxieme', PageContent::STATUS_PUBLISHED)->setOrdre(2);
        $this->createCourse('autre-sans-rang', PageContent::STATUS_PUBLISHED);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/page_contents', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        self::assertResponseIsSuccessful();
        $titles = array_column(json_decode((string) $this->client->getResponse()->getContent(), true)['member'], 'title');

        // Les cours rangés d'abord, dans l'ordre pédagogique ; les autres ensuite, du plus ancien au plus récent
        self::assertSame(['premier', 'deuxieme', 'troisieme', 'sans-rang', 'autre-sans-rang'], $titles);
    }

    public function testVisitorCannotOpenADraftByItsIdentifier(): void
    {
        $draft = $this->createCourse('brouillon', PageContent::STATUS_DRAFT);

        $this->client->request('GET', sprintf('/api/page_contents/%d', $draft->getId()), [], [], ['HTTP_ACCEPT' => 'application/ld+json']);

        self::assertResponseStatusCodeSame(404);
    }

    public function testDraftsStayHiddenWhenSearchingBySlug(): void
    {
        $this->createCourse('brouillon', PageContent::STATUS_DRAFT, '/caf-mon-compte');

        self::assertSame([], $this->titles('/api/page_contents?page.slug=/caf-mon-compte'));
    }

    public function testAdministratorSeesDraftsToReviewThem(): void
    {
        $this->createCourse('publie', PageContent::STATUS_PUBLISHED);
        $this->createCourse('brouillon', PageContent::STATUS_DRAFT);
        $admin = (new User())->setUsername('redaction')->setRoles(['ROLE_ADMIN'])->setPassword('inutilise');
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->client->loginUser($admin, 'main');

        self::assertSame(['brouillon', 'publie'], $this->titles('/api/page_contents'));
    }

    public function testStatusCannotBeChangedThroughTheApi(): void
    {
        $draft = $this->createCourse('brouillon', PageContent::STATUS_DRAFT);
        $admin = (new User())->setUsername('redaction')->setRoles(['ROLE_ADMIN'])->setPassword('inutilise');
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->client->loginUser($admin, 'main');

        // La publication passe par le back-office, jamais par l'API qu'utilisera l'assistant de rédaction
        $this->client->request('PATCH', sprintf('/api/page_contents/%d', $draft->getId()), [], [], [
            'HTTP_ACCEPT' => 'application/ld+json',
            'CONTENT_TYPE' => 'application/merge-patch+json',
        ], json_encode(['status' => PageContent::STATUS_PUBLISHED], \JSON_THROW_ON_ERROR));

        $this->entityManager->clear();
        self::assertSame(PageContent::STATUS_DRAFT, $this->entityManager->find(PageContent::class, $draft->getId())?->getStatus());
    }

    public function testSitemapOnlyListsPublishedPages(): void
    {
        $this->createCourse('publie', PageContent::STATUS_PUBLISHED, '/envoyer-un-courriel');
        $this->createCourse('brouillon', PageContent::STATUS_DRAFT, '/brouillon-secret');
        // Relire depuis la base, comme une vraie requête (les collections inverses ne sont pas tenues à jour en mémoire)
        $this->entityManager->clear();

        $this->client->request('GET', '/sitemap.xml');

        self::assertResponseIsSuccessful();
        $xml = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('/pages/envoyer-un-courriel', $xml);
        self::assertStringNotContainsString('brouillon-secret', $xml);
        self::assertStringContainsString('/apprendre', $xml);
    }

    /**
     * @return list<string>
     */
    private function titles(string $url): array
    {
        $this->client->request('GET', $url, [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        self::assertResponseIsSuccessful();
        $titles = array_column(json_decode((string) $this->client->getResponse()->getContent(), true)['member'], 'title');
        sort($titles);

        return $titles;
    }

    private function createCourse(string $title, string $status, ?string $slug = null): PageContent
    {
        $page = (new Page())->setSlug($slug ?? '/'.$title);
        $course = (new PageContent())->setTitle($title)->setStatus($status)->setPage($page);
        $this->entityManager->persist($page);
        $this->entityManager->persist($course);
        $this->entityManager->flush();

        return $course;
    }
}
