<?php

declare(strict_types=1);

namespace App\Controller;

use App\Mcp\RedactionMcpServer;
use App\Security\ConnectorUser;
use Mcp\Server\Transport\Http\Middleware\CorsMiddleware;
use Mcp\Server\Transport\StreamableHttpTransport;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Point MCP du connecteur (Streamable HTTP), protégé par un jeton OAuth (pare-feu `mcp`).
 * Chaque demande construit le serveur pour le connecteur qui appelle : il devient l'auteur ou le relecteur.
 */
final class McpController extends AbstractController
{
    /**
     * @param list<string> $redirectHosts
     */
    public function __construct(
        private RedactionMcpServer $server,
        #[Autowire('%app.oauth.redirect_hosts%')] private array $redirectHosts,
    ) {}

    #[Route('/mcp', name: 'mcp', methods: ['GET', 'POST', 'DELETE', 'OPTIONS'])]
    #[IsGranted(ConnectorUser::ROLE)]
    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();
        \assert($user instanceof ConnectorUser);

        $psr17 = new Psr17Factory();
        $psrRequest = (new PsrHttpFactory($psr17, $psr17, $psr17, $psr17))->createRequest($request);

        // Pas de protection DNS-rebinding du SDK (prévue pour un serveur local) : ce point est public et
        // chaque demande porte un jeton. CORS limité aux sites des connecteurs, pour un appel depuis le navigateur.
        $transport = new StreamableHttpTransport($psrRequest, $psr17, $psr17, middleware: [
            new CorsMiddleware(array_map(static fn (string $host) => 'https://'.$host, $this->redirectHosts)),
        ]);

        $response = (new HttpFoundationFactory())->createResponse($this->server->build($user->getAgentLabel())->run($transport));
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
