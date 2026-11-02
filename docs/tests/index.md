# Tests

> Plan formalisé a posteriori : il décrit les tests existants, il n'a pas précédé leur écriture. <Status is="code" />

## Stratégie

| Niveau | Outil | Emplacement | Exécution | Preuve |
|---|---|---|---|---|
| Unitaires et intégration backend | PHPUnit 9.5, base SQLite jetable | `tests/Entity/`, `tests/Service/`, `tests/Integration/` | `make test`, job CI « PHP » | <Status is="ci" /> |
| Fonctionnels HTTP (API, back-office, PDF, routes retirées) | PHPUnit, client Symfony | `tests/Functional/` | `make test`, job CI « PHP » | <Status is="ci" /> |
| Unitaires frontend | Vitest 4, jsdom | `front/tests/unit/` | `make test-front`, job CI « Front Vue » | <Status is="ci" /> |
| Migrations | MySQL 8.0 : montée, validation, retour arrière, remontée | `migrations/` | job CI « Migrations MySQL » | <Status is="ci" /> |
| Bout en bout | Playwright | `front/e2e/inputSearch.spec.js` (hérité de DevDoc) | non exécutable en l'état : `@playwright/test` n'est pas dans `front/package.json` | <Status is="code" /> |
| Analyse statique | PHPStan niveau 5 (baseline), ESLint | `phpstan.dist.neon`, `front/eslint.config.js` | `make check-fast`, CI | <Status is="ci" /> |

Volume présent dans le dépôt : 32 fichiers de test PHPUnit et 28 fichiers Vitest. Ce sont des fichiers comptés, pas des résultats d'exécution.

## Environnements

- **Local** : conteneurs `php` et `front` (`make check` enchaîne syntaxe PHP, PHPStan, ESLint, PHPUnit, Vitest et build Vite).
- **CI** : `.github/workflows/ci.yml` sur chaque pull request et chaque push sur `main2`, avec un `.env` de test écrit par le workflow.

## Critères

- Une pull request n'est fusionnée que si les jobs PHP, Migrations, Front et Documentation sont verts ; le déploiement dépend de ces jobs.
- Le build de cette documentation fait aussi échouer la CI sur un lien interne mort ([déploiement](../deploiement/index.md)).
- Parcours publics : compléter par un essai navigateur sur mobile, au clavier et hors ligne. Ces essais ne sont pas encore consignés : <Status is="prevu" />

## Dernière exécution vérifiée

Run [41440222629](https://github.com/cundovar/vue-symfony/actions/runs/41440222629) (`main2`, `a379bfe1`, 2026-11-02) : les 6 jobs ont réussi, dont le build de cette documentation. En local sur le même commit, `make check` a réussi (88 tests PHPUnit, 28 fichiers Vitest, build). <Status is="ci" />
