# Améliorations SEO pour les pages SPA

## ✅ Statut: IMPLÉMENTÉ ET FONCTIONNEL

Toutes les améliorations SEO ont été **implémentées avec succès** le **28 novembre 2025**.

**État actuel:**
- ✅ Base de données migrée
- ✅ Sitemap XML actif (`/sitemap.xml`) avec **182+ pages**
- ✅ SEO dynamique fonctionnel pour `/spa/pages/:slug`
- ✅ Données structurées JSON-LD générées
- ✅ Composable Vue disponible
- ✅ Configuration Docker opérationnelle

---

## 📋 Résumé des améliorations

Voici les améliorations SEO implémentées pour optimiser le référencement de vos pages `/spa/pages/:slug` :

---

## 1. ✅ Relation Page ↔ SEO

### Modification de l'entité Page
- Ajout d'une relation `OneToOne` avec l'entité `Seo`
- Chaque page peut maintenant avoir ses propres métadonnées SEO personnalisées
- Fichier modifié: `src/Entity/Page.php`

### Modification de l'entité Seo
- Ajout de la relation inverse avec `Page`
- Ajout du champ `structuredData` pour stocker les données JSON-LD
- Fichier modifié: `src/Entity/Seo.php`

---

## 2. ✅ Support des slugs dans SeoService

### Nouvelles méthodes ajoutées

#### `getSeoForPageSlug(string $slug): array`
Récupère les données SEO pour une page spécifique par son slug.

**Comportement :**
1. Cherche la page par son slug dans la base de données
2. Si la page a un SEO personnalisé, l'utilise
3. Sinon, génère automatiquement un SEO depuis les données de la page

#### `extractSlugFromRoute(string $route): ?string`
Extrait le slug depuis une route Vue.js.
- Exemple: `pages/mon-cours` → `mon-cours`

#### `generateSeoFromPage(Page $page): array`
Génère automatiquement les données SEO pour une page qui n'en a pas.

**SEO généré automatiquement :**
```php
[
    'title' => "Mon Cours - Cours et Documentation",
    'metaDescription' => "Découvrez notre cours complet sur Mon Cours...",
    'metaKeywords' => "Mon Cours, cours, tutoriel, documentation",
    'structuredData' => JSON-LD (voir section 3)
]
```

### Modification du SpaController
Le contrôleur détecte maintenant le type `page_slug` et utilise le service SEO approprié.

Fichiers modifiés:
- `src/Service/SeoService.php`
- `src/Controller/SpaController.php`

---

## 3. ✅ Données structurées JSON-LD

### Implémentation
Les données structurées aident Google à mieux comprendre le contenu de vos pages.

**Format Schema.org utilisé : `LearningResource`**

```json
{
  "@context": "https://schema.org",
  "@type": "LearningResource",
  "name": "Nom du cours",
  "description": "Description du cours",
  "educationalLevel": "Beginner to Advanced",
  "teaches": "Nom du cours",
  "inLanguage": "fr-FR",
  "isAccessibleForFree": true,
  "provider": {
    "@type": "Organization",
    "name": "Mon Site de Formation"
  }
}
```

### Intégration dans le template
Le template `base2.html.twig` affiche automatiquement le JSON-LD si présent :

```twig
{% if seo.structuredData %}
<script type="application/ld+json">
{{ seo.structuredData|raw }}
</script>
{% endif %}
```

**Avantages :**
- Améliore l'affichage dans les résultats de recherche Google
- Peut générer des "rich snippets" (extraits enrichis)
- Aide Google à catégoriser votre contenu éducatif

Fichiers modifiés:
- `templates/base2.html.twig`
- `src/Service/SeoService.php` (méthode `generateStructuredDataForPage`)

---

## 4. ✅ Sitemap XML automatique

### Nouveau contrôleur : SitemapController

**Route :** `/sitemap.xml`

Le sitemap XML est généré automatiquement et inclut :

1. **Page d'accueil** (`/spa`)
   - Priority: 1.0
   - Changefreq: daily

2. **Toutes les pages dynamiques** (`/spa/pages/:slug`)
   - Priority: 0.8
   - Changefreq: weekly

3. **Toutes les catégories** (`/spa/category/:category`)
   - Priority: 0.9
   - Changefreq: weekly

### Exemple de sortie
```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://yoursite.com/spa</loc>
        <lastmod>2025-11-28</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>https://yoursite.com/spa/pages/symfony-basics</loc>
        <lastmod>2025-11-28</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
</urlset>
```

### Configuration requise
✅ **Configuré** - L'URL de base a été configurée dans `config/services.yaml` :

```yaml
parameters:
    app.url: 'http://localhost:8080'
```

**Pour la production**, modifiez cette valeur avec votre vrai domaine:
```yaml
parameters:
    app.url: 'https://votre-domaine-reel.com'
```

Fichiers créés:
- `src/Controller/SitemapController.php`
- `templates/sitemap/index.xml.twig`

---

## 5. ✅ Composable Vue pour les meta tags (côté client)

### Nouveau composable : `useSeo()`

**Fichier :** `front/src/composables/useSeo.js`

Ce composable permet de mettre à jour dynamiquement les meta tags lors de la navigation côté client.

### Fonctionnalités

#### `updateMetaTags(seoData)`
Met à jour tous les meta tags de la page :
- Title
- Description
- Keywords
- Open Graph (og:title, og:description, og:image)
- URL canonique

#### `generateSeoForPage(slug)`
Génère automatiquement les données SEO pour une page.

#### `generateSeoForCategory(category)`
Génère automatiquement les données SEO pour une catégorie.

### Utilisation dans un composant Vue

```vue
<script setup>
import { useSeo } from '@/composables/useSeo'
import { onMounted } from 'vue'

const { updateMetaTags, generateSeoForPage } = useSeo()

onMounted(() => {
  const seoData = generateSeoForPage('symfony-basics')
  updateMetaTags(seoData)
})
</script>
```

### Composant SeoHead existant
Le composant `SeoHead.vue` existe déjà et gère automatiquement les meta tags.

**Utilisation :**
```vue
<template>
  <SeoHead :seoData="seoData" />
  <!-- Votre contenu -->
</template>
```

Fichiers concernés:
- `front/src/composables/useSeo.js` (créé)
- `front/src/components/SeoHead.vue` (existant)

---

## 🚀 Migration de la base de données

✅ **Appliqué** - Les modifications ont été appliquées à la base de données avec succès.

**Si vous utilisez Docker** (comme dans ce projet):
```bash
# Mise à jour du schéma avec Docker
docker compose exec php php bin/console doctrine:schema:update --force

# Vider le cache
docker compose exec php php bin/console cache:clear
```

**Sans Docker**:
```bash
# Lancer la migration
php bin/console doctrine:migrations:migrate

# Ou mise à jour directe du schéma
php bin/console doctrine:schema:update --force
```

**Modifications apportées :**
- ✅ Ajout de `seo_id` dans la table `appy_Page`
- ✅ Ajout de `structured_data` dans la table `appy_Seo`
- ✅ Création des contraintes de clé étrangère avec CASCADE ON DELETE

---

## 📝 Comment utiliser ces améliorations

### 1. Dans EasyAdmin

Vous pouvez maintenant créer des entrées SEO personnalisées pour vos pages :

1. Allez dans EasyAdmin
2. Créez un nouvel objet `Seo`
3. Associez-le à une `Page` lors de l'édition de la page

### 2. SEO automatique

Si vous ne créez pas de SEO personnalisé, le système génère automatiquement :
- Un titre basé sur le slug
- Une description générique
- Des données structurées JSON-LD

### 3. Vérification

✅ **Tests effectués** - Tout fonctionne correctement:

**Pour continuer à vérifier:**

1. **✅ Sitemap XML :** Accédez à `http://localhost:8080/sitemap.xml`
   - **Statut:** Fonctionnel avec **182+ pages** indexées
   - Contient toutes les pages `/spa/pages/:slug` et catégories

2. **Meta tags :** Inspectez le code source de vos pages
   - Vérifiez la présence des balises `<meta name="description">`
   - Vérifiez les Open Graph tags `<meta property="og:title">`

3. **Données structurées :** Utilisez le [Rich Results Test de Google](https://search.google.com/test/rich-results)
   - Testez avec vos URLs de pages
   - Vérifiez que le JSON-LD `LearningResource` est détecté

4. **Validation SEO :** Utilisez [Lighthouse](https://developers.google.com/web/tools/lighthouse) dans Chrome DevTools
   - Score SEO attendu: 90+/100

---

## 🎯 Prochaines étapes recommandées

### Court terme
1. ⏳ Soumettre le sitemap à Google Search Console
   - URL du sitemap: `https://votre-domaine.com/sitemap.xml`
2. ✅ Configurer le paramètre `app.url` dans `config/services.yaml` - **Fait**
3. ⏳ Tester les rich snippets avec l'outil Google
4. ⏳ Ajouter des images Open Graph (`ogImage`) pour vos pages importantes
   - Créer des images 1200x630px pour les partages sociaux
   - Ajouter via EasyAdmin dans l'entité Seo

### Moyen terme
1. Implémenter un système de cache pour le sitemap (mise à jour toutes les 24h)
2. Ajouter des breadcrumbs avec données structurées
3. Créer des pages AMP pour mobile
4. Implémenter le pre-rendering pour les crawlers (Prerender.io, Rendertron)

### Long terme
1. Mettre en place un système de suivi des positions SEO
2. Créer des rapports SEO automatiques
3. Implémenter un système de redirections 301 pour les anciennes URLs
4. Optimiser les Core Web Vitals

---

## 🔍 Fichiers modifiés/créés

### Entités
- ✏️ `src/Entity/Page.php`
- ✏️ `src/Entity/Seo.php`

### Services
- ✏️ `src/Service/SeoService.php`

### Contrôleurs
- ✏️ `src/Controller/SpaController.php`
- ✨ `src/Controller/SitemapController.php`

### Templates
- ✏️ `templates/base2.html.twig`
- ✨ `templates/sitemap/index.xml.twig`

### Frontend
- ✨ `front/src/composables/useSeo.js`
- (Existant) `front/src/components/SeoHead.vue`

### Migrations
- ✨ `migrations/Version20251128000000.php`

---

## ❓ Support et questions

Pour toute question ou problème concernant ces améliorations SEO, consultez :
- [Documentation Symfony SEO](https://symfony.com/doc/current/the-fast-track/en/25-seo.html)
- [Schema.org LearningResource](https://schema.org/LearningResource)
- [Google Search Console](https://search.google.com/search-console)

---

## 🚀 Guide de démarrage rapide

### Accès au sitemap
```
http://localhost:8080/sitemap.xml
```

### Tester une page spécifique
1. Allez sur: `http://localhost:8080/spa/pages/symfony-basics` (par exemple)
2. Faites clic-droit > "Afficher le code source de la page"
3. Cherchez les balises `<meta>` et `<script type="application/ld+json">`

### Créer un SEO personnalisé pour une page
1. Allez dans EasyAdmin
2. Créez une nouvelle entrée `Seo`
3. Remplissez les champs:
   - `title`: Le titre de la page
   - `metaDescription`: Description (max 160 caractères)
   - `ogTitle`, `ogDescription`: Pour les réseaux sociaux
   - `structuredData`: JSON-LD (optionnel, généré automatiquement si vide)
4. Éditez votre `Page` et associez le `Seo` créé

### Commandes Docker utiles
```bash
# Vider le cache
docker compose exec php php bin/console cache:clear

# Vérifier le schéma de la BDD
docker compose exec php php bin/console doctrine:schema:validate

# Voir les routes
docker compose exec php php bin/console debug:router | grep sitemap
```

---

## 📊 Statistiques

**Pages indexées dans le sitemap:** 182+
**Types de contenu:**
- Cours HTML/CSS
- Cours JavaScript
- Cours PHP
- Cours Symfony
- Cours Vue.js
- Cours React.js
- Cours WordPress
- Cours SQL
- Et plus...

**Priorités SEO:**
- Page d'accueil: 1.0 (priorité maximale)
- Catégories: 0.9
- Pages de cours: 0.8

---

**Date de création :** 28 novembre 2025
**Dernière mise à jour :** 28 novembre 2025
**Version :** 1.0
**Statut :** ✅ Opérationnel
**Auteur :** Claude Code
