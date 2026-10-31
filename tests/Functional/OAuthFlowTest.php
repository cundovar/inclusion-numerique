<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\OAuthGrant;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Connexion d'un connecteur (claude.ai, ChatGPT) : enregistrement dynamique, consentement d'un administrateur,
 * code + PKCE, jetons, rafraîchissement et révocation, puis accès au point /mcp.
 */
final class OAuthFlowTest extends WebTestCase
{
    private const CALLBACK = 'https://claude.ai/api/mcp/auth_callback';

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private User $admin;

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
        $this->entityManager->flush();
    }

    public function testMcpWithoutTokenPointsToTheOAuthMetadata(): void
    {
        $this->client->request('POST', '/mcp', [], [], ['CONTENT_TYPE' => 'application/json'], '{}');

        self::assertResponseStatusCodeSame(401);
        self::assertSame('Bearer resource_metadata="http://localhost/.well-known/oauth-protected-resource", scope="redaction"', $this->client->getResponse()->headers->get('WWW-Authenticate'));

        $resource = $this->json('GET', '/.well-known/oauth-protected-resource');
        self::assertSame(['http://localhost/mcp', ['http://localhost']], [$resource['resource'], $resource['authorization_servers']]);

        $server = $this->json('GET', '/.well-known/oauth-authorization-server');
        self::assertSame('http://localhost/oauth/register', $server['registration_endpoint']);
        self::assertSame(['S256'], $server['code_challenge_methods_supported']);
        self::assertSame(['none'], $server['token_endpoint_auth_methods_supported']);
    }

    public function testRegistrationOnlyAcceptsTheConnectorsCallbacks(): void
    {
        foreach (['https://evil.example/callback', 'http://claude.ai/api/mcp/auth_callback', 'https://claude.ai.evil.example/cb', 'https://user@claude.ai/cb'] as $uri) {
            $error = $this->json('POST', '/oauth/register', ['client_name' => 'Claude', 'redirect_uris' => [$uri]]);
            self::assertResponseStatusCodeSame(400);
            self::assertSame('invalid_redirect_uri', $error['error'], $uri);
        }

        $secret = $this->json('POST', '/oauth/register', ['client_name' => 'Claude', 'redirect_uris' => [self::CALLBACK], 'token_endpoint_auth_method' => 'client_secret_basic']);
        self::assertSame('invalid_client_metadata', $secret['error']);

        $client = $this->json('POST', '/oauth/register', ['client_name' => 'Claude <script>', 'redirect_uris' => [self::CALLBACK, 'https://chatgpt.com/connector_platform_oauth_redirect']]);
        self::assertResponseStatusCodeSame(201);
        self::assertSame(['Claude script', 'none'], [$client['client_name'], $client['token_endpoint_auth_method']]);
        self::assertMatchesRegularExpression('/^[A-Za-z0-9_-]{43}$/', $client['client_id']);
    }

    public function testFullFlowGivesAccessToMcpUntilRevoked(): void
    {
        $clientId = $this->register();
        [$verifier, $challenge] = self::pkce();

        // Pas connecté : la page de connexion, qui renverra ensuite au consentement
        $this->client->request('GET', '/oauth/authorize?'.$this->authorizeQuery($clientId, $challenge));
        self::assertResponseRedirects('/login');

        $this->client->loginUser($this->admin, 'main');
        $code = $this->approve($clientId, $challenge, 'etat-123');

        $tokens = $this->json('POST', '/oauth/token', ['grant_type' => 'authorization_code', 'code' => $code, 'redirect_uri' => self::CALLBACK, 'client_id' => $clientId, 'code_verifier' => $verifier, 'resource' => 'http://localhost/mcp'], form: true);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('no-store', (string) $this->client->getResponse()->headers->get('Cache-Control'));
        self::assertSame(['Bearer', 3600, 'redaction'], [$tokens['token_type'], $tokens['expires_in'], $tokens['scope']]);
        self::assertSame(200, $this->initializeMcp($tokens['access_token']));

        // Rafraîchissement : nouveaux jetons, les anciens ne valent plus
        $refreshed = $this->json('POST', '/oauth/token', ['grant_type' => 'refresh_token', 'refresh_token' => $tokens['refresh_token'], 'client_id' => $clientId], form: true);
        self::assertResponseIsSuccessful();
        self::assertNotSame($tokens['access_token'], $refreshed['access_token']);
        self::assertSame(401, $this->initializeMcp($tokens['access_token']));
        self::assertSame('invalid_grant', $this->json('POST', '/oauth/token', ['grant_type' => 'refresh_token', 'refresh_token' => $tokens['refresh_token'], 'client_id' => $clientId], form: true)['error']);
        self::assertSame(200, $this->initializeMcp($refreshed['access_token']));

        // Révocation
        $this->client->request('POST', '/oauth/revoke', ['token' => $refreshed['access_token']]);
        self::assertResponseIsSuccessful();
        self::assertSame(401, $this->initializeMcp($refreshed['access_token']));
    }

    public function testCodeIsSingleUseAndCheckedAgainstPkceAndRedirect(): void
    {
        $clientId = $this->register();
        $this->client->loginUser($this->admin, 'main');

        [$verifier, $challenge] = self::pkce();
        $code = $this->approve($clientId, $challenge);
        $wrongVerifier = $this->json('POST', '/oauth/token', ['grant_type' => 'authorization_code', 'code' => $code, 'redirect_uri' => self::CALLBACK, 'client_id' => $clientId, 'code_verifier' => str_repeat('a', 43)], form: true);
        self::assertSame('invalid_grant', $wrongVerifier['error']);

        [$verifier, $challenge] = self::pkce();
        $code = $this->approve($clientId, $challenge);
        $tokens = $this->json('POST', '/oauth/token', ['grant_type' => 'authorization_code', 'code' => $code, 'redirect_uri' => self::CALLBACK, 'client_id' => $clientId, 'code_verifier' => $verifier], form: true);
        self::assertSame(200, $this->initializeMcp($tokens['access_token']));

        // Code rejoué : refusé, et l'accès qu'il a ouvert est révoqué
        $replay = $this->json('POST', '/oauth/token', ['grant_type' => 'authorization_code', 'code' => $code, 'redirect_uri' => self::CALLBACK, 'client_id' => $clientId, 'code_verifier' => $verifier], form: true);
        self::assertSame('invalid_grant', $replay['error']);
        self::assertSame(401, $this->initializeMcp($tokens['access_token']));
    }

    public function testRefusalsOnTheConsentPage(): void
    {
        $clientId = $this->register();
        [, $challenge] = self::pkce();
        $this->client->loginUser($this->admin, 'main');

        // Client inconnu ou adresse de retour différente : erreur affichée, aucune redirection
        $this->client->request('GET', '/oauth/authorize?'.$this->authorizeQuery('inconnu', $challenge));
        self::assertResponseStatusCodeSame(400);
        $this->client->request('GET', '/oauth/authorize?'.http_build_query(['redirect_uri' => 'https://chatgpt.com/autre'] + $this->authorizeParams($clientId, $challenge)));
        self::assertResponseStatusCodeSame(400);

        // Sans PKCE : erreur renvoyée au connecteur
        $this->client->request('GET', '/oauth/authorize?'.http_build_query(['code_challenge_method' => 'plain'] + $this->authorizeParams($clientId, $challenge)));
        self::assertResponseRedirects();
        self::assertStringStartsWith(self::CALLBACK.'?error=invalid_request', (string) $this->client->getResponse()->headers->get('Location'));

        // Refus de l'administrateur
        $crawler = $this->client->request('GET', '/oauth/authorize?'.$this->authorizeQuery($clientId, $challenge, 'etat-refus'));
        $this->client->submit($crawler->selectButton('Refuser')->form());
        $query = $this->callbackQuery();
        self::assertSame(['access_denied', 'etat-refus'], [$query['error'], $query['state']]);
        self::assertSame(0, $this->entityManager->getRepository(OAuthGrant::class)->count([]));
    }

    public function testOnlyAdministratorsCanAuthorizeAndKeepAccess(): void
    {
        $clientId = $this->register();
        [$verifier, $challenge] = self::pkce();

        $editor = (new User())->setUsername('lecteur')->setRoles(['ROLE_USER'])->setPassword('inutilise');
        $this->entityManager->persist($editor);
        $this->entityManager->flush();
        $this->client->loginUser($editor, 'main');
        $this->client->request('GET', '/oauth/authorize?'.$this->authorizeQuery($clientId, $challenge));
        self::assertResponseStatusCodeSame(403);

        $this->client->loginUser($this->admin, 'main');
        $code = $this->approve($clientId, $challenge);
        $tokens = $this->json('POST', '/oauth/token', ['grant_type' => 'authorization_code', 'code' => $code, 'redirect_uri' => self::CALLBACK, 'client_id' => $clientId, 'code_verifier' => $verifier], form: true);

        // L'administrateur perd son rôle : le connecteur perd l'accès
        // (rechargé : le noyau réinitialise Doctrine entre deux requêtes)
        $this->entityManager->find(User::class, $this->admin->getId())->setRoles(['ROLE_USER']);
        $this->entityManager->flush();
        self::assertSame(401, $this->initializeMcp($tokens['access_token']));
        self::assertSame('invalid_grant', $this->json('POST', '/oauth/token', ['grant_type' => 'refresh_token', 'refresh_token' => $tokens['refresh_token'], 'client_id' => $clientId], form: true)['error']);
    }

    private function register(): string
    {
        $client = $this->json('POST', '/oauth/register', ['client_name' => 'Claude', 'redirect_uris' => [self::CALLBACK]]);
        self::assertResponseStatusCodeSame(201);

        return $client['client_id'];
    }

    /**
     * Consentement de l'administrateur connecté ; renvoie le code reçu par le connecteur.
     */
    private function approve(string $clientId, string $challenge, string $state = 'etat'): string
    {
        $crawler = $this->client->request('GET', '/oauth/authorize?'.$this->authorizeQuery($clientId, $challenge, $state));
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Autoriser « Claude »');
        self::assertResponseHeaderSame('X-Frame-Options', 'DENY');

        $this->client->submit($crawler->selectButton('Autoriser')->form());
        $query = $this->callbackQuery();
        self::assertSame([$state, 'http://localhost'], [$query['state'], $query['iss']]);

        return $query['code'];
    }

    /**
     * @return array<string, string>
     */
    private function callbackQuery(): array
    {
        $location = (string) $this->client->getResponse()->headers->get('Location');
        self::assertStringStartsWith(self::CALLBACK.'?', $location);
        parse_str((string) parse_url($location, \PHP_URL_QUERY), $query);

        return $query;
    }

    /**
     * @return array<string, string>
     */
    private function authorizeParams(string $clientId, string $challenge, string $state = 'etat'): array
    {
        return [
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => self::CALLBACK,
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
            'state' => $state,
            'scope' => 'redaction',
            'resource' => 'http://localhost/mcp',
        ];
    }

    private function authorizeQuery(string $clientId, string $challenge, string $state = 'etat'): string
    {
        return http_build_query($this->authorizeParams($clientId, $challenge, $state));
    }

    private function initializeMcp(string $accessToken): int
    {
        $this->client->request('POST', '/mcp', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$accessToken,
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json, text/event-stream',
        ], json_encode(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'initialize', 'params' => [
            'protocolVersion' => '2025-06-18', 'capabilities' => (object) [], 'clientInfo' => ['name' => 'test', 'version' => '1'],
        ]], \JSON_THROW_ON_ERROR));

        return $this->client->getResponse()->getStatusCode();
    }

    /**
     * @return array{0: string, 1: string} vérificateur et défi PKCE (S256)
     */
    private static function pkce(): array
    {
        $verifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        return [$verifier, rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=')];
    }

    /**
     * @param array<string, mixed>|null $body
     * @return array<string, mixed>
     */
    private function json(string $method, string $url, ?array $body = null, bool $form = false): array
    {
        if ($form) {
            $this->client->request($method, $url, $body ?? []);
        } else {
            $this->client->request($method, $url, [], [], ['CONTENT_TYPE' => 'application/json'], $body === null ? null : json_encode($body, \JSON_THROW_ON_ERROR));
        }

        return json_decode((string) $this->client->getResponse()->getContent(), true) ?? [];
    }
}
