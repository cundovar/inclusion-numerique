<?php

namespace App\Controller;

use App\Entity\UserPageVisit;
use App\Repository\UserPageVisitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/page-visits')]
class PageVisitController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPageVisitRepository $visitRepository
    ) {}

    /**
     * Enregistrer une visite de page
     */
    #[Route('/track', name: 'page_visit_track', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function trackVisit(Request $request): JsonResponse
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur a activé le tracking
        if (!$user->isTrackPageVisits()) {
            return $this->json(['message' => 'Tracking désactivé'], 200);
        }

        $data = json_decode($request->getContent(), true);
        $pageUrl = $data['pageUrl'] ?? null;
        $pageTitle = $data['pageTitle'] ?? null;

        if (!$pageUrl) {
            return $this->json(['error' => 'Page URL requis'], 400);
        }

        // Créer une nouvelle visite
        $visit = new UserPageVisit();
        $visit->setUser($user);
        $visit->setPageUrl($pageUrl);
        $visit->setPageTitle($pageTitle);
        $visit->setUserAgent($request->headers->get('User-Agent'));
        $visit->setIpAddress($request->getClientIp());

        $this->em->persist($visit);
        $this->em->flush();

        return $this->json(['message' => 'Visite enregistrée'], 200);
    }

    /**
     * Récupérer l'historique des visites de l'utilisateur connecté
     */
    #[Route('/my-history', name: 'page_visit_my_history', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getMyHistory(): JsonResponse
    {
        $user = $this->getUser();

        // Récupérer toutes les visites individuelles
        $visits = $this->visitRepository->findBy(
            ['user' => $user],
            ['visitedAt' => 'DESC']
        );

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

        return $this->json($data);
    }

    /**
     * Supprimer une visite spécifique
     */
    #[Route('/{id}', name: 'page_visit_delete', methods: ['DELETE'], requirements: ['id' => '\d+'] )]
    #[IsGranted('ROLE_USER')]
    public function deleteVisit(int $id): JsonResponse
    { 
        $user = $this->getUser();

        $visit = $this->visitRepository->find($id);

        if (!$visit) {
            return $this->json(['error' => 'Visite non trouvée'], 404);
        }

        // Vérifier que la visite appartient bien à l'utilisateur connecté
        if ($visit->getUser() !== $user) {
            return $this->json(['error' => 'Non autorisé'], 403);
        }

        $this->em->remove($visit);
        $this->em->flush();

        return $this->json(['message' => 'Visite supprimée'], 200);
    }

    /**
     * Supprimer tout l'historique de l'utilisateur
     */
    #[Route('/clear-all', name: 'page_visit_clear_all', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function clearAllHistory(): JsonResponse
    {
        $user = $this->getUser();

        // Utiliser DQL pour supprimer directement en base de données
        $query = $this->em->createQuery(
            'DELETE FROM App\Entity\UserPageVisit v WHERE v.user = :user'
        );
        $query->setParameter('user', $user);
        $deletedCount = $query->execute();

        return $this->json([
            'message' => 'Historique effacé',
            'deleted' => $deletedCount
        ], 200);
    }

    /**
     * Activer/désactiver le tracking
     */
    #[Route('/toggle-tracking', name: 'page_visit_toggle_tracking', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggleTracking(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $enabled = $data['enabled'] ?? null;

        if ($enabled === null) {
            return $this->json(['error' => 'Paramètre "enabled" requis'], 400);
        }

        $user->setTrackPageVisits((bool)$enabled);
        $this->em->flush();

        return $this->json([
            'message' => 'Préférences mises à jour',
            'trackingEnabled' => $user->isTrackPageVisits()
        ]);
    }

    /**
     * Récupérer le statut du tracking
     */
    #[Route('/tracking-status', name: 'page_visit_tracking_status', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getTrackingStatus(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'trackingEnabled' => $user->isTrackPageVisits()
        ]);
    }
}
