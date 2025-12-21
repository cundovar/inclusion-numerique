<?php

namespace App\Controller\Admin;

use App\Repository\UserPageVisitRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageVisitStatsController extends AbstractDashboardController
{
    public function __construct(
        private UserPageVisitRepository $visitRepository
    ) {}

    #[Route('/admin/stats/page-visits', name: 'admin_stats_page_visits')]
    public function pageVisitStats(): Response
    {
        // Récupérer toutes les statistiques
        $mostVisitedPages = $this->visitRepository->getGlobalMostVisitedPages(20);
        $mostActiveUsers = $this->visitRepository->getMostActiveUsers(10);
        $visitsPerDay = $this->visitRepository->getVisitsPerDay(7);
        $pagesWithMostTime = $this->visitRepository->getPagesWithMostTime(20);

        // Statistiques générales
        $totalVisits = $this->visitRepository->count([]);
        $totalUsers = count($this->visitRepository->createQueryBuilder('v')
            ->select('DISTINCT u.id')
            ->join('v.user', 'u')
            ->getQuery()
            ->getResult());

        return $this->render('admin/page_visit_stats.html.twig', [
            'mostVisitedPages' => $mostVisitedPages,
            'mostActiveUsers' => $mostActiveUsers,
            'visitsPerDay' => $visitsPerDay,
            'pagesWithMostTime' => $pagesWithMostTime,
            'totalVisits' => $totalVisits,
            'totalUsers' => $totalUsers,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Statistiques des visites');
    }
}
