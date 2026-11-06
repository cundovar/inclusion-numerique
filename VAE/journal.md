# Journal documentaire VAE

## 2026-11-06

- Reprise du connecteur MCP et préparation du dossier d'images propre (plan du jour, PR #5, `4a804ad`, `6b566b8`, co-signés par Claude Opus 5.5). Jetons OAuth cloisonnés sans changement de schéma ; script `deploy/uploads-inclusion.sh`.
- Run [42217518469](https://github.com/cundovar/inclusion-numerique/actions/runs/42217518469), commit `6b566b8` : tous les jobs réussis (PHP : 180 tests, 926 assertions), `deploy` ignoré. Le run précédent, 42217207046 sur `4a804ad`, a échoué sur une erreur du nouveau test, corrigée par `6b566b8`.
- Contrôles locaux : `make check-fast` réussi ; étapes `prepare`, `verify`, `switch` et `rollback` du script sur de faux dossiers. `make check` complet non exécuté en local, à la demande du candidat : la CI en tient lieu.
- Non vérifié : inventaire de production (lecture SSH refusée par le contrôle de permissions de l'agent), étape `inventory` du script, déploiement, parcours OAuth réel, bascule des images.

- Préparation de la CI/CD du dépôt dédié `cundovar/inclusion-numerique` : PR #1 a corrigé le déclenchement sur `main`, la génération de l'archive et la protection Apache des fichiers `.env*` ; elle a été fusionnée par l'utilisateur.
- Runs [42204743070](https://github.com/cundovar/inclusion-numerique/actions/runs/42204743070) (PR) et [42205300321](https://github.com/cundovar/inclusion-numerique/actions/runs/42205300321) (push `main`) : PHP/PHPUnit, migrations MySQL, Vue/Vitest, VitePress et build de release réussis. Le déploiement a échoué à l'authentification, car le job ciblait l'environnement `inclusion-numerique` alors que `OVH_SSH_PASSWORD` est configuré dans `production` (nom vérifié sans lire sa valeur).
- La PR #2 corrige l'environnement attendu (`production`) et rend le déploiement manuel. Son run [42206685700](https://github.com/cundovar/inclusion-numerique/actions/runs/42206685700) a validé PHP/PHPUnit, migrations MySQL, front/Vitest, VitePress et le build de release ; `deploy` a été ignoré sur la PR. Après fusion, le run [42206988450](https://github.com/cundovar/inclusion-numerique/actions/runs/42206988450) sur `main` a également réussi et a ignoré `deploy`. Aucun nouveau déploiement n'a été réalisé.
- Comparaison GitHub : `main2` de `cundovar/vue-symfony` a deux commits après le snapshot importé dans le dépôt dédié ; ils incluent des améliorations de consultation hors ligne. Le transfert de ces commits est préparé dans une branche dédiée et reste à valider avant déploiement.
- Contrôle OVH en lecture seule après retrait de l'ancien code : `/` répond 404 et `/.env` 403. Le contenu du `.env` n'a pas été consulté, la base n'a pas été modifiée et aucun déploiement du nouveau dépôt n'a été lancé.

## 2026-11-03

- Hors ligne complet (`bbd1220`, poussé sur `main2`) : le site et le texte de tous les cours s'ouvrent sans réseau après une visite en ligne ; images des captures par le bouton « Tout télécharger » de Mon espace. Contrôles de la CI rejoués sur une copie de travail et essai réel serveur arrêté, archivés dans [`evidence/2026-11-03-hors-ligne/`](evidence/2026-11-03-hors-ligne/README.md). Run CI [41702695459](https://github.com/cundovar/vue-symfony/actions/runs/41702695459) réussi (6 jobs, déploiement compris) ; en production, `/spa/sw.js` précharge la page hors ligne (contrôle `curl` du 2026-11-03), sans essai hors ligne sur le site publié.

## 2026-11-02

- Cadrage du dossier pour **Inclusion numérique** (branche `main2` de `cundovar/vue-symfony`, version `30da5e5`), archivé dans [`evidence/2026-11-02-cadrage/`](evidence/2026-11-02-cadrage/inventaire.md) :
  - référentiel : fiche RNCP37873 relue ; REAC et RE TP-01281 millésime 04 téléchargés depuis la fiche (empreinte SHA-256 consignée) ; modalités d'épreuve et pièces attendues relevées ; questions ouvertes au candidat sur la démarche et la session ;
  - provenance : DevDoc (`ec173dc`) → Médiateur (`c87941f`) → 10 commits propres à `main2`, tous co-signés par Claude Opus 5.5 ; part personnelle du candidat à préciser ;
  - CI : run [41442887513](https://github.com/cundovar/vue-symfony/actions/runs/41442887513) (`30da5e5`), 6 jobs réussis dont le déploiement OVH d'Inclusion ;
  - production : contrôle HTTP en lecture seule à 13 h 40 (`/spa/`, `/docs/`, `/api` en 200 ; `/mcp` et `/oauth/*` en 404) ;
  - état constaté : 5 compétences couvertes, 6 partielles ; les preuves de Médiateur restent attribuées à Médiateur.
- Réattribution à Inclusion numérique (phase 2 du plan) :
  - VitePress : liens vers le code, liens d'édition et lien du dépôt dirigés vers `cundovar/vue-symfony`, branche `main2` ; titre du site « Inclusion numérique » ; page d'accueil, page VAE, tests et déploiement mis à jour ; page de reprise de DevDoc attribuée à Médiateur ;
  - `VAE/` : README, rapport, matrice et registre décrivent Inclusion, avec le run 41442887513 et le contrôle du 2026-11-02 ; les runs et contrôles de Médiateur restent dans l'historique, attribués à Médiateur ;
  - `make docs-build` réussi à 13 h 44 (VitePress 1.6.4, build en 115 s) ;
  - build servi localement : titre « Dossier VAE | Inclusion numérique », 21 liens `blob/main2`, 25 liens `edit/main2`, lien du dépôt `tree/main2` ; les fichiers ciblés existent sur `origin/main2`, sauf le dossier `evidence/2026-11-02-cadrage/`, encore local (non commité) ; ancre « Origine et dépendances » suivie au clic ;
  - non vérifié : le site publié garde l'ancienne configuration tant que ces changements ne sont ni commités ni déployés.
- Conception et suivi (phase 3 du plan), archivés dans [`evidence/2026-11-02-conception/`](evidence/2026-11-02-conception/README.md) :
  - copie isolée de `30da5e5` (`git archive`, `.env` fictif, image PHP du projet, MySQL 8.0.44 jetable en `tmpfs`) : 13 migrations jouées, mapping et schéma validés, export du schéma (27 tables, dont 4 tables du connecteur de Médiateur non mappées) ;
  - nouvelles pages, toutes formalisées a posteriori : [user stories](../docs/conception/besoins.md), [écrans et enchaînements](../docs/conception/maquettes.md) avec 9 captures du site publié (13 h 54 – 13 h 57), [modèle de données](../docs/conception/base-de-donnees.md), [décisions d'architecture](../docs/conception/decisions-architecture.md), [gestion de projet](../docs/gestion-de-projet.md) ;
  - indicateurs recalculés depuis Git et GitHub Actions (commandes archivées) ;
  - corrections de l'inventaire du cadrage, notées dans la nouvelle archive : `Version20261102104351` et `MIGRATIONS_TABLE` ne sont plus dans `main2` (retirés par `95d47ac`) ; 18 entités mappées et non 19 ; pour Médiateur, 47 commits mentionnent un agent IA dont 15 ont « Claude » pour auteur ;
  - couvertures inchangées : elles seront réévaluées en phase 6.

## 2026-10-30

- Création du dossier VAE du projet Médiateur numérique (README, rapport, matrice, registre des preuves, journal). Référentiel retenu : TP CDA RNCP37873, vérifié sur la fiche France Compétences ; aucune preuve DevDoc reprise.
- Matrice amorcée depuis le dépôt : 11 compétences renseignées, statuts fondés sur le run CI [39493348934](https://github.com/cundovar/mediateur-numerique-formation/actions/runs/39493348934) (`main`, `34bef51`, 2026-10-20, 6 jobs réussis dont le déploiement OVH) et sur un contrôle `curl` du site en production (`/spa` répond 200).
- Création du site de documentation VitePress (`docs/`) ; les pages conception, architecture, sécurité, tests et déploiement sont formalisées a posteriori.
- Contrôles locaux de cette mise en place :
  - `make docs-build` réussi ; un lien interne mort ajouté pour l'essai fait échouer le build en nommant la page.
  - Règles `/docs/` des deux `.htaccess` vérifiées sur Apache 2.4 (`httpd:2.4`), racine `public/` et racine du dépôt.
  - Hooks du harness testés par payloads simulés : refus de `.env.local`, d'une migration commitée et de `VAE/evidence/`, marqueur posé après un changement de code.
  - Règles Codex vérifiées par `codex execpolicy check`.
  - Dans une copie de travail avec le `.env` de test de la CI (le dépôt local n'a pas de `.env`, donc `make check` ne peut pas démarrer ses conteneurs) :
    - syntaxe PHP réussie ;
    - PHPStan sans erreur ;
    - PHPUnit : 176 tests, 914 assertions ;
    - ESLint réussi ;
    - Vitest : 28 fichiers, 191 tests ;
    - build Vite réussi.
- Constat : le scénario Playwright `front/e2e/inputSearch.spec.js` n'est pas exécutable en l'état (`@playwright/test` absent des dépendances).
- Publication de la documentation :
  - PR #47 fusionnée dans `main` (`cd9f65e`).
  - Run CI [41056420072](https://github.com/cundovar/mediateur-numerique-formation/actions/runs/41056420072) : 7 jobs réussis, dont le nouveau job « Documentation VitePress » et le déploiement OVH.
  - Contrôle en production par `curl` et navigateur : `/docs/` et ses pages profondes servies, `VAE/` et la configuration des agents non exposés.
  - Les statuts de la publication passent à `ci` et `prod` dans le registre.
