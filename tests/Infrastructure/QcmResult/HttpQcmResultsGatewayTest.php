<?php

declare(strict_types=1);

use App\Domain\QcmResult\Exception\QcmResultsUnavailableException;
use App\Infrastructure\QcmResult\HttpQcmResultsGateway;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class HttpQcmResultsGatewayTest extends TestCase
{
    private const BASE_URI = 'https://qcm-api.test';

    public function testRecordAttemptPostsTheAttemptAsJson(): void
    {
        $response = new MockResponse('{"id":"abc","score":1,"total":2}', ['http_code' => 201]);
        $gateway = $this->gateway($response);

        $attempt = $gateway->recordAttempt(7, 'CSS', 'basique', [['questionId' => 1, 'choiceId' => 11, 'correct' => true]]);

        self::assertSame(['id' => 'abc', 'score' => 1, 'total' => 2], $attempt);
        self::assertSame('POST', $response->getRequestMethod());
        self::assertSame(self::BASE_URI.'/attempts', $response->getRequestUrl());
        self::assertSame(
            ['userId' => 7, 'language' => 'CSS', 'level' => 'basique', 'answers' => [['questionId' => 1, 'choiceId' => 11, 'correct' => true]]],
            json_decode($response->getRequestOptions()['body'], true)
        );
    }

    public function testReadsAreScopedToTheUser(): void
    {
        $history = new MockResponse('[{"id":"abc"}]');
        $progress = new MockResponse('[]');
        $deletion = new MockResponse('{"deleted":3}');
        $gateway = $this->gateway($history, $progress, $deletion);

        self::assertSame([['id' => 'abc']], $gateway->findUserAttempts(7, 5));
        self::assertSame([], $gateway->getUserProgress(7));
        self::assertSame(3, $gateway->deleteUserAttempts(7));

        self::assertSame(self::BASE_URI.'/users/7/attempts?limit=5', $history->getRequestUrl());
        self::assertSame(self::BASE_URI.'/users/7/progress', $progress->getRequestUrl());
        self::assertSame('DELETE', $deletion->getRequestMethod());
        self::assertSame(self::BASE_URI.'/users/7/attempts', $deletion->getRequestUrl());
    }

    public function testQuestionStatsOmitsEmptyFilters(): void
    {
        $response = new MockResponse('[]');

        $this->gateway($response)->getQuestionStats('CSS', '');

        self::assertSame(self::BASE_URI.'/stats/questions?language=CSS&limit=20', $response->getRequestUrl());
    }

    /**
     * @dataProvider failures
     */
    public function testServiceFailuresBecomeUnavailable(MockResponse $response): void
    {
        $this->expectException(QcmResultsUnavailableException::class);

        $this->gateway($response)->getUserProgress(7);
    }

    /**
     * @return iterable<string, array{MockResponse}>
     */
    public static function failures(): iterable
    {
        yield 'clé refusée' => [new MockResponse('{"error":"Clé d\'API invalide"}', ['http_code' => 401])];
        yield 'erreur serveur' => [new MockResponse('', ['http_code' => 500])];
        yield 'réponse non JSON' => [new MockResponse('<html>')];
        yield 'service injoignable' => [new MockResponse('', ['error' => 'Connection refused'])];
    }

    public function testTransportExceptionIsWrapped(): void
    {
        $client = new MockHttpClient(static fn () => throw new TransportException('timeout'), self::BASE_URI);

        $this->expectException(QcmResultsUnavailableException::class);

        (new HttpQcmResultsGateway($client))->deleteUserAttempts(7);
    }

    private function gateway(MockResponse ...$responses): HttpQcmResultsGateway
    {
        return new HttpQcmResultsGateway(new MockHttpClient($responses, self::BASE_URI));
    }
}
