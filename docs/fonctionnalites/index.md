# Fonctionnalités

Chaque fonctionnalité est décrite de l'écran jusqu'aux données, sur le même plan. Le badge en tête de chaque fiche indique ce qui a été vérifié : la recette en production (scénarios R-01 à R-12 du [plan de tests](../tests/plan-de-tests.md)) ne couvre pas encore l'espace connecté.

| Fonctionnalité | Écran (route Vue) | API | Accès | Fiche |
|---|---|---|---|---|
| Cours par catégorie et niveau | `/category/:category`, `/pages/:slug` | API Platform `pages`, `page_contents`, `menuses` ; `GET /api/menus` | Public | [Fiche](cours.md) |
| Recherche | Barre de recherche (ordinateur et mobile) | Contenus déjà chargés, `page_contents` | Public | [Fiche](recherche.md) |
| Exercices | `/exercices`, `/exercices/:slug` | API Platform `exos`, `exo_contents`, `exo_menus` | Public | [Fiche](exercices.md) |
| **QCM et progression** | `/qcm`, `/qcm/:index`, `/qcm/result`, onglet du profil | `q_c_ms`, `/api/qcm-results` | Public ; progression connectée | [Fiche](qcm.md) |
| Compte et connexion | `/register`, `/login` (Twig) | `/user-api/me` | Public | [Fiche](compte.md) |
| Notes chiffrées | Éditeur sur la page de cours, onglet du profil | `/api/notes` | Connecté | [Fiche](notes.md) |
| Favoris | Bouton sur les cours, onglet du profil | `/api/me-favorites` | Connecté | [Fiche](favoris.md) |
| Historique de visite | Onglet « Statistiques » du profil (si `VITE_ENABLE_PAGE_TRACKING`) | `/api/page-visits` | Connecté | [Fiche](historique.md) |
| Personnalisation | En-tête et fond ; onglet « Apparence » du profil (contenu désactivé) | `/api/customization` | Défauts : tous ; réglages : connecté | [Fiche](personnalisation.md) |
| Hors ligne (PWA) | Toute la SPA | Cache Workbox, IndexedDB `spa-db` | Public | [Fiche](hors-ligne.md) |
| Back-office | `/admin` (EasyAdmin) | — | Administrateur | [Fiche](back-office.md) |
| Propositions de cours IA | Aucun écran | `/api/proposition-ia`, `/api/admin/agent-cours` | Administrateur, agents par clé | [Fiche](propositions-ia.md) |

Les routes de la SPA sont déclarées dans [`front/src/router/index.js`](../../front/src/router/index.js), les règles d'accès dans [`config/packages/security.yaml`](../../config/packages/security.yaml).

## Défauts relevés en rédigeant les fiches

Constatés dans le code le 2026-10-13, non corrigés :

| Fiche | Défaut | Gravité |
|---|---|---|
| [Historique](historique.md#limites-connues) | `POST /api/page-visits` ignore le choix « désactiver le suivi » de l'utilisateur et enregistre son IP | Élevée si le suivi est activé |
| [QCM](qcm.md#limites-connues) | Les bonnes réponses sont lisibles dans `GET /api/q_c_ms` | Moyenne |
| [Notes](notes.md#limites-connues) | Changer `APP_SECRET` rend les notes illisibles ; chiffrement sans authentification | Moyenne |
| [Propositions IA](propositions-ia.md#limites-connues) | Aucun écran de validation ; une génération finalisée est publiée directement | Moyenne |
| [Personnalisation](personnalisation.md#limites-connues) | Onglet « Apparence » vide (contenu en commentaire) | Faible |
| [Back-office](back-office.md#limites-connues) | Second tableau de bord EasyAdmin involontaire, routes CRUD en double | Faible |
| [Historique](historique.md#limites-connues) | Deux routes enregistrent la même visite ; `my-history` déclarée deux fois | Faible |

## Plan d'une fiche

1. En une phrase, avec une capture
2. Parcours utilisateur
3. Schéma de séquence
4. Fichiers impliqués : front, back, données
5. Règles de sécurité
6. Tests qui la couvrent
7. Limites connues
