<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Entity\CourseMedia;
use App\Entity\PageContent;
use App\Entity\User;
use App\Service\CaptureFileManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Le responsable éditorial dépose une capture, la remplace sans toucher au cours,
 * voit où elle est utilisée et ce qu'il doit revérifier.
 */
final class CaptureBackOfficeTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private CaptureFileManager $files;
    /** @var list<string> */
    private array $createdFiles = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->files = static::getContainer()->get(CaptureFileManager::class);
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $admin = (new User())->setUsername('redaction')->setRoles(['ROLE_ADMIN'])->setPassword('inutilise');
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->client->loginUser($admin, 'main');
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $path) {
            @unlink($path);
        }
        parent::tearDown();
    }

    public function testUploadThenReplaceTheImageKeepsTheCaptureAndItsCourses(): void
    {
        // Dépôt d'une capture
        $crawler = $this->client->request('GET', '/admin/course-media/new');
        self::assertResponseIsSuccessful();
        $form = $crawler->filter('form[name="CourseMedia"]')->form();
        $form['CourseMedia[filename][file]']->upload($this->image(640, 400));
        $form['CourseMedia[kind]']->select(CourseMedia::KIND_CAPTURE);
        $form['CourseMedia[service]'] = 'CAF';
        $form['CourseMedia[caption]'] = 'Le bouton « Mon Compte » ouvre la connexion.';
        $form['CourseMedia[altText]'] = "Page d'accueil de caf.fr, bouton « Mon Compte » en haut à droite";
        $form['CourseMedia[sourceUrl]'] = 'https://www.caf.fr/';
        $form['CourseMedia[capturedAt]'] = '2026-10-15';
        $form['CourseMedia[nextReviewAt]'] = '2026-10-15';
        $this->client->submit($form);
        self::assertResponseRedirects();

        $capture = $this->entityManager->getRepository(CourseMedia::class)->findOneBy([]);
        self::assertNotNull($capture);
        self::assertSame(['image/png', 640, 400], [$capture->getMimeType(), $capture->getWidth(), $capture->getHeight()]);
        $firstFile = $this->files->path((string) $capture->getFilename());
        $this->createdFiles[] = $firstFile;
        self::assertFileExists($firstFile);

        // Un cours qui l'utilise par son marqueur
        $course = (new PageContent())->setTitle('Se connecter à la CAF')->setCode(sprintf('<p>%s</p>', $capture->getMarker()));
        $this->entityManager->persist($course);
        $this->entityManager->flush();

        // Remplacement du fichier : même capture, nouvelle image, ancien fichier supprimé
        $crawler = $this->client->request('GET', sprintf('/admin/course-media/%d/edit', $capture->getId()));
        self::assertResponseIsSuccessful();
        $form = $crawler->filter('form[name="CourseMedia"]')->form();
        $form['CourseMedia[filename][file]']->upload($this->image(800, 500));
        $this->client->submit($form);
        self::assertResponseRedirects();

        $this->entityManager->clear();
        $replaced = $this->entityManager->find(CourseMedia::class, $capture->getId());
        $secondFile = $this->files->path((string) $replaced->getFilename());
        $this->createdFiles[] = $secondFile;
        self::assertSame([800, 500], [$replaced->getWidth(), $replaced->getHeight()]);
        self::assertNotSame($firstFile, $secondFile);
        self::assertFileExists($secondFile);
        self::assertFileDoesNotExist($firstFile);
        self::assertStringContainsString($replaced->getMarker(), (string) $this->entityManager->find(PageContent::class, $course->getId())->getCode());

        // La fiche indique les cours qui l'utilisent
        $this->client->request('GET', sprintf('/admin/course-media/%d', $capture->getId()));
        self::assertSelectorTextContains('body', 'Utilisée dans Se connecter à la CAF');
        self::assertSelectorTextContains('body', 'Marqueur à insérer '.$capture->getMarker());

        // Le tableau de bord la signale à revérifier, avec les cours en attente de relecture
        $this->entityManager->persist((new PageContent())->setTitle('Fiche CAF à relire')->setStatus(PageContent::STATUS_IN_REVIEW));
        $this->entityManager->flush();
        $this->client->request('GET', '/admin');
        self::assertSelectorTextContains('[data-testid="captures-due"]', $replaced->getMarker());
        self::assertSelectorTextContains('[data-testid="courses-to-review"]', 'Fiche CAF à relire');
    }

    public function testCaptureWithoutSourceIsRefused(): void
    {
        $crawler = $this->client->request('GET', '/admin/course-media/new');
        $form = $crawler->filter('form[name="CourseMedia"]')->form();
        $form['CourseMedia[filename][file]']->upload($this->image(10, 10));
        $form['CourseMedia[kind]']->select(CourseMedia::KIND_CAPTURE);
        $form['CourseMedia[caption]'] = 'Légende';
        $form['CourseMedia[altText]'] = 'Texte alternatif';
        $this->client->submit($form);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('body', "Indiquez l'adresse de la page officielle capturée.");
        self::assertSame(0, $this->entityManager->getRepository(CourseMedia::class)->count([]));
    }

    private function image(int $width, int $height): string
    {
        $path = sys_get_temp_dir().'/'.uniqid('capture-', true).'.png';
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, (int) imagecolorallocate($image, random_int(0, 255), random_int(0, 255), random_int(0, 255)));
        imagepng($image, $path);
        $this->createdFiles[] = $path;

        return $path;
    }
}
