# Essai hors ligne du 2026-11-03

Commit vérifié : `bbd1220` (`main2`, « fix(pwa): open the site and every course offline »).

## Contrôles automatiques (copie de travail, `.env` de CI)

Le dossier du dépôt n'a ni `.env`, ni `vendor/`, ni `node_modules` : les contrôles de la CI ont été rejoués sur une copie jetable.

- Front, sur l'hôte (Node 24.21.0) : `npm ci`, `npm run lint`, `npm test`, `npm run check` → [check-front.txt](check-front.txt). Le premier essai du script, sans dépendances PHP, a échoué sur les étapes PHP ; elles ont été rejouées ci-dessous.
- PHP, dans l'image `mediateur_numerique-php` (même `Dockerfile`, PHP 8.3 avec `gd`) ; `vendor/` installé depuis l'hôte (`composer install --no-scripts --ignore-platform-req=ext-gd`), le réseau du conteneur refusant GitHub → [check-php.txt](check-php.txt).

## Essai réel

Build de production (`BUILD_OUTPUT_DIR=../public/build PWA_OUTPUT_DIR=../public/spa npm run build`, 41 entrées préchargées), base SQLite avec trois cours fictifs dans une même rubrique, serveur `php -S` dans un conteneur, Chrome isolé piloté par DevTools.

1. Première visite en ligne de `/spa/` : service worker `activated` ; précache : `/spa/?hors-ligne=1`, manifeste, icônes et 37 fichiers `/build/assets/` ; store IndexedDB `menus` (base `spa-db`) avec les trois cours et leur rubrique.
2. Conteneur arrêté (`curl` : connexion refusée).
3. `/spa/pages/utiliser-souris`, jamais ouvert : titre « Cours 2 : utiliser-souris », texte affiché, bandeau « Vous êtes hors ligne : version du cours enregistrée sur cet appareil lors de votre dernière connexion. », liens « Precedent » et « Suivant ».
4. Clic sur « Suivant » : « Cours 3 : ecrire-un-mail », bandeau présent.
5. `/spa/mon-espace` : bloc « Lire les cours sans connexion » et bouton « Tout télécharger pour le hors ligne ».

Non essayé : téléchargement des images (aucune capture dans les données d'essai), téléphone réel, clavier. Pas de capture d'écran exploitable.

## CI et production

- Run [41702695459](https://github.com/cundovar/vue-symfony/actions/runs/41702695459) (`bbd1220`) : les 6 jobs ont réussi (PHP, Migrations MySQL, Front Vue, Documentation, Build release, Deploy inclusion-numerique).
- Contrôle en lecture seule du 2026-11-03 à 19 h 38 (UTC), `curl` : `https://inclusion-numerique.varascundo.com/spa/` en 200 ; `/spa/sw.js` en 200, contient `/spa/?hors-ligne=1` et 18 scripts `/build/assets/`. L'ouverture hors ligne n'a pas été essayée sur le site publié.
