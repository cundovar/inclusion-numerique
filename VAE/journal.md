# Journal documentaire VAE

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
