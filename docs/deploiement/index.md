# Déploiement et CI

## Pipeline GitHub Actions

Le workflow [`.github/workflows/ci.yml`](../../.github/workflows/ci.yml) s'exécute sur les pull requests vers `main` et les pushes sur `main`. Il valide PHP, MySQL/migrations, Vue et VitePress, puis construit l'archive de production. Le déploiement OVH est **automatique** à chaque push sur `main`, donc à chaque fusion d'une PR, si tous les contrôles passent. Lancer le workflow à la main avec `deploy` coché reste possible pour redéployer sans nouveau commit. Premier déploiement automatique : run [42236440873](https://github.com/cundovar/inclusion-numerique/actions/runs/42236440873), fusion de la PR #9 (`6027dea`), jobs `build` et `deploy` réussis, site contrôlé ensuite. <Status is="prod" />

| Job | Rôle | Quand |
|---|---|---|
| `php` | Syntaxe PHP, lint Symfony/Twig/YAML, PHPStan et PHPUnit | PR et push sur `main` |
| `migrations` | Montée, validation, retour arrière et remontée sur MySQL 8 de CI | PR et push sur `main` |
| `front` | ESLint, Vitest et build Vue | PR et push sur `main` |
| `docs` | Build VitePress, avec échec sur lien interne mort | PR et push sur `main` |
| `build` | Archive de production avec dépendances PHP, build Vue et documentation | PR et push sur `main` ; relance manuelle avec `deploy=true` |
| `deploy` | Envoi à OVH et reconstruction du cache, sans migration | Push sur `main` après réussite des contrôles ; relance manuelle avec `deploy=true` |

Les runs [42204743070](https://github.com/cundovar/inclusion-numerique/actions/runs/42204743070) et [42205300321](https://github.com/cundovar/inclusion-numerique/actions/runs/42205300321) (2026-11-06) ont validé PHP, migrations MySQL, Vue, VitePress et le build de release. Le second a tenté le déploiement après fusion de la PR #1, mais l'authentification SSH a échoué car le job ne recevait pas `SSHPASS`. La PR #2 corrige l'environnement et rend le déploiement manuel. Son run [42206685700](https://github.com/cundovar/inclusion-numerique/actions/runs/42206685700) et le run post-fusion sur `main` [42206988450](https://github.com/cundovar/inclusion-numerique/actions/runs/42206988450) ont validé tous les contrôles et le build ; `deploy` a été ignoré dans les deux cas. <Status is="ci" />

Le run [42217518469](https://github.com/cundovar/inclusion-numerique/actions/runs/42217518469) (PR #5, commit `6b566b8`, 2026-11-06) a validé PHP (180 tests, 926 assertions), migrations MySQL, moteur de capture, Vue, VitePress et le build de release, avec le connecteur repris ; `deploy` a été ignoré. <Status is="ci" />

## État actuel d'OVH

Le dossier cible est `/home/egflbug/inclusion-numerique`. Le connecteur y a été déployé le 2026-11-06 par le lancement manuel [42219628118](https://github.com/cundovar/inclusion-numerique/actions/runs/42219628118) (`da91cd0`), puis la version `cd95e06` par [42232789812](https://github.com/cundovar/inclusion-numerique/actions/runs/42232789812) (correction de la page vide et service de capture en sous-module), tous deux lancés à la main, avant le passage au déploiement automatique. Aucune migration n'a été jouée. <Status is="ci" />

Contrôle du site le 2026-11-06, par `curl` et dans Chrome : <Status is="prod" />

| Adresse | Réponse |
|---|---|
| `/` | 302 vers `/spa` |
| `/spa/`, `/docs/` | 200 |
| `/.env` | 403 |
| `/mcp` sans jeton, ou avec un jeton inventé | 401, avec l'adresse des métadonnées OAuth |
| `/.well-known/oauth-protected-resource`, `/.well-known/oauth-authorization-server` | Adresses d'Inclusion |
| `/public/spa/` | Le routeur ramène à `/spa/`, l'accueil s'affiche |

Le 404 relevé à la racine avant ce déploiement ne vaut donc plus.

### Page vide après une ancienne redirection

Un navigateur pouvait arriver sur une page vide à `/spa/public/spa/`. Le serveur répondait correctement, mais Chrome gardait en cache une redirection de `/spa` vers `/public/spa/`, dont l'origine n'est pas établie. Le routeur corrige désormais ces adresses ([`ancienneAdressePublic.js`](../../front/src/router/ancienneAdressePublic.js)). Une redirection côté serveur aurait pu boucler avec ce cache.

## Procédure de déploiement

::: warning Fusionner, c'est mettre en ligne
Une entité modifiée a besoin de sa migration dans la base commune, jouée par le déploiement de Médiateur : ce workflow n'en lance aucune. Coordonner les deux avant de fusionner. Seules les PR annulent leur run précédent : un push sur `main` ne coupe pas un déploiement en cours, il attend son tour.
:::

1. Fusionner la PR dans `main`.
2. Le workflow relance les contrôles ; s'ils passent, il reconstruit l'archive, puis l'envoie par `rsync --delete` dans `/home/egflbug/inclusion-numerique`.
3. `rsync` conserve sur OVH `.env`, `.env.*`, `.ovhconfig`, `var/` et `public/uploads`. Le job supprime `var/cache/prod` et exécute `cache:warmup`.
4. Vérifier les pages du site et les ressources après déploiement. Aucune migration de production n'est lancée par ce workflow.

Le mot de passe SSH est le secret `OVH_SSH_PASSWORD` de l'environnement GitHub `production` (**Settings → Environments → production → Environment secrets**). Le workflow doit référencer le même nom avec `environment: production`. L'empreinte SSH est dans [`.github/ovh_known_hosts`](../../.github/ovh_known_hosts). Le `.env` de production reste sur OVH, jamais dans le dépôt ni dans GitHub Actions.

### Base et fichiers partagés

Le site a historiquement utilisé la base partagée avec mediateur. Le workflow n'exécute aucune migration : les changements de schéma doivent être coordonnés avec le déploiement de mediateur. Le contenu du `.env` OVH n'a pas été lu.

L'exclusion `--exclude='public/uploads'` sans barre finale protège ce chemin, qu'il soit un lien ou un dossier ; `var/`, exclu aussi, garde la copie de préparation.

## Connecteur MCP et images

Inclusion reprend le connecteur de Médiateur et a son propre dossier d'images, pour pouvoir continuer après l'arrêt de Médiateur. La base reste commune pendant la transition. **Ne pas arrêter Médiateur tant que les [points ouverts](#points-ouverts) ne sont pas levés.**

### Contrôles avant déploiement

| Contrôle | Pourquoi |
|---|---|
| Sauvegarde de la base | Le code du connecteur écrit dans la base commune. Faite par le candidat avant le déploiement ; restauration non essayée |
| `deploy/uploads-inclusion.sh inventory` | Fichiers, volume, liens, espace libre, images manquantes, URL absolues en base. Lecture seule. Résultat ci-dessous |
| Présence de `oauth_client`, `oauth_grant`, `oauth_authorization_code` et `qcm_draft` | Ce site ne joue aucune migration : le schéma commun vient de Médiateur (`c87941f`, mêmes migrations). Aucune migration n'a été ajoutée |
| `.env` OVH : `MCP_PUBLIC_URL=https://inclusion-numerique.varascundo.com` | Adresse annoncée aux connecteurs |
| `.env` OVH : `CAPTURE_SERVICE_URL`, `CAPTURE_SERVICE_TOKEN` | Sans eux, `capturer_page` échoue : OVH n'a ni Node ni Chromium. Le reste du connecteur fonctionne |

Résultat de `inventory` le 2026-11-06, lancé par le candidat : 91 fichiers (107 Mo, dossier `course-media`), 91 médias en base, 0 manquant, 0 lien, aucune URL absolue vers `/uploads`. Le script ne compte pas les noms lus en base : le contrôle de l'API ci-dessous le complète. <Status is="prod" />

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

### Résultat de la bascule

Le 2026-11-06, le candidat a lancé `switch` sur le serveur et transmis sa sortie : « Copie conforme : 91 fichiers, 107M », « Bascule faite ». `ls -ld public/uploads` montre un dossier et non plus un lien, avec 91 fichiers. <Status is="prod" />

Contrôle depuis l'extérieur après la bascule : les 91 images listées par `/api/captures` répondent 200 sur Inclusion, avec le même contenu que sur Médiateur (SHA-256) ; les PDF de `utiliser-le-clavier` et `utiliser-la-souris` répondent 200 et contiennent respectivement 2 et 3 images. Ces PDF étaient peut-être déjà en cache. <Status is="prod" />

Un premier contrôle identique, fait avant la bascule réelle, passait par l'ancien lien : seul le contrôle du dossier sur le serveur distingue les deux états.

### Connexion réelle du connecteur

Le 2026-11-06, le candidat a connecté le connecteur par OAuth avec son compte administrateur. Une lecture réelle (`lister_catalogue_qcm`) a répondu avec l'adresse d'Inclusion : le jeton et l'en-tête `Authorization` arrivent donc jusqu'à PHP. Aucune écriture n'a été essayée. <Status is="prod" />

### Points ouverts

| À vérifier ou corriger | Moyen |
|---|---|
| Jetons de Médiateur refusés sur Inclusion, connecteur de Médiateur toujours utilisable | Essai réel ; pour l'instant, seulement les tests automatiques (<Status is="ci" />) |
| Image créée après la bascule, capture par le service Netcup | Essai depuis le back-office et le connecteur |
| PWA hors ligne avec les images déplacées, parcours sur mobile | Essai dans un navigateur |
| `/sitemap.xml` annonce des adresses en `http://localhost:8080` | Anomalie constatée le 2026-11-06, à corriger |
| Le site répond aussi sous `/public/...` (par exemple `/public/spa/`, `/public/build/...`) | Adresses en double, à fermer sans boucler avec les caches |

Reconnecter les autres clients, puis arrêter Médiateur seulement après ces contrôles.

## Publication VitePress

La documentation est construite avec `base: '/docs/'` et `cleanUrls`, puis copiée dans `public/docs/`. Les règles Apache `public/.htaccess` et `.htaccess` racine servent les pages profondes sans extension `.html` et redirigent `/docs` vers `/docs/`. Le build VitePress de la PR #2 a réussi dans le run 42206685700 ; la publication sur le domaine attend un déploiement. <Status is="ci" />

## Service de capture Netcup

Le service isolé (`capture.mediateur.varascundo.com`) a son propre dépôt, [`capture-service`](https://github.com/cundovar/capture-service), qui porte sa recette Docker, sa CI et sa procédure de déploiement. Ce site n'en garde qu'un sous-module pour le développement : il n'est pas envoyé sur OVH, qui n'a ni Node ni Chromium. Inclusion l'appelle en HTTPS avec `CAPTURE_SERVICE_URL` et `CAPTURE_SERVICE_TOKEN`, sans modification du service. Ne pas arrêter ce service en même temps que Médiateur tant qu'Inclusion s'en sert.

## Variables d'environnement

Modèle sans secret : [`docs/env.example`](../env.example). En production, le `.env` OVH contient les réglages applicatifs. Selon la configuration retenue, prévoir `MAILER_DSN`, `MAILER_FROM`, `MCP_PUBLIC_URL`, `SITE_NAME`, `CAPTURE_SERVICE_URL` et `CAPTURE_SERVICE_TOKEN`.

## Exécution locale

Voir le [harness](../harness/index.md#démarrage) pour le démarrage Docker et les commandes de contrôle.
