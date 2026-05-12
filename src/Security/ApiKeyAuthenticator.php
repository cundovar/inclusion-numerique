<?php

namespace App\Security;

use App\Domain\User\Repository\UserRepositoryInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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

/**
 * @see https://symfony.com/doc/current/security/custom_authenticator.html
 */
class ApiKeyAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        // Seulement pour les routes d'API admin
        return str_starts_with($request->getPathInfo(), '/api/admin/') && 
               $request->headers->has('X-API-KEY');
    }

    public function authenticate(Request $request): Passport
    {
        $apiKey = $request->headers->get('X-API-KEY');
        if (null === $apiKey) {
            throw new CustomUserMessageAuthenticationException('API key manquante');
        }

        // Vérifier la clé API contre les clés configurées
        $validApiKeys = $this->getValidApiKeys();
        
        if (!isset($validApiKeys[$apiKey])) {
            throw new CustomUserMessageAuthenticationException('API key invalide');
        }

        // Récupérer l'utilisateur associé à cette clé
        $userName = $validApiKeys[$apiKey]['user'];
        $user = $this->userRepository->findByUsername($userName);
        
        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Utilisateur associé à l\'API key non trouvé');
        }

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier(), function ($userIdentifier) {
                return $this->userRepository->findByUsername($userIdentifier);
            })
        );
    }

    private function getValidApiKeys(): array
    {
        $apiKeys = [];
    
        // 1) Si le paramètre container "api_keys" existe, on le lit
        if ($this->parameterBag->has('api_keys')) {
            $raw = $this->parameterBag->get('api_keys');
    
            if (is_array($raw)) {
                $apiKeys = $raw;
            } elseif (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $apiKeys = $decoded;
                }
            }
        }
    
        // 2) Fallback: variables d'env simples si rien n'est défini
        if (empty($apiKeys)) {
            $n8nApiKey  = $_ENV['N8N_API_KEY'] ?? $_SERVER['N8N_API_KEY'] ?? null;
            $adminEmail = $_ENV['API_ADMIN_USER_EMAIL'] ?? $_SERVER['API_ADMIN_USER_EMAIL'] ?? 'cundo2';
    
            if ($n8nApiKey) {
                $apiKeys = [
                    $n8nApiKey => [
                        'user'   => $adminEmail,
                        'name'   => 'n8n',
                        'scopes' => ['admin'],
                    ],
                ];
            }
        }
    
        return $apiKeys;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    // public function start(Request $request, ?AuthenticationException $authException = null): Response
    // {
    //     /*
    //      * If you would like this class to control what happens when an anonymous user accesses a
    //      * protected page (e.g. redirect to /login), uncomment this method and make this class
    //      * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
    //      *
    //      * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
    //      */
    // }
}
