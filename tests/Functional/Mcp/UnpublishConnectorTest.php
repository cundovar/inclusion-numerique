<?php

declare(strict_types=1);

namespace App\Tests\Functional\Mcp;

use App\Entity\Category;
use App\Entity\Menus;
use App\Entity\PageContent;

/**
 * Retrait du site à la demande : un cours, ou tous les cours publiés d'un module ou d'un parcours,
 * repassent en brouillon. Rien n'est supprimé, et un cours non modifié peut être republié.
 */
final class UnpublishConnectorTest extends McpTestCase
{
    private Category $parcours;
    private Menus $module;
    private Menus $autreModule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parcours = (new Category())->setName('Premiers gestes numériques')->setAudience(Category::AUDIENCE_APPRENDRE);
        $this->module = (new Menus())->setLabel('Ordinateur : premiers gestes')->setCategory($this->parcours);
        $this->autreModule = (new Menus())->setLabel('Internet : premiers pas')->setCategory($this->parcours);
        $this->entityManager->persist($this->parcours);
        $this->entityManager->persist($this->module);
        $this->entityManager->persist($this->autreModule);
        $this->entityManager->flush();
    }

    public function testACourseIsTakenOfflineAndCanBeRepublishedUnchanged(): void
    {
        $id = $this->createCourse('utiliser-la-souris', PageContent::STATUS_PUBLISHED, $this->module);
        self::assertSame(1, $this->publicCourseCount());

        $result = $this->tool(self::CLAUDE, 'mettre_en_brouillon', ['type' => 'cours', 'id' => $id]);
        self::assertFalse($result['error'], $result['text']);
        self::assertSame([[$id, PageContent::STATUS_DRAFT]], array_map(static fn (array $c) => [$c['id'], $c['statut']], $result['data']['misEnBrouillon']));
        self::assertSame(0, $this->publicCourseCount());

        // Déjà hors ligne : refusé avec une explication
        self::assertStringContainsString("n'est pas publié", $this->tool(self::CLAUDE, 'mettre_en_brouillon', ['type' => 'cours', 'id' => $id])['text']);

        // Contenu et relecture gardés : la republication passe sans nouvelle relecture
        $republished = $this->tool(self::CLAUDE, 'publier_cours', ['id' => $id]);
        self::assertFalse($republished['error'], $republished['text']);
        self::assertSame(1, $this->publicCourseCount());
    }

    public function testAModuleOrAParcoursTakesOnlyItsPublishedCoursesOffline(): void
    {
        $first = $this->createCourse('premier', PageContent::STATUS_PUBLISHED, $this->module);
        $draft = $this->createCourse('en-cours', PageContent::STATUS_DRAFT, $this->module);
        $other = $this->createCourse('autre', PageContent::STATUS_PUBLISHED, $this->autreModule);
        $inReview = $this->createCourse('en-revue', PageContent::STATUS_IN_REVIEW, $this->autreModule);

        $module = $this->tool(self::CLAUDE, 'mettre_en_brouillon', ['type' => 'module', 'id' => $this->module->getId()]);
        self::assertFalse($module['error'], $module['text']);
        self::assertSame([$first], array_column($module['data']['misEnBrouillon'], 'id'));
        self::assertSame(1, $this->publicCourseCount());

        $parcours = $this->tool(self::CLAUDE, 'mettre_en_brouillon', ['type' => 'parcours', 'id' => $this->parcours->getId()]);
        self::assertSame([$other], array_column($parcours['data']['misEnBrouillon'], 'id'));
        self::assertSame(0, $this->publicCourseCount());

        // Une seconde demande ne change rien ; les cours non publiés gardent leur statut
        self::assertSame([], $this->tool(self::CLAUDE, 'mettre_en_brouillon', ['type' => 'parcours', 'id' => $this->parcours->getId()])['data']['misEnBrouillon']);
        $this->entityManager->clear();
        self::assertSame(PageContent::STATUS_DRAFT, $this->entityManager->find(PageContent::class, $draft)->getStatus());
        self::assertSame(PageContent::STATUS_IN_REVIEW, $this->entityManager->find(PageContent::class, $inReview)->getStatus());
    }

    public function testUnknownTargetsAreRejected(): void
    {
        self::assertStringContainsString('Module introuvable', $this->tool(self::CLAUDE, 'mettre_en_brouillon', ['type' => 'module', 'id' => 999999])['text']);
        self::assertStringContainsString('Parcours introuvable', $this->tool(self::CLAUDE, 'mettre_en_brouillon', ['type' => 'parcours', 'id' => 999999])['text']);
        self::assertTrue($this->tool(self::CLAUDE, 'mettre_en_brouillon', ['type' => 'cours', 'id' => 999999])['error']);
        self::assertTrue($this->tool(self::CLAUDE, 'mettre_en_brouillon', ['type' => 'qcm', 'id' => 1])['error']);
    }

    public function testAModuleIsDeletedOnlyOnceEmptyAndNeverWithItsCourses(): void
    {
        $brouillon = $this->createCourse('utiliser-la-souris', PageContent::STATUS_DRAFT, $this->module);
        $moduleId = $this->module->getId();

        // Un cours, même en brouillon, bloque la suppression : refus avec la liste, rien ne change
        $refused = $this->tool(self::CLAUDE, 'supprimer_module', ['moduleId' => $moduleId]);
        self::assertTrue($refused['error']);
        self::assertStringContainsString('pas vide', $refused['text']);
        self::assertStringContainsString('Cours utiliser-la-souris', $refused['text']);
        $this->entityManager->clear();
        self::assertNotNull($this->entityManager->find(Menus::class, $moduleId));
        self::assertSame($moduleId, $this->entityManager->find(PageContent::class, $brouillon)->getMenu()?->getId());

        // Une fois le cours déplacé, le module vide est supprimé et le cours reste intact
        self::assertFalse($this->tool(self::CLAUDE, 'classer_cours', ['coursIds' => [$brouillon], 'moduleId' => $this->autreModule->getId()])['error']);
        $deleted = $this->tool(self::CLAUDE, 'supprimer_module', ['moduleId' => $moduleId]);
        self::assertFalse($deleted['error'], $deleted['text']);
        self::assertSame(['id' => $moduleId, 'nom' => 'Ordinateur : premiers gestes', 'parcoursId' => $this->parcours->getId()], $deleted['data']['supprime']);

        $this->entityManager->clear();
        self::assertNull($this->entityManager->find(Menus::class, $moduleId));
        self::assertSame(PageContent::STATUS_DRAFT, $this->entityManager->find(PageContent::class, $brouillon)->getStatus());
        self::assertStringContainsString('Module introuvable', $this->tool(self::CLAUDE, 'supprimer_module', ['moduleId' => $moduleId])['text']);
    }

    private function createCourse(string $slug, string $status, Menus $module): int
    {
        $result = $this->tool(self::CLAUDE, 'creer_brouillon', ['titre' => 'Cours '.$slug, 'slug' => $slug, 'parcoursId' => $this->parcours->getId(), 'moduleId' => $module->getId(), 'contenu' => '<p>Texte.</p><h2>À vous</h2><p>Essayez.</p>']);
        self::assertFalse($result['error'], $result['text']);
        $id = (int) $result['data']['id'];
        $review = $this->tool(self::CHATGPT, 'enregistrer_relecture', ['id' => $id, 'avis' => 'favorable', 'resume' => 'Prêt.', 'points' => []]);
        self::assertFalse($review['error'], $review['text']);

        $this->entityManager->find(PageContent::class, $id)->setStatus($status);
        $this->entityManager->flush();

        return $id;
    }

    private function publicCourseCount(): int
    {
        $this->client->request('GET', '/api/page_contents', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);

        return json_decode((string) $this->client->getResponse()->getContent(), true)['totalItems'];
    }
}
