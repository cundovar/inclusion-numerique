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
- Hooks partagés (`.agents/hooks/`, branchés par `.claude/settings.json` et `.codex/hooks.json`) : lint automatique du fichier modifié, blocage des fichiers protégés, et rappel en fin de tâche tant que `make check` n'a pas réussi depuis la dernière modification de code.

## Conventions
- Détails backend : `src/AGENTS.md`. Détails frontend : `front/AGENTS.md`.
- Schéma BDD : uniquement via migrations Doctrine (`doctrine:migrations:diff`), jamais de SQL manuel sur la base.
- Ne pas modifier une migration déjà commitée : en créer une nouvelle.

## Interdits
- Lire ou modifier les vrais fichiers d'environnement (`.env.local`, `.env.prod`) et les dumps (`backup*.sql`, `backups/`, `mysql_data/`).
- `make down-volumes` / `docker compose down -v` : supprime la base MySQL.
- `git push` et migrations sans accord explicite.

# Règle documentaire du projet

À la fin de toute tâche technique terminée, utiliser le skill
`vae-documentation-sync` (`.claude/skills/`, exposé à Codex via `.agents/skills/`) si le changement peut affecter
la documentation VAE, l'architecture, la sécurité, les tests, la CI/CD, le
déploiement ou la présentation technique du projet.

La documentation ne doit être mise à jour qu'après validation du code. Toute
affirmation doit être soutenue par une preuve vérifiable dans le dépôt, un test
effectivement exécuté, la CI ou une validation de déploiement clairement
identifiée.
