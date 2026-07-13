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
nouvelles erreurs restent bloquantes. La baseline ne doit etre regeneree qu'apres
revue des erreurs supprimees ou acceptees.

## Configuration du deploiement

Creer l'environnement GitHub `production` et, de preference, activer une
validation obligatoire. Ajouter les secrets suivants a cet environnement :

| Secret | Contenu |
| --- | --- |
| `DEPLOY_HOST` | Nom DNS ou adresse du serveur |
| `DEPLOY_USER` | Utilisateur SSH Deployer |
| `DEPLOY_PATH` | Repertoire racine des releases sur le serveur |
| `DEPLOY_SSH_KEY` | Cle privee SSH dediee au deploiement |
| `DEPLOY_KNOWN_HOSTS` | Ligne `known_hosts` verifiee du serveur |

Le serveur doit disposer de Git, Docker avec `docker compose`, et d'un acces en
lecture au depot GitHub. Deployer construit EasyAdmin avec une image Node 22,
puis Vue avec le service `front-build`; les deux alimentent `public/build`. Un
push valide sur `main` lance le deploiement. Il peut
aussi etre lance depuis l'onglet Actions avec `Run workflow`, sur `main`, en
cochant l'option de deploiement.

## Protection de branche

Dans les regles de protection de `main`, rendre obligatoires les checks `PHP
quality`, `Frontend quality`, `Unit tests`, `Security audit` et `Report` avant
fusion.
