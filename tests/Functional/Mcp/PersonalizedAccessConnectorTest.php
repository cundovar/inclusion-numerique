<?php

declare(strict_types=1);

namespace App\Tests\Functional\Mcp;

use App\Entity\Category;
use App\Entity\LanguageQCM;
use App\Entity\NiveauQCM;
use App\Entity\Page;
use App\Entity\PageContent;
use App\Entity\User;

/**
 * Le connecteur réserve des cours, parcours et QCM à des apprenants : seuls l'identifiant et le pseudo
 * d'un apprenant sortent du site, et un contenu réservé ne redevient public que sur demande explicite.
 */
final class PersonalizedAccessConnectorTest extends McpTestCase
{
    private User $alice;
    private User $bruno;
    private Category $parcours;

    protected function setUp(): void
    {
        parent::setUp();
        $this->alice = (new User())->setUsername('alice')->setPassword('inutilise');
        $this->bruno = (new User())->setUsername('bruno')->setPassword('inutilise');
        $this->parcours = (new Category())->setName('Premiers pas')->setAudience(Category::AUDIENCE_APPRENDRE);
        foreach ([$this->alice, $this->bruno, $this->parcours] as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }

    public function testLearnersAreListedWithoutAdministratorsOrPersonalData(): void
    {
        $learners = $this->tool(self::CLAUDE, 'lister_apprenants')['data'];

        self::assertSame(['alice', 'bruno'], array_column($learners, 'pseudo'));
        self::assertSame(['id', 'pseudo', 'contenusReserves'], array_keys($learners[0]));
        self::assertSame(['cours' => 0, 'parcours' => 0, 'qcm' => 0], $learners[0]['contenusReserves']);
    }

    public function testGrantingRestrictsThePublishedCourseToItsLearners(): void
    {
        $course = $this->publishedCourse('whatsapp-pour-alice');

        $granted = $this->tool(self::CLAUDE, 'attribuer_acces', ['type' => 'cours', 'id' => $course->getId(), 'apprenantIds' => [$this->alice->getId()]]);
        self::assertFalse($granted['error'], $granted['text']);
        self::assertSame('ses destinataires seulement', $granted['data']['visiblePour']);
        self::assertSame([['id' => $this->alice->getId(), 'pseudo' => 'alice']], $granted['data']['destinataires']);
        self::assertSame(['cours' => 1, 'parcours' => 0, 'qcm' => 0], $this->tool(self::CLAUDE, 'lister_apprenants')['data'][0]['contenusReserves']);
        self::assertSame([], $this->publicTitles());

        // Retirer le dernier destinataire ne rend pas le cours public
        $revoked = $this->tool(self::CLAUDE, 'retirer_acces', ['type' => 'cours', 'id' => $course->getId(), 'apprenantIds' => [$this->alice->getId()]]);
        self::assertSame('personne (réservé sans destinataire)', $revoked['data']['visiblePour']);
        self::assertSame([], $this->publicTitles());

        $opened = $this->tool(self::CLAUDE, 'ouvrir_a_tous', ['type' => 'cours', 'id' => $course->getId()]);
        self::assertSame('tout le monde', $opened['data']['visiblePour']);
        self::assertSame(['whatsapp-pour-alice'], $this->publicTitles());
    }

    public function testUnknownLearnerIsRejectedWithoutChangingAnything(): void
    {
        $course = $this->publishedCourse('cours-ouvert');

        $result = $this->tool(self::CLAUDE, 'attribuer_acces', ['type' => 'cours', 'id' => $course->getId(), 'apprenantIds' => [$this->alice->getId(), 999]]);

        self::assertTrue($result['error']);
        self::assertStringContainsString('999', $result['text']);
        self::assertSame(['cours-ouvert'], $this->publicTitles());
        self::assertTrue($this->tool(self::CLAUDE, 'attribuer_acces', ['type' => 'module', 'id' => 1, 'apprenantIds' => [$this->alice->getId()]])['error']);
    }

    public function testPersonalCourseIsReservedFromItsCreation(): void
    {
        $created = $this->tool(self::CLAUDE, 'creer_brouillon', [
            'titre' => 'Envoyer une photo à sa fille',
            'slug' => 'photo-pour-alice',
            'parcoursId' => $this->parcours->getId(),
            'contenu' => '<p>Bonjour</p>',
            'apprenantIds' => [$this->alice->getId()],
        ]);
        self::assertFalse($created['error'], $created['text']);
        self::assertSame(PageContent::STATUS_DRAFT, $created['data']['statut']);
        self::assertTrue($created['data']['acces']['reserve']);

        $listed = $this->tool(self::CLAUDE, 'lister_acces', ['apprenantId' => $this->alice->getId()])['data'];
        self::assertSame([['cours', $created['data']['id']]], array_map(static fn (array $item) => [$item['type'], $item['id']], $listed));
        self::assertSame([], $this->tool(self::CLAUDE, 'lister_acces', ['apprenantId' => $this->bruno->getId()])['data']);

        // Identifiant inconnu : aucun brouillon n'est créé
        $refused = $this->tool(self::CLAUDE, 'creer_brouillon', ['titre' => 'X', 'slug' => 'jamais-cree', 'parcoursId' => $this->parcours->getId(), 'contenu' => '', 'apprenantIds' => [999]]);
        self::assertTrue($refused['error']);
        self::assertNull($this->entityManager->getRepository(Page::class)->findOneBy(['slug' => '/jamais-cree']));
    }

    public function testPersonalExerciseIsPublishedForItsLearnersOnly(): void
    {
        $theme = (new LanguageQCM())->setName('Sécurité');
        $level = (new NiveauQCM())->setTitre('Débutant');
        $this->entityManager->persist($theme);
        $this->entityManager->persist($level);
        $this->entityManager->flush();
        $draft = $this->tool(self::CLAUDE, 'creer_brouillon_qcm', [
            'question' => 'Un message vous demande de payer un colis. Que faites-vous ?',
            'explication' => 'Un vrai transporteur ne demande pas de payer par un lien.',
            'reponses' => [
                ['texte' => 'Je clique sur le lien', 'correcte' => false, 'explication' => 'Le lien peut mener à un faux site.'],
                ['texte' => 'Je supprime le message', 'correcte' => true],
            ],
            'themeId' => $theme->getId(),
            'niveauId' => $level->getId(),
        ]);
        self::assertFalse($draft['error'], $draft['text']);
        $id = (int) $draft['data']['id'];
        $this->tool(self::CHATGPT, 'enregistrer_relecture_qcm', ['id' => $id, 'avis' => 'favorable', 'resume' => 'Relecture du QCM.', 'points' => []]);

        $published = $this->tool(self::CLAUDE, 'publier_qcm', ['id' => $id, 'apprenantIds' => [$this->alice->getId()]]);

        self::assertFalse($published['error'], $published['text']);
        self::assertTrue($published['data']['qcm']['reserve']);
        self::assertSame([['id' => $this->alice->getId(), 'pseudo' => 'alice']], $published['data']['qcm']['destinataires']);
        $this->client->request('GET', '/api/q_c_ms', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        self::assertSame([], json_decode((string) $this->client->getResponse()->getContent(), true)['member']);
    }

    private function publishedCourse(string $slug): PageContent
    {
        $page = (new Page())->setSlug('/'.$slug);
        $course = (new PageContent())->setTitle($slug)->setStatus(PageContent::STATUS_PUBLISHED)->setPage($page)->setCategory($this->parcours);
        $this->entityManager->persist($page);
        $this->entityManager->persist($course);
        $this->entityManager->flush();

        return $course;
    }

    /**
     * @return list<string>
     */
    private function publicTitles(): array
    {
        $this->client->request('GET', '/api/page_contents', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        self::assertResponseIsSuccessful();

        return array_column(json_decode((string) $this->client->getResponse()->getContent(), true)['member'], 'title');
    }
}
