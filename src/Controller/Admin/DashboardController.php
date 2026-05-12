<?php
namespace App\Controller\Admin;

use App\Domain\Category\Repository\CategoryRepositoryInterface;
use App\Domain\ExoContent\Repository\ExoContentRepositoryInterface;
use App\Domain\PageContent\Repository\PageContentRepositoryInterface;
use App\Domain\QCM\Repository\QCMRepositoryInterface;
use App\Domain\SiteConfiguration\Repository\SiteConfigurationRepositoryInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\UserPageVisit\Repository\UserPageVisitRepositoryInterface;
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
use App\Entity\SuperMenu;
use App\Entity\UserCustomization;
use App\Entity\SiteConfiguration;
use App\Controller\Admin\SiteConfigurationCrudController;
use App\Entity\NiveauCours;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Route('/admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private UserRepositoryInterface $userRepository,
        private PageContentRepositoryInterface $pageContentRepository,
        private QCMRepositoryInterface $qcmRepository,
        private ExoContentRepositoryInterface $exoContentRepository,
        private UserPageVisitRepositoryInterface $userPageVisitRepository,
        private SiteConfigurationRepositoryInterface $siteConfigRepository,
        private AdminUrlGenerator $adminUrlGenerator,
        #[Autowire(param: 'app.user_page_visit.enabled')] private bool $trackingEnabled,
    ) {}

    #[Route('/site-config', name: 'admin_site_config')]
    public function siteConfig(): Response
    {
        // Récupère ou crée la config par défaut
        $config = $this->siteConfigRepository->getDefault();

        // Redirige vers l'édition
        $url = $this->adminUrlGenerator
            ->setController(SiteConfigurationCrudController::class)
            ->setAction('edit')
            ->setEntityId($config->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        // Récupérer les statistiques
        $stats = [
            'users' => $this->userRepository->countAll(),
            'pages' => $this->pageContentRepository->countAll(),
            'categories' => count($this->categoryRepository->findAll()),
            'qcm' => $this->qcmRepository->countAll(),
            'exercices' => $this->exoContentRepository->countAll(),
            'visits' => $this->trackingEnabled
                ? $this->userPageVisitRepository->countAll()
                : 0,
        ];

        // Récupérer les dernières visites
        $recentVisits = $this->trackingEnabled
            ? $this->userPageVisitRepository->findLatest(10)
            : [];

        // Récupérer les derniers utilisateurs
        $recentUsers = $this->userRepository->findLatest(5);

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'recentVisits' => $recentVisits,
            'recentUsers' => $recentUsers,
            'trackingEnabled' => $this->trackingEnabled,
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
        yield MenuItem::linkToCrud('SuperMenus', 'fa fa-bars', SuperMenu::class);

        yield MenuItem::section('📚 Niveau des cours');
        yield MenuItem::linkToCrud('NiveauCours', 'fa fa-bars', NiveauCours::class);

        yield MenuItem::section('👥 Utilisateurs');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);
        yield MenuItem::linkToCrud('Personnalisations', 'fa fa-palette', UserCustomization::class);
        if ($this->trackingEnabled) {
            yield MenuItem::linkToCrud('Visites', 'fa fa-chart-line', UserPageVisit::class);
            yield MenuItem::linkToRoute('Statistiques des visites', 'fa fa-chart-bar', 'admin_stats_page_visits');
        }

        yield MenuItem::section('🔗 ajout des doc dans menu');
        yield MenuItem::linkToCrud('Doc de code', 'fa fa-code', DocDeCode::class);

        yield MenuItem::section('🔗 Logos');
        yield MenuItem::linkToCrud('Logos', 'fa fa-bars', Logo::class);

        yield MenuItem::section('🔍 SEO');
        yield MenuItem::linkToCrud('SEO', 'fa fa-search', Seo::class);

        yield MenuItem::section('⚙️ Configuration');
        yield MenuItem::linkToRoute('Config globale', 'fa fa-cog', 'admin_site_config');
    }
}
