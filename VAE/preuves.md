# Registre des preuves VAE

Chaque ligne relie un sujet à une preuve vérifiable dans le dépôt, la CI ou le site déployé. Les niveaux sont décrits en [fin de page](#niveaux-de-preuve).

## Registre

| Bloc | Sujet | Preuve | État | À confirmer |
|---|---|---|---|---|
| BC01 | Environnement Docker | `compose.yaml` (php, front, nginx, db, phpmyadmin, storybook, docs), `Dockerfile`, `Makefile`, `docs/env.example` | `code` | Lancement complet de l'environnement à consigner |
| BC01 | Interfaces Vue | `front/src/views/`, `front/src/components/`, stores Pinia `front/src/stores/`, PWA (`front/vite.config.js`) | `ci` — job « Front Vue » réussi, [run 39493348934](https://github.com/cundovar/mediateur-numerique-formation/actions/runs/39493348934) (2026-10-20, `34bef51`) | Essai mobile, clavier et hors ligne à consigner |
| BC01 | Composants métier | `src/Service/Redaction/` (`DraftChecker`, `QcmDraftService`, `PersonalDataChecker`), `src/Service/CourseVisibility.php`, `src/Service/Pdf/`, `src/Service/Capture/` | `ci` — job « PHP » réussi, même run | — |
| BC01 | Connecteur MCP | `src/Mcp/RedactionMcpServer.php`, `src/Controller/McpController.php`, tests `tests/Functional/Mcp/` | `ci` — job « PHP » réussi, même run | Essai réel depuis claude.ai ou ChatGPT à consigner |
| BC01 | Sécurité des accès | `config/packages/security.yaml` (hachage `auto`, `access_control`), `src/Security/`, `src/Service/OAuth/`, `tests/Functional/Security/AccessControlTest.php`, `tests/Functional/OAuthFlowTest.php` | `ci` — job « PHP » réussi, même run | — |
| BC01 | Gestion de projet | Historique Git de `main` (84 commits hors fusions, 47 fusions de PR, du 2026-10-15 au 2026-10-20), plans `aidd_docs/tasks/`, `PLAN_APP_FORMATION_MEDIATEUR_NUMERIQUE.md` | `code` | Méthode, backlog et indicateurs non formalisés |
| BC02 | Besoins et publics | `PLAN_APP_FORMATION_MEDIATEUR_NUMERIQUE.md` (§ 1 à 3), référentiel éditorial `regles/` | `code` | Maquettes absentes du dépôt |
| BC02 | Architecture en couches | `src/Domain/`, `src/Infrastructure/`, `src/Entity/`, `src/Controller/`, `src/Service/` ; service de capture séparé `capture-service/`, `deploy/netcup-capture/` | `code` | Formalisée a posteriori dans `docs/architecture/` |
| BC02 | Base relationnelle | 23 entités `src/Entity/`, 13 migrations `migrations/` | `ci` — job « Migrations MySQL » (montée, validation du schéma, retour arrière, remontée) réussi, même run | MCD/MLD non formalisés |
| BC02 | Accès aux données SQL | Repositories `src/Infrastructure/*/`, filtre `src/Infrastructure/ApiPlatform/PublishedContentExtension.php`, `tests/Integration/Doctrine/MappingTest.php`, `tests/Functional/PublishedContentApiTest.php` | `ci` — job « PHP » réussi, même run | — |
| BC02 | Accès aux données NoSQL (client) | IndexedDB hors ligne `front/src/utils/offlineDb.js`, test `front/tests/unit/utils/offlineDb.spec.js` | `ci` — job « Front Vue » réussi, même run | Pas de base NoSQL côté serveur |
| BC03 | Tests automatisés | 32 fichiers PHPUnit (`tests/`), 28 fichiers Vitest (`front/tests/unit/`), tests du moteur de capture (`capture/test/`), 1 scénario Playwright (`front/e2e/`) | `ci` pour PHPUnit, Vitest et capture (même run) ; `code` pour Playwright | Plan de tests formalisé a posteriori ; scénario Playwright non exécutable en l'état (`@playwright/test` absent de `front/package.json`) |
| BC03 | Documentation du déploiement | `docs/deploiement/index.md`, `docs/harness/index.md`, `deploy/netcup-capture/docker-compose.yml` | `code` | — |
| BC03 | Mise en production | `.github/workflows/ci.yml` (jobs `build` et `deploy` vers OVH) | `ci` — job « Deploy production (OVH) » réussi, même run ; `prod` — `https://mediateur.varascundo.com/spa` répond 200 (contrôle `curl` du 2026-10-30) | — |
| BC03 | Documentation publiée sous `/docs/` | Job `docs`, build VitePress dans le job `build`, copie vers `public/docs/`, règles `/docs/` des `.htaccess` (PR #47) | `ci` — [run 41056420072](https://github.com/cundovar/mediateur-numerique-formation/actions/runs/41056420072) (`main`, `cd9f65e`, 2026-10-30) : 7 jobs réussis dont « Documentation VitePress », « Build release » et « Deploy production (OVH) » ; `prod` — contrôle du 2026-10-30 par `curl` et navigateur : `/docs/` et quatre pages profondes répondent 200, feuille de style et script servis, navigation par clic sans erreur console, `/docs` redirige en 301, `/VAE/README.md` et `/.claude/settings.json` répondent 404 | — |

## Couverture du référentiel

Référentiel : [RNCP37873](https://www.francecompetences.fr/recherche/rncp/37873/), voir [README](README.md#référentiel-visé). Détail par compétence : [matrice](matrice-competences.md).

| Couverture | Définition |
|---|---|
| **Couvert** | Une preuve exécutée existe (locale, CI ou production) |
| **Partiel** | Des preuves sont présentes dans le code ou la documentation, mais incomplètes ou non exécutées |
| **Lacune** | Aucune preuve dans le dépôt |

## Niveaux de preuve

Les mêmes niveaux sont affichés par le composant `<Status>` du site de documentation.

| Statut | Libellé | Preuve exigée |
|---|---|---|
| `code` | Présent dans le code | Fichier, classe ou configuration identifiable dans le dépôt |
| `local` | Vérifié localement | Commande exécutée et résultat consigné (date, commande, sortie) |
| `ci` | Validé par la CI | Run GitHub Actions identifié (numéro, commit, job) |
| `prod` | En production | Contrôle sur le site déployé (date, URL, méthode) |
| `prevu` | Prévu | Décision ou plan, sans réalisation vérifiée |

Un fichier présent ne prouve pas qu'il fonctionne ; un run CI réussi ne prouve pas l'état du site déployé.
