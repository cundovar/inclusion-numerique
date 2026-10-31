<?php

declare(strict_types=1);

namespace App\Tests\Functional\Mcp;

use App\Entity\Category;
use App\Entity\Menus;
use App\Entity\PageContent;

/**
 * Le connecteur prépare des brouillons, les fait relire par un autre connecteur que son auteur, puis les publie
 * (relecture favorable et aucun contrôle bloquant) ou les soumet à la relecture humaine. Il ne touche pas à ce
 * qui est en relecture humaine ; un cours publié reste modifiable.
 */
final class RedactionConnectorTest extends McpTestCase
{
    private Category $parcours;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parcours = (new Category())->setName('démarches en ligne');
        $this->entityManager->persist($this->parcours);
        $this->entityManager->flush();
    }

    public function testToolsPromptsAndRulesAreOffered(): void
    {
        $tools = array_column($this->rpc(self::CLAUDE, 'tools/list')['result']['tools'], 'name');
        sort($tools);

        self::assertSame([
            'attribuer_acces', 'capturer_page', 'classer_cours', 'classer_parcours', 'creer_brouillon', 'creer_brouillon_qcm', 'creer_diaporama', 'creer_module',
            'creer_niveau', 'creer_niveau_qcm', 'creer_parcours', 'creer_rubrique', 'creer_theme_qcm', 'definir_illustration_parcours', 'enregistrer_relecture',
            'enregistrer_relecture_diaporama', 'enregistrer_relecture_qcm', 'lire_regles', 'lister_acces', 'lister_apprenants', 'lister_brouillons',
            'lister_brouillons_qcm', 'lister_captures', 'lister_catalogue', 'lister_catalogue_qcm', 'lister_cours_publies', 'lister_diaporamas',
            'mettre_en_brouillon', 'modifier_brouillon', 'modifier_brouillon_qcm', 'modifier_diaporama', 'obtenir_modele', 'ordonner_parcours',
            'ordonner_rubriques', 'ouvrir_a_tous', 'preparer_depot_image', 'preparer_diaporama', 'publier_cours', 'publier_diaporama', 'publier_qcm',
            'remplacer_capture', 'retirer_acces', 'soumettre_en_revue', 'verifier_brouillon', 'verifier_brouillon_qcm', 'verifier_diaporama',
            'voir_brouillon', 'voir_brouillon_qcm', 'voir_diaporama',
        ], $tools);

        $prompts = array_column($this->rpc(self::CLAUDE, 'prompts/list')['result']['prompts'], 'name');
        sort($prompts);
        self::assertSame(['rediger_cours', 'rediger_diaporama', 'rediger_qcm', 'relire_brouillon', 'relire_diaporama', 'relire_qcm'], $prompts);
        $writing = $this->rpc(self::CLAUDE, 'prompts/get', ['name' => 'rediger_cours', 'arguments' => ['sujet' => 'créer un premier contrôleur']]);
        self::assertStringContainsString('Ne publie jamais sans relecture favorable', $writing['result']['messages'][0]['content']['text']);

        $resources = array_column($this->rpc(self::CLAUDE, 'resources/list')['result']['resources'], 'uri');
        self::assertContains('regles:///01-redaction.md', $resources);
        $readme = $this->rpc(self::CLAUDE, 'resources/read', ['uri' => 'regles:///README.md']);
        self::assertStringContainsString('Référentiel de rédaction DevDoc', $readme['result']['contents'][0]['text']);
    }

    public function testRulesAreRead(): void
    {
        $index = $this->tool(self::CLAUDE, 'lire_regles')['data'];
        self::assertStringContainsString('premier jet', $index['statut']);
        self::assertContains(['id' => 'COD-01', 'titre' => 'Un langage par bloc', 'fichier' => '02-code.md'], $index['regles']);
        self::assertSame(['cours'], $index['modeles']);

        $text = $this->tool(self::CLAUDE, 'lire_regles', ['identifiants' => ['COD-01', 'SRC-01']])['text'];
        self::assertStringContainsString('## COD-01 — Un langage par bloc', $text);
        self::assertStringContainsString('## SRC-01', $text);

        self::assertTrue($this->tool(self::CLAUDE, 'lire_regles', ['identifiants' => ['XYZ-99']])['error']);
        self::assertStringContainsString('Pour aller plus loin', $this->tool(self::CLAUDE, 'obtenir_modele', ['modele' => 'cours'])['text']);
    }

    public function testCatalogueListsParcours(): void
    {
        $data = $this->tool(self::CLAUDE, 'lister_catalogue')['data'];

        self::assertSame('démarches en ligne', $data['parcours'][0]['nom']);
        self::assertArrayNotHasKey('public', $data['parcours'][0]);
    }

    public function testCatalogueStructureCanBeCreatedWithoutDuplicates(): void
    {
        $level = $this->tool(self::CLAUDE, 'creer_niveau', ['nom' => 'Débutant']);
        self::assertFalse($level['error'], $level['text']);
        self::assertSame(1, $level['data']['ordre']);
        self::assertSame(2, $this->tool(self::CLAUDE, 'creer_niveau', ['nom' => 'Intermédiaire'])['data']['ordre']);

        $parcours = $this->tool(self::CLAUDE, 'creer_parcours', ['nom' => "J'apprends le numérique", 'niveauId' => $level['data']['id']]);
        self::assertFalse($parcours['error'], $parcours['text']);

        $module = $this->tool(self::CLAUDE, 'creer_module', ['nom' => "Prendre en main l'ordinateur", 'parcoursId' => $parcours['data']['id'], 'niveauId' => $level['data']['id']]);
        self::assertFalse($module['error'], $module['text']);

        $catalogue = $this->tool(self::CLAUDE, 'lister_catalogue')['data'];
        self::assertContains(['id' => $module['data']['id'], 'nom' => "Prendre en main l'ordinateur", 'parcoursId' => $parcours['data']['id']], $catalogue['modules']);
        self::assertSame(['Débutant', 'Intermédiaire'], array_column($catalogue['niveaux'], 'nom'));

        // Un doublon renvoie l'identifiant existant ; un champ ou un parcours inconnu est refusé
        self::assertStringContainsString('identifiant '.$parcours['data']['id'], $this->tool(self::CLAUDE, 'creer_parcours', ['nom' => "J'apprends le numérique"])['text']);
        self::assertStringContainsString('identifiant '.$module['data']['id'], $this->tool(self::CLAUDE, 'creer_module', ['nom' => "Prendre en main l'ordinateur", 'parcoursId' => $parcours['data']['id']])['text']);
        self::assertTrue($this->tool(self::CLAUDE, 'creer_niveau', ['nom' => 'Débutant'])['error']);
        self::assertTrue($this->tool(self::CLAUDE, 'creer_parcours', ['nom' => 'Autre', 'public' => 'tout le monde'])['error']);
        self::assertTrue($this->tool(self::CLAUDE, 'creer_module', ['nom' => 'Orphelin', 'parcoursId' => 999999])['error']);
    }

    public function testDraftIsCreatedAsADraftEvenIfAskedOtherwiseAndIsSanitized(): void
    {
        $result = $this->tool(self::CLAUDE, 'creer_brouillon', [
            'titre' => 'Trouver « Mon Compte »',
            'slug' => 'caf-trouver-mon-compte',
            'parcoursId' => $this->parcours->getId(),
            'contenu' => '<p>Bonjour</p><script>alert(1)</script>',
        ]);
        self::assertFalse($result['error'], $result['text']);
        self::assertSame(PageContent::STATUS_DRAFT, $result['data']['statut']);
        self::assertSame($this->agentLabel(self::CLAUDE), $result['data']['auteur']);
        self::assertStringStartsWith('connecteur:Claude#', $result['data']['auteur']);
        self::assertStringNotContainsString('<script', (string) $result['data']['contenu']);

        // Un statut envoyé est refusé par le schéma de l'outil
        self::assertTrue($this->tool(self::CLAUDE, 'modifier_brouillon', ['id' => $result['data']['id'], 'statut' => PageContent::STATUS_PUBLISHED])['error']);

        // Le brouillon n'est pas visible du public
        $this->client->request('GET', '/api/page_contents', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        self::assertSame(0, json_decode((string) $this->client->getResponse()->getContent(), true)['totalItems']);
    }

    public function testInvalidDraftsAreRejectedWithReasons(): void
    {
        $other = (new Category())->setName('autre');
        $foreignModule = (new Menus())->setLabel('Module étranger')->setCategory($other);
        $this->entityManager->persist($other);
        $this->entityManager->persist($foreignModule);
        $this->entityManager->flush();
        $this->createDraft('deja-pris');

        $taken = $this->tool(self::CLAUDE, 'creer_brouillon', ['titre' => 'X', 'slug' => 'deja-pris', 'parcoursId' => $this->parcours->getId(), 'contenu' => '']);
        self::assertTrue($taken['error']);
        self::assertStringContainsString('déjà utilisée', $taken['text']);

        self::assertTrue($this->tool(self::CLAUDE, 'creer_brouillon', ['titre' => 'X', 'slug' => 'Pas Valide', 'parcoursId' => $this->parcours->getId(), 'contenu' => ''])['error']);

        $module = $this->tool(self::CLAUDE, 'creer_brouillon', ['titre' => 'X', 'slug' => 'module-etranger', 'parcoursId' => $this->parcours->getId(), 'contenu' => '', 'moduleId' => $foreignModule->getId()]);
        self::assertTrue($module['error']);
        self::assertStringContainsString('"champ": "moduleId"', $module['text']);

        $source = $this->tool(self::CLAUDE, 'creer_brouillon', ['titre' => 'X', 'slug' => 'mauvaise-source', 'parcoursId' => $this->parcours->getId(), 'contenu' => '', 'sourceOfficielle' => 'pas une adresse']);
        self::assertTrue($source['error']);
        self::assertSame(1, $this->entityManager->getRepository(PageContent::class)->count([]));
    }

    public function testAgentCannotEditACourseInHumanReview(): void
    {
        $id = $this->createDraft('mon-cours');

        self::assertFalse($this->tool(self::CLAUDE, 'modifier_brouillon', ['id' => $id, 'titre' => 'Nouveau titre'])['error']);
        $this->entityManager->clear();
        $course = $this->entityManager->find(PageContent::class, $id);
        self::assertSame(['Nouveau titre', PageContent::STATUS_DRAFT], [$course->getTitle(), $course->getStatus()]);

        $course->setStatus(PageContent::STATUS_IN_REVIEW);
        $this->entityManager->flush();
        self::assertStringContainsString('seul le back-office', $this->tool(self::CLAUDE, 'modifier_brouillon', ['id' => $id, 'titre' => 'Modifié'])['text']);
        self::assertTrue($this->tool(self::CLAUDE, 'soumettre_en_revue', ['id' => $id])['error']);
        self::assertTrue($this->tool(self::CLAUDE, 'publier_cours', ['id' => $id])['error']);
    }

    public function testCourseIsPublishedOnlyAfterAFavourableReviewAndStaysEditable(): void
    {
        $id = $this->createDraft('a-publier', '<p>Texte.</p><h2>À vous</h2><p>Essayez.</p>');

        self::assertStringContainsString('RELECTURE_MANQUANTE', $this->tool(self::CLAUDE, 'publier_cours', ['id' => $id])['text']);
        $this->review($id, PageContent::REVIEW_RESERVES, [['regle' => 'RED-01', 'constat' => 'Une phrase est longue.', 'suggestion' => null]]);
        self::assertStringContainsString('RELECTURE_AVEC_RESERVES', $this->tool(self::CLAUDE, 'publier_cours', ['id' => $id])['text']);

        $this->review($id, PageContent::REVIEW_FAVORABLE);
        // Sans module, la publication demande un choix explicite
        self::assertStringContainsString('MODULE_MANQUANT', $this->tool(self::CLAUDE, 'publier_cours', ['id' => $id])['text']);
        $result = $this->tool(self::CLAUDE, 'publier_cours', ['id' => $id, 'sansModule' => true]);
        self::assertFalse($result['error'], $result['text']);
        self::assertSame(PageContent::STATUS_PUBLISHED, $result['data']['cours']['statut']);
        self::assertTrue($this->tool(self::CLAUDE, 'publier_cours', ['id' => $id])['error']);

        // Visible du public, et toujours modifiable par l'agent
        $this->client->request('GET', '/api/page_contents', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        self::assertSame(1, json_decode((string) $this->client->getResponse()->getContent(), true)['totalItems']);
        $detail = $this->tool(self::CLAUDE, 'modifier_brouillon', ['id' => $id, 'titre' => 'Titre corrigé'])['data'];
        self::assertSame(['Titre corrigé', PageContent::STATUS_PUBLISHED], [$detail['titre'], $detail['statut']]);
    }

    public function testPublishedCoursesCanBeFoundThroughDedicatedTool(): void
    {
        $id = $this->createDraft('cours-a-publier');
        $course = $this->entityManager->find(PageContent::class, $id);
        $course->setStatus(PageContent::STATUS_PUBLISHED);
        $this->entityManager->flush();

        $published = $this->tool(self::CLAUDE, 'lister_cours_publies')['data'];
        self::assertSame([$id], array_column($published, 'id'));
        self::assertSame('cours-a-publier', $published[0]['slug']);
        self::assertSame(PageContent::STATUS_PUBLISHED, $published[0]['statut']);
        self::assertSame([], $this->tool(self::CLAUDE, 'lister_brouillons')['data']);
    }

    public function testSubmissionIsRefusedUntilBlockingChecksPassThenGoesToReview(): void
    {
        $id = $this->createDraft('caf-mon-compte', '<p>Appelez le 06 12 34 56 78.</p>');

        $report = $this->tool(self::CLAUDE, 'verifier_brouillon', ['id' => $id])['data'];
        self::assertFalse($report['soumissible']);
        $phone = array_values(array_filter($report['controles'], static fn (array $c) => $c['code'] === 'TELEPHONE'))[0];
        self::assertSame('Aucun secret ni donnée réelle', $phone['regleTitre']);

        self::assertTrue($this->tool(self::CLAUDE, 'soumettre_en_revue', ['id' => $id])['error']);
        $this->entityManager->clear();
        self::assertSame(PageContent::STATUS_DRAFT, $this->entityManager->find(PageContent::class, $id)->getStatus());

        $this->tool(self::CLAUDE, 'modifier_brouillon', ['id' => $id, 'contenu' => '<p>Appelez le 06 XX XX XX XX.</p><h2>À vous</h2><p>Essayez seul.</p>']);
        $this->review($id, PageContent::REVIEW_FAVORABLE);
        $result = $this->tool(self::CLAUDE, 'soumettre_en_revue', ['id' => $id]);
        self::assertFalse($result['error'], $result['text']);
        self::assertSame(PageContent::STATUS_IN_REVIEW, $result['data']['cours']['statut']);
        $this->entityManager->clear();
        self::assertSame(PageContent::STATUS_IN_REVIEW, $this->entityManager->find(PageContent::class, $id)->getStatus());
    }

    public function testSubmissionNeedsAReviewByAnotherConnector(): void
    {
        $id = $this->createDraft('a-relire', '<p>Texte.</p><h2>À vous</h2><p>Essayez.</p>');

        self::assertStringContainsString('RELECTURE_MANQUANTE', $this->tool(self::CLAUDE, 'soumettre_en_revue', ['id' => $id])['text']);

        // Le connecteur auteur ne peut pas se relire lui-même
        $self = $this->tool(self::CLAUDE, 'enregistrer_relecture', ['id' => $id, 'avis' => 'favorable', 'resume' => 'Moi-même.', 'points' => []]);
        self::assertTrue($self['error']);
        self::assertStringContainsString("l'autre connecteur", $self['text']);

        $detail = $this->review($id, PageContent::REVIEW_RESERVES, [['regle' => 'RED-01', 'constat' => 'Une phrase est longue.', 'suggestion' => 'La couper en deux.']]);
        self::assertSame($this->agentLabel(self::CHATGPT), $detail['relecture']['par']);
        self::assertSame('RED-01', $detail['relecture']['points'][0]['regle']);

        self::assertFalse($this->tool(self::CLAUDE, 'soumettre_en_revue', ['id' => $id])['error']);
    }

    public function testEditingClearsTheReviewAndAnUnfavourableReviewBlocks(): void
    {
        $id = $this->createDraft('a-corriger', '<p>Texte.</p><h2>À vous</h2><p>Essayez.</p>');
        $this->review($id, PageContent::REVIEW_UNFAVORABLE, [['regle' => 'COD-02', 'constat' => "L'exemple ne compile pas.", 'suggestion' => null]]);

        self::assertStringContainsString('RELECTURE_DEFAVORABLE', $this->tool(self::CLAUDE, 'soumettre_en_revue', ['id' => $id])['text']);

        $detail = $this->tool(self::CLAUDE, 'modifier_brouillon', ['id' => $id, 'contenu' => '<p>Texte corrigé.</p><h2>À vous</h2><p>Essayez.</p>'])['data'];
        self::assertNull($detail['relecture']);
    }

    public function testMalformedReviewIsRejectedBeforeReachingTheSite(): void
    {
        $id = $this->createDraft('relecture-invalide');

        $result = $this->tool(self::CHATGPT, 'enregistrer_relecture', ['id' => $id, 'avis' => 'parfait', 'resume' => 'x', 'points' => [['regle' => 'pos1', 'constat' => '']]]);

        self::assertTrue($result['error']);
        $this->entityManager->clear();
        self::assertNull($this->entityManager->find(PageContent::class, $id)->getReviewReport());
    }

    /**
     * @param list<array<string, mixed>> $points
     * @return array<string, mixed>
     */
    private function review(int $id, string $opinion, array $points = []): array
    {
        $result = $this->tool(self::CHATGPT, 'enregistrer_relecture', ['id' => $id, 'avis' => $opinion, 'resume' => 'Relecture selon la grille.', 'points' => $points]);
        self::assertFalse($result['error'], $result['text']);

        return $result['data'];
    }

    private function createDraft(string $slug, string $content = '<p>Texte</p>'): int
    {
        $result = $this->tool(self::CLAUDE, 'creer_brouillon', [
            'titre' => 'Cours '.$slug,
            'slug' => $slug,
            'parcoursId' => $this->parcours->getId(),
            'contenu' => $content,
        ]);
        self::assertFalse($result['error'], $result['text']);

        return (int) $result['data']['id'];
    }
}
