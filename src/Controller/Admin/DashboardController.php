<?php
namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Menus;
use App\Entity\Page;
use App\Entity\PageContent;
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

    
        yield MenuItem::section('📋 Navigation');

        yield MenuItem::linkToCrud('Categories', 'fa fa-bars', Category::class);
        yield MenuItem::linkToCrud('Pages', 'fa fa-bars', Page::class);
        yield MenuItem::linkToCrud('Menus', 'fa fa-bars', Menus::class);



    }
}
