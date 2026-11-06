# Plan de tests — Inclusion numérique — fixé le 2026-11-02 avant exécution

Archive immuable. Ce plan est rédigé et archivé **avant** les essais de la phase 4 ; les résultats sont consignés dans une archive distincte, datée après celle-ci. Version testée : `main2`, `30da5e5`.

## Environnements

| Code | Environnement | Usage autorisé |
|---|---|---|
| ISO | copie `git archive 30da5e5` hors du dépôt ; Docker Compose avec un nom de projet dédié (`vae-inclusion-iso`), ports et volumes propres ; `.env` aux valeurs fictives ; base MySQL vide créée par les migrations ; données fictives créées pour l'essai | toutes les opérations, y compris écriture |
| CI | GitHub Actions, run du SHA testé | lecture des jobs et artefacts |
| PROD | `https://inclusion-numerique.varascundo.com` | **lecture seule** : requêtes `GET`, navigation sans compte, émulation hors ligne dans le navigateur. Aucune création de compte, aucune soumission de formulaire, aucune mutation |

Données fictives (ISO uniquement) : administrateur `admin-iso`, apprenants `apprenant-a` et `apprenant-b` (adresses en `@example.test`), un parcours public, un cours publié, un cours brouillon, un cours réservé à `apprenant-a`. Mots de passe générés pour l'essai, non consignés.

## Contrôles automatisés

| # | Contrôle | Environnement | Résultat attendu |
|---|---|---|---|
| A1 | Installation : construction des images, démarrage des conteneurs, `composer install`, migrations | ISO | Conteneurs `php`, `front`, `nginx`, `db` démarrés ; 13 migrations jouées ; `doctrine:schema:validate` sans écart |
| A2 | `make check` (syntaxe PHP, PHPStan, ESLint, PHPUnit, Vitest, build Vite) | ISO | Code de sortie 0 ; aucun test en échec |
| A3 | `make docs-build` | dépôt (documentation modifiée) | Code de sortie 0, aucun lien interne mort |
| A4 | Jobs du run du SHA | CI | 6 jobs `success` |
| A5 | Scénario Playwright hérité `front/e2e/inputSearch.spec.js` | ISO | Résultat attendu fixé : **non exécutable** tant que `@playwright/test` est absent de `front/package.json` ; on consigne l'erreur. Aucune dépendance n'est ajoutée dans le dépôt sans accord |

## Scénarios fonctionnels

| # | Scénario | Env. | Étapes | Résultat attendu |
|---|---|---|---|---|
| F1 | Deux entrées publiques | PROD | ouvrir `/spa/` | Titre « Inclusion numérique » ; cartes « J'apprends le numérique » et « J'accompagne des personnes » |
| F2 | Parcours d'un public | PROD | ouvrir « J'apprends le numérique » puis un parcours | Seuls des parcours `apprendre` ; le parcours affiche ses modules et ses cours |
| F3 | Recherche | PROD | saisir « clavier » dans la recherche | Au moins un résultat « Utiliser le clavier », qui ouvre le cours |
| F4 | Cours | PROD | ouvrir `/spa/pages/utiliser-le-clavier` | Titre, niveau, sommaire, bouton PDF |
| F5 | PDF | PROD | `GET /cours/utiliser-le-clavier.pdf` | 200, `application/pdf`, fichier commençant par `%PDF` |
| F6 | QCM | PROD | choisir un niveau et une langue, démarrer, répondre à une question | Une question s'affiche avec ses choix, puis la correction ; aucune donnée envoyée hors lecture (`GET`) |
| F7 | Accès réservés | ISO | API publique sans compte, puis connecté comme `apprenant-b`, puis comme `apprenant-a` | Le cours réservé et le brouillon absents pour l'anonyme et `apprenant-b` ; le cours réservé présent pour `apprenant-a` ; le brouillon jamais servi |
| F8 | Compte facultatif | ISO | créer un compte, se connecter, ouvrir `/spa/mon-espace` | Compte créé ; mon espace accessible ; aucun contenu réservé tant qu'aucun accès n'est attribué |
| F9 | Back-office protégé | PROD | `GET /admin` sans session | Redirection vers la connexion (302), aucun contenu d'administration |
| F10 | Routes retirées et fichiers sensibles | PROD | `GET /mcp`, `/oauth/authorize`, `/.well-known/oauth-authorization-server`, `/VAE/README.md`, `/.env` | 404 pour les quatre premières, 403 ou 404 pour `/.env` |

## Essais d'usage

Ils vérifient des points précis ; ils ne valent pas audit RGAA.

| # | Essai | Env. | Résultat attendu |
|---|---|---|---|
| U1 | Mobile 390 × 844 | PROD | Pas de défilement horizontal sur l'accueil et un cours ; navigation accessible par le menu |
| U2 | Clavier seul | PROD | Depuis l'accueil, `Tab` atteint la recherche, la connexion et les deux entrées ; le focus est visible ; `Entrée` ouvre l'entrée choisie |
| U3 | Connexion faible | PROD | Avec un débit réduit émulé, l'accueil puis un cours s'affichent ; poids transféré relevé |
| U4 | Hors ligne | PROD | Après une visite du cours en ligne, l'émulation hors ligne puis un rechargement affichent encore le cours (service worker) |

## Jeu d'essai de la fonctionnalité la plus représentative

Fonctionnalité retenue : **la visibilité des contenus** (`PublishedContentExtension`, `CourseVisibility`), qui conditionne l'API publique, le PDF, le plan du site et l'espace apprenant.

| Entrée | Personne | Attendu |
|---|---|---|
| cours `publie`, non réservé | anonyme | servi |
| cours `brouillon` | anonyme, apprenant | non servi (absent de la liste, 404 à l'unité) |
| cours `brouillon` | administrateur | servi |
| cours `publie`, réservé à A | anonyme, B | non servi |
| cours `publie`, réservé à A | A | servi |
| cours `publie` d'un parcours réservé à A | B | non servi |

Les données obtenues viennent de F7 (ISO) et des tests `PublishedContentApiTest`, `PersonalizedContentApiTest` et `CoursePdfTest` exécutés par A2 ; les écarts sont analysés dans l'archive des résultats.

## Anomalies

Toute différence avec un résultat attendu est consignée avec l'environnement, l'heure, la reproduction et sa gravité (bloquante, majeure, mineure). Une anomalie reste ouverte tant que sa correction n'a pas été vérifiée ; la correction du produit est un travail séparé. Observations déjà connues avant exécution : bouton sans libellé visible du formulaire QCM ; projet Docker Compose nommé `mediateur_numerique` dans ce dépôt.
