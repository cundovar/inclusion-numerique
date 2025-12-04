<?php
namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Menus;
use App\Entity\Page;
use App\Entity\PageContent;
use App\Entity\Exo;
use App\Entity\ExoMenu;
use App\Entity\ExoContent;
use App\Entity\QCM;
use App\Entity\LanguageQCM;
use App\Entity\NiveauQCM;
use App\Entity\ChoicesQCM;
use App\Entity\User;
use App\Entity\UserPageVisit;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\DocDeCode;
use App\Entity\Logo;
use App\Entity\PositionMenus;
use App\Entity\Seo;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        // Récupérer les statistiques
        $stats = [
            'users' => $this->entityManager->getRepository(User::class)->count([]),
            'pages' => $this->entityManager->getRepository(PageContent::class)->count([]),
            'categories' => $this->entityManager->getRepository(Category::class)->count([]),
            'qcm' => $this->entityManager->getRepository(QCM::class)->count([]),
            'exercices' => $this->entityManager->getRepository(ExoContent::class)->count([]),
            'visits' => $this->entityManager->getRepository(UserPageVisit::class)->count([]),
        ];

        // Récupérer les dernières visites
        $recentVisits = $this->entityManager->getRepository(UserPageVisit::class)
            ->findBy([], ['visitedAt' => 'DESC'], 10);

        // Récupérer les derniers utilisateurs
        $recentUsers = $this->entityManager->getRepository(User::class)
            ->findBy([], ['id' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'recentVisits' => $recentVisits,
            'recentUsers' => $recentUsers,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Admin');
    }



    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
    
        yield MenuItem::section('📚 Cours');
        yield MenuItem::linkToCrud('PageContents', 'fa fa-bars', PageContent::class);

        yield MenuItem::section('📝 Exercices');
        yield MenuItem::linkToCrud('ExoContents', 'fa fa-book', ExoContent::class);
        yield MenuItem::linkToCrud('Exos', 'fa fa-list', Exo::class);
        yield MenuItem::linkToCrud('ExoMenus', 'fa fa-bars', ExoMenu::class);

        yield MenuItem::section('🧠 QCM');
        yield MenuItem::linkToCrud('QCM', 'fa fa-question-circle', QCM::class);
        yield MenuItem::linkToCrud('Langages', 'fa fa-code', LanguageQCM::class);
        yield MenuItem::linkToCrud('Niveaux', 'fa fa-layer-group', NiveauQCM::class);
        yield MenuItem::linkToCrud('Choix', 'fa fa-check-square', ChoicesQCM::class);

        yield MenuItem::section('📋 Navigation');
        yield MenuItem::linkToCrud('Categories', 'fa fa-bars', Category::class);
        yield MenuItem::linkToCrud('Pages', 'fa fa-bars', Page::class);
        yield MenuItem::linkToCrud('Menus', 'fa fa-bars', Menus::class);
        yield MenuItem::linkToCrud('Positions', 'fa fa-bars', PositionMenus::class);

        yield MenuItem::section('👥 Utilisateurs');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);
        yield MenuItem::linkToCrud('Visites', 'fa fa-chart-line', UserPageVisit::class);
        yield MenuItem::linkToRoute('Statistiques des visites', 'fa fa-chart-bar', 'admin_stats_page_visits');

        yield MenuItem::section('🔗 ajout des doc dans menu');
        yield MenuItem::linkToCrud('Doc de code', 'fa fa-code', DocDeCode::class);

        yield MenuItem::section('🔗 Logos');
        yield MenuItem::linkToCrud('Logos', 'fa fa-bars', Logo::class);

        yield MenuItem::section('🔍 SEO');
        yield MenuItem::linkToCrud('SEO', 'fa fa-search', Seo::class);
    }
}
