<?php

declare(strict_types=1);

namespace App\Tests\Functional\Mcp;

use App\Entity\ChoicesQCM;
use App\Entity\LanguageQCM;
use App\Entity\NiveauQCM;
use App\Entity\QCM;
use App\Entity\QcmDraft;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

/**
 * Le connecteur prépare un QCM en brouillon, le fait relire par un autre connecteur que son auteur, puis le publie
 * dans le modèle public (QCM et ChoicesQCM). Avant la publication, rien n'apparaît dans l'API publique.
 */
final class QcmConnectorTest extends McpTestCase
{
    private LanguageQCM $theme;
    private NiveauQCM $level;

    protected function setUp(): void
    {
        parent::setUp();
        $this->theme = (new LanguageQCM())->setName('Sécurité');
        $this->level = (new NiveauQCM())->setTitre('Débutant');
        $this->entityManager->persist($this->theme);
        $this->entityManager->persist($this->level);
        $this->entityManager->flush();
    }

    public function testPromptsGuideBothConnectorsThroughWritingAndReview(): void
    {
        foreach ([self::CLAUDE, self::CHATGPT] as $who) {
            $tools = array_column($this->rpc($who, 'tools/list')['result']['tools'], null, 'name');
            self::assertTrue($tools['voir_brouillon_qcm']['annotations']['readOnlyHint']);
            self::assertFalse($tools['creer_brouillon_qcm']['annotations']['readOnlyHint']);
            self::assertSame(6, $tools['creer_brouillon_qcm']['inputSchema']['properties']['reponses']['maxItems']);
            self::assertFalse($tools['creer_theme_qcm']['annotations']['readOnlyHint']);
            self::assertFalse($tools['creer_niveau_qcm']['annotations']['readOnlyHint']);
            self::assertStringContainsString('autre connecteur', $tools['publier_qcm']['description']);
        }

        $writing = $this->rpc(self::CLAUDE, 'prompts/get', ['name' => 'rediger_qcm', 'arguments' => ['sujet' => 'reconnaître un faux message', 'public' => 'apprendre']]);
        $text = $writing['result']['messages'][0]['content']['text'];
        self::assertStringContainsString('lister_catalogue_qcm', $text);
        self::assertStringContainsString('creer_theme_qcm', $text);
        self::assertStringContainsString('creer_niveau_qcm', $text);
        self::assertLessThan(strpos($text, 'publier_qcm'), strpos($text, 'relire_qcm'));
        self::assertStringContainsString('Ne publie jamais sans relecture favorable', $text);

        $review = $this->rpc(self::CHATGPT, 'prompts/get', ['name' => 'relire_qcm', 'arguments' => ['id' => '7']]);
        self::assertStringContainsString('voir_brouillon_qcm 7', $review['result']['messages'][0]['content']['text']);

        $rules = $this->tool(self::CLAUDE, 'lire_regles', ['identifiants' => ['QCM-01', 'QCM-04']])['text'];
        self::assertStringContainsString('## QCM-01 — Une question, une seule bonne réponse', $rules);
    }

    public function testCatalogueListsThemesAndLevelsWithThePublicAddress(): void
    {
        $data = $this->tool(self::CLAUDE, 'lister_catalogue_qcm')['data'];

        self::assertSame([['id' => $this->theme->getId(), 'nom' => 'Sécurité', 'qcmPublies' => 0]], $data['themes']);
        self::assertSame([['id' => $this->level->getId(), 'nom' => 'Débutant', 'qcmPublies' => 0]], $data['niveaux']);
        self::assertStringEndsWith('/spa/qcm', $data['adressePublique']);
    }

    public function testMissingThemeAndLevelCanBeCreatedThroughTheConnectorWithoutDuplicates(): void
    {
        $this->entityManager->remove($this->theme);
        $this->entityManager->remove($this->level);
        $this->entityManager->flush();

        self::assertSame(['themes' => [], 'niveaux' => []], array_intersect_key($this->tool(self::CLAUDE, 'lister_catalogue_qcm')['data'], ['themes' => true, 'niveaux' => true]));

        $theme = $this->tool(self::CLAUDE, 'creer_theme_qcm', ['nom' => 'Premiers gestes numériques']);
        self::assertFalse($theme['error'], $theme['text']);
        self::assertTrue($theme['data']['cree']);
        self::assertSame('Premiers gestes numériques', $theme['data']['nom']);
        $sameTheme = $this->tool(self::CLAUDE, 'creer_theme_qcm', ['nom' => 'Premiers gestes numériques']);
        self::assertFalse($sameTheme['error'], $sameTheme['text']);
        self::assertFalse($sameTheme['data']['cree']);
        self::assertSame($theme['data']['id'], $sameTheme['data']['id']);

        $level = $this->tool(self::CLAUDE, 'creer_niveau_qcm', ['nom' => 'Débutant']);
        self::assertFalse($level['error'], $level['text']);
        self::assertTrue($level['data']['cree']);
        $sameLevel = $this->tool(self::CLAUDE, 'creer_niveau_qcm', ['nom' => 'Débutant']);
        self::assertFalse($sameLevel['error'], $sameLevel['text']);
        self::assertFalse($sameLevel['data']['cree']);
        self::assertSame($level['data']['id'], $sameLevel['data']['id']);

        $catalogue = $this->tool(self::CLAUDE, 'lister_catalogue_qcm')['data'];
        self::assertSame('Premiers gestes numériques', $catalogue['themes'][0]['nom']);
        self::assertSame('Débutant', $catalogue['niveaux'][0]['nom']);
        $draft = $this->tool(self::CLAUDE, 'creer_brouillon_qcm', ['themeId' => $theme['data']['id'], 'niveauId' => $level['data']['id']] + $this->qcm());
        self::assertFalse($draft['error'], $draft['text']);
        self::assertSame([$theme['data']['id'], $level['data']['id']], [$draft['data']['themeId'], $draft['data']['niveauId']]);
    }

    public function testDraftIsCreatedReadAndCheckedWithoutBeingPublic(): void
    {
        $created = $this->tool(self::CLAUDE, 'creer_brouillon_qcm', $this->qcm());
        self::assertFalse($created['error'], $created['text']);
        self::assertSame(QcmDraft::STATUS_DRAFT, $created['data']['statut']);
        self::assertSame($this->agentLabel(self::CLAUDE), $created['data']['auteur']);
        self::assertSame([], $created['data']['controles']);

        $shown = $this->tool(self::CHATGPT, 'voir_brouillon_qcm', ['id' => $created['data']['id']])['data'];
        self::assertSame($this->qcm()['question'], $shown['question']);
        self::assertSame([
            ['texte' => 'Je clique sur le lien', 'correcte' => false, 'explication' => 'Le lien peut mener à un faux site.'],
            ['texte' => 'Je supprime le message', 'correcte' => true, 'explication' => null],
            ['texte' => 'Je réponds au message', 'correcte' => false, 'explication' => 'Répondre confirme que votre numéro existe.'],
        ], $shown['reponses']);
        self::assertSame([$created['data']['id']], array_column($this->tool(self::CLAUDE, 'lister_brouillons_qcm')['data'], 'id'));

        $check = $this->tool(self::CLAUDE, 'verifier_brouillon_qcm', ['id' => $created['data']['id']])['data'];
        self::assertTrue($check['sansControleBloquant']);
        self::assertFalse($check['publiable']);
        self::assertSame(['RELECTURE_MANQUANTE'], array_column($check['controles'], 'code'));
        self::assertSame('Tester la compréhension avant de publier', $check['controles'][0]['regleTitre']);

        // Ni QCM ni réponse publics tant que le brouillon n'est pas publié
        self::assertSame(0, $this->entityManager->getRepository(QCM::class)->count([]));
        self::assertSame(0, $this->entityManager->getRepository(ChoicesQCM::class)->count([]));
        self::assertSame([], $this->publicQcms());
    }

    public function testInvalidDraftsAreRefusedWithoutBeingSaved(): void
    {
        $unknownLevel = $this->tool(self::CLAUDE, 'creer_brouillon_qcm', ['niveauId' => 999999] + $this->qcm());
        self::assertTrue($unknownLevel['error']);
        self::assertStringContainsString('Niveau introuvable', $unknownLevel['text']);

        $twoCorrect = $this->qcm();
        $twoCorrect['reponses'][0]['correcte'] = true;
        $refused = $this->tool(self::CLAUDE, 'creer_brouillon_qcm', $twoCorrect);
        self::assertTrue($refused['error']);
        self::assertStringContainsString('BONNE_REPONSE_UNIQUE', $refused['text']);

        // Le schéma de l'outil refuse une seule réponse
        self::assertTrue($this->tool(self::CLAUDE, 'creer_brouillon_qcm', ['reponses' => [$this->qcm()['reponses'][1]]] + $this->qcm())['error']);

        $personal = $this->tool(self::CLAUDE, 'creer_brouillon_qcm', ['explication' => 'Appelez le 06 12 34 56 78 pour vérifier.'] + $this->qcm());
        self::assertTrue($personal['error']);
        self::assertStringContainsString('TELEPHONE', $personal['text']);

        self::assertSame(0, $this->entityManager->getRepository(QcmDraft::class)->count([]));
    }

    public function testRefusedUpdateLeavesTheSavedDraftUnchanged(): void
    {
        $id = $this->createDraft();

        $refused = $this->tool(self::CLAUDE, 'modifier_brouillon_qcm', ['id' => $id, 'question' => 'Votre conseillère, jean.dupont@gmail.com, vous écrit. Que faites-vous ?']);
        self::assertTrue($refused['error']);
        self::assertStringContainsString('COURRIEL_REEL', $refused['text']);

        // Une demande suivante n'enregistre pas la modification refusée
        $this->tool(self::CLAUDE, 'modifier_brouillon_qcm', ['id' => $id, 'themeId' => $this->theme->getId()]);
        $this->entityManager->clear();
        self::assertSame($this->qcm()['question'], $this->entityManager->find(QcmDraft::class, $id)?->getQuestion());
    }

    public function testOnlyAnotherConnectorCanReviewAndOnlyAFavorableReviewPublishes(): void
    {
        $id = $this->createDraft();

        $self = $this->tool(self::CLAUDE, 'enregistrer_relecture_qcm', $this->review($id, 'favorable'));
        self::assertTrue($self['error']);
        self::assertStringContainsString("d'un autre agent", $self['text']);

        $reserves = $this->tool(self::CHATGPT, 'enregistrer_relecture_qcm', $this->review($id, 'reserves'));
        self::assertFalse($reserves['error'], $reserves['text']);
        self::assertSame(QcmDraft::STATUS_TO_FIX, $reserves['data']['statut']);
        self::assertSame($this->agentLabel(self::CHATGPT), $reserves['data']['relecture']['par']);
        $blocked = $this->tool(self::CLAUDE, 'publier_qcm', ['id' => $id]);
        self::assertTrue($blocked['error']);
        self::assertStringContainsString('RELECTURE_AVEC_RESERVES', $blocked['text']);

        $this->tool(self::CHATGPT, 'enregistrer_relecture_qcm', $this->review($id, 'defavorable'));
        self::assertStringContainsString('RELECTURE_DEFAVORABLE', $this->tool(self::CLAUDE, 'publier_qcm', ['id' => $id])['text']);

        // Un avis favorable puis une modification : l'avis est effacé et la publication bloquée
        $this->tool(self::CHATGPT, 'enregistrer_relecture_qcm', $this->review($id, 'favorable'));
        $updated = $this->tool(self::CLAUDE, 'modifier_brouillon_qcm', ['id' => $id, 'question' => 'Un SMS vous demande de payer un colis. Que faites-vous ?']);
        self::assertFalse($updated['error'], $updated['text']);
        self::assertNull($updated['data']['relecture']);
        self::assertSame(QcmDraft::STATUS_DRAFT, $updated['data']['statut']);
        self::assertStringContainsString('RELECTURE_MANQUANTE', $this->tool(self::CLAUDE, 'publier_qcm', ['id' => $id])['text']);

        self::assertSame(0, $this->entityManager->getRepository(QCM::class)->count([]));
    }

    public function testPublicationCreatesTheQcmOnceInThePublicFormat(): void
    {
        $id = $this->createDraft(['explication' => "Un vrai transporteur ne demande pas de payer par un lien.\n\nEn cas de doute, allez sur le site <officiel>."]);
        $this->tool(self::CHATGPT, 'enregistrer_relecture_qcm', $this->review($id, 'favorable'));

        $published = $this->tool(self::CLAUDE, 'publier_qcm', ['id' => $id]);
        self::assertFalse($published['error'], $published['text']);
        self::assertSame(QcmDraft::STATUS_PUBLISHED, $published['data']['brouillon']['statut']);
        self::assertSame(['id' => $published['data']['qcm']['id'], 'theme' => 'Sécurité', 'niveau' => 'Débutant', 'reponses' => 3, 'reserve' => false, 'destinataires' => []], $published['data']['qcm']);
        self::assertStringEndsWith('/spa/qcm', $published['data']['adressePublique']);

        // Même requête que la série publique (front/src/views/QCM/questionQCM.vue)
        $qcms = $this->publicQcms('Sécurité', 'Débutant');
        self::assertCount(1, $qcms);
        self::assertSame('Un message vous demande de payer un colis. Que faites-vous ?', $qcms[0]['titre']);
        self::assertSame('<p>Un vrai transporteur ne demande pas de payer par un lien.</p><p>En cas de doute, allez sur le site &lt;officiel&gt;.</p>', $qcms[0]['solution']);
        self::assertSame(
            [['Je clique sur le lien', false, '<p>Le lien peut mener à un faux site.</p>'], ['Je supprime le message', true, null], ['Je réponds au message', false, '<p>Répondre confirme que votre numéro existe.</p>']],
            array_map(static fn (array $c) => [$c['question'], $c['isCorrect'], $c['explication'] ?? null], $qcms[0]['choicesQCMs']),
        );
        self::assertSame([], $this->publicQcms('Sécurité', 'Avancé'));

        // Une seconde publication, une relecture ou une modification sont refusées
        self::assertStringContainsString('déjà publié', $this->tool(self::CLAUDE, 'publier_qcm', ['id' => $id])['text']);
        self::assertTrue($this->tool(self::CHATGPT, 'enregistrer_relecture_qcm', $this->review($id, 'favorable'))['error']);
        self::assertTrue($this->tool(self::CLAUDE, 'modifier_brouillon_qcm', ['id' => $id, 'question' => 'Autre ?'])['error']);
        self::assertSame(1, $this->entityManager->getRepository(QCM::class)->count([]));
        self::assertSame(1, $this->tool(self::CLAUDE, 'lister_catalogue_qcm')['data']['themes'][0]['qcmPublies']);
    }

    public function testFailedPublicationLeavesNothingPublished(): void
    {
        $id = $this->createDraft();
        $this->tool(self::CHATGPT, 'enregistrer_relecture_qcm', $this->review($id, 'favorable'));
        // L'écriture des réponses échoue au milieu de la publication
        $this->entityManager->getEventManager()->addEventListener([Events::onFlush], new class {
            public function onFlush(OnFlushEventArgs $args): void
            {
                foreach ($args->getObjectManager()->getUnitOfWork()->getScheduledEntityInsertions() as $entity) {
                    if ($entity instanceof ChoicesQCM) {
                        throw new \RuntimeException('Écriture impossible');
                    }
                }
            }
        });

        self::assertTrue($this->tool(self::CLAUDE, 'publier_qcm', ['id' => $id])['error']);

        $connection = $this->entityManager->getConnection();
        self::assertSame(0, (int) $connection->fetchOne('SELECT COUNT(*) FROM appy_QCM'));
        self::assertSame(0, (int) $connection->fetchOne('SELECT COUNT(*) FROM appy_ChoicesQCM'));
        self::assertSame(QcmDraft::STATUS_DRAFT, $connection->fetchOne('SELECT status FROM qcm_draft WHERE id = ?', [$id]));
        self::assertNull($connection->fetchOne('SELECT published_qcm_id FROM qcm_draft WHERE id = ?', [$id]));
    }

    /**
     * @return array<string, mixed>
     */
    private function qcm(): array
    {
        return [
            'question' => 'Un message vous demande de payer un colis. Que faites-vous ?',
            'explication' => 'Un vrai transporteur ne demande pas de payer par un lien.',
            'reponses' => [
                ['texte' => 'Je clique sur le lien', 'correcte' => false, 'explication' => 'Le lien peut mener à un faux site.'],
                ['texte' => 'Je supprime le message', 'correcte' => true],
                ['texte' => 'Je réponds au message', 'correcte' => false, 'explication' => 'Répondre confirme que votre numéro existe.'],
            ],
            'themeId' => $this->theme->getId(),
            'niveauId' => $this->level->getId(),
        ];
    }

    /**
     * @param array<string, mixed> $changes
     */
    private function createDraft(array $changes = []): int
    {
        $result = $this->tool(self::CLAUDE, 'creer_brouillon_qcm', $changes + $this->qcm());
        self::assertFalse($result['error'], $result['text']);

        return (int) $result['data']['id'];
    }

    /**
     * @return array<string, mixed>
     */
    private function review(int $id, string $opinion): array
    {
        return ['id' => $id, 'avis' => $opinion, 'resume' => 'Relecture du QCM.', 'points' => $opinion === 'favorable' ? [] : [['regle' => 'QCM-02', 'constat' => 'La réponse 3 est peu plausible.']]];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function publicQcms(?string $theme = null, ?string $level = null): array
    {
        $query = $theme === null ? [] : ['languageQCM.name' => $theme, 'niveauQCM.titre' => $level];
        $this->client->request('GET', '/api/q_c_ms', $query, [], ['HTTP_ACCEPT' => 'application/ld+json']);
        self::assertResponseIsSuccessful();

        return json_decode((string) $this->client->getResponse()->getContent(), true)['member'];
    }
}
