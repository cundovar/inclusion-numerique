# Harness Codex et Claude Code

Ce projet reprend la structure du harness de DevDoc, adaptée à un dépôt indépendant : consignes partagées, protections automatiques et boucle de validation. Il ne réutilise pas les règles VAE de DevDoc. Le code applicatif a été repris de DevDoc puis allégé (voir [la reprise de DevDoc](../conception/reprise-devdoc.md)).

## 1. Consignes partagées

- `AGENTS.md` définit le périmètre, les priorités, les règles de contenu et les preuves attendues.
- `CLAUDE.md` importe `@AGENTS.md` ; les deux assistants reçoivent les mêmes consignes.
- `PLAN_APP_FORMATION_MEDIATEUR_NUMERIQUE.md` garde les décisions produit détaillées.

## 2. Protections automatiques

| Hook | Moment | Effet |
|---|---|---|
| `.agents/hooks/lib.sh` | — | Extrait les fichiers ciblés des appels `Write`, `Edit`, `NotebookEdit` et `apply_patch` |
| `.agents/hooks/protect-files.sh` | Avant écriture | Refuse un `.env` réel, une sauvegarde, une base locale, un chemin hors du projet, une migration déjà commitée et une preuve archivée `VAE/evidence/` ; les exemples `.env.example` restent autorisés |
| `.agents/hooks/lint-file.sh` | Après écriture | `php -l` (conteneur `php`) ou ESLint (conteneur `front`) sur le fichier modifié, si le conteneur tourne ; pose le marqueur `.agents/.needs-check` pour tout changement de code ou de configuration |
| `.agents/hooks/stop-check.sh` | Fin de tâche | Tant que le marqueur existe, demande `make check`, la mise à jour de `docs/` et, si la preuve VAE change, le skill `vae-documentation-sync` |

`make check` efface le marqueur seulement si tous ses contrôles réussissent (fichier ignoré par Git).

- `.claude/settings.json` active les trois hooks côté Claude Code, autorise les cibles de contrôle (`make check`, `make docs-build`…) et bloque la lecture des fichiers sensibles, `docker compose down -v` et `make down-volumes`. `.codex/hooks.json` active les mêmes scripts côté Codex.
- `.codex/rules/project.rules` autorise les cibles de contrôle et encadre les commandes à risque. Ces règles ne remplacent pas les autorisations de l'environnement d'exécution.

## Skill documentaire

`.claude/skills/vae-documentation-sync/SKILL.md`, exposé à Codex par le lien symbolique `.agents/skills/vae-documentation-sync`. À la fin d'une tâche technique validée, il fait vérifier les pages `docs/` et les fichiers `VAE/` touchés par le changement, avec des niveaux de preuve (`code`, `local`, `ci`, `prod`, `prevu`) qui ne montent qu'avec la commande, le run CI ou le contrôle de production correspondant. Voir la section « Documentation VitePress et dossier VAE » de `AGENTS.md`.

Ces hooks ciblent les outils d'édition indiqués dans leur configuration. Ils ne protègent pas les écritures indirectes exécutées par un shell, un connecteur ou un programme. Les secrets restent exclus du dépôt par `.gitignore` et ne doivent jamais être copiés depuis DevDoc.

## 3. Boucle de validation

À chaque tâche : constater les fichiers et l'état Git disponibles, modifier le périmètre demandé, contrôler les fichiers touchés et expliquer les preuves obtenues.

### Configuration locale

Symfony exige un fichier `.env` à la racine. Le modèle est `docs/env.example` (`cp docs/env.example .env`). Il n'est pas à la racine parce que les règles de `.claude/settings.json` interdisent aux agents de lire `./.env.*`. Les agents ne créent pas le `.env` du projet : ils vérifient dans une copie de travail, comme la CI qui écrit son propre `.env` de test.

### Démarrage

Avec Docker (ports choisis pour ne pas entrer en conflit avec DevDoc) :

```bash
make up && make composer && make npm-install && make migrate
docker compose exec php php bin/console app:create-admin <nom>   # compte du back-office
```

Assistant de rédaction : pas de connecteur sur ce site, on utilise celui de mediateur (voir [le connecteur](../fonctionnalites/connecteur.md)).

Site : <http://localhost:8094/spa> ; back-office : <http://localhost:8094/admin> ; phpMyAdmin : <http://localhost:8095> ; MySQL hôte : `localhost:3310`.

Sans Docker, avec PHP 8.2+ et SQLite (`DATABASE_URL` SQLite dans `.env`) :

```bash
composer install && npm ci --prefix front
php bin/console doctrine:schema:create      # ou doctrine:migrations:migrate sur MySQL
php bin/console app:create-admin <nom>
./build-assets.sh                           # build Vite dans public/build et public/spa
php -S 127.0.0.1:8000 -t public             # le serveur intégré a besoin d'un routeur vers public/index.php pour /spa
```

### Contrôles

| Contrôle | Commande | Make (Docker) |
| --- | --- | --- |
| Syntaxe PHP | `find src tests migrations -name '*.php' -print0 \| xargs -0 -n1 php -l` | `make lint-php` |
| Conteneur, Twig, YAML | `php bin/console lint:container`, `lint:twig templates`, `lint:yaml config` | — |
| PHPStan (niveau 5, baseline) | `composer analyse` | `make analyse` |
| PHPUnit (SQLite jetable) | `composer test` | `make test` |
| Migrations MySQL | `doctrine:migrations:migrate`, puis `doctrine:schema:validate` | `make migrate` |
| ESLint | `npm run lint --prefix front` | `make lint-front` |
| Vitest | `npm test --prefix front` | `make test-front` |
| Build Vite | `npm run check --prefix front` | `make build-front` |
| Documentation VitePress | `npm ci --prefix docs && npm run build --prefix docs` | `make docs-build` |

`make check-fast` enchaîne syntaxe PHP, PHPStan et ESLint ; `make check` y ajoute PHPUnit, Vitest et le build, puis efface le marqueur `.agents/.needs-check`. La CI (`.github/workflows/ci.yml`) exécute les mêmes contrôles, plus les migrations sur un MySQL 8.0 (montée, validation du schéma, retour arrière et remontée) et le build de la documentation.

`make docs-dev` sert la documentation sur <http://localhost:5176/docs/> (service Docker `docs`, profil `docs`, port `HOST_DOCS_PORT`). `make docs-build` échoue sur un lien interne mort.

Pour un parcours public, compléter par un essai dans le navigateur sur ordinateur et mobile, et en mode hors ligne. Ne pas présenter les tests de DevDoc comme une validation de cette application.

Dans un environnement où le proxy refuse les archives GitHub, `composer install --prefer-source` fonctionne, sauf PHPStan, qui n'est distribué qu'en archive ; le cloner alors en profondeur 1 depuis `github.com/phpstan/phpstan` au tag du `composer.lock` (le clone complet est trop lourd pour certains proxys).


### Déploiement en production

`.github/workflows/ci.yml` (« CI/CD ») déploie à chaque push sur `main`, une fois tous les contrôles verts :

1. `build` assemble l'archive : `composer install --no-dev`, build Vue dans `public/build` et `public/spa`, documentation VitePress dans `public/docs`, sans les dossiers de développement (`tests`, `docs`, `VAE`, `front`, configuration des agents…). Détail : [déploiement et CI](../deploiement/index.md).
2. `deploy` (environnement GitHub `production`) l'envoie sur l'hébergement OVH par `rsync --delete`, en gardant sur le serveur `.env`, `var/` et `public/uploads/` (captures déposées), puis lance `doctrine:migrations:migrate`, puis supprime `var/cache/prod` et le reconstruit avec `cache:warmup`.

Le mot de passe SSH est le secret `OVH_SSH_PASSWORD` ; l'empreinte du serveur est dans `.github/ovh_known_hosts`. Le `.env` de production se crée et se modifie directement sur le serveur, jamais dans le dépôt.

Un merge sur `main` passe donc en production, migrations comprises : vérifier les migrations sur MySQL (job `migrations`) avant de fusionner.

L'hébergement mutualisé OVH n'a ni Node ni Chromium ; il n'exécute donc jamais Playwright. Le service `deploy/netcup-capture/` est déployé sur Netcup, où Docker et le proxy Traefik sont présents. L'enregistrement DNS `A capture.mediateur.varascundo.com → 152.53.114.87` doit répondre avant le premier démarrage. Générer un jeton avec `openssl rand -hex 32`, le placer dans le `.env` Netcup, puis renseigner la même valeur et `CAPTURE_SERVICE_URL=https://capture.mediateur.varascundo.com` dans le `.env` OVH. Vérifier ensuite `GET /health` et une capture réelle, sans publier de données d'usager. Le `.env` de production doit aussi fixer `MCP_PUBLIC_URL` (adresse https du site) pour le connecteur.

Contrôles du harness lui-même :

```bash
jq empty .claude/settings.json .codex/hooks.json
bash -n .agents/hooks/*.sh
codex execpolicy check --pretty --rules .codex/rules/project.rules -- git push origin main
codex execpolicy check --pretty --rules .codex/rules/project.rules -- make docs-build
# Simuler un appel d'outil : le hook doit refuser l'écriture (permissionDecision "deny")
echo '{"cwd":"'"$PWD"'","tool_input":{"file_path":"VAE/evidence/x.md"}}' | bash .agents/hooks/protect-files.sh
```

## Activation

Ouvrir le dépôt comme projet de confiance dans chaque outil, puis redémarrer la session pour charger ses réglages. Dans Claude Code, vérifier les fichiers chargés avec `/context`. Pour Codex, vérifier que les hooks et règles du projet sont effectivement pris en compte. La présence des fichiers seule ne prouve pas leur activation.
