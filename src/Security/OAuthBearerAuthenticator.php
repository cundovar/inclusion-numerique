<?php

declare(strict_types=1);

namespace App\Security;

use App\Service\OAuth\OAuthServer;
use App\Service\OAuth\PublicUrl;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Authentifie un connecteur sur /mcp par « Authorization: Bearer <jeton OAuth> ».
 * Sans jeton valide, le 401 annonce les métadonnées OAuth (RFC 9728) pour que le connecteur lance la connexion.
 */
final class OAuthBearerAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(private OAuthServer $server, private PublicUrl $url) {}

    public function supports(Request $request): bool
    {
        // Sans jeton, access_control refuse et start() répond 401 sans code d'erreur (RFC 6750, 3.1)
        return str_starts_with((string) $request->headers->get('Authorization', ''), 'Bearer ');
    }

    public function authenticate(Request $request): Passport
    {
        $header = (string) $request->headers->get('Authorization', '');
        if (!preg_match('/^Bearer\s+([A-Za-z0-9_-]{43})$/', $header, $matches)) {
            throw new CustomUserMessageAuthenticationException('Jeton mal formé.');
        }

        $learner = str_starts_with($request->getPathInfo(), '/mcp/apprenant');
        $resource = $this->url->resource($request, $learner ? '/mcp/apprenant' : '/mcp');
        $grant = $this->server->authenticate($matches[1], $resource);
        if ($grant === null) {
            throw new CustomUserMessageAuthenticationException('Jeton invalide, expiré ou révoqué.');
        }

        $user = $learner ? new LearnerConnectorUser($grant) : new ConnectorUser($grant);

        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier(), static fn () => $user));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return $this->unauthorized($request, strtr($exception->getMessageKey(), $exception->getMessageData()), 'invalid_token');
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return $this->unauthorized($request, 'Connexion requise.', null);
    }

    private function unauthorized(Request $request, string $message, ?string $error): JsonResponse
    {
        $learner = str_starts_with($request->getPathInfo(), '/mcp/apprenant');
        $suffix = $learner ? '/mcp/apprenant' : '';
        $scope = $learner ? OAuthServer::SCOPE_LEARNER_READ : OAuthServer::SCOPE_REDACTION;
        $challenge = sprintf('Bearer resource_metadata="%s/.well-known/oauth-protected-resource%s", scope="%s"', $this->url->base($request), $suffix, $scope);
        if ($error !== null) {
            $challenge .= sprintf(', error="%s"', $error);
        }

        return new JsonResponse(['erreur' => $message], Response::HTTP_UNAUTHORIZED, ['WWW-Authenticate' => $challenge]);
    }
}
