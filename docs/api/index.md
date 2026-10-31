# Carte des API

Toutes les routes sont servies par Symfony sous le même domaine que la SPA. La liste vient de `php bin/console debug:router` ; les accès viennent de [`security.yaml`](../../config/packages/security.yaml) et des attributs `#[IsGranted]`.

| Famille | Préfixe | Authentification | Utilisée par |
|---|---|---|---|
| API Platform | `/api/{ressource}` | Lecture publique ; écriture `ROLE_ADMIN` | SPA (contenus) |
| API métier | `/api/notes`, `/api/me-favorites`, `/api/page-visits`, `/api/customization`, `/api/qcm-results` | Session, `ROLE_USER` | SPA (espace personnel) |
| Compte | `/login`, `/register`, `/logout`, `/user-api/me` | Formulaire et session | SPA, pages Twig |
| Connecteurs MCP | `/mcp`, `/mcp/apprenant` | Jeton OAuth `Bearer` (portée `redaction` ou `apprenant.read`) | Claude, ChatGPT |
| Serveur OAuth | `/oauth/*`, `/.well-known/oauth-*` | Public ; consentement en session | Connecteurs MCP |
| API d'administration | `/api/admin/…` | En-tête `X-API-KEY` ou session admin | Agents de cours IA (ancien circuit, n8n) |
| Propositions IA | `/api/proposition-ia` | `ROLE_ADMIN` | Back-office |
| Micro-service | `qcm-results` (hors de Symfony) | Clé partagée `X-Api-Key` | Symfony uniquement |

## API Platform

Ressources exposées (lecture publique, documentation interactive sur `/api/docs`) :

`categories` · `pages` · `page_contents` · `page_blocks` · `menuses` · `super_menus` · `position_menuses` · `niveau_cours` · `exos` · `exo_contents` · `exo_blocks` · `exo_menus` · `q_c_ms` · `choices_q_c_ms` · `language_q_c_ms` · `niveau_q_c_ms` · `doc_de_codes` · `logos` · `seos` · `users`

Réponses au format JSON-LD (`member`, `@id`). Le filtre de `q_c_ms` porte sur `languageQCM.name` et `niveauQCM.titre`.

`page_contents` ne sert que les cours au statut « publié », en liste comme à l'unité ; un administrateur connecté voit aussi les brouillons ([`PublishedContentExtension`](../../src/Infrastructure/ApiPlatform/PublishedContentExtension.php)).

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

## Connecteurs MCP et OAuth

Détail dans la fiche [Connecteurs MCP](../fonctionnalites/connecteur-mcp.md).

| Routes | Accès | Rôle |
|---|---|---|
| `GET, POST, DELETE /mcp` | Jeton OAuth, portée `redaction` (`ROLE_AGENT_REDACTION`) | Serveur MCP de rédaction (Streamable HTTP, JSON-RPC) |
| `GET, POST, DELETE /mcp/apprenant` | Jeton OAuth, portée `apprenant.read` (`ROLE_AGENT_APPRENANT`) | Serveur MCP apprenant, lecture seule |
| `GET /.well-known/oauth-protected-resource[/mcp\|/mcp/apprenant]` | Public | Métadonnées de la ressource (RFC 9728) |
| `GET /.well-known/oauth-authorization-server[/mcp]` | Public | Métadonnées du serveur d'autorisation (RFC 8414) |
| `POST /oauth/register` | Public, limité à 30 enregistrements par heure | Enregistrement dynamique (RFC 7591), clients publics avec PKCE |
| `GET, POST /oauth/authorize` | Connecté ; administrateur pour la portée `redaction` | Page de consentement |
| `POST /oauth/token`, `POST /oauth/revoke` | Public (client et code ou jeton) | Jetons et révocation (RFC 7009) |

Sans jeton valide, `/mcp` et `/mcp/apprenant` répondent 401 avec `WWW-Authenticate: Bearer resource_metadata="…"`.

| Routes | Accès | Rôle |
|---|---|---|
| `POST /depot-image/{jeton}` | Jeton à usage unique, valable 10 minutes, donné par `preparer_depot_image` | Dépôt d'une image pour un cours |
| `GET /api/captures`, `GET /api/captures/{id}` | Public, lecture seule | Images des cours (marqueurs `[capture:ID]`), sans auteur ni dates de suivi |
| `GET /api/diaporamas`, `GET /api/diaporamas/{id}` | Public, lecture seule | Diaporamas publiés dont la cible est publiée et ouverte à tous |

Les cours, parcours et QCM réservés à certains comptes ne sont servis par `/api/page_contents`, `/api/categories`, `/api/menuses` et `/api/q_c_ms` qu'à leurs destinataires ; un administrateur voit tout.

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
| 401 | Non connecté, clé d'API absente, ou jeton OAuth absent, expiré ou révoqué (`/mcp`) |
| 403 | Connecté sans le rôle requis |
| 400 | Corps invalide (API métier) |
| 422 | Validation API Platform |
| 503 | Micro-service des résultats de QCM indisponible |
