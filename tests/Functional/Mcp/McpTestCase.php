<?php

declare(strict_types=1);

namespace App\Tests\Functional\Mcp;

use App\Entity\OAuthClient;
use App\Entity\OAuthGrant;
use App\Entity\User;
use App\Service\OAuth\OAuthServer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Appels réels au point /mcp, au nom de deux connecteurs autorisés par un administrateur :
 * « Claude » (rédacteur) et « ChatGPT » (relecteur).
 */
abstract class McpTestCase extends WebTestCase
{
    protected const CLAUDE = 'claude';
    protected const CHATGPT = 'chatgpt';

    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;
    protected User $admin;
    /** @var array<string, OAuthClient> */
    protected array $connectors = [];
    /** @var array<string, string> jeton d'accès par connecteur */
    protected array $tokens = [];
    /** @var array<string, string> session MCP par connecteur */
    private array $sessions = [];
    private int $nextId = 1;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $this->admin = (new User())->setUsername('redaction')->setRoles(['ROLE_ADMIN'])->setPassword('inutilise');
        $this->entityManager->persist($this->admin);
        foreach ([self::CLAUDE => 'Claude', self::CHATGPT => 'ChatGPT'] as $key => $name) {
            $this->connectors[$key] = new OAuthClient(bin2hex(random_bytes(16)), $name, ['https://claude.ai/api/mcp/auth_callback']);
            $this->entityManager->persist($this->connectors[$key]);
            $this->tokens[$key] = $this->grant($this->connectors[$key]);
        }
        $this->entityManager->flush();
    }

    protected function agentLabel(string $who): string
    {
        return $this->connectors[$who]->getAgentLabel();
    }

    /**
     * Accès valide pour ce connecteur, au nom de l'administrateur ; renvoie le jeton d'accès.
     */
    protected function grant(OAuthClient $connector, string $access = '+1 hour'): string
    {
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $grant = new OAuthGrant($connector, $this->admin, null);
        $grant->rotate(OAuthServer::hash($token), new \DateTimeImmutable($access), OAuthServer::hash('refresh-'.$token), new \DateTimeImmutable('+30 days'));
        $this->entityManager->persist($grant);

        return $token;
    }

    /**
     * Appelle un outil ; `error` indique un refus (résultat isError ou erreur JSON-RPC, par exemple un schéma non respecté).
     *
     * @param array<string, mixed> $arguments
     * @return array{error: bool, text: string, data: mixed}
     */
    protected function tool(string $who, string $name, array $arguments = []): array
    {
        $response = $this->rpc($who, 'tools/call', ['name' => $name, 'arguments' => (object) $arguments]);
        if (isset($response['error'])) {
            return ['error' => true, 'text' => (string) $response['error']['message'], 'data' => null];
        }
        $text = (string) ($response['result']['content'][0]['text'] ?? '');

        return ['error' => (bool) ($response['result']['isError'] ?? false), 'text' => $text, 'data' => json_decode($text, true)];
    }

    /**
     * @param array<string, mixed>|object $params
     * @return array<string, mixed>
     */
    protected function rpc(string $who, string $method, array|object $params = []): array
    {
        if (!isset($this->sessions[$who])) {
            $this->post($who, ['jsonrpc' => '2.0', 'id' => $this->nextId++, 'method' => 'initialize', 'params' => [
                'protocolVersion' => '2025-06-18',
                'capabilities' => (object) [],
                'clientInfo' => ['name' => 'test-'.$who, 'version' => '1.0.0'],
            ]]);
            self::assertResponseIsSuccessful();
            $this->sessions[$who] = (string) $this->client->getResponse()->headers->get('Mcp-Session-Id');
            $this->post($who, ['jsonrpc' => '2.0', 'method' => 'notifications/initialized']);
        }

        return $this->post($who, ['jsonrpc' => '2.0', 'id' => $this->nextId++, 'method' => $method, 'params' => (object) $params]);
    }

    /**
     * @param array<string, mixed> $message
     * @return array<string, mixed>
     */
    private function post(string $who, array $message): array
    {
        $headers = [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->tokens[$who],
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json, text/event-stream',
        ];
        if (isset($this->sessions[$who])) {
            $headers['HTTP_MCP_SESSION_ID'] = $this->sessions[$who];
            $headers['HTTP_MCP_PROTOCOL_VERSION'] = '2025-06-18';
        }
        $this->client->request('POST', '/mcp', [], [], $headers, json_encode($message, \JSON_THROW_ON_ERROR));

        return self::decode((string) $this->client->getResponse()->getContent());
    }

    /**
     * Réponse JSON, ou flux SSE dont on lit le dernier message.
     *
     * @return array<string, mixed>
     */
    private static function decode(string $body): array
    {
        if (preg_match_all('/^data: (.+)$/m', $body, $matches) > 0) {
            $body = (string) end($matches[1]);
        }

        return json_decode($body, true) ?? [];
    }
}
