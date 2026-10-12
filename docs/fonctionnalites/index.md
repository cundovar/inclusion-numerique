# Fonctionnalités

Chaque fonctionnalité est décrite de l'écran jusqu'aux données. [QCM et progression](qcm.md) est la fiche modèle ; les autres fiches suivront le même plan.

| Fonctionnalité | Écran (route Vue) | API | Accès | Fiche |
|---|---|---|---|---|
| Cours par catégorie et niveau | `/category/:category`, `/pages/:slug` | API Platform `pages`, `page_contents`, `menuses` ; `GET /api/menus` | Public | À rédiger |
| Recherche | Barre de recherche (ordinateur et mobile) | Contenus déjà chargés, `page_contents` | Public | À rédiger |
| Exercices | `/exercices`, `/exercices/:slug` | API Platform `exos`, `exo_contents`, `exo_menus` | Public | À rédiger |
| **QCM et progression** | `/qcm`, `/qcm/:index`, `/qcm/result`, onglet du profil | `q_c_ms`, `/api/qcm-results` | Public ; progression connectée | [Fiche](qcm.md) |
| Compte et connexion | `/register`, `/login` (Twig) | `/user-api/me` | Public | À rédiger |
| Notes chiffrées | Éditeur sur la page de cours, onglet du profil | `/api/notes` | Connecté | À rédiger |
| Favoris | Bouton sur les cours, onglet du profil | `/api/me-favorites` | Connecté | À rédiger |
| Historique de visite | Onglet « Statistiques » du profil (si `VITE_ENABLE_PAGE_TRACKING`) | `/api/page-visits` | Connecté | À rédiger |
| Personnalisation | Onglet « Apparence » du profil | `/api/customization` | Connecté (valeurs par défaut publiques) | À rédiger |
| Hors ligne (PWA) | Toute la SPA | Cache Workbox, IndexedDB `spa-db` | Public | Voir [Données](../donnees/index.md) |
| Back-office | `/admin` (EasyAdmin) | — | Administrateur | À rédiger |
| Propositions de cours IA | Back-office | `/api/proposition-ia`, `/api/admin/agent-cours` | Administrateur, agents par clé | Voir [Intégrations](../migration-mcp-vers-symfony.md) |

Les routes de la SPA sont déclarées dans [`front/src/router/index.js`](../../front/src/router/index.js), les règles d'accès dans [`config/packages/security.yaml`](../../config/packages/security.yaml).

## Plan d'une fiche

1. En une phrase, avec une capture
2. Parcours utilisateur
3. Schéma de séquence
4. Fichiers impliqués : front, back, données
5. Règles de sécurité
6. Tests qui la couvrent
7. Limites connues
