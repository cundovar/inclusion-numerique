# 📄 Système de Pages Dynamiques - DevDoc

Ce README explique le fonctionnement du système de génération de pages dynamiques basé sur une architecture API REST avec Vue.js.

## 🏗️ Architecture Générale

Le système utilise une architecture **API-First** avec Symfony comme backend et Vue.js comme frontend :

```
┌─────────────────┐    API REST    ┌─────────────────┐
│   Vue.js SPA    │◄──────────────►│  Symfony API    │
│   (Frontend)    │                │   (Backend)     │
└─────────────────┘                └─────────────────┘
                                            │
                                   ┌─────────────────┐
                                   │   Base MySQL    │
                                   │   (Données)     │
                                   └─────────────────┘
```

## 🗄️ Structure des Données

### Entités Principales

#### 1. **Category** (Catégories)
```php
- id: int (ID unique)
- name: string (Nom de la catégorie)
```
**Exemple :** Vue.js, React, PHP, JavaScript

#### 2. **Menus** (Sous-menus)
```php
- id: int
- label: string (Label du menu)
- category: Category (Relation ManyToOne)
```
**Exemple :** Composants, Routing, State Management

#### 3. **Page** (Pages)
```php
- id: int
- slug: string (URL slug, ex: "/vue/composants")
- menus: Menus (Relation ManyToOne)
```

#### 4. **PageContent** (Contenu des pages)
```php
- id: int
- title: string (Titre de la page)
- type: string (Type de contenu)
- content: text (Contenu HTML/Markdown)
- code: text (Code source avec coloration syntaxique)
- page: Page (Relation ManyToOne)
- category: Category (Relation ManyToOne)
- menu: Menus (Relation ManyToOne)
- pageBlocks: Collection<PageBlock> (Blocs additionnels)
```

### Hiérarchie des Données
```
Category (Vue.js)
├── Menu (Composants)
│   ├── Page (/vue/composants-base)
│   │   └── PageContent (Titre, contenu, code...)
│   └── Page (/vue/composants-avances)
│       └── PageContent (Titre, contenu, code...)
└── Menu (Routing)
    ├── Page (/vue/routing-basic)
    └── Page (/vue/routing-advanced)
```

## 🔄 Flux de Données

### 1. **Génération du Menu Dynamique**

```javascript
// Dans App.vue - Récupération des données
const { menus, user, cats, fetchMenus, fetchUser } = useData()

// Structure générée automatiquement :
menusByCategory = {
  "Vue.js": [
    {
      label: "Composants",
      items: [
        { title: "Composants de base", page: { slug: "/vue/composants-base" } },
        { title: "Composants avancés", page: { slug: "/vue/composants-avances" } }
      ]
    }
  ]
}
```

### 2. **Routing Dynamique**

```javascript
// Dans Vue Router
{
  path: '/pages/:slug*',
  name: 'DynamicPage',
  component: PageComponent,
  props: route => ({ slug: route.params.slug })
}
```

### 3. **Chargement du Contenu**

```javascript
// Dans PageComponent.vue
async fetchPageContent() {
  const res = await axios.get(
    `/api/page_contents?page.slug=/${encodeURIComponent(this.slug)}`
  );
  this.pageContent = res.data.member[0];
}
```

## 🚀 APIs Disponibles

### Endpoints Principaux

#### 1. **Pages API** (`/api/`)
```
GET /api/                    - Liste toutes les pages
GET /api/{id}               - Page par ID
GET /api/menus              - Liste tous les menus
```

#### 2. **PageContent API** (API Platform)
```
GET /api/page_contents                     - Tous les contenus
GET /api/page_contents?page.slug=/slug     - Contenu par slug de page
```

#### 3. **Categories API**
```
GET /api/categories         - Toutes les catégories
```

## 🎨 Rendu des Pages

### Template Principal
```vue
<template>
  <div class="page-container">
    <!-- Breadcrumb dynamique -->
    <div v-html="'accueil/' + pageContent.menu.label"></div>
    
    <!-- Titre avec bouton favoris -->
    <h1>{{ pageContent.title }}</h1>
    <FavoriteButton :pageId="getPageId(pageContent.page)" />
    
    <!-- Contenu principal -->
    <div v-html="pageContent.content"></div>
    
    <!-- Code avec coloration syntaxique -->
    <div v-html="pageContent.code"></div>
  </div>
</template>
```

### Coloration Syntaxique
Le système utilise **Prism.js** pour la coloration automatique :

```javascript
// Auto-détection et application
document.querySelectorAll("pre code").forEach((el) => {
  if (!el.className.includes("language-")) {
    el.classList.add("language-js"); // Défaut JavaScript
  }
  el.innerHTML = el.innerHTML.replace(/^\s+/gm, ""); // Nettoyage
});
Prism.highlightAll(); // Application des styles
```

## 🔍 Fonctionnalités Avancées

### 1. **Recherche Dynamique**
```javascript
// Recherche en temps réel dans tous les contenus
searchResults = menus.filter((menu) => {
  const searchableText = [
    menu.title, menu.content, menu.code,
    menu.category?.name, menu.menu?.label
  ].join(" ");
  return searchableText.toLowerCase().includes(query);
});
```

### 2. **Système de Favoris**
- Chaque page peut être mise en favoris
- Stockage persistant par utilisateur
- Interface dédiée pour gérer les favoris

### 3. **Navigation Intelligente**
- Menu généré automatiquement à partir des données
- Breadcrumb contextuel
- Navigation responsive (mobile/desktop)

## 📱 Interface Utilisateur

### Navigation Adaptive
```vue
<!-- Desktop : Menu latéral fixe -->
<nav class="xl:w-1/6 xl:fixed">
  <div v-for="cat in cats" :key="cat.id">
    <h1>{{ cat.name }}</h1>
    <div v-for="group in menusByCategory[cat.name]">
      <div>{{ group.label }}</div>
      <router-link v-for="menu in group.items" 
                   :to="`/pages${menu.page.slug}`">
        {{ menu.title }}
      </router-link>
    </div>
  </div>
</nav>

<!-- Mobile : Menu hamburger -->
<div class="xl:hidden">
  <button @click="toggleMenu">☰</button>
  <!-- Menu mobile similaire -->
</div>
```

## 🔧 Administration (EasyAdmin)

### Gestion du Contenu
L'administration permet de gérer facilement :

1. **Catégories** - Ajout/modification des grandes sections
2. **Menus** - Organisation des sous-sections
3. **Pages** - Création des URLs et structure
4. **PageContent** - Rédaction du contenu, code, etc.

### Workflow de Création
```
1. Créer une Category (ex: "Node.js")
2. Créer des Menus liés (ex: "Express", "API")
3. Créer des Pages avec slugs (ex: "/nodejs/express-basics")
4. Rédiger le PageContent associé
5. ✅ La page apparaît automatiquement dans l'interface !
```

## 🚀 Avantages du Système

### ✅ **Pour les Développeurs**
- **Extensible** : Ajout facile de nouvelles fonctionnalités
- **Maintenir** : Code organisé et séparation des responsabilités
- **Performance** : Vue.js avec Vite pour un dev rapide

### ✅ **Pour les Rédacteurs**
- **Interface simple** : EasyAdmin pour la gestion de contenu
- **Aperçu en temps réel** : Modifications visibles immédiatement
- **Organisation logique** : Structure hiérarchique claire

### ✅ **Pour les Utilisateurs**
- **Navigation intuitive** : Menu généré automatiquement
- **Recherche puissante** : Dans tous les contenus
- **Responsive** : Parfait sur mobile et desktop
- **Performance** : SPA rapide avec cache intelligent

## 🛠️ Technologies Utilisées

- **Backend** : Symfony 6+ avec API Platform
- **Frontend** : Vue.js 3 avec Vue Router
- **Base de données** : MySQL avec Doctrine ORM
- **Build** : Vite pour Vue.js, Webpack pour EasyAdmin
- **Styles** : Tailwind CSS
- **Icons** : PrimeIcons
- **Syntax Highlighting** : Prism.js

Cette architecture permet de créer facilement des centaines de pages de documentation technique avec une interface utilisateur moderne et une administration simple !