# Pipeline CI/CD GitHub Actions

Le workflow `.github/workflows/ci.yml` valide les pull requests vers `main`,
`deploy` et `main2`, ainsi que chaque push sur ces branches. Les controles PHP, frontend,
tests et securite s'executent en parallele apres l'installation des dependances.
Une couverture Clover et les assets de production sont conserves 14 jours dans
les artifacts GitHub Actions.

Le job `QCM results service` installe le micro-service `services/qcm-results`
et lance ses tests contre un conteneur de service MongoDB 7.0.25. Il est bloquant
(verifie par le rapport). Le dossier `services/` est exclu de l'archive de
production (`.gitattributes`, `export-ignore`) : ce service se deploie sur le
VPS, pas sur OVH.

Le build Vite produit les assets Vue dans `public/build` et la PWA dans
`public/spa`. Le build VitePress compile `docs/` puis ajoute le site statique
dans `public/docs` ; après déploiement de cette configuration, il sera
accessible à l'adresse
[`https://devdoc.varascundo.com/docs/`](https://devdoc.varascundo.com/docs/).
Le préfixe `/docs/` est défini dans `docs/.vitepress/config.mjs` et le `.htaccess`
du projet transmet les chemins de fichiers et de dossiers publics à `public/`.

La syntaxe PHP, PHPUnit et les builds sont bloquants. PHPStan et les audits de
securite sont informatifs pendant la remise a niveau de la codebase :
leurs erreurs apparaissent dans les jobs et dans le rapport, sans bloquer une
fusion ou un deploiement. Il suffira ensuite de retirer `continue-on-error` pour
les rendre bloquants.

Les variables applicatives necessaires a la compilation (`APP_ENV`, base SQLite,
mailer, messenger, CORS et variables Vite) ont des valeurs non sensibles definies
directement dans le workflow. Elles ne necessitent aucune configuration GitHub.

## Execution locale

```bash
composer install
composer analyse
composer test
npm ci --prefix front
npm run check --prefix front
```

Avec Docker (conteneurs `php` et `front` démarrés), le `Makefile` regroupe les
mêmes contrôles, plus ESLint : `make check-fast` (syntaxe PHP, PHPStan, ESLint)
et `make check` (ajoute PHPUnit, Vitest, les tests du micro-service
`qcm-results` et le build Vite ; conteneurs `qcm-results` et `qcm-mongo`
demarres par `make up`). En local, ces cibles sont
bloquantes, PHPStan compris. Le conteneur `php` utilise PHP 8.3 (`Dockerfile`)
alors que la CI et la release utilisent PHP 8.2.

PHPStan utilise `phpstan-baseline.neon` pour representer la dette existante. Les
nouvelles erreurs restent visibles dans le job informatif. La baseline ne doit
etre regeneree qu'apres revue des erreurs supprimees ou acceptees.

## Configuration du deploiement OVH

Creer l'environnement GitHub `production` et, de preference, activer une
validation obligatoire. Ajouter un seul secret a cet environnement :

| Secret | Contenu |
| --- | --- |
| `OVH_SSH_PASSWORD` | Mot de passe SSH du compte OVH `egflbug` |

GitHub construit la release complete avec PHP 8.2, Composer et Node 22. Il envoie
ensuite le code, `vendor`, `public/build`, `public/spa` et `public/docs` par rsync/SSH vers
`/home/egflbug/devdoc/prod`. La synchronisation conserve les fichiers `.env`,
`.ovhconfig`, `var`, `backups`, `.git` et `public/uploads` deja presents sur OVH.
Le cache Symfony de production est vide apres la synchronisation.

Un push valide sur `main` lance le deploiement. Le flux recommande est de
travailler sur `deploy`, ouvrir ou mettre a jour une pull request vers `main`,
puis merger quand les checks sont verts. Il peut aussi etre lance depuis
l'onglet Actions avec `Run workflow`, sur `main`, en cochant l'option de
deploiement.

### Second site : `main2` vers inclusion-numerique

La branche `main2` (DevDoc avec les connecteurs MCP du gabarit `app-neutre`) se
deploie sur un second site, sans toucher a la production de `main`. Le job
`Setup variables` associe chaque branche a son site :

| Branche | Environnement GitHub | Dossier OVH | Adresse |
| --- | --- | --- | --- |
| `main` | `production` | `/home/egflbug/devdoc/prod` | `https://devdoc.varascundo.com` |
| `main2` | `inclusion-numerique` | `/home/egflbug/inclusion-numerique` | `https://inclusion-numerique.varascundo.com` |

Un push sur `main2` lance les memes controles et la meme release, puis deploie
dans son dossier ; `Run workflow` sur `main2` avec l'option cochee fait de meme.
Le job de deploiement refuse tout autre dossier, car `rsync --delete` vide la
cible. Aucune autre branche ne deploie.

A preparer une seule fois, avant le premier deploiement :

- environnement GitHub `inclusion-numerique` avec le secret `OVH_SSH_PASSWORD` ;
- sur OVH, dans `/home/egflbug/inclusion-numerique` : un `.ovhconfig` en PHP 8.2
  (comme `/home/egflbug/devdoc/.ovhconfig`) et un `.env` propre au site, avec
  sa propre base de donnees et `MCP_PUBLIC_URL` sur la nouvelle adresse ;
- le multisite OVH pointe sur ce dossier (le `.htaccess` racine transmet a
  `public/`).

Le workflow ne lance pas les migrations : sur la base du second site, elles se
lancent a la main apres le premier deploiement
(`php bin/console doctrine:migrations:migrate --env=prod`).

Le deploiement rsync remplace les anciens `git pull` manuels sur OVH. Le depot
`.git` distant est conserve par securite, mais ne doit plus servir a mettre la
production a jour.

## Protection de branche

Dans les regles de protection de `main`, rendre obligatoires les checks `PHP
quality`, `Frontend quality`, `Unit tests`, `Security audit` et `Report` avant
fusion.
