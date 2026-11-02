<?php

declare(strict_types=1);

namespace App\Service\OAuth;

use App\Entity\OAuthAuthorizationCode;
use App\Entity\OAuthClient;
use App\Entity\OAuthGrant;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Serveur d'autorisation OAuth 2.1 du connecteur MCP : enregistrement dynamique des connecteurs (RFC 7591),
 * code d'autorisation avec PKCE S256 obligatoire, jetons opaques dont seule l'empreinte est stockée,
 * rafraîchissement avec rotation et révocation (RFC 7009).
 *
 * Seuls les administrateurs du back-office peuvent autoriser un connecteur, et l'accès s'arrête dès
 * qu'ils perdent ce rôle.
 */
final class OAuthServer
{
    public const SCOPE = 'redaction';
    public const REQUIRED_ROLE = 'ROLE_ADMIN';

    private const CODE_TTL = 'PT5M';
    private const ACCESS_TTL = 3600;
    private const REFRESH_TTL = 'P30D';
    // Garde-fou contre l'enregistrement en masse (l'enregistrement est ouvert, comme l'exige la RFC 7591)
    private const MAX_REGISTRATIONS_PER_HOUR = 30;
    private const LOCAL_HOSTS = ['localhost', '127.0.0.1'];

    /**
     * @param list<string> $redirectHosts hôtes autorisés pour le retour d'un connecteur (claude.ai, chatgpt.com…)
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire('%app.oauth.redirect_hosts%')] private array $redirectHosts,
    ) {}

    /**
     * @param array<string, mixed> $metadata
     */
    public function register(array $metadata): OAuthClient
    {
        $uris = $metadata['redirect_uris'] ?? null;
        if (!\is_array($uris) || $uris === [] || \count($uris) > 5) {
            throw new OAuthError('invalid_redirect_uri', 'Entre une et cinq adresses de retour (redirect_uris) sont attendues.');
        }
        foreach ($uris as $uri) {
            if (!\is_string($uri) || !$this->isAllowedRedirectUri($uri)) {
                throw new OAuthError('invalid_redirect_uri', 'Adresse de retour non autorisée. Hôtes acceptés : '.implode(', ', $this->redirectHosts).'.');
            }
        }
        $method = $metadata['token_endpoint_auth_method'] ?? 'none';
        if ($method !== 'none') {
            throw new OAuthError('invalid_client_metadata', 'Seuls les clients publics avec PKCE sont acceptés (token_endpoint_auth_method « none »).');
        }
        foreach ((array) ($metadata['grant_types'] ?? []) as $grantType) {
            if (!\in_array($grantType, ['authorization_code', 'refresh_token'], true)) {
                throw new OAuthError('invalid_client_metadata', 'Types d\'autorisation acceptés : authorization_code et refresh_token.');
            }
        }

        $since = new \DateTimeImmutable('-1 hour');
        $recent = (int) $this->entityManager->createQuery('SELECT COUNT(c.id) FROM '.OAuthClient::class.' c WHERE c.createdAt > :since')
            ->setParameter('since', $since)
            ->getSingleScalarResult();
        if ($recent >= self::MAX_REGISTRATIONS_PER_HOUR) {
            throw new OAuthError('temporarily_unavailable', 'Trop d\'enregistrements récents : réessayez plus tard.', 429);
        }

        $name = trim(preg_replace('/[^\p{L}\p{N} ._-]/u', '', (string) ($metadata['client_name'] ?? '')) ?? '');
        $client = new OAuthClient(self::randomToken(), mb_substr($name === '' ? 'Connecteur' : $name, 0, 60), array_values($uris));
        $this->entityManager->persist($client);
        $this->entityManager->flush();

        return $client;
    }

    public function findClient(string $clientId): ?OAuthClient
    {
        return $clientId === '' ? null : $this->entityManager->getRepository(OAuthClient::class)->findOneBy(['clientId' => $clientId]);
    }

    /**
     * Vérifie une demande d'autorisation. Les erreurs sur le client ou l'adresse de retour ne doivent pas
     * être renvoyées vers cette adresse (`$redirectable` faux) : elles s'affichent sur le site.
     *
     * @param array<string, mixed> $query
     * @return array{client: OAuthClient, redirectUri: string, codeChallenge: string, state: ?string}
     */
    public function validateAuthorizationRequest(array $query, string $resourceUrl): array
    {
        $client = $this->findClient((string) ($query['client_id'] ?? ''));
        if ($client === null) {
            throw new OAuthError('invalid_client', 'Connecteur inconnu : ajoutez-le de nouveau dans Claude ou ChatGPT.');
        }
        $redirectUri = (string) ($query['redirect_uri'] ?? '');
        if (!$client->hasRedirectUri($redirectUri)) {
            throw new OAuthError('invalid_request', 'Adresse de retour différente de celle enregistrée pour ce connecteur.');
        }

        // À partir d'ici, l'erreur peut être renvoyée au connecteur
        if (($query['response_type'] ?? '') !== 'code') {
            throw new RedirectableOAuthError('unsupported_response_type', 'Seul response_type=code est accepté.');
        }
        $challenge = (string) ($query['code_challenge'] ?? '');
        if (($query['code_challenge_method'] ?? '') !== 'S256' || preg_match('/^[A-Za-z0-9_-]{43}$/', $challenge) !== 1) {
            throw new RedirectableOAuthError('invalid_request', 'PKCE obligatoire : code_challenge en S256.');
        }
        $scope = trim((string) ($query['scope'] ?? ''));
        if ($scope !== '' && !\in_array(self::SCOPE, explode(' ', $scope), true)) {
            throw new RedirectableOAuthError('invalid_scope', 'Portée acceptée : '.self::SCOPE.'.');
        }
        if (isset($query['resource']) && !self::sameResource((string) $query['resource'], $resourceUrl)) {
            throw new RedirectableOAuthError('invalid_target', 'Ressource inconnue : seul '.$resourceUrl.' est servi.');
        }

        return [
            'client' => $client,
            'redirectUri' => $redirectUri,
            'codeChallenge' => $challenge,
            'state' => isset($query['state']) ? (string) $query['state'] : null,
        ];
    }

    public function issueCode(OAuthClient $client, User $user, string $redirectUri, string $codeChallenge): string
    {
        $code = self::randomToken();
        $this->entityManager->persist(new OAuthAuthorizationCode(self::hash($code), $client, $user, $redirectUri, $codeChallenge, (new \DateTimeImmutable())->add(new \DateInterval(self::CODE_TTL))));
        $this->entityManager->flush();

        return $code;
    }

    /**
     * Point d'échange de jetons (`grant_type` authorization_code ou refresh_token).
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function token(array $params, string $resourceUrl): array
    {
        if (isset($params['resource']) && !self::sameResource((string) $params['resource'], $resourceUrl)) {
            throw new OAuthError('invalid_target', 'Ressource inconnue : seul '.$resourceUrl.' est servi.');
        }

        return match ($params['grant_type'] ?? null) {
            'authorization_code' => $this->exchangeCode($params),
            'refresh_token' => $this->refresh($params),
            default => throw new OAuthError('unsupported_grant_type', 'grant_type attendu : authorization_code ou refresh_token.'),
        };
    }

    /**
     * Révocation (RFC 7009) : un jeton inconnu n'est pas une erreur.
     */
    public function revoke(string $token): void
    {
        $hash = self::hash($token);
        $repository = $this->entityManager->getRepository(OAuthGrant::class);
        $grant = $repository->findOneBy(['accessTokenHash' => $hash]) ?? $repository->findOneBy(['refreshTokenHash' => $hash]);
        if ($grant !== null) {
            $grant->revoke();
            $this->entityManager->flush();
        }
    }

    /**
     * Accès en cours pour un jeton présenté au point MCP, ou null s'il est inconnu, expiré, révoqué,
     * ou si la personne n'est plus administrateur.
     */
    public function authenticate(string $accessToken): ?OAuthGrant
    {
        $grant = $this->entityManager->getRepository(OAuthGrant::class)->findOneBy(['accessTokenHash' => self::hash($accessToken)]);
        if ($grant === null || !$grant->isAccessValid(new \DateTimeImmutable()) || !self::canAuthorize($grant->getUser())) {
            return null;
        }

        return $grant;
    }

    public static function canAuthorize(User $user): bool
    {
        return \in_array(self::REQUIRED_ROLE, $user->getRoles(), true);
    }

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function sameResource(string $given, string $expected): bool
    {
        return rtrim($given, '/') === rtrim($expected, '/');
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function exchangeCode(array $params): array
    {
        $client = $this->requireClient($params);
        $code = $this->entityManager->getRepository(OAuthAuthorizationCode::class)->findOneBy(['codeHash' => self::hash((string) ($params['code'] ?? ''))]);
        if ($code === null || $code->getClient() !== $client) {
            throw new OAuthError('invalid_grant', 'Code d\'autorisation inconnu.');
        }
        if ($code->isUsed()) {
            // Code rejoué : l'accès qu'il a ouvert est peut-être compromis
            foreach ($this->entityManager->getRepository(OAuthGrant::class)->findBy(['authorizationCode' => $code]) as $grant) {
                $grant->revoke();
            }
            $this->entityManager->flush();

            throw new OAuthError('invalid_grant', 'Code d\'autorisation déjà utilisé.');
        }
        $code->markUsed();
        $this->entityManager->flush();

        if ($code->isExpired(new \DateTimeImmutable())) {
            throw new OAuthError('invalid_grant', 'Code d\'autorisation expiré.');
        }
        if (($params['redirect_uri'] ?? null) !== $code->getRedirectUri()) {
            throw new OAuthError('invalid_grant', 'Adresse de retour différente de celle de la demande.');
        }
        $verifier = (string) ($params['code_verifier'] ?? '');
        if (preg_match('/^[A-Za-z0-9._~-]{43,128}$/', $verifier) !== 1 || !hash_equals($code->getCodeChallenge(), self::base64Url(hash('sha256', $verifier, true)))) {
            throw new OAuthError('invalid_grant', 'Vérification PKCE échouée.');
        }
        if (!self::canAuthorize($code->getUser())) {
            throw new OAuthError('invalid_grant', 'Ce compte n\'est plus administrateur.');
        }

        $grant = new OAuthGrant($client, $code->getUser(), $code);
        $this->entityManager->persist($grant);

        return $this->issueTokens($grant);
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function refresh(array $params): array
    {
        $client = $this->requireClient($params);
        $grant = $this->entityManager->getRepository(OAuthGrant::class)->findOneBy(['refreshTokenHash' => self::hash((string) ($params['refresh_token'] ?? ''))]);
        if ($grant === null || $grant->getClient() !== $client || !$grant->isRefreshValid(new \DateTimeImmutable()) || !self::canAuthorize($grant->getUser())) {
            throw new OAuthError('invalid_grant', 'Jeton de rafraîchissement invalide, expiré ou révoqué : reconnectez le connecteur.');
        }

        return $this->issueTokens($grant);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function requireClient(array $params): OAuthClient
    {
        $client = $this->findClient((string) ($params['client_id'] ?? ''));
        if ($client === null) {
            throw new OAuthError('invalid_client', 'Connecteur inconnu.', 401);
        }
        $client->markUsed();

        return $client;
    }

    /**
     * @return array<string, mixed>
     */
    private function issueTokens(OAuthGrant $grant): array
    {
        $now = new \DateTimeImmutable();
        $access = self::randomToken();
        $refresh = self::randomToken();
        $grant->rotate(self::hash($access), $now->modify('+'.self::ACCESS_TTL.' seconds'), self::hash($refresh), $now->add(new \DateInterval(self::REFRESH_TTL)));
        $this->entityManager->flush();

        return [
            'access_token' => $access,
            'token_type' => 'Bearer',
            'expires_in' => self::ACCESS_TTL,
            'refresh_token' => $refresh,
            'scope' => self::SCOPE,
        ];
    }

    private function isAllowedRedirectUri(string $uri): bool
    {
        $parts = parse_url($uri);
        if ($parts === false || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment']) || !isset($parts['host'], $parts['scheme'])) {
            return false;
        }
        $host = strtolower($parts['host']);
        if (!\in_array($host, $this->redirectHosts, true)) {
            return false;
        }

        // http seulement pour un outil de test local (MCP Inspector), si l'hôte local est explicitement autorisé
        return $parts['scheme'] === 'https' || ($parts['scheme'] === 'http' && \in_array($host, self::LOCAL_HOSTS, true));
    }

    private static function randomToken(): string
    {
        return self::base64Url(random_bytes(32));
    }

    private static function base64Url(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
