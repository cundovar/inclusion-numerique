# Fonctionnalités

Vue d'ensemble des fonctions présentes. Les badges renvoient au run CI [41440222629](https://github.com/cundovar/vue-symfony/actions/runs/41440222629) (`main2`, `a379bfe1`, 2026-11-02) quand des tests automatisés couvrent la fonction.

| Fonction | Où | Tests | Preuve |
|---|---|---|---|
| Accueil à deux entrées, catalogues par public | `front/src/views/AudiencePage.vue`, routes `/apprendre` et `/accompagner` | `front/tests/unit/views/HomeAndAudience.spec.js`, `tests/Functional/CategoryAudienceApiTest.php` | <Status is="ci" /> |
| Cours, parcours, modules | `front/src/views/CategoryPage.vue`, `src/Entity/PageContent.php` | `tests/Functional/PublishedContentApiTest.php` | <Status is="ci" /> |
| QCM avec correction immédiate, sans score enregistré | `front/src/views/QCM/`, `src/Controller/Api/QCMApiController.php` | `front/tests/unit/stores/qcm.spec.js` | <Status is="ci" /> |
| Recherche | composants de navigation repris de DevDoc | `front/e2e/inputSearch.spec.js` (Playwright, non exécutable en l'état, voir [tests](../tests/index.md)) | <Status is="code" /> |
| Hors ligne (PWA, IndexedDB) : site et texte de tous les cours, images par « Tout télécharger » | `front/pwa/`, `front/src/utils/offlineDb.js`, `fetchDataPwa.js`, `front/src/services/captures.js`, `telechargementHorsLigne.js` ; voir [hors ligne](#hors-ligne) | `front/tests/unit/pwa/`, `front/tests/unit/services/`, `offlineDb.spec.js`, `fetchDataPwa.spec.js` (run [41702695459](https://github.com/cundovar/vue-symfony/actions/runs/41702695459), `bbd1220`) | <Status is="ci" /> |
| PDF d'un cours ou d'un module | `/cours/<slug>.pdf`, `/module/<id>.pdf`, `src/Service/Pdf/` | `tests/Functional/CoursePdfTest.php` | <Status is="ci" /> |
| Compte apprenant facultatif, mot de passe oublié | `/register`, `/mot-de-passe-oublie`, `src/Service/Account/` | `LearnerAccountTest`, `ResetPasswordTest` | <Status is="ci" /> |
| Contenus personnalisés (réservés à un apprenant) : affichage seulement, l'attribution se fait par le connecteur de mediateur | `src/Entity/AccesReserve.php`, `src/Infrastructure/ApiPlatform/PublishedContentExtension.php` | `PersonalizedContentApiTest` | <Status is="ci" /> |
| Back-office éditorial (EasyAdmin) | `src/Controller/Admin/` | `Admin/BackOfficePagesTest`, `Admin/CaptureBackOfficeTest` | <Status is="ci" /> |
| Captures sourcées dans les cours | marqueur `[capture:ID]`, `src/Entity/CourseMedia.php` ; voir le [guide d'édition](edition.md) | `CaptureApiTest`, `CourseMediaValidationTest` | <Status is="ci" /> |
| Assistant de rédaction (MCP) et captures automatiques | Retirés de ce site : on utilise le [connecteur de mediateur](connecteur.md) | `RemovedFeaturesTest` | <Status is="local" /> |
| Pages légales | `/mentions-legales`, `/privacy`, `/terms`, `src/Service/LegalInformation.php` | `tests/Functional/LegalPagesTest.php` | <Status is="ci" /> |

Le site en ligne répond sur `https://mediateur.varascundo.com/spa` (contrôle du 2026-10-30). <Status is="prod" />

## Hors ligne

Le site s'ouvre sans réseau dès qu'il a été visité une fois en ligne, même pour un cours jamais ouvert. Essai réel serveur coupé : <Status is="local" /> ([tests](../tests/index.md#essai-hors-ligne)). Le service worker publié contient la page hors ligne (contrôle du 2026-11-03) ; l'ouverture hors ligne du site publié n'a pas été essayée.

- **Page et scripts** : à l'installation, le service worker précharge la page `/spa/?hors-ligne=1` et tous les scripts et styles du build, y compris ceux des vues chargées à la demande ([`front/pwa/precache.js`](../../front/pwa/precache.js)). Une navigation sans réseau reçoit cette page ([`spaNavigation.js`](../../front/pwa/spaNavigation.js)).
- **Texte des cours** : à chaque lancement, `fetchMenus()` enregistre la liste complète des cours publics (`/api/page_contents`, avec leur texte) dans IndexedDB. Sur une erreur réseau, la page d'un cours relit cette copie, ainsi que la liste de sa rubrique pour les liens « précédent » et « suivant », et affiche un bandeau « Vous êtes hors ligne ».
- **Images des captures** : gardées quand elles ont été vues ; le bouton « Tout télécharger pour le hors ligne » de Mon espace, visible avec ou sans compte, les télécharge toutes d'avance (`TelechargementHorsLigne.vue`).
- **Réponses de l'API** : seules les lectures publiques sont gardées ([`apiCache.js`](../../front/pwa/apiCache.js)) ; le serveur marque les réponses à ne jamais garder (`ApiOfflineCacheListener`). Le cache est vidé à la déconnexion.

QCM et diaporamas ne sont pas prévus hors ligne.

## Hors périmètre

Suivi de progression, attestations, réservations et statistiques ne font pas partie de la première version (décision dans [`AGENTS.md`](../../AGENTS.md)). La veille automatique des captures (refaire et comparer) est <Status is="prevu" />.
