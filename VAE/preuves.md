# Registre des preuves VAE

Chaque ligne relie un sujet à une preuve vérifiable dans le dépôt, la CI ou le site déployé. Les niveaux sont décrits en [fin de page](#niveaux-de-preuve).

Projet : **Inclusion numérique**, branche `main2` de `cundovar/vue-symfony`, version de référence `30da5e5` ([cadrage du 2026-11-02](evidence/2026-11-02-cadrage/inventaire.md)). Abréviations : run **41442887513** = [run CI de `30da5e5`](https://github.com/cundovar/vue-symfony/actions/runs/41442887513) (2026-11-02, 6 jobs réussis) ; **contrôle du 2026-11-02** = [contrôle HTTP en lecture seule](evidence/2026-11-02-cadrage/prod-http.txt) de `https://inclusion-numerique.varascundo.com` à 13 h 40.

## Registre

| Bloc | Sujet | Preuve | État | À confirmer |
|---|---|---|---|---|
| BC01 | Environnement Docker | `compose.yaml`, `Dockerfile`, `Makefile`, `docs/env.example` (hérités) | `code` | Installation isolée et `make check` local à archiver |
| BC01 | Interfaces Vue | `front/src/views/`, `front/src/components/`, `front/src/stores/`, PWA (`front/vite.config.js`) | `ci` — job « Front Vue (ESLint, Vitest, build) », run 41442887513 ; `prod` pour la seule page d'accueil — `/spa/` en 200, titre « Inclusion numérique - Formations libres et gratuites », contrôle du 2026-11-02 | Essais mobile, clavier et hors ligne à consigner |
| BC01 | Hors ligne (PWA) | `bbd1220` : `front/pwa/`, `front/src/utils/fetchDataPwa.js`, `front/src/services/captures.js`, `telechargementHorsLigne.js`, `PageContent.vue` ; tests `front/tests/unit/pwa/`, `front/tests/unit/services/` | `local` — contrôles de la CI rejoués et essai réel serveur coupé, cours jamais ouvert affiché ([essai du 2026-11-03](evidence/2026-11-03-hors-ligne/README.md)) ; `ci` — run [41702695459](https://github.com/cundovar/vue-symfony/actions/runs/41702695459), 6 jobs réussis ; `prod` — `/spa/sw.js` publié précharge `/spa/?hors-ligne=1`, contrôle du 2026-11-03 | Ouverture hors ligne du site publié, téléchargement des images et téléphone réel non essayés |
| BC01 | Composants métier | `src/Service/CourseVisibility.php`, `src/Service/Pdf/`, `src/Service/CaptureFileManager.php`, `src/Service/Account/`, `src/Service/LegalInformation.php` | `ci` — job « PHP (syntaxe, PHPStan, PHPUnit) », run 41442887513 | Jeu d'essai à rédiger |
| BC01 | Retrait du connecteur MCP | `a379bfe` ; `tests/Functional/RemovedFeaturesTest.php` | `ci` — job « PHP », run 41442887513 ; `prod` — `/mcp`, `/oauth/authorize`, `/.well-known/oauth-authorization-server` en 404, contrôle du 2026-11-02 | — |
| BC01 | Sécurité des accès | `config/packages/security.yaml`, `src/Security/`, `tests/Functional/Security/AccessControlTest.php`, `tests/Functional/PersonalizedContentApiTest.php` | `ci` — job « PHP », run 41442887513 ; `prod` — `/VAE/README.md` en 404 et `/.env` en 403, contrôle du 2026-11-02 | — |
| BC01 | Gestion de projet | 10 commits propres à `main2` (`ec173dc..30da5e5`, 2026-10-31 → 2026-11-02), tous co-signés par Claude Opus 5.5 ([relevé](evidence/2026-11-02-cadrage/git-provenance.txt)) ; plans hérités `aidd_docs/tasks/2026_10/` ; [`docs/gestion-de-projet.md`](../docs/gestion-de-projet.md) (formalisé a posteriori) | `local` — indicateurs recalculés le 2026-11-02 ([commandes et sorties](evidence/2026-11-02-conception/indicateurs-git.txt)) | Part personnelle du candidat ; aucun suivi tenu pendant le projet |
| BC02 | Besoins et publics | `PLAN_APP_FORMATION_MEDIATEUR_NUMERIQUE.md`, `regles/` (hérités de Médiateur) ; [user stories](../docs/conception/besoins.md) et [écrans](../docs/conception/maquettes.md) formalisés a posteriori | `code` ; `prod` pour les captures d'écran du 2026-11-02 ([journal des captures](evidence/2026-11-02-conception/captures.log)) | Aucune maquette antérieure au développement |
| BC02 | Architecture en couches | `src/Domain/`, `src/Infrastructure/`, `src/Entity/`, `src/Controller/`, `src/Service/` ; [décisions d'architecture](../docs/conception/decisions-architecture.md) | `code` | Rôle du candidat dans les décisions |
| BC02 | Base relationnelle | 18 entités `src/Entity/`, 13 migrations `migrations/` (jusqu'à `Version20261016220000`) ; `DOCTRINE_SCHEMA_FILTER` (`config/services.yaml`) ; [MCD et MLD](../docs/conception/base-de-donnees.md) | `ci` — job « Migrations MySQL » (montée, validation, retour arrière, remontée sur base vide), run 41442887513 ; `local` — migrations jouées et schéma validé sur MySQL 8.0.44 jetable, export du schéma ([contrôle du 2026-11-02](evidence/2026-11-02-conception/README.md)) | En production le schéma partagé évolue par le déploiement de Médiateur, pas par Inclusion ; non inspecté |
| BC02 | Accès aux données SQL | Repositories `src/Infrastructure/*/`, `src/Infrastructure/ApiPlatform/PublishedContentExtension.php`, `tests/Integration/Doctrine/MappingTest.php`, `tests/Functional/PublishedContentApiTest.php` | `ci` — job « PHP », run 41442887513 | — |
| BC02 | Accès aux données NoSQL (client) | IndexedDB `front/src/utils/offlineDb.js`, test `front/tests/unit/utils/offlineDb.spec.js` | `ci` — job « Front Vue », run 41442887513 | Pas de base NoSQL côté serveur |
| BC03 | Tests automatisés | 18 fichiers PHPUnit (`tests/`), 28 fichiers Vitest (`front/tests/unit/`), 1 scénario Playwright (`front/e2e/`) | `ci` pour PHPUnit et Vitest, run 41442887513 ; `code` pour Playwright | Plan de tests formalisé a posteriori ; `@playwright/test` absent de `front/package.json` ; jeu d'essai |
| BC03 | Documentation du déploiement | `docs/deploiement/index.md` (`e32f39e`, `30da5e5`), `docs/env.example` | `ci` — job « Documentation VitePress », run 41442887513 | — |
| BC03 | Mise en production | `.github/workflows/ci.yml` (jobs `build` et `deploy` vers OVH) | `ci` — runs [42206685700](https://github.com/cundovar/inclusion-numerique/actions/runs/42206685700) (PR) et [42206988450](https://github.com/cundovar/inclusion-numerique/actions/runs/42206988450) (main) : contrôles et build réussis, `deploy` ignoré ; le run précédent [42205300321](https://github.com/cundovar/inclusion-numerique/actions/runs/42205300321) a échoué à l'auth SSH car le workflow ciblait le mauvais environnement. Contrôle SSH du 2026-11-06 : `/` répond 404, `/.env` répond 403 | Validation de la synchronisation des commits `main2`, déploiement manuel du nouveau dépôt et contrôle applicatif |
| BC03 | Documentation publiée sous `/docs/` | Job `docs`, build VitePress dans le job `build`, copie vers `public/docs/`, règles `/docs/` des `.htaccess` | `ci` — [run PR 42206685700](https://github.com/cundovar/inclusion-numerique/actions/runs/42206685700) : VitePress et build de release réussis ; aucun déploiement de cette version effectué | Publication sous `/docs/` à vérifier après déploiement |

### Preuves historiques d'autres projets

| Projet | Preuves | Usage dans ce dossier |
|---|---|---|
| Médiateur numérique (`cundovar/mediateur-numerique-formation`) | runs [39493348934](https://github.com/cundovar/mediateur-numerique-formation/actions/runs/39493348934) et [41056420072](https://github.com/cundovar/mediateur-numerique-formation/actions/runs/41056420072), contrôles de `mediateur.varascundo.com` du 2026-10-30 ([journal](journal.md#2026-10-30)) | Historique de la provenance du code ; aucune preuve d'Inclusion |
| DevDoc (`cundovar/vue-symfony`, `main`) | aucune preuve reprise | Origine du socle ([reprise](../docs/conception/reprise-devdoc.md)) |

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
