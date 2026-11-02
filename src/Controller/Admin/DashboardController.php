<?php
namespace App\Controller\Admin;

use App\Domain\Category\Repository\CategoryRepositoryInterface;
use App\Domain\PageContent\Repository\PageContentRepositoryInterface;
use App\Domain\CourseMedia\Repository\CourseMediaRepositoryInterface;
use App\Domain\SiteConfiguration\Repository\SiteConfigurationRepositoryInterface;
use App\Entity\Category;
use App\Entity\Menus;
use App\Entity\Page;
use App\Entity\PageContent;
use App\Entity\CourseMedia;
use App\Entity\QCM;
use App\Entity\LanguageQCM;
use App\Entity\NiveauQCM;
use App\Entity\ChoicesQCM;
use App\Entity\User;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Logo;
use App\Entity\PositionMenus;
use App\Entity\Seo;
use App\Entity\SuperMenu;
use App\Entity\SiteConfiguration;
use App\Controller\Admin\SiteConfigurationCrudController;
use App\Entity\NiveauCours;
use App\Service\LegalInformation;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

// AdminDashboard fixe le préfixe des routes CRUD générées par EasyAdmin : sans lui, elles
// sont dérivées de la route de index() et exposées hors de /admin (donc hors access_control).
#[AdminDashboard(routePath: '/admin', routeName: 'dashboard')]
#[Route('/admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private PageContentRepositoryInterface $pageContentRepository,
        private CourseMediaRepositoryInterface $courseMediaRepository,
        private SiteConfigurationRepositoryInterface $siteConfigRepository,
        private AdminUrlGenerator $adminUrlGenerator,
        private LegalInformation $legalInformation,
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

    public function index(): Response
    {
        $today = new \DateTimeImmutable('today');

        $stats = [
            'published' => count($this->pageContentRepository->findByStatuses([PageContent::STATUS_PUBLISHED])),
            'pages' => $this->pageContentRepository->countAll(),
            'categories' => count($this->categoryRepository->findAll()),
            'captures' => $this->courseMediaRepository->countAll(),
        ];

        // Travail éditorial en attente : cours à relire ou à mettre à jour, captures à comparer au site officiel
        $coursesToReview = $this->pageContentRepository->findByStatuses([PageContent::STATUS_IN_REVIEW, PageContent::STATUS_TO_REVISE]);
        $capturesDue = $this->courseMediaRepository->findDueForReview($today);

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            // Mentions légales et contact RGPD : ce qui manque encore sur le site public
            'legalMissing' => $this->legalInformation->missing(),
            'coursesToReview' => array_map(fn (PageContent $course) => [
                'course' => $course,
                'url' => $this->adminUrlGenerator->unsetAll()->setController(PageContentCrudController::class)->setAction('edit')->setEntityId($course->getId())->generateUrl(),
            ], $coursesToReview),
            'capturesDue' => array_map(fn (CourseMedia $capture) => [
                'capture' => $capture,
                'url' => $this->adminUrlGenerator->unsetAll()->setController(CourseMediaCrudController::class)->setAction('edit')->setEntityId($capture->getId())->generateUrl(),
            ], $capturesDue),
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
        yield MenuItem::linkToCrud('Cours', 'fa fa-file-lines', PageContent::class);
        yield MenuItem::linkToCrud('Captures', 'fa fa-image', CourseMedia::class);

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

        yield MenuItem::section('🔗 Logos');
        yield MenuItem::linkToCrud('Logos', 'fa fa-bars', Logo::class);

        yield MenuItem::section('🔍 SEO');
        yield MenuItem::linkToCrud('SEO', 'fa fa-search', Seo::class);

        yield MenuItem::section('⚙️ Configuration');
        yield MenuItem::linkToRoute('Config globale', 'fa fa-cog', 'admin_site_config');
    }
}
