<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Category;
use App\Entity\Diaporama;
use App\Entity\Menus;
use App\Entity\Page;
use App\Entity\PageContent;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * L'API publique ne sert un diaporama que publié, et seulement si sa cible est publiée et accessible
 * (docs/PLAN_DIAPORAMAS.md § 7). Format lu par front/src/services/diaporamas.js.
 */
final class DiaporamaApiTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private Category $parcours;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $this->parcours = (new Category())->setName('consignes');
        $this->entityManager->persist($this->parcours);
    }

    public function testPublishedCourseDiaporamaIsServedInTheFrontFormat(): void
    {
        $cours = $this->course('cours');
        $this->diaporama($cours, null, Diaporama::STATUS_PUBLISHED);

        $diaporamas = $this->get('/api/diaporamas?cours='.$cours->getId());

        self::assertCount(1, $diaporamas);
        self::assertSame('Diaporama de démonstration', $diaporamas[0]['titre']);
        self::assertSame('Un sous-titre', $diaporamas[0]['sousTitre']);
        self::assertSame($cours->getId(), $diaporamas[0]['cours']);
        self::assertArrayNotHasKey('chapitre', $diaporamas[0]);
        self::assertSame('objectifs', $diaporamas[0]['diapositives'][0]['modele']);
        // Ni l'auteur, ni la relecture, ni l'empreinte ne sortent de l'API
        self::assertSame(['@id', '@type', 'id', 'titre', 'sousTitre', 'diapositives', 'cours'], array_keys($diaporamas[0]));
    }

    public function testDraftDiaporamaOrUnpublishedCourseStaysHidden(): void
    {
        $brouillon = $this->diaporama($this->course('a'), null, Diaporama::STATUS_DRAFT);
        $this->diaporama($this->course('b', PageContent::STATUS_DRAFT), null, Diaporama::STATUS_PUBLISHED);

        self::assertSame([], $this->get('/api/diaporamas'));
        $this->client->request('GET', '/api/diaporamas/'.$brouillon->getId(), [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        self::assertResponseStatusCodeSame(404);
    }

    public function testDiaporamaOfAReservedCourseIsOnlyForItsLearners(): void
    {
        $apprenant = (new User())->setUsername('lea')->setRoles(['ROLE_USER'])->setPassword('inutilise');
        $this->entityManager->persist($apprenant);
        $cours = $this->course('reserve')->setReserve(true)->addDestinataire($apprenant);
        $this->diaporama($cours, null, Diaporama::STATUS_PUBLISHED);

        self::assertSame([], $this->get('/api/diaporamas'));
        $this->client->loginUser($apprenant);
        self::assertCount(1, $this->get('/api/diaporamas'));
    }

    public function testChapterDiaporamaNeedsAllItsCoursesPublishedAndOpen(): void
    {
        $chapitre = (new Menus())->setLabel('Chapitre 1')->setCategory($this->parcours);
        $autre = (new Menus())->setLabel('Chapitre 2')->setCategory($this->parcours);
        $this->entityManager->persist($chapitre);
        $this->entityManager->persist($autre);
        $chapitre->addPageContent($this->course('un'));
        $deux = $this->course('deux', PageContent::STATUS_DRAFT);
        $chapitre->addPageContent($deux);
        $autre->addPageContent($this->course('trois'));
        $this->diaporama(null, $chapitre, Diaporama::STATUS_PUBLISHED);
        $this->diaporama(null, $autre, Diaporama::STATUS_PUBLISHED);

        $query = sprintf('/api/diaporamas?chapitre[]=%d&chapitre[]=%d', $chapitre->getId(), $autre->getId());
        self::assertSame([$autre->getId()], array_column($this->get($query), 'chapitre'));

        // Tous les cours publiés : le chapitre est servi ; un cours réservé le masque de nouveau.
        // Chaque requête vide le gestionnaire d'entités : le cours est relu avant d'être modifié.
        $this->entityManager->find(PageContent::class, $deux->getId())?->setStatus(PageContent::STATUS_PUBLISHED);
        $this->entityManager->flush();
        self::assertEqualsCanonicalizing([$chapitre->getId(), $autre->getId()], array_column($this->get($query), 'chapitre'));

        $this->entityManager->find(PageContent::class, $deux->getId())?->setReserve(true);
        $this->entityManager->flush();
        self::assertSame([$autre->getId()], array_column($this->get($query), 'chapitre'));
    }

    public function testReservedParcoursHidesItsChapterDiaporama(): void
    {
        $this->parcours->setReserve(true);
        $chapitre = (new Menus())->setLabel('Chapitre 1')->setCategory($this->parcours);
        $this->entityManager->persist($chapitre);
        $chapitre->addPageContent($this->course('un'));
        $this->diaporama(null, $chapitre, Diaporama::STATUS_PUBLISHED);

        self::assertSame([], $this->get('/api/diaporamas'));
    }

    private function course(string $title, string $status = PageContent::STATUS_PUBLISHED): PageContent
    {
        $page = (new Page())->setSlug('/'.$title);
        $course = (new PageContent())->setTitle($title)->setStatus($status)->setPage($page)->setCategory($this->parcours);
        $this->entityManager->persist($page);
        $this->entityManager->persist($course);
        $this->entityManager->flush();

        return $course;
    }

    private function diaporama(?PageContent $cours, ?Menus $chapitre, string $status): Diaporama
    {
        $diaporama = (new Diaporama('connecteur:Claude#1'))
            ->setCible($cours, $chapitre)
            ->setTitre('Diaporama de démonstration')
            ->setSousTitre('Un sous-titre')
            ->setDiapositives([['modele' => 'objectifs', 'titre' => 'Objectifs', 'points' => ['Un', 'Deux']]])
            ->setStatus($status);
        $this->entityManager->persist($diaporama);
        $this->entityManager->flush();

        return $diaporama;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function get(string $url): array
    {
        $this->client->request('GET', $url, [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        self::assertResponseIsSuccessful();

        return json_decode((string) $this->client->getResponse()->getContent(), true)['member'];
    }
}
