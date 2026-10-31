<?php

declare(strict_types=1);

namespace App\Tests\Functional\Mcp;

use App\Entity\Category;
use App\Entity\Diaporama;
use App\Entity\Menus;
use App\Entity\Page;
use App\Entity\PageContent;

/**
 * Le connecteur rédige le diaporama d'un cours ou d'un chapitre, un autre connecteur le relit, puis il est publié
 * (docs/PLAN_DIAPORAMAS.md). Avant la publication, rien n'apparaît dans l'API publique.
 */
final class DiaporamaConnectorTest extends McpTestCase
{
    private const DEMO = __DIR__.'/../../../front/tests/unit/diaporamas/demo.json';
    private const COURS = '<h2>Une consigne claire</h2><p>Une intelligence artificielle répond toujours à la question exacte que la personne lui a posée, sans deviner son intention.</p>';

    private Category $parcours;
    private PageContent $cours;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parcours = (new Category())->setName('consignes');
        $this->entityManager->persist($this->parcours);
        $this->cours = $this->course('bien-ecrire-une-consigne');
        $this->entityManager->flush();
    }

    public function testToolsAndPromptsGuideBothConnectors(): void
    {
        $tools = array_column($this->rpc(self::CHATGPT, 'tools/list')['result']['tools'], null, 'name');
        self::assertTrue($tools['preparer_diaporama']['annotations']['readOnlyHint']);
        self::assertFalse($tools['creer_diaporama']['annotations']['readOnlyHint']);
        self::assertSame(5, $tools['creer_diaporama']['inputSchema']['properties']['diapositives']['items']['properties']['points']['maxItems']);
        self::assertStringContainsString('autre connecteur', $tools['publier_diaporama']['description']);

        $writing = $this->rpc(self::CLAUDE, 'prompts/get', ['name' => 'rediger_diaporama', 'arguments' => ['cible' => 'module', 'id' => '4']]);
        $text = $writing['result']['messages'][0]['content']['text'];
        self::assertStringContainsString('preparer_diaporama avec moduleId 4', $text);
        self::assertStringContainsString('10 à 20 diapositives', $text);
        self::assertLessThan(strpos($text, 'publier_diaporama'), strpos($text, 'relire_diaporama'));

        $review = $this->rpc(self::CHATGPT, 'prompts/get', ['name' => 'relire_diaporama', 'arguments' => ['id' => '7']]);
        self::assertStringContainsString('voir_diaporama 7', $review['result']['messages'][0]['content']['text']);

        self::assertStringContainsString('## DIA-02 — Synthétiser, pas copier', $this->tool(self::CLAUDE, 'lire_regles', ['identifiants' => ['DIA-02']])['text']);
    }

    public function testSourceGivesTheCourseTextAndTheExpectedFormat(): void
    {
        $source = $this->tool(self::CLAUDE, 'preparer_diaporama', ['coursId' => $this->cours->getId()]);

        self::assertFalse($source['error'], $source['text']);
        self::assertSame(['type' => 'cours', 'id' => $this->cours->getId(), 'titre' => 'bien-ecrire-une-consigne'], $source['data']['cible']);
        self::assertSame([6, 12], $source['data']['diapositivesAttendues']);
        self::assertStringContainsString('sans deviner son intention', $source['data']['cours'][0]['texte']);
        self::assertNull($source['data']['diaporamaExistant']);

        $both = $this->tool(self::CLAUDE, 'preparer_diaporama', ['coursId' => $this->cours->getId(), 'moduleId' => 1]);
        self::assertTrue($both['error']);
        self::assertStringContainsString('pas les deux', $both['text']);
    }

    public function testDiaporamaIsWrittenReviewedByAnotherConnectorThenPublished(): void
    {
        $created = $this->tool(self::CLAUDE, 'creer_diaporama', ['coursId' => $this->cours->getId()] + $this->demo());
        self::assertFalse($created['error'], $created['text']);
        $id = $created['data']['id'];
        self::assertSame(Diaporama::STATUS_DRAFT, $created['data']['statut']);
        self::assertSame([], $this->publicDiaporamas());

        $self = $this->tool(self::CLAUDE, 'enregistrer_relecture_diaporama', $this->review($id, 'favorable'));
        self::assertStringContainsString("d'un autre agent", $self['text']);

        $reserves = $this->tool(self::CHATGPT, 'enregistrer_relecture_diaporama', $this->review($id, 'reserves'));
        self::assertSame(Diaporama::STATUS_TO_FIX, $reserves['data']['statut']);
        self::assertStringContainsString('RELECTURE_AVEC_RESERVES', $this->tool(self::CLAUDE, 'publier_diaporama', ['id' => $id])['text']);

        $this->tool(self::CHATGPT, 'enregistrer_relecture_diaporama', $this->review($id, 'favorable'));
        $published = $this->tool(self::CLAUDE, 'publier_diaporama', ['id' => $id]);
        self::assertFalse($published['error'], $published['text']);
        self::assertSame(Diaporama::STATUS_PUBLISHED, $published['data']['statut']);
        self::assertStringEndsWith('/spa/pages/bien-ecrire-une-consigne', $published['data']['adressePublique']);

        $public = $this->publicDiaporamas();
        self::assertCount(1, $public);
        self::assertSame('Bien écrire une consigne', $public[0]['titre']);
        self::assertCount(10, $public[0]['diapositives']);

        // Une cible n'a qu'un diaporama ; un diaporama publié se corrige et reste visible, sans relecture
        self::assertStringContainsString('déjà le diaporama', $this->tool(self::CLAUDE, 'creer_diaporama', ['coursId' => $this->cours->getId()] + $this->demo())['text']);
        $updated = $this->tool(self::CLAUDE, 'modifier_diaporama', ['id' => $id, 'titre' => 'Écrire une bonne consigne']);
        self::assertFalse($updated['error'], $updated['text']);
        self::assertNull($updated['data']['relecture']);
        self::assertSame(Diaporama::STATUS_PUBLISHED, $updated['data']['statut']);
        self::assertSame('Écrire une bonne consigne', $this->publicDiaporamas()[0]['titre']);
    }

    public function testCopiedOrInvalidDiaporamaIsRefusedWithoutBeingSaved(): void
    {
        $demo = $this->demo();
        $demo['diapositives'][2]['points'] = ['Répond toujours à la question exacte que la personne lui a posée', 'Le contexte compte'];
        $copied = $this->tool(self::CLAUDE, 'creer_diaporama', ['coursId' => $this->cours->getId()] + $demo);
        self::assertTrue($copied['error']);
        self::assertStringContainsString('DIAPO_COPIE', $copied['text']);

        $demo = $this->demo();
        $demo['diapositives'][3]['notes'] = str_pad('Appelez le 06 12 34 56 78.', 320, ' Notes.');
        self::assertStringContainsString('TELEPHONE', $this->tool(self::CLAUDE, 'creer_diaporama', ['coursId' => $this->cours->getId()] + $demo)['text']);

        self::assertSame(0, $this->entityManager->getRepository(Diaporama::class)->count([]));
    }

    public function testChapterWaitsForAllItsCourses(): void
    {
        $chapitre = (new Menus())->setLabel('Chapitre 1')->setCategory($this->parcours);
        $this->entityManager->persist($chapitre);
        $chapitre->addPageContent($this->entityManager->find(PageContent::class, $this->cours->getId()));
        $chapitre->addPageContent($this->course('cours-en-cours', PageContent::STATUS_DRAFT));
        $this->entityManager->flush();

        $source = $this->tool(self::CLAUDE, 'preparer_diaporama', ['moduleId' => $chapitre->getId()]);
        self::assertSame(['cours-en-cours'], $source['data']['coursNonPublies']);

        $refused = $this->tool(self::CLAUDE, 'creer_diaporama', ['moduleId' => $chapitre->getId()] + $this->demo());
        self::assertTrue($refused['error']);
        self::assertStringContainsString('DIAPO_CHAPITRE_INCOMPLET', $refused['text']);
    }

    private function course(string $slug, string $status = PageContent::STATUS_PUBLISHED): PageContent
    {
        $page = (new Page())->setSlug('/'.$slug);
        $course = (new PageContent())->setTitle($slug)->setStatus($status)->setPage($page)->setCategory($this->parcours)->setCode(self::COURS);
        $this->entityManager->persist($page);
        $this->entityManager->persist($course);

        return $course;
    }

    /**
     * Diaporama de démonstration du front, sans sa capture (aucune capture dans ce test).
     *
     * @return array{titre: string, sousTitre: string, diapositives: list<array<string, mixed>>}
     */
    private function demo(): array
    {
        $demo = json_decode((string) file_get_contents(self::DEMO), true, flags: \JSON_THROW_ON_ERROR);
        $demo['diapositives'][5] = ['modele' => 'notion', 'points' => ['La consigne est précise', 'La réponse suit le format']] + $demo['diapositives'][5];
        unset($demo['diapositives'][5]['capture']);

        return $demo;
    }

    /**
     * @return array<string, mixed>
     */
    private function review(int $id, string $opinion): array
    {
        return ['id' => $id, 'avis' => $opinion, 'resume' => 'Relecture du diaporama.', 'points' => $opinion === 'favorable' ? [] : [['regle' => 'DIA-03', 'constat' => 'Les notes de la diapositive 4 sont trop vagues.']]];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function publicDiaporamas(): array
    {
        $this->client->request('GET', '/api/diaporamas', ['cours' => $this->cours->getId()], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        self::assertResponseIsSuccessful();

        return json_decode((string) $this->client->getResponse()->getContent(), true)['member'];
    }
}
