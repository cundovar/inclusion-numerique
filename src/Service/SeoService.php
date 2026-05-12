<?php

namespace App\Service;

use App\Domain\Category\Repository\CategoryRepositoryInterface;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Domain\Seo\Repository\SeoRepositoryInterface;
use App\Entity\Category;
use App\Entity\Seo;

class SeoService
{
    public function __construct(
        private SeoRepositoryInterface $seoRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private PageRepositoryInterface $pageRepository
    ) {}

    /**
     * Récupère les données SEO pour une page donnée
     */
    public function getSeoForPage(?string $page = null): array
    {
        $seo = null;

        // Si une page est spécifiée, essayer de la trouver
        if ($page) {
            $seo = $this->seoRepository->findByPage($page);
        }

        // Si pas de SEO trouvé, retourner les valeurs par défaut
        if (!$seo) {
            return $this->getDefaultSeo();
        }

        return $this->formatSeoData($seo);
    }

    /**
     * Récupère les données SEO pour une catégorie
     */
    public function getSeoForCategory(string $categoryName): array
    {
        $category = $this->categoryRepository->findByName($categoryName);

        if (!$category) {
            return $this->getDefaultSeo();
        }

        // Si la catégorie a un SEO défini, l'utiliser
        if ($category->getSeo()) {
            return $this->formatSeoData($category->getSeo(), $category);
        }

        // Sinon, générer un SEO depuis les données de la catégorie
        return $this->generateSeoFromCategory($category);
    }

    /**
     * Récupère les données SEO pour une page par slug
     */
    public function getSeoForPageSlug(string $slug): array
    {
        $page = $this->pageRepository->findBySlug($slug);

        if (!$page) {
            return $this->getDefaultSeo();
        }

        // Si la page a un SEO défini, l'utiliser
        if ($page->getSeo()) {
            return $this->formatSeoData($page->getSeo());
        }

        // Sinon, générer un SEO depuis les données de la page
        return $this->generateSeoFromPage($page);
    }

    /**
     * Formate les données SEO pour l'utilisation dans les templates
     */
    private function formatSeoData(Seo $seo, ?Category $category = null): array
    {
        return [
            'title' => $seo->getTitle(),
            'metaDescription' => $seo->getMetaDescription(),
            'metaKeywords' => $seo->getMetaKeywords(),
            'ogTitle' => $seo->getOgTitle() ?? $seo->getTitle(),
            'ogDescription' => $seo->getOgDescription() ?? $seo->getMetaDescription(),
            'ogImage' => $seo->getOgImage(),
            'canonicalUrl' => $seo->getCanonicalUrl(),
            'noIndex' => $seo->isNoIndex(),
            'noFollow' => $seo->isNoFollow(),
            'structuredData' => $seo->getStructuredData(),
        ];
    }

    /**
     * Génère un SEO depuis les données d'une page
     */
    private function generateSeoFromPage(\App\Entity\Page $page): array
    {
        $slug = ucfirst(str_replace('-', ' ', $page->getSlug()));

        return [
            'title' => "$slug - Cours et Documentation",
            'metaDescription' => "Découvrez notre cours complet sur $slug avec exemples et exercices pratiques",
            'metaKeywords' => $slug . ', cours, tutoriel, documentation',
            'ogTitle' => "$slug - Formation",
            'ogDescription' => "Apprenez $slug avec notre cours complet et nos exercices pratiques",
            'ogImage' => null,
            'canonicalUrl' => null,
            'noIndex' => false,
            'noFollow' => false,
            'structuredData' => $this->generateStructuredDataForPage($page),
        ];
    }

    /**
     * Génère un SEO depuis les données d'une catégorie
     */
    private function generateSeoFromCategory(Category $category): array
    {
        $categoryName = ucfirst($category->getName());
        $description = $category->getDescription();

        // Extraire le texte brut de la description HTML
        $plainDescription = strip_tags($description ?? '');
        $shortDescription = mb_substr($plainDescription, 0, 160);

        return [
            'title' => "$categoryName - Cours et Tutoriels",
            'metaDescription' => $shortDescription ?: "Découvrez nos cours et tutoriels sur $categoryName",
            'metaKeywords' => $categoryName . ', cours, tutoriel, formation',
            'ogTitle' => "$categoryName - Formation",
            'ogDescription' => mb_substr($plainDescription, 0, 200) ?: "Apprenez $categoryName avec nos cours complets",
            'ogImage' => null,
            'canonicalUrl' => null,
            'noIndex' => false,
            'noFollow' => false,
        ];
    }

    /**
     * Retourne les valeurs SEO par défaut
     */
    public function getDefaultSeo(): array
    {
        return [
            'title' => 'Mon Site - Formation et Cours en ligne',
            'metaDescription' => 'Plateforme de formation et de cours en ligne pour apprendre la programmation et le développement web',
            'metaKeywords' => 'formation, cours, programmation, développement web, tutoriels',
            'ogTitle' => 'Mon Site - Formation et Cours',
            'ogDescription' => 'Plateforme de formation et de cours en ligne pour apprendre la programmation',
            'ogImage' => null,
            'canonicalUrl' => null,
            'noIndex' => false,
            'noFollow' => false,
        ];
    }

    /**
     * Extrait le nom de la catégorie depuis une route Vue.js
     * Ex: category/javascript -> javascript
     * Ex: /category/javascript -> javascript
     */
    public function extractCategoryFromRoute(string $route): ?string
    {
        // Supporte à la fois "category/javascript" et "/category/javascript"
        if (preg_match('#/?category/([^/]+)#', $route, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extrait le slug depuis une route Vue.js
     * Ex: pages/mon-cours -> mon-cours
     * Ex: /pages/mon-cours -> mon-cours
     */
    public function extractSlugFromRoute(string $route): ?string
    {
        // Supporte à la fois "pages/slug" et "/pages/slug"
        if (preg_match('#/?pages/([^/]+)#', $route, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Détermine le type de page depuis une route Vue.js
     */
    public function getPageTypeFromRoute(string $route): string
    {
        if (str_contains($route, 'category/')) {
            return 'category';
        }

        if (str_contains($route, 'pages/')) {
            return 'page_slug';
        }

        if ($route === '/' || $route === '') {
            return 'home';
        }

        if (str_contains($route, 'about')) {
            return 'about';
        }

        if (str_contains($route, 'contact')) {
            return 'contact';
        }

        return 'default';
    }

    /**
     * Génère les données structurées JSON-LD pour une page
     */
    private function generateStructuredDataForPage(\App\Entity\Page $page): string
    {
        $slug = ucfirst(str_replace('-', ' ', $page->getSlug()));

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'LearningResource',
            'name' => $slug,
            'description' => "Cours complet sur $slug avec exemples et exercices pratiques",
            'educationalLevel' => 'Beginner to Advanced',
            'teaches' => $slug,
            'inLanguage' => 'fr-FR',
            'isAccessibleForFree' => true,
            'provider' => [
                '@type' => 'Organization',
                'name' => 'Mon Site de Formation'
            ]
        ];

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
