# DevDoc — plateforme pédagogique Symfony/Vue

DevDoc est une plateforme pédagogique composée d’un backend Symfony et d’une
application frontend Vue. Le dépôt regroupe le code applicatif, la
configuration Docker, les tests, la documentation technique et le dossier VAE
CDA.

## Stack technique

- **Backend** : PHP 8.1+, Symfony 6.4, Doctrine ORM, API Platform et MySQL 8.
- **Frontend** : Vue 3, Pinia, Vue Router, Vite, Tailwind CSS et PWA.
- **Qualité** : PHPUnit, PHPStan et ESLint.
- **Développement local** : Docker Compose, PHP-FPM, Nginx, MySQL,
  phpMyAdmin et Vite.

## Prérequis

- Git
- Docker avec le plugin Docker Compose
- GNU Make

Le fichier `.env` utilisé par Docker doit être présent à la racine. Utilisez
`.env.exemple` et `.env.local.example` comme références pour préparer une
configuration locale, sans copier de secrets dans Git.

## Démarrage rapide

Depuis la racine du dépôt :

```bash
make up
make composer
make npm-install
```

L’application est ensuite disponible sur :

| Service | Adresse |
| --- | --- |
| Application via Nginx | <http://localhost:8092> |
| Serveur Vite | <http://localhost:5173> |
| phpMyAdmin | <http://localhost:8091> |
| Storybook | <http://localhost:6006> |
| MySQL depuis l’hôte | `localhost:3308` |

Le port HTTP principal peut être changé avec `HOST_HTTP_PORT`.

Vérifiez l’état des conteneurs avec :

```bash
docker compose ps
```

## Commandes courantes

### Docker et développement

```bash
make up             # Construire et démarrer les services
make dev            # Démarrer l’environnement de développement
make logs           # Suivre les logs
make bash           # Ouvrir un shell PHP
make front-bash     # Ouvrir un shell frontend
make down           # Arrêter les conteneurs, conserver les volumes
make restart        # Arrêter puis redémarrer l’environnement
```

`make down-volumes` supprime aussi le volume MySQL et donc les données locales.
Ne l’utilisez que volontairement.

### Symfony et base de données

```bash
make symfo          # Lancer une commande bin/console interactive
make migrate        # Exécuter les migrations Doctrine
```

Le schéma de base de données doit évoluer exclusivement avec des migrations
Doctrine. Une migration déjà commitée ne doit pas être modifiée : créez-en une
nouvelle.

### Validation

Les conteneurs `php` et `front` doivent être démarrés avant ces commandes :

```bash
make check-fast     # Syntaxe PHP, PHPStan et ESLint
make check          # check-fast, PHPUnit et build Vite
```

`make check` est la validation complète attendue avant de considérer une tâche
technique comme terminée.

## Organisation du dépôt

```text
src/                 Backend Symfony (entités, contrôleurs, services)
config/              Configuration Symfony et Doctrine
migrations/          Migrations Doctrine
tests/               Tests PHPUnit
front/src/           Application Vue
public/              Ressources publiques et builds frontend
compose.yaml         Services Docker de développement
Makefile             Commandes de développement et de validation
docs/                Documentation de conception et de CI/CD
documentations/      Guides techniques détaillés
VAE/                 Dossier documentaire VAE CDA
.agents/             Hooks et skills partagés entre les agents
.claude/             Configuration Claude Code
.codex/              Hooks et règles Codex du projet
```

## Documentation

- [Documentation technique](documentations/README.md)
- [Guide d’installation détaillé](documentations/INSTALLATION.md)
- [Documentation frontend](documentations/README-front.md)
- [CI/CD et qualité](docs/CI_CD.md)
- [Dossier VAE CDA](VAE/README.md)
- [Règles de contribution](documentations/CONTRIBUTING.md)

## Règles de contribution

Les consignes communes sont dans [AGENTS.md](AGENTS.md). Les règles
spécifiques au backend et au frontend sont respectivement dans
[`src/AGENTS.md`](src/AGENTS.md) et [`front/AGENTS.md`](front/AGENTS.md).

Avant de livrer une modification :

1. préserver les fichiers locaux et les secrets existants ;
2. lancer `make check` et traiter tout échec ;
3. mettre à jour la documentation concernée après validation du code ;
4. ne pas pousser de branche ni lancer une migration sans accord explicite.

Les hooks configurés dans `.codex/hooks.json` et `.claude/settings.json`
appliquent automatiquement une partie de ces contrôles pendant les sessions
Codex et Claude Code.
