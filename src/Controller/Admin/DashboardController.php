<?php
namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Menus;
use App\Entity\Page;
use App\Entity\PageContent;
use App\Entity\CourseAnalysis;
use App\Entity\ContentRecommendation;
use App\Entity\LearningPath;
use App\Entity\UserLearningAnalytics;
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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\Admin\PageContentCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;

#[Route('/admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
       
    ) {}

    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(PageContentCrudController::class)
            ->generateUrl();

        return new RedirectResponse($url);
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

        yield MenuItem::section('👥 Utilisateurs');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);
        yield MenuItem::linkToCrud('Visites', 'fa fa-chart-line', UserPageVisit::class);
        yield MenuItem::linkToRoute('Statistiques des visites', 'fa fa-chart-bar', 'admin_stats_page_visits');
    }
}
