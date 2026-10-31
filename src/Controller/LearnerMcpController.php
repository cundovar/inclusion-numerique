<?php

declare(strict_types=1);

namespace App\Controller;

use App\Mcp\LearnerMcpServer;
use App\Security\LearnerConnectorUser;
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

/** Point MCP apprenant, isolé du connecteur de rédaction. */
final class LearnerMcpController extends AbstractController
{
    /** @param list<string> $redirectHosts */
    public function __construct(private LearnerMcpServer $server, #[Autowire('%app.oauth.redirect_hosts%')] private array $redirectHosts) {}

    #[Route('/mcp/apprenant', name: 'mcp_learner', methods: ['GET', 'POST', 'DELETE', 'OPTIONS'])]
    #[IsGranted(LearnerConnectorUser::ROLE)]
    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();
        \assert($user instanceof LearnerConnectorUser);
        $psr17 = new Psr17Factory();
        $psrRequest = (new PsrHttpFactory($psr17, $psr17, $psr17, $psr17))->createRequest($request);
        $transport = new StreamableHttpTransport($psrRequest, $psr17, $psr17, middleware: [new CorsMiddleware(array_map(static fn (string $host) => 'https://'.$host, $this->redirectHosts))]);
        $response = (new HttpFoundationFactory())->createResponse($this->server->build($user->getLearner())->run($transport));
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
