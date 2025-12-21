# Plateforme E-Learning Full-Stack

## Apercu du Projet

Application web de formation en ligne permettant d'apprendre les technologies du développement web (Symfony, Vue.js, React, WordPress). Le projet combine une API RESTful robuste, une interface utilisateur moderne et un backoffice d'administration complet.

---

## Stack Technique

### Backend
- **Framework** : Symfony 6.4
- **API** : API Platform (REST)
- **ORM** : Doctrine 3
- **Base de données** : MySQL 8.0
- **Authentification** : Symfony Security Bundle

### Frontend
- **Framework** : Vue.js 3 (Composition API)
- **State Management** : Pinia
- **Bundler** : Vite
- **UI** : Tailwind CSS + Element Plus
- **PWA** : vite-plugin-pwa

### Backoffice Admin
- **Framework** : React 18
- **UI** : Material-UI (MUI)
- **Charts** : Recharts

### Infrastructure
- **Containerisation** : Docker & Docker Compose
- **Serveur Web** : Nginx
- **Documentation** : Storybook

---

## Fonctionnalites Principales

### Interface Utilisateur (Vue.js)

| Fonctionnalite | Description |
|----------------|-------------|
| **Navigation par categories** | Cours organises par technologie (Symfony, Vue, React, WordPress) |
| **Systeme de cours** | Pages de contenu structurees avec table des matieres dynamique |
| **Exercices interactifs** | Editeur Monaco integre pour la pratique du code |
| **QCM/Quiz** | Systeme de questions a choix multiples avec filtres par langage et niveau |
| **Favoris** | Sauvegarde des pages et exercices preferes |
| **Prise de notes** | Editeur TipTap avec persistance des notes |
| **Recherche intelligente** | Service d'analyse semantique des contenus |
| **Mode hors ligne** | PWA avec Service Worker et synchronisation |
| **Responsive design** | Interface adaptee mobile et desktop |

### API Backend (Symfony)

| Endpoint | Methode | Description |
|----------|---------|-------------|
| `/api/pages` | GET | Liste des pages de cours |
| `/api/pages/{id}` | GET | Detail d'une page |
| `/api/qcm` | GET | Liste des QCM |
| `/api/qcm/language/{lang}` | GET | QCM filtres par langage |
| `/api/favorites` | GET/POST/DELETE | Gestion des favoris |
| `/api/notes` | GET/POST/PUT/DELETE | Gestion des notes |
| `/api/user-visits` | POST | Tracking des visites |

### Backoffice Administration (React)

- **Dashboard** : Statistiques et metriques de frequentation
- **Gestion CRUD** : Categories, Pages, Exercices, QCM, Menus
- **Graphiques** : Visualisation des visites avec Recharts
- **Interface intuitive** : Material-UI pour une UX moderne

---

## Architecture

```
my_project/
├── src/                      # Backend Symfony
│   ├── Controller/           # Controleurs API
│   ├── Entity/               # 25 entites Doctrine
│   ├── Repository/           # Requetes base de donnees
│   ├── Service/              # Logique metier
│   └── Security/             # Authentification
├── front/                    # Frontend Vue.js
│   ├── src/components/       # 30+ composants Vue
│   ├── src/views/            # Pages applicatives
│   ├── src/store/            # State Pinia
│   ├── src/services/         # Services API
│   └── src/router/           # Configuration routes
├── backoffice/               # Admin React
│   ├── src/pages/            # 9 pages d'administration
│   └── src/components/       # Composants reutilisables
└── compose.yaml              # Configuration Docker
```

---

## Modele de Donnees

### Entites Principales

```
User ─────┬──── Favorite ──── Page
          ├──── Note
          ├──── UserPageVisit
          └──── UserCustomization

Page ─────┬──── PageContent
          ├──── Seo
          └──── Menus ──── Category

Exo ──────┬──── ExoContent
          └──── ExoMenu ──── Category

QCM ──────┬──── ChoicesQCM
          ├──── LanguageQCM
          └──── NiveauQCM
```

### Statistiques du Schema
- **25 entites** Doctrine
- Relations complexes (1:N, N:1, 1:1)
- Groupes de serialisation pour l'API

---

## Composants Cles

### Vue.js

| Composant | Role |
|-----------|------|
| `PageComponent.vue` | Rendu des pages de cours |
| `ExoComponent.vue` | Interface exercices avec editeur code |
| `PageQCM.vue` | Systeme de quiz interactif |
| `FavoriteButton.vue` | Gestion des favoris |
| `NoteEditor.vue` | Editeur de notes TipTap |
| `TableOfContents.vue` | Navigation dans le contenu |
| `SeoHead.vue` | Gestion meta-donnees SEO |

### React (Backoffice)

| Composant | Role |
|-----------|------|
| `Dashboard.jsx` | Vue d'ensemble statistiques |
| `VisitsChart.jsx` | Graphique des visites |
| `StatCard.jsx` | Cartes metriques |
| `MostVisitedList.jsx` | Top pages consultees |

---

## Points Techniques Notables

### Performance
- **Code splitting** avec Vite pour chargement optimal
- **Lazy loading** des routes et composants
- **Service Worker** pour cache intelligent
- **Pagination API** configuree (1000 items max)

### Securite
- **CORS** configure avec nelmio/cors-bundle
- **Hashage passwords** Symfony Security
- **CSRF Protection** sur les formulaires
- **Roles utilisateur** (ROLE_USER, ROLE_ADMIN)

### UX/UI
- **PWA installable** sur mobile
- **Mode offline** avec synchronisation
- **Syntax highlighting** avec Prism.js
- **Editeur code** Monaco (VS Code engine)

### DevOps
- **Environnement Docker** complet
- **Hot Module Replacement** en developpement
- **Storybook** pour documentation composants
- **Tests** : Vitest (JS) + PHPUnit (PHP)

---

## Demonstration des Competences

### Backend
- Conception API RESTful avec API Platform
- Modelisation base de donnees complexe
- Services metier decouplés
- Gestion authentification/autorisation

### Frontend
- Architecture composants Vue.js 3
- State management avec Pinia
- Integration PWA complete
- Responsive design Tailwind CSS

### Full-Stack
- Communication Frontend/Backend
- Serialisation/Deserialisation JSON
- Gestion des erreurs end-to-end
- Optimisation performance globale

### DevOps
- Containerisation Docker
- Configuration Nginx reverse proxy
- Build pipeline Vite
- Environnements dev/prod

---

## Technologies Maitrisees

```
Backend          Frontend         Outils
─────────        ────────         ──────
Symfony 6.4      Vue.js 3         Docker
API Platform     React 18         Nginx
Doctrine ORM     Pinia            Git
MySQL 8          Tailwind CSS     Vite
PHP 8            Element Plus     Storybook
                 Material-UI
                 Monaco Editor
```

---

## Liens

- **Code source** : [Repository GitHub]
- **Demo live** : [URL de demonstration]

---

*Projet realise dans le cadre d'une application de formation en ligne, demonstrant une maitrise complete du developpement web full-stack moderne.*
