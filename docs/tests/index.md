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

Volume présent dans le dépôt (`main2`, `30da5e5`, comptage du 2026-11-02) : 18 fichiers de test PHPUnit, 28 fichiers Vitest et 1 scénario Playwright. Ce sont des fichiers comptés, pas des résultats d'exécution.

## Environnements

- **Local** : conteneurs `php` et `front` (`make check` enchaîne syntaxe PHP, PHPStan, ESLint, PHPUnit, Vitest et build Vite).
- **CI** : `.github/workflows/ci.yml` sur les pull requests vers `main` et les pushes sur `main`, avec un `.env` de test écrit par le workflow.

## Critères

- Le déploiement dépend des jobs PHP, Migrations, Front et Documentation (`needs` du job `build`). Les commits de `main2` sont poussés directement, sans pull request : la CI s'exécute après le push.
- Le build de cette documentation fait aussi échouer la CI sur un lien interne mort ([déploiement](../deploiement/index.md)).
- Parcours publics : compléter par un essai navigateur sur mobile, au clavier et hors ligne. Ces essais ne sont pas encore consignés : <Status is="prevu" />

## Dernière exécution vérifiée

Runs [42206685700](https://github.com/cundovar/inclusion-numerique/actions/runs/42206685700) (PR) et [42206988450](https://github.com/cundovar/inclusion-numerique/actions/runs/42206988450) (post-fusion sur `main`, 2026-11-06) : PHP/PHPUnit, migrations MySQL, ESLint/Vitest/build Vue, VitePress et build de release réussis ; `deploy` a été ignoré. Le run précédent après fusion de la PR #1 a échoué car `SSHPASS` était vide : le workflow ciblait l'environnement `inclusion-numerique` au lieu de `production`, où se trouve le secret. <Status is="ci" />

Run [41442887513](https://github.com/cundovar/vue-symfony/actions/runs/41442887513) (`main2`, `30da5e5`, 2026-11-02) : les 6 jobs ont réussi, dont le build de cette documentation ([relevé](../../VAE/evidence/2026-11-02-cadrage/ci-main2.txt)). <Status is="ci" />

Le run précédent, [41440222629](https://github.com/cundovar/vue-symfony/actions/runs/41440222629) (`a379bfe1`), a aussi réussi ; le `make check` local annoncé sur ce commit n'a pas de sortie archivée et sera refait (plan de tests de la phase 4).

## Essai hors ligne

Le 2026-11-03, sur le correctif hors ligne (`bbd1220`), dans une copie de travail : contrôles de la CI réussis (ESLint, 215 tests Vitest, build, syntaxe PHP, lint conteneur/Twig/YAML, PHPStan, 89 tests PHPUnit), puis essai réel sur un build de production avec trois cours fictifs. <Status is="local" />

- Première visite en ligne dans un Chrome isolé : service worker actif, précache contenant `/spa/?hors-ligne=1` et les 37 fichiers du build, trois cours enregistrés dans IndexedDB.
- Serveur arrêté (plus aucune réponse) : un cours jamais ouvert s'affiche avec son texte et le bandeau hors ligne ; le lien « Suivant » ouvre le cours d'après ; Mon espace affiche le bouton « Tout télécharger ».
- Non essayé : le téléchargement des images (pas de capture dans les données d'essai), le mobile réel. Le run CI [41702695459](https://github.com/cundovar/vue-symfony/actions/runs/41702695459) a ensuite réussi sur `bbd1220` (6 jobs, déploiement compris). Sorties et déroulé archivés : [essai hors ligne](../../VAE/evidence/2026-11-03-hors-ligne/README.md).
