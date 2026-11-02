# Déploiement et CI

## Pipeline

Workflow unique : [`.github/workflows/ci.yml`](../../.github/workflows/ci.yml) (« CI/CD »), sur chaque pull request, chaque push sur `main2` et à la demande. Cette copie du code de mediateur vit dans le dépôt DevDoc (`cundovar/vue-symfony`), branche `main2`, et se déploie sur le site inclusion-numerique (voir [plus bas](#site-inclusion-numerique-branche-main2)).

| Job | Rôle | Quand |
|---|---|---|
| `php` | Syntaxe PHP, lint conteneur/Twig/YAML, PHPStan, PHPUnit | Toujours |
| `migrations` | Migrations sur MySQL 8.0 : montée, validation, retour arrière, remontée | Toujours |
| `front` | ESLint, Vitest, build Vite | Toujours |
| `docs` | Build VitePress de `docs/` (échoue sur un lien interne mort) | Toujours |
| `build` | Archive de production : `composer install --no-dev`, build Vue, documentation copiée dans `public/docs/` | Push sur `main2` |
| `deploy` | Envoi sur OVH dans `/home/egflbug/inclusion-numerique`, reconstruction du cache, sans migration | Après `build` |

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

## Site inclusion-numerique (branche `main2`)

Inclusion-numerique affiche le contenu de mediateur avec son propre nom : même code, même base, mêmes images.

| Élément | Réglage | Pourquoi |
|---|---|---|
| Code | Copie de `cundovar/mediateur-numerique-formation` (`c87941f`), branche `main2` de `cundovar/vue-symfony` | Le front de mediateur est fait pour ces données |
| Déploiement | Job `deploy`, environnement GitHub `inclusion-numerique`, dossier `/home/egflbug/inclusion-numerique` ; tout autre dossier est refusé | `rsync --delete` vide la cible |
| Base | `egflbugcundo6`, celle de mediateur | Contenu commun |
| Migrations | Aucune au déploiement | Le schéma de la base partagée évolue par le déploiement de mediateur |
| Schéma | `DOCTRINE_SCHEMA_FILTER` (défaut dans [`config/services.yaml`](../../config/services.yaml)) ignore les 14 tables DevDoc ajoutées dans la base partagée (`appy_Exo*`, `appy_Note`, `revision`, `diaporama`…) | Doctrine ne propose jamais de les supprimer |
| Nom du site | `SITE_NAME`, défaut « Inclusion numérique » ; l'emporte sur `siteName` de la configuration en base (`DoctrineSiteConfigurationRepository::getDefaultSettings()`), et sert aux gabarits Twig (`site_name`), au SEO, aux e-mails et aux PDF | La configuration en base est partagée : la modifier changerait aussi le nom de mediateur |
| Images | `public/uploads` est un lien symbolique vers `/home/egflbug/mediateur/public/uploads` | Les cours de la base pointent vers ces fichiers ; une capture ajoutée depuis l'un des deux sites s'affiche sur l'autre |
| Connecteur MCP | Aucun sur ce site (code retiré) ; on utilise celui de mediateur, `https://mediateur.varascundo.com/mcp` | Base et images partagées : ses modifications s'appliquent aux deux sites. Voir [Connecteur MCP](../fonctionnalites/connecteur.md) |
| Adresse publique | `MCP_PUBLIC_URL=https://inclusion-numerique.varascundo.com` (nom conservé) | Liens envoyés par e-mail (mot de passe oublié) |

Le lien des images se crée une fois sur le serveur. `rsync` exclut `public/uploads`, sans barre finale : avec `public/uploads/`, l'exclusion ne protège qu'un vrai dossier, et `--delete` a supprimé le lien au déploiement du 2026-11-02 (images en 404 jusqu'à sa recréation) :

```bash
ln -s ~/mediateur/public/uploads ~/inclusion-numerique/public/uploads
```

Vérifié en production le 2026-11-02 :

- run [41430174319](https://github.com/cundovar/vue-symfony/actions/runs/41430174319) (`95d47ac0`) : contrôles PHP, migrations, capture, front, documentation et build réussis, puis déploiement ;
- lien `public/uploads` créé, deux images de cours qui répondaient 404 répondent 200.

<Status is="prod" />

## Service de capture Netcup

Le service de capture (`capture.mediateur.varascundo.com`) appartient à mediateur et n'est appelé que par son connecteur. Ce site n'a plus de moteur de capture ni de variables `CAPTURE_*`.

## Variables d'environnement

Modèle sans secret : [`docs/env.example`](../env.example). En production, fixer aussi `MAILER_DSN` (Brevo), `MAILER_FROM`, `MCP_PUBLIC_URL` (adresse publique du site) et, au besoin, `SITE_NAME`.

## Exécution locale

Voir le [harness](../harness/index.md#démarrage) pour le démarrage Docker et les commandes de contrôle.
