# DevDoc — plateforme pédagogique Symfony/Vue

> Consignes communes à tous les agents (Codex lit `AGENTS.md`, Claude Code l'importe via `CLAUDE.md`).

## Stack
- Backend : Symfony 6.4, Doctrine ORM, API Platform, MySQL (`src/`, `config/`, `migrations/`).
- Frontend : Vue 3, Pinia, Vue Router, Vite, Tailwind 4, PWA (`front/`).
- Exécution : Docker Compose (`compose.yaml`). Services utiles : `php`, `front`, `nginx`, `db`.

## Lancer l'app
- `make up` (build + démarrage) ou `make dev`.
- App : http://localhost:8092 (port `HOST_HTTP_PORT`), Vite : 5173, Storybook : 6006, phpMyAdmin : 8091.
- Commandes PHP/Symfony : `docker compose exec php php bin/console …` ; front : `docker compose exec front npm …`.

## Validation (obligatoire)
- Pendant l'itération : `make check-fast` (syntaxe PHP, PHPStan, ESLint).
- **Une tâche n'est terminée que si `make check` passe** (ajoute PHPUnit, Vitest et le build Vite).
- Ne jamais masquer un échec : le signaler avec la sortie. Les erreurs préexistantes sont nommées, pas ignorées.
- Les conteneurs `php` et `front` doivent tourner (`docker compose ps`).
- Parcours utilisateur modifiés (navigation, recherche, QCM, accès, hors ligne) : `make test-e2e` (Playwright, `e2e/`), sur l'application locale compilée (`docker compose run --rm front-build`) et ses données de développement. Hors de `make check`.
- Hooks partagés (`.agents/hooks/`, branchés par `.claude/settings.json` et `.codex/hooks.json`) : lint automatique du fichier modifié, blocage des fichiers protégés, et rappel en fin de tâche tant que `make check` n'a pas réussi depuis la dernière modification de code.

## Conventions
- Détails backend : `src/AGENTS.md`. Détails frontend : `front/AGENTS.md`.
- Schéma BDD : uniquement via migrations Doctrine (`doctrine:migrations:diff`), jamais de SQL manuel sur la base.
- Ne pas modifier une migration déjà commitée : en créer une nouvelle.

## Interdits
- Lire ou modifier les vrais fichiers d'environnement (`.env.local`, `.env.prod`) et les dumps (`backup*.sql`, `backups/`, `mysql_data/`).
- `make down-volumes` / `docker compose down -v` : supprime la base MySQL.
- `git push` et migrations sans accord explicite.

# Documentation VitePress (`docs/`)

Toute modification ou implémentation **validée** (`make check` réussi) est reportée dans le site de documentation si elle change ce qu'il décrit : une fonctionnalité, un écran ou un parcours, une route d'API ou ses règles d'accès, une entité ou un schéma de données, l'architecture, une intégration (MCP, micro-service), les tests, la CI ou le déploiement. Un refactoring interne sans effet visible ni changement de structure n'en a pas besoin : le dire explicitement dans le compte rendu.

- Où écrire :

  | Changement | Page |
  |---|---|
  | Fonctionnalité nouvelle ou modifiée | `docs/fonctionnalites/<nom>.md`, au plan de `qcm.md`, et sa ligne dans `docs/fonctionnalites/index.md` |
  | Route, rôle, code de retour | `docs/api/index.md` et la fiche de la fonctionnalité |
  | Entité, migration, base NoSQL | `docs/conception/base-de-donnees.md`, `docs/donnees/index.md` |
  | Couches, micro-service, sécurité | `docs/conception/architecture.md` ; choix structurant : `docs/decisions/index.md` |
  | Tests, CI/CD, déploiement | `docs/tests/plan-de-tests.md`, `docs/CI_CD.md` |
  | Commande ou service local | `docs/demarrer/local.md` ; terme nouveau : `docs/demarrer/glossaire.md` |

- Nouvelle page : l'ajouter à la barre latérale (`docs/.vitepress/config.mjs`).
- Liens vers le code en chemins relatifs (réécrits vers GitHub au build) ; ancres au format GitHub.
- Badges `<Status is="code|local|ci|prod|prevu" />` fidèles à la preuve réelle, comme dans `VAE/preuves.md` : pas de « En production » sans vérification en production.
- Vérifier avec `make docs-build` (échoue sur un lien mort) et rapporter le résultat.

# Règle documentaire du projet

À la fin de toute tâche technique terminée, utiliser le skill
`vae-documentation-sync` (`.claude/skills/`, exposé à Codex via `.agents/skills/`) si le changement peut affecter
la documentation VAE, l'architecture, la sécurité, les tests, la CI/CD, le
déploiement ou la présentation technique du projet.

La documentation ne doit être mise à jour qu'après validation du code. Toute
affirmation doit être soutenue par une preuve vérifiable dans le dépôt, un test
effectivement exécuté, la CI ou une validation de déploiement clairement
identifiée.
