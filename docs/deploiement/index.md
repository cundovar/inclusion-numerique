# Déploiement et CI

## Pipeline GitHub Actions

Le workflow [`.github/workflows/ci.yml`](../../.github/workflows/ci.yml) s'exécute sur les pull requests vers `main` et les pushes sur `main`. Il valide PHP, MySQL/migrations, Vue et VitePress, puis construit l'archive de production. Le déploiement OVH est **manuel** : depuis la branche `main`, lancer le workflow et cocher `deploy`.

| Job | Rôle | Quand |
|---|---|---|
| `php` | Syntaxe PHP, lint Symfony/Twig/YAML, PHPStan et PHPUnit | PR et push sur `main` |
| `migrations` | Montée, validation, retour arrière et remontée sur MySQL 8 de CI | PR et push sur `main` |
| `front` | ESLint, Vitest et build Vue | PR et push sur `main` |
| `docs` | Build VitePress, avec échec sur lien interne mort | PR et push sur `main` |
| `build` | Archive de production avec dépendances PHP, build Vue et documentation | PR et push sur `main` ; lancement manuel avec `deploy=true` |
| `deploy` | Envoi à OVH et reconstruction du cache, sans migration | Lancement manuel depuis `main` avec `deploy=true` seulement |

Les runs [42204743070](https://github.com/cundovar/inclusion-numerique/actions/runs/42204743070) et [42205300321](https://github.com/cundovar/inclusion-numerique/actions/runs/42205300321) (2026-11-06) ont validé PHP, migrations MySQL, Vue, VitePress et le build de release. Le second a tenté le déploiement après fusion de la PR #1, mais l'authentification SSH a échoué car le job ne recevait pas `SSHPASS`. La PR #2 corrige l'environnement et rend le déploiement manuel. Son run [42206685700](https://github.com/cundovar/inclusion-numerique/actions/runs/42206685700) et le run post-fusion sur `main` [42206988450](https://github.com/cundovar/inclusion-numerique/actions/runs/42206988450) ont validé tous les contrôles et le build ; `deploy` a été ignoré dans les deux cas. <Status is="ci" />

## État actuel d'OVH

Le dossier cible est `/home/egflbug/inclusion-numerique`. Le 2026-11-06, l'ancien code applicatif a été retiré de ce dossier en conservant `.env`, `.ovhconfig`, `var/` et le lien `public/uploads`. La base de données n'a pas été modifiée. Le nouveau dépôt n'a pas encore été déployé.

Contrôle en lecture seule après le nettoyage : la racine `https://inclusion-numerique.varascundo.com/` répond 404 car `public/index.php` n'est pas encore déployé ; `/.env` répond 403. Le `.htaccess` racine désactive le listing et bloque l'accès HTTP aux fichiers `.env*`. <Status is="prod" />

## Procédure du déploiement manuel

1. Vérifier que la PR de synchronisation des deux commits récents de `main2` est fusionnée et que la CI est verte avant de choisir la version à déployer.
2. Dans GitHub, lancer « CI/CD » sur `main` avec `deploy` coché. Le job reconstruit l'archive, puis l'envoie par `rsync --delete` dans `/home/egflbug/inclusion-numerique`.
3. `rsync` conserve sur OVH `.env`, `.env.*`, `.ovhconfig`, `var/` et le lien `public/uploads`. Le job supprime `var/cache/prod` et exécute `cache:warmup`.
4. Vérifier les pages du site et les ressources après déploiement. Aucune migration de production n'est lancée par ce workflow.

Le mot de passe SSH est le secret `OVH_SSH_PASSWORD` de l'environnement GitHub `production` (**Settings → Environments → production → Environment secrets**). Le workflow doit référencer le même nom avec `environment: production`. L'empreinte SSH est dans [`.github/ovh_known_hosts`](../../.github/ovh_known_hosts). Le `.env` de production reste sur OVH, jamais dans le dépôt ni dans GitHub Actions.

### Base et fichiers partagés

Le site a historiquement utilisé la base partagée avec mediateur. Le workflow n'exécute aucune migration : les changements de schéma doivent être coordonnés avec le déploiement de mediateur. Le contenu exact du `.env` OVH n'a pas été lu pendant la préparation de cette PR.

`public/uploads` est un lien symbolique vers `/home/egflbug/mediateur/public/uploads`. L'exclusion `--exclude='public/uploads'` sans barre finale est nécessaire pour que `rsync --delete` préserve ce lien.

## Publication VitePress

La documentation est construite avec `base: '/docs/'` et `cleanUrls`, puis copiée dans `public/docs/`. Les règles Apache `public/.htaccess` et `.htaccess` racine servent les pages profondes sans extension `.html` et redirigent `/docs` vers `/docs/`. Le build VitePress de la PR #2 a réussi dans le run 42206685700 ; la publication sur le domaine attend un déploiement. <Status is="ci" />

## Service de capture Netcup

Le service de capture (`capture.mediateur.varascundo.com`) appartient à mediateur et n'est appelé que par son connecteur. Ce site n'a plus de moteur de capture ni de variables `CAPTURE_*`.

## Variables d'environnement

Modèle sans secret : [`docs/env.example`](../env.example). En production, le `.env` OVH contient les réglages applicatifs. Selon la configuration retenue, prévoir `MAILER_DSN`, `MAILER_FROM`, `MCP_PUBLIC_URL` et `SITE_NAME`.

## Exécution locale

Voir le [harness](../harness/index.md#démarrage) pour le démarrage Docker et les commandes de contrôle.
