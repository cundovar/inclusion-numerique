# Déploiement et CI

## Pipeline GitHub Actions

Le workflow [`.github/workflows/ci.yml`](../../.github/workflows/ci.yml) s'exécute sur les pull requests vers `main` et les pushes sur `main`. Il valide PHP, MySQL/migrations, Vue et VitePress, puis construit l'archive de production. Le déploiement OVH est **manuel** : depuis la branche `main`, lancer le workflow et cocher `deploy`.

| Job | Rôle | Quand |
|---|---|---|
| `php` | Syntaxe PHP, lint Symfony/Twig/YAML, PHPStan et PHPUnit | PR et push sur `main` |
| `migrations` | Montée, validation, retour arrière et remontée sur MySQL 8 de CI | PR et push sur `main` |
| `capture` | Moteur de capture : syntaxe et tests sur un site local avec Chromium | PR et push sur `main` |
| `front` | ESLint, Vitest et build Vue | PR et push sur `main` |
| `docs` | Build VitePress, avec échec sur lien interne mort | PR et push sur `main` |
| `build` | Archive de production avec dépendances PHP, build Vue et documentation | PR et push sur `main` ; lancement manuel avec `deploy=true` |
| `deploy` | Envoi à OVH et reconstruction du cache, sans migration | Lancement manuel depuis `main` avec `deploy=true` seulement |

Les runs [42204743070](https://github.com/cundovar/inclusion-numerique/actions/runs/42204743070) et [42205300321](https://github.com/cundovar/inclusion-numerique/actions/runs/42205300321) (2026-11-06) ont validé PHP, migrations MySQL, Vue, VitePress et le build de release. Le second a tenté le déploiement après fusion de la PR #1, mais l'authentification SSH a échoué car le job ne recevait pas `SSHPASS`. La PR #2 corrige l'environnement et rend le déploiement manuel. Son run [42206685700](https://github.com/cundovar/inclusion-numerique/actions/runs/42206685700) et le run post-fusion sur `main` [42206988450](https://github.com/cundovar/inclusion-numerique/actions/runs/42206988450) ont validé tous les contrôles et le build ; `deploy` a été ignoré dans les deux cas. <Status is="ci" />

Le run [42217518469](https://github.com/cundovar/inclusion-numerique/actions/runs/42217518469) (PR #5, commit `6b566b8`, 2026-11-06) a validé PHP (180 tests, 926 assertions), migrations MySQL, moteur de capture, Vue, VitePress et le build de release, avec le connecteur repris ; `deploy` a été ignoré. <Status is="ci" />

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

`public/uploads` est encore un lien symbolique vers `/home/egflbug/mediateur/public/uploads`. L'exclusion `--exclude='public/uploads'` sans barre finale préserve aussi bien ce lien que le dossier propre qui le remplacera ; `var/`, exclu aussi, garde la copie de préparation.

## Connecteur MCP et images

Inclusion reprend le connecteur de Médiateur et aura son propre dossier d'images, pour pouvoir continuer après l'arrêt de Médiateur. La base reste commune pendant la transition. <Status is="prevu" />

### Avant de déployer

| Contrôle | Pourquoi |
|---|---|
| Sauvegarde de la base, restauration essayée sur une copie isolée | Le code du connecteur écrit dans la base commune |
| `deploy/uploads-inclusion.sh inventory` | Fichiers, volume, liens, espace libre, images manquantes, URL absolues en base. Lecture seule. **Pas encore exécuté** |
| Présence de `oauth_client`, `oauth_grant`, `oauth_authorization_code` et `qcm_draft` | Ce site ne joue aucune migration : le schéma commun vient de Médiateur (`c87941f`, mêmes migrations). Aucune migration n'a été ajoutée |
| `.env` OVH : `MCP_PUBLIC_URL=https://inclusion-numerique.varascundo.com` | Adresse annoncée aux connecteurs |
| `.env` OVH : `CAPTURE_SERVICE_URL`, `CAPTURE_SERVICE_TOKEN` | Sans eux, `capturer_page` échoue : OVH n'a ni Node ni Chromium. Le reste du connecteur fonctionne |

### Bascule des images

Le script [`deploy/uploads-inclusion.sh`](../../deploy/uploads-inclusion.sh) se lance sur le serveur, depuis `~/inclusion-numerique`. Il n'écrit jamais dans Médiateur ni en base.

1. `prepare` puis `verify` : copie dans `var/uploads-copie`, puis comparaison des nombres, tailles et empreintes.
2. Gel : plus aucun ajout d'image, ni sur Médiateur ni sur Inclusion (back-office et connecteurs).
3. `switch` : dernière copie, nouveau contrôle, puis le dossier remplace le lien. Le script refuse de continuer si la copie diffère.
4. Contrôler une image en page, une miniature, l'API, un PDF et la PWA hors ligne.
5. Fin du gel : les nouvelles images se déposent uniquement sur Inclusion.

Retour arrière : `rollback` remet le lien, conserve le dossier dans `var/uploads-avant-retour` et liste les images créées depuis la bascule, à recopier à la main.

::: warning Après la bascule
Les deux dossiers ne se synchronisent pas. La base étant commune, une image ajoutée sur Inclusion est référencée aussi sur Médiateur, où elle répond 404. Remplacer ou supprimer une capture sur Inclusion n'efface plus le fichier de Médiateur. C'est accepté : Inclusion devient la source principale, et Médiateur s'arrête après les contrôles.
:::

Les chemins `/uploads/course-media/<nom>` sont relatifs : aucune réécriture en base n'est prévue, sauf si l'inventaire trouve des URL absolues.

### Après le déploiement

1. Contrôler `/mcp` sans jeton (401), `/.well-known/oauth-authorization-server`, un parcours OAuth réel avec un compte administrateur et l'envoi de l'en-tête `Authorization` jusqu'à PHP.
2. Vérifier qu'un connecteur de Médiateur continue de fonctionner sur Médiateur, et que ses jetons sont refusés sur Inclusion.
3. Contrôler une image existante et une image créée après la bascule.
4. Reconnecter les clients sur Inclusion. Arrêter Médiateur seulement après ces contrôles.

## Publication VitePress

La documentation est construite avec `base: '/docs/'` et `cleanUrls`, puis copiée dans `public/docs/`. Les règles Apache `public/.htaccess` et `.htaccess` racine servent les pages profondes sans extension `.html` et redirigent `/docs` vers `/docs/`. Le build VitePress de la PR #2 a réussi dans le run 42206685700 ; la publication sur le domaine attend un déploiement. <Status is="ci" />

## Service de capture Netcup

Le service isolé ([`deploy/netcup-capture/`](../../deploy/netcup-capture/docker-compose.yml), `capture.mediateur.varascundo.com`) n'a pas changé. Inclusion l'appelle en HTTPS avec `CAPTURE_SERVICE_URL` et `CAPTURE_SERVICE_TOKEN`, sans modification du service. Ne pas arrêter ce service en même temps que Médiateur tant qu'Inclusion s'en sert.

## Variables d'environnement

Modèle sans secret : [`docs/env.example`](../env.example). En production, le `.env` OVH contient les réglages applicatifs. Selon la configuration retenue, prévoir `MAILER_DSN`, `MAILER_FROM`, `MCP_PUBLIC_URL`, `SITE_NAME`, `CAPTURE_SERVICE_URL` et `CAPTURE_SERVICE_TOKEN`.

## Exécution locale

Voir le [harness](../harness/index.md#démarrage) pour le démarrage Docker et les commandes de contrôle.
