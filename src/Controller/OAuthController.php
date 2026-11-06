<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use App\Entity\User;
use App\Service\OAuth\OAuthError;
use App\Service\OAuth\OAuthServer;
use App\Service\OAuth\PublicUrl;
use App\Service\OAuth\RedirectableOAuthError;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Serveur d'autorisation OAuth du connecteur MCP (claude.ai, ChatGPT) : métadonnées, enregistrement
 * dynamique, consentement d'un administrateur, jetons et révocation. Voir docs/fonctionnalites/connecteur.md.
 */
final class OAuthController extends AbstractController
{
    // Paramètres de la demande repris tels quels dans le formulaire de consentement
    private const AUTHORIZE_PARAMS = ['response_type', 'client_id', 'redirect_uri', 'code_challenge', 'code_challenge_method', 'state', 'scope', 'resource'];

    public function __construct(
        private OAuthServer $server,
        private PublicUrl $url,
        #[Autowire('%app.site_name%')] private string $siteName = 'Inclusion numérique',
    ) {}

    // Métadonnées de la ressource protégée (RFC 9728), annoncées par le 401 du point MCP
    #[Route('/.well-known/oauth-protected-resource', name: 'oauth_protected_resource', methods: ['GET'])]
    #[Route('/.well-known/oauth-protected-resource/mcp', name: 'oauth_protected_resource_mcp', methods: ['GET'])]
    public function protectedResource(Request $request): JsonResponse
    {
        return $this->metadata([
            'resource' => $this->url->resource($request),
            'authorization_servers' => [$this->url->base($request)],
            'scopes_supported' => [OAuthServer::SCOPE],
            'bearer_methods_supported' => ['header'],
            'resource_name' => $this->siteName.' — assistant de rédaction',
        ]);
    }

    // Métadonnées du serveur d'autorisation (RFC 8414)
    #[Route('/.well-known/oauth-authorization-server', name: 'oauth_authorization_server', methods: ['GET'])]
    #[Route('/.well-known/oauth-authorization-server/mcp', name: 'oauth_authorization_server_mcp', methods: ['GET'])]
    public function authorizationServer(Request $request): JsonResponse
    {
        $base = $this->url->base($request);

        return $this->metadata([
            'issuer' => $base,
            'authorization_endpoint' => $base.'/oauth/authorize',
            'token_endpoint' => $base.'/oauth/token',
            'registration_endpoint' => $base.'/oauth/register',
            'revocation_endpoint' => $base.'/oauth/revoke',
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'code_challenge_methods_supported' => ['S256'],
            'token_endpoint_auth_methods_supported' => ['none'],
            'revocation_endpoint_auth_methods_supported' => ['none'],
            'scopes_supported' => [OAuthServer::SCOPE],
            'authorization_response_iss_parameter_supported' => true,
        ]);
    }

    #[Route('/oauth/register', name: 'oauth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $metadata = json_decode($request->getContent(), true, 8, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->oauthError(new OAuthError('invalid_client_metadata', 'Corps JSON attendu.'));
        }

        try {
            $client = $this->server->register(\is_array($metadata) ? $metadata : []);
        } catch (OAuthError $error) {
            return $this->oauthError($error);
        }

        return $this->noStore(new JsonResponse([
            'client_id' => $client->getClientId(),
            'client_id_issued_at' => $client->getCreatedAt()->getTimestamp(),
            'client_name' => $client->getName(),
            'redirect_uris' => $client->getRedirectUris(),
            'grant_types' => ['authorization_code', 'refresh_token'],
            'response_types' => ['code'],
            'token_endpoint_auth_method' => 'none',
            'scope' => OAuthServer::SCOPE,
        ], Response::HTTP_CREATED));
    }

    // Réservé aux administrateurs connectés (access_control) : la connexion renvoie ici
    #[Route('/oauth/authorize', name: 'oauth_authorize', methods: ['GET'])]
    public function authorize(Request $request): Response
    {
        $params = $this->authorizeParams($request->query->all());
        try {
            $authorization = $this->server->validateAuthorizationRequest($params, $this->url->resource($request));
        } catch (OAuthError $error) {
            return $this->consentPage(['error' => $error->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (RedirectableOAuthError $error) {
            return $this->redirectWithError($request, (string) $params['redirect_uri'], $error->error, $error->getMessage(), $params['state'] ?? null);
        }

        return $this->consentPage([
            'client' => $authorization['client'],
            'params' => $params,
            'redirect_host' => parse_url($authorization['redirectUri'], \PHP_URL_HOST),
        ]);
    }

    /**
     * Page de consentement, jamais affichée dans un cadre d'un autre site (détournement de clic).
     *
     * @param array<string, mixed> $parameters
     */
    private function consentPage(array $parameters, int $status = Response::HTTP_OK): Response
    {
        $response = $this->render('oauth/authorize.html.twig', $parameters, new Response(status: $status));
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'none'");

        return $response;
    }

    #[Route('/oauth/authorize', name: 'oauth_authorize_decision', methods: ['POST'])]
    public function decide(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('oauth_authorize', (string) $request->request->get('_csrf_token'))) {
            return $this->consentPage(['error' => 'Formulaire expiré : recommencez depuis Claude ou ChatGPT.'], Response::HTTP_BAD_REQUEST);
        }

        $params = $this->authorizeParams($request->request->all());
        try {
            $authorization = $this->server->validateAuthorizationRequest($params, $this->url->resource($request));
        } catch (OAuthError $error) {
            return $this->consentPage(['error' => $error->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (RedirectableOAuthError $error) {
            return $this->redirectWithError($request, (string) $params['redirect_uri'], $error->error, $error->getMessage(), $params['state'] ?? null);
        }

        if ($request->request->get('decision') !== 'approve') {
            return $this->redirectWithError($request, $authorization['redirectUri'], 'access_denied', 'Accès refusé par l\'administrateur.', $authorization['state']);
        }

        $user = $this->getUser();
        if (!$user instanceof User || !OAuthServer::canAuthorize($user)) {
            throw $this->createAccessDeniedException();
        }

        $code = $this->server->issueCode($authorization['client'], $user, $authorization['redirectUri'], $authorization['codeChallenge']);

        return $this->redirectTo($authorization['redirectUri'], array_filter([
            'code' => $code,
            'state' => $authorization['state'],
            'iss' => $this->url->base($request),
        ], static fn ($value) => $value !== null));
    }

    #[Route('/oauth/token', name: 'oauth_token', methods: ['POST'])]
    public function token(Request $request): JsonResponse
    {
        try {
            return $this->noStore(new JsonResponse($this->server->token($request->request->all(), $this->url->resource($request))));
        } catch (OAuthError $error) {
            return $this->oauthError($error);
        }
    }

    #[Route('/oauth/revoke', name: 'oauth_revoke', methods: ['POST'])]
    public function revoke(Request $request): Response
    {
        $this->server->revoke((string) $request->request->get('token', ''));

        return $this->noStore(new Response(status: Response::HTTP_OK));
    }

    /**
     * @param array<string, mixed> $source
     * @return array<string, string>
     */
    private function authorizeParams(array $source): array
    {
        $params = [];
        foreach (self::AUTHORIZE_PARAMS as $name) {
            if (isset($source[$name]) && \is_string($source[$name])) {
                $params[$name] = $source[$name];
            }
        }

        return $params;
    }

    private function redirectWithError(Request $request, string $redirectUri, string $error, string $description, ?string $state): RedirectResponse
    {
        return $this->redirectTo($redirectUri, array_filter([
            'error' => $error,
            'error_description' => $description,
            'state' => $state,
            'iss' => $this->url->base($request),
        ], static fn ($value) => $value !== null));
    }

    /**
     * @param array<string, string> $query
     */
    private function redirectTo(string $redirectUri, array $query): RedirectResponse
    {
        $separator = str_contains($redirectUri, '?') ? '&' : '?';

        return new RedirectResponse($redirectUri.$separator.http_build_query($query));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function metadata(array $data): JsonResponse
    {
        $response = new JsonResponse($data);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->setPublic();
        $response->setMaxAge(3600);

        return $response;
    }

    private function oauthError(OAuthError $error): JsonResponse
    {
        return $this->noStore(new JsonResponse($error->toArray(), $error->status));
    }

    /**
     * @template T of Response
     * @param T $response
     * @return T
     */
    private function noStore(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'no-store');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
