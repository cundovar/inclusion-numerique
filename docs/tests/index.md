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
- **CI** : `.github/workflows/ci.yml` sur les pull requests vers `main` et les pushes sur `main`, avec un `.env` de test écrit par le workflow.

## Critères

- Une pull request n'est fusionnée que si les jobs PHP, Migrations, Front et Documentation sont verts ; le déploiement dépend de ces jobs.
- Le build de cette documentation fait aussi échouer la CI sur un lien interne mort ([déploiement](../deploiement/index.md)).
- Parcours publics : compléter par un essai navigateur sur mobile, au clavier et hors ligne. Ces essais ne sont pas encore consignés : <Status is="prevu" />

## Dernière exécution vérifiée

Runs [42204743070](https://github.com/cundovar/inclusion-numerique/actions/runs/42204743070) et [42205300321](https://github.com/cundovar/inclusion-numerique/actions/runs/42205300321) (2026-11-06) : PHP/PHPUnit, migrations MySQL, ESLint/Vitest/build Vue, VitePress et build de release réussis. Le déploiement du push vers `main` a échoué car `SSHPASS` était vide : le workflow ciblait l'environnement GitHub `inclusion-numerique`, alors que le secret est dans `production`. La PR #2 corrige le nom d'environnement et garde le déploiement manuel. <Status is="ci" />
