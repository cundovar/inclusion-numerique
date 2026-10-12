<?php

declare(strict_types=1);

namespace App\Tests\Functional\QcmResult;

use App\Domain\QcmResult\Exception\QcmResultsUnavailableException;
use App\Domain\QcmResult\QcmResultsGatewayInterface;
use App\Entity\ChoicesQCM;
use App\Entity\LanguageQCM;
use App\Entity\NiveauQCM;
use App\Entity\QCM;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * API /api/qcm-results : accès, validation et dégradation quand le micro-service est indisponible.
 * Le micro-service est remplacé par une passerelle en mémoire ; ses propres tests sont dans services/qcm-results.
 */
final class QcmResultApiTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private InMemoryQcmResultsGateway $gateway;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        // Sans redémarrage du noyau entre deux requêtes, la passerelle en mémoire reste en place
        $this->client->disableReboot();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $this->gateway = new InMemoryQcmResultsGateway();
        static::getContainer()->set(QcmResultsGatewayInterface::class, $this->gateway);
    }

    /**
     * @dataProvider userEndpoints
     */
    public function testAnonymousVisitorIsRejected(string $method, string $path): void
    {
        $this->jsonRequest($method, $path, 'POST' === $method ? ['language' => 'CSS', 'level' => 'basique', 'answers' => []] : null);

        self::assertResponseStatusCodeSame(401);
        self::assertSame([], $this->gateway->attempts);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function userEndpoints(): iterable
    {
        yield 'enregistrement' => ['POST', '/api/qcm-results'];
        yield 'historique' => ['GET', '/api/qcm-results/me'];
        yield 'progression' => ['GET', '/api/qcm-results/me/progress'];
        yield 'effacement' => ['DELETE', '/api/qcm-results/me'];
        yield 'statistiques' => ['GET', '/api/qcm-results/stats/questions'];
    }

    public function testUserRecordsAnAttemptCorrectedByTheServer(): void
    {
        $alice = $this->createUser('alice');
        [$question, $right, $wrong] = $this->createQuestion('CSS', 'basique');
        $this->client->loginUser($alice, 'main');

        $this->jsonRequest('POST', '/api/qcm-results', [
            'language' => 'CSS',
            'level' => 'basique',
            'answers' => [['questionId' => $question->getId(), 'choiceId' => $wrong->getId()]],
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertCount(1, $this->gateway->attempts);
        self::assertSame($alice->getId(), $this->gateway->attempts[0]['userId']);
        // Le navigateur n'envoie que le choix : la correction vient de MySQL
        self::assertSame([['questionId' => $question->getId(), 'choiceId' => $wrong->getId(), 'correct' => false]], $this->gateway->attempts[0]['answers']);
        self::assertNotSame($right->getId(), $wrong->getId());
    }

    /**
     * @dataProvider invalidBodies
     *
     * @param array<string, mixed> $body
     */
    public function testInvalidAttemptIsRejected(array $body): void
    {
        $this->createQuestion('CSS', 'basique');
        $this->client->loginUser($this->createUser('alice'), 'main');

        $this->jsonRequest('POST', '/api/qcm-results', $body);

        self::assertResponseStatusCodeSame(400);
        self::assertSame([], $this->gateway->attempts);
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function invalidBodies(): iterable
    {
        yield 'langage manquant' => [['level' => 'basique', 'answers' => [['questionId' => 1, 'choiceId' => 1]]]];
        yield 'identifiants non entiers' => [['language' => 'CSS', 'level' => 'basique', 'answers' => [['questionId' => '1', 'choiceId' => 1]]]];
        yield 'question répondue deux fois' => [['language' => 'CSS', 'level' => 'basique', 'answers' => [['questionId' => 1, 'choiceId' => 1], ['questionId' => 1, 'choiceId' => 2]]]];
        yield 'question inconnue' => [['language' => 'CSS', 'level' => 'basique', 'answers' => [['questionId' => 999, 'choiceId' => 1]]]];
        yield 'aucune réponse' => [['language' => 'CSS', 'level' => 'basique', 'answers' => []]];
    }

    public function testUserOnlyReadsAndDeletesHisOwnResults(): void
    {
        $alice = $this->createUser('alice');
        $this->gateway->attempts = [
            ['userId' => $alice->getId(), 'language' => 'CSS'],
            ['userId' => 999, 'language' => 'PHP'],
        ];
        $this->client->loginUser($alice, 'main');

        $this->jsonRequest('GET', '/api/qcm-results/me');
        self::assertResponseIsSuccessful();
        self::assertSame([['userId' => $alice->getId(), 'language' => 'CSS']], $this->responseJson());

        $this->jsonRequest('DELETE', '/api/qcm-results/me');
        self::assertSame(['deleted' => 1], $this->responseJson());
        self::assertSame([['userId' => 999, 'language' => 'PHP']], array_values($this->gateway->attempts));
    }

    public function testQuestionStatsAreForAdminsAndCarryTheQuestionTitle(): void
    {
        [$question] = $this->createQuestion('CSS', 'basique');
        $this->gateway->stats = [['questionId' => (int) $question->getId(), 'answered' => 4, 'correct' => 1, 'successPercent' => 25]];

        $this->client->loginUser($this->createUser('alice'), 'main');
        $this->jsonRequest('GET', '/api/qcm-results/stats/questions');
        self::assertResponseStatusCodeSame(403);

        $this->client->loginUser($this->createUser('admin', ['ROLE_ADMIN']), 'main');
        $this->jsonRequest('GET', '/api/qcm-results/stats/questions?language=CSS');
        self::assertResponseIsSuccessful();
        self::assertSame('Que fait flex-wrap ?', $this->responseJson()[0]['title']);
    }

    public function testUnavailableServiceAnswers503(): void
    {
        $this->gateway->available = false;
        $this->client->loginUser($this->createUser('alice'), 'main');

        $this->jsonRequest('GET', '/api/qcm-results/me/progress');

        self::assertResponseStatusCodeSame(503);
    }

    /**
     * @param array<string, mixed>|null $body
     */
    private function jsonRequest(string $method, string $path, ?array $body = null): void
    {
        $this->client->request($method, $path, [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ], null === $body ? null : json_encode($body, \JSON_THROW_ON_ERROR));
    }

    /**
     * @return array<mixed>
     */
    private function responseJson(): array
    {
        return json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
    }

    /**
     * @param list<string> $roles
     */
    private function createUser(string $username, array $roles = ['ROLE_USER']): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setPassword('hashed');
        $user->setRoles($roles);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @return array{QCM, ChoicesQCM, ChoicesQCM} la question, son bon choix, un mauvais choix
     */
    private function createQuestion(string $language, string $level): array
    {
        $languageQcm = (new LanguageQCM())->setName($language);
        $levelQcm = (new NiveauQCM())->setTitre($level);
        $right = (new ChoicesQCM())->setQuestion('Retour à la ligne')->setIsCorrect(true);
        $wrong = (new ChoicesQCM())->setQuestion('Centre les éléments')->setIsCorrect(false);

        $question = (new QCM())->setTitre('Que fait flex-wrap ?')->setSolution('Retour à la ligne');
        $question->setLanguageQCM($languageQcm)->setNiveauQCM($levelQcm);
        $question->addChoicesQCM($right)->addChoicesQCM($wrong);

        $this->entityManager->persist($languageQcm);
        $this->entityManager->persist($levelQcm);
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        return [$question, $right, $wrong];
    }
}

/**
 * Double du micro-service : stocke en mémoire, ou simule une panne.
 */
final class InMemoryQcmResultsGateway implements QcmResultsGatewayInterface
{
    /** @var list<array<string, mixed>> */
    public array $attempts = [];

    /** @var list<array{questionId: int, answered: int, correct: int, successPercent: int}> */
    public array $stats = [];

    public bool $available = true;

    public function recordAttempt(int $userId, string $language, string $level, array $answers): array
    {
        $this->ensureAvailable();
        $attempt = ['userId' => $userId, 'language' => $language, 'level' => $level, 'answers' => $answers];
        $this->attempts[] = $attempt;

        return $attempt;
    }

    public function findUserAttempts(int $userId, int $limit = 20): array
    {
        $this->ensureAvailable();

        return array_values(array_filter($this->attempts, static fn (array $a): bool => $a['userId'] === $userId));
    }

    public function getUserProgress(int $userId): array
    {
        $this->ensureAvailable();

        return [];
    }

    public function deleteUserAttempts(int $userId): int
    {
        $this->ensureAvailable();
        $before = \count($this->attempts);
        $this->attempts = array_values(array_filter($this->attempts, static fn (array $a): bool => $a['userId'] !== $userId));

        return $before - \count($this->attempts);
    }

    public function getQuestionStats(?string $language = null, ?string $level = null, int $limit = 20): array
    {
        $this->ensureAvailable();

        return $this->stats;
    }

    private function ensureAvailable(): void
    {
        if (!$this->available) {
            throw QcmResultsUnavailableException::because('panne simulée');
        }
    }
}
