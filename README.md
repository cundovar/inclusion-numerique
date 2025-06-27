# Symfony + Vite.js : Architecture SPA intégrée

port symfony : 8080
port front : 5173
myadmin : 8081

Ce que tu as dans ton projet, c’est une configuration d’application hybride avec **Vite.js pour le front**, intégrée dans un projet **Symfony**.  
Ce type de structure est souvent appelé une **Single Page Application (SPA)** intégrée à Symfony.

## 🔧 Nom de cette configuration

On parle généralement de :

- **Monorepo SPA Symfony + Vite.js**
- **Symfony SPA intégrée**
- **Symfony avec frontend modernisé via Vite (ou Webpack Encore)**

### Tu utilises ici :

- **Symfony** pour le backend (routing, controllers, API REST ou pages server-rendered)
- **Vite.js** pour compiler et servir ton code JavaScript moderne (React, Vue, ou vanilla JS)
- Et tu montes ton SPA sur un seul `<div id="app">` dans un template Twig (`index.html.twig`)

---

## ⚖️ Comparaison avec une architecture API + Front séparés

| Aspect              | SPA intégrée (comme ton projet)                                                                 | Symfony API + Front séparés                                                             |
|---------------------|--------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------|
| **Structure**       | Symfony gère le front et le back dans le même projet                                             | Backend Symfony (API), frontend séparé (React/Vue via Vite/CRA)                         |
| **Communication**   | Symfony peut directement injecter Twig, variables, routes, etc.                                  | Le front communique via HTTP (REST ou GraphQL)                                          |
| **Déploiement**     | Un seul déploiement backend + frontend (Vite peut build dans `/public/`)                        | Deux déploiements distincts (API + front)                                               |
| **Dév local**       | Plus rapide à mettre en place, pas de CORS à gérer                                               | Besoin de configurer CORS, reverse proxy, ports, etc.                                   |
| **Souplesse**       | Moins modulaire, tout dans le même repo                                                          | Plus découplé, évolutif, peut migrer vers microservices                                 |
| **SEO**             | Peut poser souci (JS lourd), sauf si SSR intégré                                                 | Même problème, sauf si le front gère SSR (e.g. Nuxt, Next.js)                           |
| **Partage de code** | Facile de partager les DTO/formats entre back et front                                           | Nécessite des libs partagées ou contrats API formalisés                                 |

---

