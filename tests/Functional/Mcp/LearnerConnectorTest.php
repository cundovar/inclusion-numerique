<?php

declare(strict_types=1);

namespace App\Tests\Functional\Mcp;

use App\Entity\Category;
use App\Entity\ChoicesQCM;
use App\Entity\OAuthClient;
use App\Entity\Page;
use App\Entity\PageContent;
use App\Entity\QCM;
use App\Entity\User;
use App\Service\OAuth\OAuthServer;

/**
 * Connecteur apprenant (/mcp/apprenant) : lecture seule des cours et QCM publiés, jeton distinct de la rédaction.
 */
final class LearnerConnectorTest extends McpTestCase
{
    private const LEARNER = 'apprenant';

    private int $published;
    private int $draft;

    protected function setUp(): void
    {
        parent::setUp();

        $learner = (new User())->setUsername('apprenant')->setRoles([])->setPassword('inutilise');
        $connector = new OAuthClient(bin2hex(random_bytes(16)), 'Claude', ['https://claude.ai/api/mcp/auth_callback']);
        $parcours = (new Category())->setName('Symfony');
        $this->entityManager->persist($learner);
        $this->entityManager->persist($connector);
        $this->entityManager->persist($parcours);
        $this->tokens[self::LEARNER] = $this->grant($connector, user: $learner, resource: '/mcp/apprenant', scope: OAuthServer::SCOPE_LEARNER_READ);
        $this->paths[self::LEARNER] = '/mcp/apprenant';

        $this->published = $this->course($parcours, 'Premier contrôleur', 'premier-controleur', PageContent::STATUS_PUBLISHED);
        $this->draft = $this->course($parcours, 'Contrôleur en brouillon', 'controleur-brouillon', PageContent::STATUS_DRAFT);
    }

    public function testOnlyReadOnlyToolsAreOffered(): void
    {
        $tools = array_column($this->rpc(self::LEARNER, 'tools/list')['result']['tools'], 'name');
        sort($tools);

        self::assertSame(['demarrer_qcm', 'lire_cours', 'lister_parcours', 'lister_qcm', 'rechercher_cours', 'repondre_qcm'], $tools);
    }

    public function testDraftsAreNeverServed(): void
    {
        self::assertSame(['Symfony'], array_column($this->tool(self::LEARNER, 'lister_parcours')['data'], 'titre'));

        $found = $this->tool(self::LEARNER, 'rechercher_cours', ['recherche' => 'contrôleur'])['data'];
        self::assertSame([$this->published], array_column($found, 'id'));
        self::assertStringContainsString('contrôleur', $found[0]['citation']['texte']);

        self::assertSame('Premier contrôleur', $this->tool(self::LEARNER, 'lire_cours', ['id' => $this->published])['data']['titre']);
        self::assertSame(['erreur' => 'Cours introuvable.'], $this->tool(self::LEARNER, 'lire_cours', ['id' => $this->draft])['data']);
    }

    public function testQcmIsPlayedWithoutRevealingTheAnswerFirst(): void
    {
        $qcm = (new QCM())->setTitre('Quelle commande crée un contrôleur ?')->setSolution('make:controller génère la classe et le gabarit.');
        $right = (new ChoicesQCM())->setQuestion('make:controller')->setIsCorrect(true);
        $wrong = (new ChoicesQCM())->setQuestion('make:route')->setIsCorrect(false);
        $qcm->addChoicesQCM($right)->addChoicesQCM($wrong);
        $this->entityManager->persist($qcm);
        $this->entityManager->flush();

        self::assertSame([$qcm->getId()], array_column($this->tool(self::LEARNER, 'lister_qcm')['data'], 'id'));
        $started = $this->tool(self::LEARNER, 'demarrer_qcm', ['id' => $qcm->getId()])['data'];
        self::assertStringNotContainsString('correct', json_encode($started['choix'], \JSON_THROW_ON_ERROR));

        $answer = $this->tool(self::LEARNER, 'repondre_qcm', ['session' => $started['session'], 'choixId' => $right->getId()])['data'];
        self::assertTrue($answer['correct']);
    }

    public function testEachTokenOnlyOpensItsOwnEndpoint(): void
    {
        $this->client->request('POST', '/mcp', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$this->tokens[self::LEARNER], 'CONTENT_TYPE' => 'application/json'], '{}');
        self::assertResponseStatusCodeSame(401);

        $this->client->request('POST', '/mcp/apprenant', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$this->tokens[self::CLAUDE], 'CONTENT_TYPE' => 'application/json'], '{}');
        self::assertResponseStatusCodeSame(401);
    }

    private function course(Category $parcours, string $title, string $slug, string $status): int
    {
        $page = (new Page())->setSlug('/'.$slug);
        $course = (new PageContent())->setTitle($title)->setCategory($parcours)->setPage($page)
            ->setCode('<p>Un contrôleur reçoit la requête et renvoie la réponse.</p>')->setStatus($status);
        $this->entityManager->persist($page);
        $this->entityManager->persist($course);
        $this->entityManager->flush();

        return (int) $course->getId();
    }
}
