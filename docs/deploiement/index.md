# Déploiement et CI

## Pipeline

Workflow unique : [`.github/workflows/ci.yml`](../../.github/workflows/ci.yml) (« CI/CD »), sur chaque pull request, chaque push sur `main` et à la demande.

| Job | Rôle | Quand |
|---|---|---|
| `php` | Syntaxe PHP, lint conteneur/Twig/YAML, PHPStan, PHPUnit | Toujours |
| `migrations` | Migrations sur MySQL 8.0 : montée, validation, retour arrière, remontée | Toujours |
| `capture` | Lint et tests du moteur de capture avec Chromium | Toujours |
| `front` | ESLint, Vitest, build Vite | Toujours |
| `docs` | Build VitePress de `docs/` (échoue sur un lien interne mort) | Toujours |
| `build` | Archive de production : `composer install --no-dev`, build Vue, documentation copiée dans `public/docs/` | Push sur `main` |
| `deploy` | Envoi sur OVH, migrations, reconstruction du cache | Après `build` |

Dernier déploiement vérifié : run [41056420072](https://github.com/cundovar/mediateur-numerique-formation/actions/runs/41056420072) (`main`, `cd9f65e`, 2026-10-30) : les 7 jobs ont réussi, dont `docs`, `build` et `deploy`. <Status is="ci" />

## Production OVH

1. `build` assemble l'archive à partir de `git archive HEAD`, sans les dossiers de développement (`tests`, `docs`, `VAE`, `front`, configuration des agents…). La documentation construite est copiée dans `public/docs/`.
2. `deploy` (environnement GitHub `production`) l'envoie par `rsync --delete` en conservant sur le serveur `.env`, `var/` et `public/uploads/`, puis lance `doctrine:migrations:migrate` et reconstruit `var/cache/prod`.
3. Le mot de passe SSH est le secret `OVH_SSH_PASSWORD` ; l'empreinte du serveur est dans `.github/ovh_known_hosts`. Le `.env` de production se crée sur le serveur, jamais dans le dépôt.

Le site répond sur `https://mediateur.varascundo.com/spa` (contrôle `curl` du 2026-10-30). <Status is="prod" />

### Documentation publiée

Le site VitePress est construit avec `base: '/docs/'` et `cleanUrls`. Les règles Apache (`public/.htaccess` et `.htaccess` racine, pour les deux racines web possibles) servent `/docs/` et ses pages profondes sans extension `.html` (`/docs/tests/` → `tests/index.html`, `/docs/fonctionnalites/edition` → `fonctionnalites/edition.html`) ; `/docs` redirige vers `/docs/`, une page inconnue retombe sur Symfony.

- Règles vérifiées le 2026-10-30 sur Apache 2.4 (image `httpd:2.4`) avec le build local copié sous `public/docs/`, racine `public/` et racine du dépôt : racine, page profonde, URL propre et feuille de style servies, redirection `/docs` en 301. <Status is="local" />
- Contrôle en production le 2026-10-30, après le déploiement du run 41056420072 :
  - `/docs/`, `/docs/tests/`, `/docs/fonctionnalites/edition`, `/docs/conception/reprise-devdoc` et `/docs/vae/` répondent 200 ;
  - feuille de style et script servis ;
  - `/docs` redirige en 301 ;
  - une page inconnue reçoit la 404 de Symfony ;
  - navigation par clic dans le navigateur, sans erreur console.

  <Status is="prod" />

## Service de capture Netcup

[`deploy/netcup-capture/docker-compose.yml`](../../deploy/netcup-capture/docker-compose.yml) lance `capture-service/` derrière Traefik, sur `capture.mediateur.varascundo.com`. Le site OVH l'appelle en HTTPS avec `CAPTURE_SERVICE_URL` et `CAPTURE_SERVICE_TOKEN` (même jeton des deux côtés, généré par `openssl rand -hex 32`). Vérifier `GET /health` puis une capture réelle après chaque mise à jour. <Status is="code" />

## Variables d'environnement

Modèle sans secret : [`docs/env.example`](../env.example). En production, fixer aussi `MAILER_DSN` (Brevo), `MAILER_FROM` et `MCP_PUBLIC_URL`.

## Exécution locale

Voir le [harness](../harness/index.md#démarrage) pour le démarrage Docker et les commandes de contrôle.
