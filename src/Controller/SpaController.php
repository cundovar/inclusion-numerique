<?php

namespace App\Controller;

use App\Service\SeoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SpaController extends AbstractController
{
    public function __construct(
        private SeoService $seoService
    ) {}

    // Note: Le préfixe '/spa' est défini dans config/routes.yaml (spa_routes)
    #[Route('/{vueRouting}', name: 'spa_app', requirements: ['vueRouting' => '.*'], defaults: ['vueRouting' => ''])]
    public function index(string $vueRouting = ''): Response
    {
        // Debug: logger la route reçue
        error_log("📍 Route reçue: '$vueRouting'");

        // Déterminer le type de page depuis la route Vue
        $pageType = $this->seoService->getPageTypeFromRoute($vueRouting);
        error_log("📄 Type de page: $pageType");

        $seoData = [];

        // Récupérer les données SEO selon le type de page
        if ($pageType === 'category') {
            $categoryName = $this->seoService->extractCategoryFromRoute($vueRouting);
            error_log("🏷️  Catégorie extraite: " . ($categoryName ?? 'null'));

            if ($categoryName) {
                $seoData = $this->seoService->getSeoForCategory($categoryName);
                error_log("✅ SEO trouvé pour '$categoryName': " . $seoData['title']);
            } else {
                $seoData = $this->seoService->getDefaultSeo();
                error_log("⚠️  Pas de catégorie, SEO par défaut");
            }
        } elseif ($pageType === 'page_slug') {
            $slug = $this->seoService->extractSlugFromRoute($vueRouting);
            error_log("📄 Slug extrait: " . ($slug ?? 'null'));

            if ($slug) {
                $seoData = $this->seoService->getSeoForPageSlug($slug);
                error_log("✅ SEO trouvé pour slug '$slug': " . $seoData['title']);
            } else {
                $seoData = $this->seoService->getDefaultSeo();
                error_log("⚠️  Pas de slug, SEO par défaut");
            }
        } elseif ($pageType === 'home') {
            $seoData = $this->seoService->getSeoForPage('home');
        } else {
            $seoData = $this->seoService->getSeoForPage($pageType);
        }

        return $this->render('spa/index.html.twig', [
            'seo' => $seoData
        ]);
    }
}
