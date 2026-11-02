<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Category;
use App\Entity\CourseMedia;
use App\Entity\Menus;
use App\Entity\NiveauCours;
use App\Entity\Page;
use App\Entity\PageContent;
use App\Entity\User;
use App\Service\Pdf\CourseHtmlBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Téléchargement d'un cours ou d'un module en PDF : mêmes règles d'accès que l'API publique, captures
 * insérées avec leur légende et leur source, lues sur le disque du site.
 */
final class CoursePdfTest extends WebTestCase
{
    // PNG de 1 × 1 pixel : le test ne dépend pas de GD pour fabriquer une image
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private Category $parcours;
    private Menus $module;
    /** @var list<string> */
    private array $files = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $this->parcours = (new Category())->setName('Premiers gestes numériques');
        $this->module = (new Menus())->setLabel('Découvrir son appareil')->setCategory($this->parcours);
        $this->entityManager->persist($this->parcours);
        $this->entityManager->persist($this->module);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }
        parent::tearDown();
    }

    public function testCaptureBecomesAFigureReadFromTheDiskWithItsSource(): void
    {
        $capture = $this->capture('souris');
        $course = $this->course('utiliser-la-souris', "<h2>Étape 1 : prendre la souris</h2>\n<p>[capture:{$capture->getId()}]</p><p>Texte [capture:999999] suivi.</p><h2>À vous</h2><p>Essayez.</p>");
        $this->entityManager->flush();

        $html = static::getContainer()->get(CourseHtmlBuilder::class)->build($course);

        self::assertStringContainsString('<figure class="capture-figure">', $html);
        self::assertStringContainsString('src="'.static::getContainer()->getParameter('kernel.project_dir').'/public/uploads/course-media/'.$capture->getFilename().'"', $html);
        self::assertStringContainsString('La souris posée sur le tapis', $html);
        self::assertStringContainsString('Page officielle : https://www.example.com/souris', $html);
        self::assertStringNotContainsString('[capture:', $html, 'Une capture introuvable disparaît sans laisser son marqueur');
        self::assertStringContainsString('Texte  suivi.', $html);
        self::assertStringContainsString('cours-titre--etape', $html);
        self::assertStringContainsString('cours-titre--exercice', $html);
        self::assertStringNotContainsString('http://', $html);
    }

    /**
     * @requires extension gd
     */
    public function testPublishedCourseIsDownloadedAsAPdfThatIsReusedUntilItChanges(): void
    {
        $level = (new NiveauCours())->setName('Je découvre')->setOrdre(1);
        $this->entityManager->persist($level);
        $capture = $this->capture('clavier');
        $course = $this->course('utiliser-le-clavier', "<p>Appuyez sur une touche.</p><p>[capture:{$capture->getId()}]</p>")->setNiveauCours($level);
        $this->entityManager->flush();

        $this->client->request('GET', '/cours/utiliser-le-clavier.pdf');
        $response = $this->client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/pdf', $response->headers->get('Content-Type'));
        self::assertStringContainsString('attachment; filename=utiliser-le-clavier.pdf', (string) $response->headers->get('Content-Disposition'));
        self::assertStringStartsWith('%PDF', (string) file_get_contents($response->getFile()->getPathname()));
        self::assertSame([true, true], [$response->headers->hasCacheControlDirective('private'), $response->headers->hasCacheControlDirective('no-cache')], 'Le navigateur le garde et revérifie ; pas un cache partagé');
        $etag = (string) $response->getEtag();

        // Même contenu : même empreinte, et le navigateur qui l'a déjà n'est pas resservi
        $this->client->request('GET', '/cours/utiliser-le-clavier.pdf', [], [], ['HTTP_IF_NONE_MATCH' => $etag]);
        self::assertSame(304, $this->client->getResponse()->getStatusCode());

        // Le cours change : nouvelle empreinte
        $this->entityManager->find(PageContent::class, $course->getId())->setTitle('Utiliser le clavier, pas à pas');
        $this->entityManager->flush();
        $this->client->request('GET', '/cours/utiliser-le-clavier.pdf');
        self::assertNotSame($etag, $this->client->getResponse()->getEtag());
    }

    public function testDraftsAndReservedCoursesAreNotFoundForOtherPeople(): void
    {
        $alice = $this->user('alice');
        $this->course('brouillon', '<p>Pas encore prêt.</p>')->setStatus(PageContent::STATUS_DRAFT);
        $this->course('pour-alice', '<p>Atelier.</p>')->setReserve(true)->addDestinataire($alice);
        $this->entityManager->flush();

        foreach (['/cours/brouillon.pdf', '/cours/pour-alice.pdf', '/cours/inconnu.pdf', '/module/999999.pdf'] as $url) {
            $this->client->request('GET', $url);
            self::assertSame(404, $this->client->getResponse()->getStatusCode(), $url);
        }

        $this->client->loginUser($alice, 'main');
        $this->client->request('GET', '/cours/pour-alice.pdf');
        $response = $this->client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->hasCacheControlDirective('no-store'), 'Un cours réservé n\'est gardé nulle part');

        $this->client->loginUser($this->user('redaction', ['ROLE_ADMIN']), 'main');
        $this->client->request('GET', '/cours/brouillon.pdf');
        self::assertSame(200, $this->client->getResponse()->getStatusCode(), 'Un administrateur relit les brouillons');
    }

    public function testModuleSheetGathersOnlyTheCoursesThePersonCanSee(): void
    {
        $this->course('allumer', '<p>Appuyez sur le bouton.</p>');
        $this->course('brouillon-du-module', '<p>Pas prêt.</p>')->setStatus(PageContent::STATUS_DRAFT);
        $empty = (new Menus())->setLabel('Module sans cours publié')->setCategory($this->parcours);
        $this->entityManager->persist($empty);
        $this->entityManager->flush();
        $this->course('brouillon-seul', '<p>Pas prêt.</p>')->setMenu($empty)->setStatus(PageContent::STATUS_DRAFT);
        $this->entityManager->flush();

        $this->client->request('GET', '/module/'.$this->module->getId().'.pdf');
        $response = $this->client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('filename=module-decouvrir-son-appareil.pdf', (string) $response->headers->get('Content-Disposition'));

        $this->client->request('GET', '/module/'.$empty->getId().'.pdf');
        self::assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @param list<string> $roles
     */
    private function user(string $name, array $roles = []): User
    {
        $user = (new User())->setUsername($name)->setRoles($roles)->setPassword('inutilise');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function course(string $slug, string $content): PageContent
    {
        $page = (new Page())->setSlug('/'.$slug)->setMenus($this->module);
        $course = (new PageContent())->setTitle($slug)->setStatus(PageContent::STATUS_PUBLISHED)->setPage($page)
            ->setCategory($this->parcours)->setMenu($this->module)->setCode($content);
        $this->entityManager->persist($page);
        $this->entityManager->persist($course);

        return $course;
    }

    private function capture(string $name): CourseMedia
    {
        $filename = 'test-pdf-'.$name.'-'.bin2hex(random_bytes(4)).'.png';
        $path = static::getContainer()->getParameter('kernel.project_dir').'/public'.CourseMedia::PUBLIC_DIR.'/'.$filename;
        if (!is_dir(\dirname($path))) {
            mkdir(\dirname($path), 0775, true);
        }
        file_put_contents($path, base64_decode(self::PNG));
        $this->files[] = $path;

        $capture = (new CourseMedia())->setKind(CourseMedia::KIND_CAPTURE)->setFilename($filename)->setService('Exemple')
            ->setSourceUrl('https://www.example.com/'.$name)->setCapturedAt(new \DateTimeImmutable('2026-10-15'))
            ->setCaption(ucfirst($name === 'souris' ? 'la souris posée sur le tapis' : 'le clavier'))->setAltText('Photo : '.$name);
        $this->entityManager->persist($capture);
        $this->entityManager->flush();

        return $capture;
    }
}
