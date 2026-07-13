# Pipeline CI/CD GitHub Actions

Le workflow `.github/workflows/ci.yml` valide les pull requests vers `main` et
`deploy`, ainsi que chaque push sur ces branches. Les controles PHP, frontend,
tests et securite s'executent en parallele apres l'installation des dependances.
Une couverture Clover et les assets de production sont conserves 14 jours dans
les artifacts GitHub Actions.

Le build EasyAdmin est execute en premier, puis Vite ajoute les assets Vue dans
`public/build` sans effacer ceux d'Encore. La PWA est produite dans `public/spa`.
Ces chemins correspondent a la configuration Twig/Pentatrion de Symfony.

La syntaxe PHP, PHPUnit et les builds sont bloquants. PHPStan, ESLint et les
audits de securite sont informatifs pendant la remise a niveau de la codebase :
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
npm ci
npm run lint
npm ci --prefix front
npm run check --prefix front
```

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
ensuite le code, `vendor`, `public/build` et `public/spa` par rsync/SSH vers
`/home/egflbug/devdoc/prod`. La synchronisation conserve les fichiers `.env`,
`.ovhconfig`, `var`, `backups`, `.git` et `public/uploads` deja presents sur OVH.
Le cache Symfony de production est vide apres la synchronisation.

Un push valide sur `main` lance le deploiement. Le flux recommande est de
travailler sur `deploy`, ouvrir ou mettre a jour une pull request vers `main`,
puis merger quand les checks sont verts. Il peut aussi etre lance depuis
l'onglet Actions avec `Run workflow`, sur `main`, en cochant l'option de
deploiement.

Le deploiement rsync remplace les anciens `git pull` manuels sur OVH. Le depot
`.git` distant est conserve par securite, mais ne doit plus servir a mettre la
production a jour.

## Protection de branche

Dans les regles de protection de `main`, rendre obligatoires les checks `PHP
quality`, `Frontend quality`, `Unit tests`, `Security audit` et `Report` avant
fusion.
