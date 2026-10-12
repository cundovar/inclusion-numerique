<?php

declare(strict_types=1);

namespace App\Infrastructure\QcmResult;

use App\Domain\QcmResult\Exception\QcmResultsUnavailableException;
use App\Domain\QcmResult\QcmResultsGatewayInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Adaptateur HTTP vers le micro-service `services/qcm-results` (Node + MongoDB, déployé sur le VPS).
 * Le client injecté est le client « qcm_results.client » : URL de base et clé d'API (en-tête X-Api-Key)
 * sont fixées dans config/packages/framework.yaml.
 */
final class HttpQcmResultsGateway implements QcmResultsGatewayInterface
{
    private const TIMEOUT_SECONDS = 3;

    public function __construct(
        private HttpClientInterface $qcmResultsClient
    ) {}

    public function recordAttempt(int $userId, string $language, string $level, array $answers): array
    {
        return $this->request('POST', '/attempts', ['json' => [
            'userId' => $userId,
            'language' => $language,
            'level' => $level,
            'answers' => $answers,
        ]]);
    }

    public function findUserAttempts(int $userId, int $limit = 20): array
    {
        /** @var list<array<string, mixed>> */
        return $this->request('GET', sprintf('/users/%d/attempts', $userId), ['query' => ['limit' => $limit]]);
    }

    public function getUserProgress(int $userId): array
    {
        /** @var list<array<string, mixed>> */
        return $this->request('GET', sprintf('/users/%d/progress', $userId));
    }

    public function deleteUserAttempts(int $userId): int
    {
        return (int) ($this->request('DELETE', sprintf('/users/%d/attempts', $userId))['deleted'] ?? 0);
    }

    public function getQuestionStats(?string $language = null, ?string $level = null, int $limit = 20): array
    {
        $query = array_filter(['language' => $language, 'level' => $level], static fn (?string $v): bool => null !== $v && '' !== $v);
        $query['limit'] = $limit;

        /** @var list<array{questionId: int, answered: int, correct: int, successPercent: int}> */
        return $this->request('GET', '/stats/questions', ['query' => $query]);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<mixed>
     */
    private function request(string $method, string $path, array $options = []): array
    {
        try {
            $response = $this->qcmResultsClient->request($method, $path, $options + ['timeout' => self::TIMEOUT_SECONDS]);
            $status = $response->getStatusCode();
            if ($status >= 300) {
                throw QcmResultsUnavailableException::because(sprintf('%s %s a répondu %d', $method, $path, $status));
            }

            return $response->toArray();
        } catch (ExceptionInterface $e) {
            throw QcmResultsUnavailableException::because($e->getMessage(), $e);
        }
    }
}
