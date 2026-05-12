<?php

namespace App\Controller\Api;

use App\Domain\UserPageVisit\Repository\UserPageVisitRepositoryInterface;
use App\Entity\UserPageVisit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/page-visits')]
class UserPageVisitController extends AbstractController
{
    public function __construct(
        private UserPageVisitRepositoryInterface $visitRepository,
        #[Autowire(param: 'app.user_page_visit.enabled')] private bool $trackingEnabled
    ) {}

    #[Route('', name: 'api_page_visit_track', methods: ['POST'])]
    public function track(Request $request): JsonResponse
    {
        if (!$this->trackingEnabled) {
            return new JsonResponse([
                'success' => false,
                'trackingEnabled' => false,
                'message' => 'Le suivi des visites est désactivé par configuration'
            ], Response::HTTP_OK);
        }

        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $pageUrl = $data['pageUrl'] ?? '';

        // Ignorer les pages d'administration EasyAdmin
        if (str_contains($pageUrl, '/admin') || str_contains($pageUrl, '/api/')) {
            return new JsonResponse(['success' => true, 'ignored' => true], Response::HTTP_OK);
        }

        $visit = new UserPageVisit();
        $visit->setUser($user);
        $visit->setPageUrl($pageUrl);
        $visit->setPageTitle($data['pageTitle'] ?? null);
        $visit->setTimeSpent($data['timeSpent'] ?? null);
        $visit->setUserAgent($request->headers->get('User-Agent'));
        $visit->setIpAddress($request->getClientIp());

        $this->visitRepository->save($visit);

        return new JsonResponse(['success' => true], Response::HTTP_CREATED);
    }

    #[Route('/my-history', name: 'api_page_visit_history', methods: ['GET'])]
    public function getHistory(Request $request): JsonResponse
    {
        if (!$this->trackingEnabled) {
            return new JsonResponse([], Response::HTTP_OK);
        }

        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $limit = $request->query->getInt('limit', 1000); // Par défaut 1000 visites
        $visits = $this->visitRepository->findRecentVisitsByUser($user, $limit);

        $data = array_map(function($visit) {
            return [
                'id' => $visit->getId(),
                'page' => [
                    'slug' => $visit->getPageUrl(),
                    'title' => $visit->getPageTitle() ?? 'Sans titre'
                ],
                'visitedAt' => $visit->getVisitedAt()->format('Y-m-d H:i:s'),
                'timeSpent' => $visit->getTimeSpent()
            ];
        }, $visits);

        return new JsonResponse($data);
    }

    #[Route('/my-stats', name: 'api_page_visit_stats', methods: ['GET'])]
    public function getStats(): JsonResponse
    {
        if (!$this->trackingEnabled) {
            return new JsonResponse([
                'mostVisitedPages' => [],
                'totalVisits' => 0,
                'trackingEnabled' => false,
            ]);
        }

        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $mostVisited = $this->visitRepository->getMostVisitedPagesByUser($user);
        $totalVisits = $this->visitRepository->countByUser($user);

        return new JsonResponse([
            'mostVisitedPages' => $mostVisited,
            'totalVisits' => $totalVisits
        ]);
    }
}
