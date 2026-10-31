<?php

declare(strict_types=1);

namespace App\Service\Rag;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/** Client du service vectoriel privé ; null signifie que le repli MySQL doit être utilisé. */
final class VectorStore
{
    public function __construct(private HttpClientInterface $http, #[Autowire('%env(RAG_VECTOR_URL)%')] private string $url, #[Autowire('%env(RAG_VECTOR_TOKEN)%')] private string $token) {}

    public function isEnabled(): bool
    {
        return $this->url !== '' && $this->token !== '';
    }

    /** @param list<array{id:string,text:string,course_id:int,position:int,checksum:string}> $passages */
    public function index(array $passages): bool
    {
        // Le calcul des vecteurs se fait sur processeur : plus long qu'une recherche
        return $passages === [] || $this->request('POST', '/index', ['passages' => $passages], 60) !== null;
    }

    /** @return list<array{course_id:int,position:int,text:string,score:float}>|null */
    public function search(string $query): ?array
    {
        $response = $this->request('POST', '/search', ['query' => $query, 'limit' => 30]);
        if (!is_array($response) || !is_array($response['results'] ?? null)) return null;
        return $response['results'];
    }

    public function deleteCourse(int $courseId): bool
    {
        return $this->request('DELETE', '/courses/'.$courseId) !== null;
    }

    private function request(string $method, string $path, ?array $json = null, int $timeout = 8): ?array
    {
        if (!$this->isEnabled()) return null;
        try {
            $response = $this->http->request($method, rtrim($this->url, '/').$path, ['headers' => ['Authorization' => 'Bearer '.$this->token], 'json' => $json, 'timeout' => $timeout]);
            return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300 ? $response->toArray(false) : null;
        } catch (TransportExceptionInterface) {
            return null;
        }
    }
}
