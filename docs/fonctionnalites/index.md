# Fonctionnalités

Vue d'ensemble des fonctions présentes. Les badges renvoient au run CI [39493348934](https://github.com/cundovar/mediateur-numerique-formation/actions/runs/39493348934) (`main`, 2026-10-20) quand des tests automatisés couvrent la fonction.

| Fonction | Où | Tests | Preuve |
|---|---|---|---|
| Accueil à deux entrées, catalogues par public | `front/src/views/AudiencePage.vue`, routes `/apprendre` et `/accompagner` | `front/tests/unit/views/HomeAndAudience.spec.js`, `tests/Functional/CategoryAudienceApiTest.php` | <Status is="ci" /> |
| Cours, parcours, modules | `front/src/views/CategoryPage.vue`, `src/Entity/PageContent.php` | `tests/Functional/PublishedContentApiTest.php` | <Status is="ci" /> |
| QCM avec correction immédiate, sans score enregistré | `front/src/views/QCM/`, `src/Controller/Api/QCMApiController.php` | `front/tests/unit/stores/qcm.spec.js` | <Status is="ci" /> |
| Recherche | composants de navigation repris de DevDoc | `front/e2e/inputSearch.spec.js` (Playwright, non exécutable en l'état, voir [tests](../tests/index.md)) | <Status is="code" /> |
| Hors ligne (PWA, IndexedDB) | `front/vite.config.js`, `front/src/utils/offlineDb.js` | `front/tests/unit/utils/offlineDb.spec.js`, `fetchDataPwa.spec.js` | <Status is="ci" /> |
| PDF d'un cours ou d'un module | `/cours/<slug>.pdf`, `/module/<id>.pdf`, `src/Service/Pdf/` | `tests/Functional/CoursePdfTest.php` | <Status is="ci" /> |
| Compte apprenant facultatif, mot de passe oublié | `/register`, `/mot-de-passe-oublie`, `src/Service/Account/` | `LearnerAccountTest`, `ResetPasswordTest` | <Status is="ci" /> |
| Contenus personnalisés (réservés à un apprenant) : affichage seulement, l'attribution se fait par le connecteur de mediateur | `src/Entity/AccesReserve.php`, `src/Infrastructure/ApiPlatform/PublishedContentExtension.php` | `PersonalizedContentApiTest` | <Status is="ci" /> |
| Back-office éditorial (EasyAdmin) | `src/Controller/Admin/` | `Admin/BackOfficePagesTest`, `Admin/CaptureBackOfficeTest` | <Status is="ci" /> |
| Captures sourcées dans les cours | marqueur `[capture:ID]`, `src/Entity/CourseMedia.php` ; voir le [guide d'édition](edition.md) | `CaptureApiTest`, `CourseMediaValidationTest` | <Status is="ci" /> |
| Assistant de rédaction (MCP) et captures automatiques | Retirés de ce site : on utilise le [connecteur de mediateur](connecteur.md) | `RemovedFeaturesTest` | <Status is="local" /> |
| Pages légales | `/mentions-legales`, `/privacy`, `/terms`, `src/Service/LegalInformation.php` | `tests/Functional/LegalPagesTest.php` | <Status is="ci" /> |

Le site en ligne répond sur `https://mediateur.varascundo.com/spa` (contrôle du 2026-10-30). <Status is="prod" />

## Hors périmètre

Suivi de progression, attestations, réservations et statistiques ne font pas partie de la première version (décision dans [`AGENTS.md`](../../AGENTS.md)). La veille automatique des captures (refaire et comparer) est <Status is="prevu" />.
