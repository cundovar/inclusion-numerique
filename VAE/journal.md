# Journal documentaire VAE

## 2026-11-06

- Préparation de la CI/CD du dépôt dédié `cundovar/inclusion-numerique` : PR #1 a corrigé le déclenchement sur `main`, la génération de l'archive et la protection Apache des fichiers `.env*` ; elle a été fusionnée par l'utilisateur.
- Runs [42204743070](https://github.com/cundovar/inclusion-numerique/actions/runs/42204743070) (PR) et [42205300321](https://github.com/cundovar/inclusion-numerique/actions/runs/42205300321) (push `main`) : PHP/PHPUnit, migrations MySQL, Vue/Vitest, VitePress et build de release réussis. Le déploiement a échoué à l'authentification, car le job ciblait l'environnement `inclusion-numerique` alors que `OVH_SSH_PASSWORD` est configuré dans `production` (nom vérifié sans lire sa valeur).
- La PR #2 corrige l'environnement attendu (`production`) et rend le déploiement manuel. Son run [42206685700](https://github.com/cundovar/inclusion-numerique/actions/runs/42206685700) a validé PHP/PHPUnit, migrations MySQL, front/Vitest, VitePress et le build de release ; `deploy` a été ignoré sur la PR. Aucun nouveau déploiement n'a été réalisé.
- Comparaison GitHub : `main2` de `cundovar/vue-symfony` a deux commits après le snapshot importé dans le dépôt dédié ; ils incluent des améliorations de consultation hors ligne. La version à déployer reste à synchroniser et vérifier.
- Contrôle OVH en lecture seule après retrait de l'ancien code : `/` répond 404 et `/.env` 403. Le contenu du `.env` n'a pas été consulté, la base n'a pas été modifiée et aucun déploiement du nouveau dépôt n'a été lancé.

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
