# Carte des API

Toutes les routes sont servies par Symfony sous le même domaine que la SPA. La liste vient de `php bin/console debug:router` ; les accès viennent de [`security.yaml`](../../config/packages/security.yaml) et des attributs `#[IsGranted]`.

| Famille | Préfixe | Authentification | Utilisée par |
|---|---|---|---|
| API Platform | `/api/{ressource}` | Lecture publique ; écriture `ROLE_ADMIN` | SPA (contenus) |
| API métier | `/api/notes`, `/api/me-favorites`, `/api/page-visits`, `/api/customization`, `/api/qcm-results` | Session, `ROLE_USER` | SPA (espace personnel) |
| Compte | `/login`, `/register`, `/logout`, `/user-api/me` | Formulaire et session | SPA, pages Twig |
| API d'administration | `/api/admin/…` | En-tête `X-API-KEY` ou session admin | Agents de cours IA |
| Propositions IA | `/api/proposition-ia` | `ROLE_ADMIN` | Back-office |
| Micro-service | `qcm-results` (hors de Symfony) | Clé partagée `X-Api-Key` | Symfony uniquement |

## API Platform

Ressources exposées (lecture publique, documentation interactive sur `/api/docs`) :

`categories` · `pages` · `page_contents` · `page_blocks` · `menuses` · `super_menus` · `position_menuses` · `niveau_cours` · `exos` · `exo_contents` · `exo_blocks` · `exo_menus` · `q_c_ms` · `choices_q_c_ms` · `language_q_c_ms` · `niveau_q_c_ms` · `doc_de_codes` · `logos` · `seos` · `users`

Réponses au format JSON-LD (`member`, `@id`). Le filtre de `q_c_ms` porte sur `languageQCM.name` et `niveauQCM.titre`.

## API métier

| Routes | Accès | Fonctionnalité |
|---|---|---|
| `POST /api/notes`, `GET /api/notes/my-notes`, `GET /api/notes/page/{pageId}`, `DELETE /api/notes/{id}` | Connecté | Notes chiffrées |
| `GET /api/me-favorites/list-my-favorites`, `POST …/toggle/{pageId}`, `GET …/check/{pageId}`, `DELETE /api/me-favorites/{id}` | Connecté | Favoris |
| `POST /api/page-visits`, `GET …/my-history`, `GET …/my-stats`, `DELETE …/clear-all`, `POST …/toggle-tracking` | Connecté | Historique de visite |
| `GET /api/customization`, `POST /api/customization`, `POST …/reset` ; `GET …/defaults`, `GET …/allowed-classes` | Connecté | Personnalisation |
| `/api/qcm-results` | Connecté, statistiques admin | [QCM et progression](../fonctionnalites/qcm.md#api) |
| `GET /api/`, `GET /api/{id}`, `GET /api/menus` | Public | Pages et menus pour la SPA |
| `GET /api/qcm/…` | Public | QCM par langage et difficulté |

::: info Constat
`GET /api/page-visits/my-history` est déclarée deux fois, dans [`UserPageVisitController`](../../src/Controller/Api/UserPageVisitController.php) et [`PageVisitController`](../../src/Controller/PageVisitController.php) : seule la première chargée répond.
:::

## API d'administration

Sans navigateur, pour les agents de cours IA : authentification par en-tête `X-API-KEY` ([`ApiKeyAuthenticator`](../../src/Security/ApiKeyAuthenticator.php)), contrôleurs de [`src/Controller/Api/AdminCrud/`](../../src/Controller/Api/AdminCrud/ApiAgentCourseController.php).

| Préfixe | Opérations |
|---|---|
| `/api/admin/categories` | Liste, détail, création, modification, suppression ; contenus et menus d'une catégorie |
| `/api/admin/pages`, `/api/admin/page-contents` | CRUD ; contenus par page, par catégorie |
| `/api/admin/menus`, `/api/admin/positions-menus`, `/api/admin/super-menus` | CRUD de la navigation |
| `/api/admin/niveau-cours` | CRUD des niveaux |
| `/api/admin/exo-contents` | CRUD ; par exercice, par catégorie |
| `/api/admin/course-media` | Liste, détail, envoi, suppression de médias |
| `/api/admin/agent-cours` | Création, lecture, modification de cours générés ; révisions et application |
| `/api/admin/agent-cours/generations` | Suivi d'une génération, finalisation, échec |

Détail du circuit : [Propositions de cours IA](../fonctionnalites/propositions-ia.md).

## Codes de retour

| Code | Sens dans DevDoc |
|---|---|
| 401 | Non connecté, ou clé d'API absente |
| 403 | Connecté sans le rôle requis |
| 400 | Corps invalide (API métier) |
| 422 | Validation API Platform |
| 503 | Micro-service des résultats de QCM indisponible |
