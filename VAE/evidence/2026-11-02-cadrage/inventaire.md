# Inventaire des preuves — Inclusion numérique — 2026-11-02

Archive immuable (phase 1 du plan `aidd_docs/tasks/2026_11/2026_11_02_vae_inclusion_numerique`, dossier VAE personnel du candidat). Sorties brutes dans ce dossier : [`git-provenance.txt`](git-provenance.txt), [`ci-main2.txt`](ci-main2.txt), [`prod-http.txt`](prod-http.txt). Référentiel : [`sources-referentiel.md`](sources-referentiel.md).

Cet inventaire constate des faits. Il ne relève aucun statut : la matrice et le registre sont réattribués en phase 2.

## 1. Version de référence

| Élément | Valeur | Preuve |
|---|---|---|
| Projet | Inclusion numérique, <https://inclusion-numerique.varascundo.com/spa/> | `prod-http.txt` : titre de `/spa/` « Inclusion numérique - Formations libres et gratuites » |
| Dépôt | `cundovar/vue-symfony` (même dépôt GitHub que DevDoc, dont `main` reste la branche) | `git remote -v` ; message de `d148fd6` |
| Branche | `main2` (seule branche déclenchant la CI/CD de ce workflow : `.github/workflows/ci.yml`, `branches: [main2]`) | `ci.yml` ligne 7 |
| SHA de référence | `30da5e5b98c17fa1ad3740346f53a782cd3543dc`, identique à `origin/main2` après `git fetch` le 2026-11-02 à 13 h 40 ; copie de travail propre (0 ligne dans `git status --short`) | `git-provenance.txt` |
| CI du SHA | run [41442887513](https://github.com/cundovar/vue-symfony/actions/runs/41442887513) : 6 jobs réussis (PHP, Front Vue, Migrations MySQL, Documentation VitePress, Build release, Deploy inclusion-numerique (OVH)), fin 2026-11-02 12:16 UTC | `ci-main2.txt` |
| Production | contrôle HTTP en lecture seule le 2026-11-02 à 13 h 40 : `/spa/`, `/spa`, `/docs/`, `/api` en 200 ; `/mcp`, `/oauth/authorize`, `/.well-known/oauth-authorization-server`, `/VAE/README.md` en 404 ; `/.env` en 403 | `prod-http.txt` |

Le contrôle de production ne prouve pas que le site sert exactement `30da5e5` : aucun identifiant de version n'est exposé. Il prouve l'état du site à cette heure-là, après le déploiement réussi du run 41442887513.

## 2. Provenance et contributions

### Chaîne de reprise

| Étape | Dépôt et commit | Dates | Contenu |
|---|---|---|---|
| DevDoc | `cundovar/vue-symfony`, `main`, jusqu'à `ec173dc` | 2025-06-05 → 2026-10-19 | plateforme Symfony/Vue d'origine ; 167 commits hors fusions et 65 fusions, contenus dans l'historique de `main2` |
| Médiateur | `cundovar/mediateur-numerique-formation`, `main`, jusqu'à `c87941f` (2026-10-30 14:09) | 2026-10-15 → 2026-10-30 | socle DevDoc importé (`ee88484`, 2026-10-15) puis adapté ; 86 commits hors fusions, 49 fusions |
| Inclusion | `cundovar/vue-symfony`, `main2`, `ec173dc..30da5e5` | 2026-10-31 → 2026-11-02 | 10 commits, détaillés ci-dessous |

Commits propres à `main2` (`git log ec173dc..HEAD`) :

| Commit | Date | Effet |
|---|---|---|
| `2b7de33` | 2026-10-31 | connecteurs MCP du gabarit app-neutre adaptés à DevDoc ; remplacé ensuite par `95d47ac` |
| `90623fd` | 2026-10-31 | hooks : racine du projet résolue depuis le dossier de session (worktrees) |
| `d148fd6` | 2026-11-02 | CI : déploiement de `main2` vers `/home/egflbug/inclusion-numerique`, environnement GitHub `inclusion-numerique`, refus de toute autre cible |
| `deb7543` | 2026-11-02 | base partagée `egflbugcundo6` : colonnes de Médiateur conservées, `MIGRATIONS_TABLE`, `DOCTRINE_SCHEMA_FILTER`, migration additive `Version20261102104351` |
| `a3ca623` | 2026-11-02 | documentation VAE de cette migration |
| `95d47ac` | 2026-11-02 | reprise du code de Médiateur (`c87941f`) : 1 004 fichiers modifiés ; déploiement sans migration ; `SITE_NAME` |
| `e32f39e` | 2026-11-02 | documentation du site sur la base partagée |
| `8ed912f` | 2026-11-02 | correctif rsync : conserver le lien `public/uploads` vers les images de Médiateur |
| `a379bfe` | 2026-11-02 | retrait du connecteur MCP, d'OAuth, des services de rédaction et du moteur de capture ; test `RemovedFeaturesTest` |
| `30da5e5` | 2026-11-02 | documentation du déploiement et de la CI d'Inclusion |

### Auteurs et aide des outils IA

| Périmètre | Auteur Git | Commits avec mention `Co-Authored-By: Claude` |
|---|---|---|
| `main2` propre (`ec173dc..HEAD`) | 10 / 10 « Facundo VARAS » | 10 / 10 (Claude Opus 5.5) |
| Médiateur jusqu'à `c87941f` | 71 « Facundo VARAS », 15 « Claude » | 47 (43 Opus 5.5, 4 Sonnet 5.5), parmi les 71 |
| DevDoc jusqu'à `ec173dc` | 153 « Facundo VARAS », 14 « cundo » (dont 13 depuis l'adresse d'un serveur) | 33 (32 Opus 5.5, 1 Haiku 4.5) ; des branches `codex/*` existent aussi sur le dépôt |

Ce que Git permet d'affirmer : le candidat est l'auteur Git, le propriétaire des dépôts et celui qui a fusionné les PR. Ce que Git ne permet pas d'affirmer : la part écrite par le candidat lui-même dans un commit co-signé par un agent IA, ni dans les 15 commits dont l'auteur est « Claude ». **Aucune réalisation n'est attribuée au candidat sur la seule présence du code dans la branche.**

À faire préciser par le candidat (questions ouvertes) :

1. Son rôle : expression du besoin, choix d'architecture, rédaction des consignes `AGENTS.md`/`regles/`, revue et validation des propositions des agents, tests manuels, déploiement, configuration des serveurs OVH et Netcup.
2. Les décisions qu'il a prises lui-même, avec un exemple vérifiable chacune (par exemple le partage de la base `egflbugcundo6`, le retrait du MCP sur Inclusion, le compte apprenant facultatif du 16 octobre).
3. Les parties qu'il sait expliquer et refaire sans agent, en vue de l'entretien technique.
4. Pourquoi Inclusion vit dans le dépôt de DevDoc (`main2`) et non dans un dépôt dédié.

## 3. Constats qui changent le discours actuel

| Affirmation actuelle | Constat au 2026-11-02 | Source |
|---|---|---|
| « le dépôt, la base et le déploiement sont indépendants » (`VAE/rapport.md`) ; « dépôts, bases et déploiements distincts » (`AGENTS.md`) | Faux pour Inclusion : dépôt de DevDoc (branche `main2`), base `egflbugcundo6` partagée avec Médiateur, images partagées par le lien `public/uploads`. Seul le dossier de déploiement est distinct. | `d148fd6`, `deb7543`, `95d47ac`, `8ed912f` |
| Projet nommé « Médiateur numérique » (`VAE/README.md`, matrice, rapport) | Le site présenté s'appelle Inclusion numérique | `prod-http.txt` |
| Connecteur MCP, OAuth, relecture croisée, capture automatique comme composants du projet (rapport, matrice, registre, `AGENTS.md`, `README.md`) | Retirés de `main2` par `a379bfe` ; `/mcp` et `/oauth/*` en 404. Les contenus sont édités par le connecteur de Médiateur, hors de ce dépôt. | `a379bfe`, `prod-http.txt` |
| `capture/`, `capture-service/`, `deploy/netcup-capture/` cités comme preuves | Absents de `main2` | `ls` du 2026-11-02 |
| Runs 39493348934 et 41056420072, contrôles de `mediateur.varascundo.com` du 2026-10-30 | Preuves de Médiateur (`mediateur-numerique-formation`), pas d'Inclusion | `VAE/preuves.md`, `VAE/journal.md` |
| « 32 fichiers PHPUnit », « 23 entités » (registre) | `main2` : 18 fichiers `*Test.php`, 19 entités, 13 migrations, 28 fichiers Vitest, 1 scénario Playwright | comptage `ls`/`find` du 2026-11-02 |
| Migrations « vérifiées en CI » | Vrai sur une base vide en CI ; en production, aucune migration n'est jouée par Inclusion : le schéma partagé évolue par le déploiement de Médiateur | `ci.yml` ligne 264 |
| Site VitePress | Liens de code et d'édition vers `mediateur-numerique-formation`, branche `main` (`docs/.vitepress/config.mjs` lignes 10, 11, 144) ; titre publié de `/docs/` : « Site de formation à la médiation numérique \| Médiation numérique » | fichier ; `prod-http.txt` |

Fichiers qui citent encore Médiateur, le MCP, Netcup ou la capture automatique (comptage `grep` du 2026-11-02) : `AGENTS.md` (6), `README.md` (3), `VAE/` (17 en tout), `docs/.vitepress/config.mjs` (2), `docs/index.md` (2), `docs/architecture/index.md` (4), `docs/deploiement/index.md` (5), `docs/fonctionnalites/*.md` (13, dont `connecteur.md` qui explique justement le retrait), `docs/harness/index.md` (2), `docs/securite/index.md` (2), `docs/conception/reprise-devdoc.md` (1), `regles/` (4). Certaines mentions sont justes (renvoi au connecteur de Médiateur, historique) ; le tri est fait en phase 2. `README.md`, `AGENTS.md` et `regles/` ne figurent pas dans le périmètre des phases 2 à 4 : à traiter séparément.

## 4. Inventaire par compétence

Légende des états : définitions de `VAE/preuves.md` (`code`, `local`, `ci`, `prod`, `prevu` ; Couvert, Partiel, Lacune). « Hérité » signale du code venu de DevDoc ou de Médiateur sans modification propre à `main2` ; la contribution personnelle reste à établir (section 2).

### BC01 — Développer une application sécurisée

| Compétence | Réalisation dans `main2` | Preuve disponible pour Inclusion | Preuve à obtenir | État constaté |
|---|---|---|---|---|
| Installer et configurer son environnement de travail | `compose.yaml`, `Dockerfile`, `Makefile` (`check`, `check-fast`, `docs-build`), harness `.agents/hooks/` (hérité, adapté par `90623fd`) | fichiers ; jobs CI qui reconstruisent l'environnement (run 41442887513) | installation consignée sur un environnement isolé avec données fictives (phase 4) ; `make check` local sur `30da5e5` | Partiel — `code` |
| Développer des interfaces utilisateur | SPA Vue 3 hérité de Médiateur : accueil à deux entrées, catalogues, cours, QCM, espace apprenant, PWA ; `SITE_NAME` (`95d47ac`) | job « Front Vue » réussi, run 41442887513 ; `/spa/` en 200 avec le titre d'Inclusion (`prod-http.txt`) | essais mobile, clavier, connexion faible et hors ligne consignés ; captures d'écran | Couvert — `ci` (réponse `prod` de la page d'accueil seulement) |
| Développer des composants métier | `CourseVisibility`, `src/Service/Pdf/`, `CaptureFileManager`, `LegalInformation`, `src/Service/Account/`, `PublicUrl` (hérités) | job « PHP » réussi, run 41442887513 ; tests `CoursePdfTest`, `PersonalizedContentApiTest`, `ResetPasswordTest`, `LearnerAccountTest`, `RemovedFeaturesTest` | jeu d'essai de la fonctionnalité la plus représentative (exigé par le RE) | Couvert — `ci` ; les composants de rédaction cités par le dossier actuel n'existent plus |
| Contribuer à la gestion d'un projet informatique | 10 commits `main2` en 3 jours ; plans `aidd_docs/tasks/2026_10/` (hérités de Médiateur) et plan du 2026-11-02 dans le dossier VAE personnel | historique Git (`git-provenance.txt`) | méthode, backlog daté, indicateurs, chronologie attribuable (phase 3) | Partiel — `code` |

### BC02 — Concevoir et développer une application sécurisée organisée en couches

| Compétence | Réalisation dans `main2` | Preuve disponible pour Inclusion | Preuve à obtenir | État constaté |
|---|---|---|---|---|
| Analyser les besoins et maquetter | `PLAN_APP_FORMATION_MEDIATEUR_NUMERIQUE.md`, `regles/`, `docs/conception/index.md` (hérités, formalisés a posteriori) | fichiers | user stories avec critères ; maquettes et enchaînement, datés comme rétrospectifs ; cas d'utilisation (RE) | Partiel — `code` |
| Définir l'architecture logicielle | `src/Domain`, `src/Infrastructure`, `src/Entity`, `src/Controller`, `src/Service`, API Platform ; dépendance à la base et aux images de Médiateur | fichiers ; `docs/architecture/index.md` (cite encore MCP et capture) | décisions datées (base partagée, retrait MCP, branche `main2`) ; diagramme de séquence (RE) | Partiel — `code` |
| Concevoir et mettre en place une base relationnelle | 19 entités, 13 migrations ; `Version20261102104351` additive et défensive (`deb7543`) ; `DOCTRINE_SCHEMA_FILTER`, `MIGRATIONS_TABLE` | job « Migrations MySQL » réussi (montée, validation, retour arrière, remontée sur base vide), run 41442887513 | MCD et MLD tirés de `main2` ; écart entre schéma attendu et schéma partagé réellement observé ; script de création (RE) | Partiel — `ci` |
| Développer des composants d'accès aux données SQL et NoSQL | repositories `src/Infrastructure/*`, `PublishedContentExtension`, `front/src/utils/offlineDb.js` (IndexedDB) | job « PHP » (`MappingTest`, `PublishedContentApiTest`) et job « Front Vue » (`offlineDb.spec.js`), run 41442887513 | — (pas de base NoSQL côté serveur : à expliquer) | Couvert — `ci` |

### BC03 — Préparer le déploiement d'une application sécurisée

| Compétence | Réalisation dans `main2` | Preuve disponible pour Inclusion | Preuve à obtenir | État constaté |
|---|---|---|---|---|
| Préparer et exécuter les plans de tests | 18 fichiers PHPUnit, 28 fichiers Vitest, 1 scénario Playwright (`front/e2e/inputSearch.spec.js`, qui importe `@playwright/test`, absent de `front/package.json`) | jobs « PHP » et « Front Vue » réussis, run 41442887513 | plan de tests rédigé avant les nouveaux essais ; jeu d'essai ; scénario E2E exécuté ; résultats locaux sur `30da5e5` | Partiel — `ci` (tests automatisés exécutés, plan et E2E absents) |
| Préparer et documenter le déploiement | `docs/deploiement/index.md` mis à jour par `e32f39e` et `30da5e5` ; `docs/env.example` | job « Documentation VitePress » réussi, run 41442887513 | relecture des pages encore liées à Médiateur (Netcup, capture) | Couvert — `code` (documentation) et `ci` (build) |
| Contribuer à la mise en production DevOps | `ci.yml` : contrôles, build, déploiement rsync vers OVH, garde-fou de cible (`d148fd6`), correctif du lien `uploads` (`8ed912f`) | job « Deploy inclusion-numerique (OVH) » réussi, runs 41442887513 et 41440222629 ; contrôle HTTP du 2026-11-02 (`prod-http.txt`) | — | Couvert — `prod` |

Bilan constaté : 5 compétences couvertes, 6 partielles, aucune lacune complète. Le dossier actuel annonçait 6 couvertes et 5 partielles, mais avec des preuves de Médiateur : la compétence « plans de tests » descend à Partiel faute de plan, de jeu d'essai et d'E2E exécuté pour Inclusion.

## 5. Preuves à obtenir, par phase

| Phase | Preuve |
|---|---|
| 2 | `make docs-build` ; liens VitePress vers `vue-symfony`/`main2` vérifiés |
| 3 | user stories, maquettes datées, MCD/MLD de `main2`, décisions d'architecture, chronologie et indicateurs |
| 4 | installation isolée ; `make check` et `make docs-build` sur le SHA retenu ; plan de tests et jeu d'essai fixés avant exécution ; E2E ; essais mobile, clavier, hors ligne ; contrôle prod en lecture seule ; archives datées |
| 5 | réponses du candidat (section 2 et `sources-referentiel.md`) |
