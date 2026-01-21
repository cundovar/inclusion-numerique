<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapController extends AbstractController
{
    public function __construct(
        private PageRepository $pageRepository,
        private CategoryRepository $categoryRepository,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    #[Route('/sitemap.xml', name: 'sitemap', defaults: ['_format' => 'xml'])]
    public function index(): Response
    {
        $urls = [];
        $hostname = $this->getParameter('app.url') ?? 'https://yoursite.com';
        $spaPath = $this->getParameter('app.spa_path') ?? '/spa';

        // Page d'accueil
        $urls[] = [
            'loc' => $hostname . $spaPath,
            'changefreq' => 'daily',
            'priority' => '1.0',
            'lastmod' => (new \DateTime())->format('Y-m-d')
        ];

        // Pages dynamiques (pages/:slug)
        $pages = $this->pageRepository->findAll();
        foreach ($pages as $page) {
            $slug = ltrim($page->getSlug(), '/');
            $urls[] = [
                'loc' => $hostname . $spaPath . '/pages/' . $slug,
                'changefreq' => 'weekly',
                'priority' => '0.8',
                'lastmod' => (new \DateTime())->format('Y-m-d')
            ];
        }

        // Catégories
        $categories = $this->categoryRepository->findAll();
        foreach ($categories as $category) {
            $urls[] = [
                'loc' => $hostname . $spaPath . '/category/' . $category->getName(),
                'changefreq' => 'weekly',
                'priority' => '0.9',
                'lastmod' => (new \DateTime())->format('Y-m-d')
            ];
        }

        $response = new Response(
            $this->renderView('sitemap/index.xml.twig', ['urls' => $urls]),
            200
        );
        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }
}
