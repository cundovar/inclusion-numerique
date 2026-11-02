# Matrice des compétences

Référentiel : TP Concepteur développeur d'applications, [RNCP37873](https://www.francecompetences.fr/recherche/rncp/37873/). Intitulés des blocs et compétences repris de la fiche France Compétences (consultée le 2026-10-30). Statuts et couvertures : voir [`preuves.md`](preuves.md#niveaux-de-preuve).

Seules des réalisations du projet Médiateur numérique figurent ici. « Formalisé a posteriori » signale un document écrit après la réalisation.

## RNCP37873BC01 — Développer une application sécurisée

| Compétence | Réalisation Médiateur | Preuve existante | Preuve manquante | Statut |
|---|---|---|---|---|
| Installer et configurer son environnement de travail en fonction du projet | Environnement Docker à 7 services (dont `docs`), Makefile de validation, harness Claude Code / Codex | `compose.yaml`, `Dockerfile`, `Makefile`, `docs/env.example`, [`docs/harness/`](../docs/harness/index.md) | Trace d'une installation complète sur un poste neuf | Partiel — `code` |
| Développer des interfaces utilisateur | SPA Vue 3 à deux entrées publiques, catalogues, cours, QCM, espace apprenant, PWA hors ligne | `front/src/views/`, `front/src/components/`, tests `front/tests/unit/` ; [`docs/fonctionnalites/`](../docs/fonctionnalites/index.md) | Essai mobile, clavier et hors ligne consigné | Couvert — `ci` |
| Développer des composants métier | Contrôles bloquants de brouillons, relecture croisée, PDF, captures, visibilité des contenus réservés | `src/Service/Redaction/`, `src/Service/Pdf/`, `src/Service/Capture/`, `src/Service/CourseVisibility.php`, `tests/Service/`, `tests/Functional/Mcp/` | — | Couvert — `ci` |
| Contribuer à la gestion d'un projet informatique | Plan produit, plans de phase, travail par branches et PR | `PLAN_APP_FORMATION_MEDIATEUR_NUMERIQUE.md`, `aidd_docs/tasks/`, historique Git de `main` | Méthode, backlog priorisé et indicateurs | Partiel — `code` |

## RNCP37873BC02 — Concevoir et développer une application sécurisée organisée en couches

| Compétence | Réalisation Médiateur | Preuve existante | Preuve manquante | Statut |
|---|---|---|---|---|
| Analyser les besoins et maquetter une application | Deux publics, parcours pédagogiques, référentiel éditorial | `PLAN_APP_FORMATION_MEDIATEUR_NUMERIQUE.md`, `regles/` ; [`docs/conception/`](../docs/conception/index.md) (formalisé a posteriori) | Maquettes, user stories avec critères d'acceptation | Partiel — `code` |
| Définir l'architecture logicielle d'une application | Couches Domain / Infrastructure / Service / Controller, API Platform, MCP, service de capture isolé | `src/`, `capture-service/`, `deploy/netcup-capture/` ; [`docs/architecture/`](../docs/architecture/index.md) (formalisé a posteriori) | Décisions d'architecture datées | Partiel — `code` |
| Concevoir et mettre en place une base de données relationnelle | Modèle Doctrine MySQL, migrations réversibles | `src/Entity/`, `migrations/`, job CI « Migrations MySQL » | MCD / MLD | Partiel — `ci` |
| Développer des composants d'accès aux données SQL et NoSQL | Repositories Doctrine, filtre API des contenus publiés et réservés ; IndexedDB côté client | `src/Infrastructure/`, `PublishedContentExtension.php`, `tests/Integration/Doctrine/`, `front/src/utils/offlineDb.js` et son test | — | Couvert — `ci` |

## RNCP37873BC03 — Préparer le déploiement d'une application sécurisée

| Compétence | Réalisation Médiateur | Preuve existante | Preuve manquante | Statut |
|---|---|---|---|---|
| Préparer et exécuter les plans de tests d'une application | PHPUnit, Vitest, tests du moteur de capture, Playwright | `tests/`, `front/tests/unit/`, `capture/test/`, `front/e2e/` ; [`docs/tests/`](../docs/tests/index.md) (formalisé a posteriori) | Plan de tests rédigé avant exécution ; scénario de bout en bout exécutable et exécuté | Couvert — `ci` |
| Préparer et documenter le déploiement d'une application | Procédure OVH et Netcup, variables d'environnement, CI | [`docs/deploiement/`](../docs/deploiement/index.md), `deploy/netcup-capture/`, `docs/env.example` | — | Couvert — `ci` |
| Contribuer à la mise en production dans une démarche DevOps | Pipeline GitHub Actions : contrôles, build, déploiement automatique sur `main` | `.github/workflows/ci.yml`, runs 39493348934 et 41056420072, site et documentation en ligne (contrôle du 2026-10-30) | — | Couvert — `prod` |
