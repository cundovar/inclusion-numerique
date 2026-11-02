# Reprise du code de DevDoc

Date : 15 octobre 2026.

## Source

- Dépôt : `cundovar/vue-symfony` (DevDoc), branche par défaut.
- Commit source : `fe8f55985682a8e40fe35c119fa5ba4ab2cb0257` (14 octobre 2026, « Merge pull request #51 from cundovar/docs/vitepress-site »).
- Mode de copie : instantané des fichiers suivis par Git, sans l'historique de DevDoc. L'historique de DevDoc contient des sauvegardes SQL, le dossier VAE et des builds ; les reprendre aurait importé ces données dans ce dépôt. L'origine reste traçable par le commit ci-dessus.
- Droits : DevDoc et ce projet appartiennent au même auteur. DevDoc n'a pas de fichier de licence. Les icônes PWA (`public/spa/pwa.png`, `pwa1.png`) et la capture `public/spa/screenshot.png` viennent de DevDoc et sont à remplacer par des visuels propres au site.

## Essai court et décision

Le plan demandait un essai dans une copie isolée avant la reprise complète.

> Les résultats de tests cités dans cette section concernent DevDoc au moment de l'essai. Ils ne valident pas cette application et ne figurent pas dans le dossier VAE.

1. DevDoc démarre tel quel avec PHP 8.3 et SQLite : PHPUnit 76/76, Vitest 38/38, build Vite réussi après restauration de `front/src/services/` (voir « Constats » plus bas).
2. Retrait des fonctions écartées : environ 140 fichiers PHP supprimés. Il est resté une quinzaine de fichiers à corriger : relations des entités, configuration des services et de la sécurité, menu du back-office, gabarits.
3. Retrait côté front : exercices et éditeur Monaco, favoris, notes, profil, boutons de connexion apprenant, suivi des visites, enregistrement des résultats de QCM, documentation de langages.
4. Résultat : le site, l'API, la connexion et les pages du back-office fonctionnent ; les tests conservés passent.

**Décision : la simplification est maîtrisable ; le projet continue à partir de la copie de DevDoc.**

## Repris

- Backend Symfony 6.4, Doctrine, API Platform, EasyAdmin : cours (`Page`, `PageContent`, `PageBlock`), navigation (`Category`, `Menus`, `SuperMenu`, `PositionMenus`), niveaux, QCM, SEO, configuration du site, logos, médias de cours (`CourseMedia`, base des futures captures), utilisateurs du back-office.
- Front Vue 3 : composants `AppHeader`, `AppHeaderMobile`, `AppNavigation`, `AppButton`, `AppCard`, `CourseCard`, `PageContent`, sommaire, recherche, QCM, styles Tailwind et PWA.
- Tests : PHPUnit (accès, mapping Doctrine, entités) et Vitest (gardes du routeur, HTML sécurisé, store QCM, cache hors ligne).
- Environnement Docker (`compose.yaml`, `Dockerfile`, Nginx, PHP-FPM, `Makefile`), adapté : nom de projet `mediateur_numerique`, ports distincts de DevDoc.

## Retiré

| Fonction DevDoc | Raison |
| --- | --- |
| Exercices de code, éditeur Monaco, documentation de langages (`DocDeCode`), vocabulaire par langage, liens MDN/GitHub | Contenus de programmation |
| Inscription, favoris, notes, personnalisation par visiteur, profil | Aucun espace apprenant dans la première version |
| Suivi des visites, Google Analytics (identifiant de DevDoc) | Statistiques hors périmètre ; pas de traceur tiers |
| Micro-service des résultats de QCM (Node + MongoDB), enregistrement des tentatives | Aucun score enregistré |
| Agents de génération de cours, propositions IA, API d'administration par clé (`/api/admin`, n8n/MCP) | Génération automatique hors périmètre |
| Recherche « IA » et QCM générés via `ia-search.varascundo.com` et `ia-qcm.varascundo.com` | Services de production de DevDoc ; le site doit rester indépendant |
| 18 migrations de DevDoc | Remplacées par une migration initiale unique du schéma conservé |

Non importés : `VAE/`, `docs/` et `documentations/` de DevDoc, `aidd_docs/`, `.bmad-core/`, `agents/`, `teams/`, `expansion-packs/`, `api-platform/` (application de démonstration), `services/`, `tools/`, `e2e/`, sauvegardes et scripts SQL, scripts de déploiement (`deploy.php`, `deploy.sh`, `compose.prod.yaml`, `nginx.prod.conf`), fichiers `.env*`, builds (`public/build/`, fichiers générés de `public/spa/`).

## Ajouté ou modifié

- `app:create-admin` : crée ou met à jour le compte du responsable éditorial (seul accès au back-office, faute d'inscription).
- `/api/site-configuration` : apparence globale du site en lecture publique. Elle remplace `/api/customization`, qui était liée aux comptes visiteurs.
- Recherche entièrement locale dans les cours chargés.
- Icônes PrimeIcons chargées depuis le paquet npm au lieu d'un CDN ; kit Font Awesome lié à DevDoc retiré (le paquet npm suffit).
- Tests ajoutés : routes retirées absentes, toutes les pages du back-office s'ouvrent, création d'admin, configuration publique, recherche locale, constructeurs d'entités.
- CI propre au projet, sans déploiement.

## Constats sur DevDoc

À signaler à DevDoc, sans modifier ce dépôt depuis ici :

- `.gitattributes` exclut `services/` à tous les niveaux : `git archive` omet aussi `front/src/services/` et le build échoue. Le motif `/services/` (ancré à la racine) suffirait.
- `public/build/` et une partie de `public/spa/` (service worker, workbox) sont versionnés alors qu'ils sont générés.
- Les gabarits chargent Google Analytics et un kit Font Awesome lié au compte DevDoc.

## Reste à adapter

- Icônes PWA et capture d'écran de l'application : encore celles de DevDoc. Accueil, nom provisoire du site et titres sont adaptés depuis le 15 octobre 2026.
- Modèle « parcours → modules → leçons » et fiches de service : à construire sur `Category`, `Menus` et `PageContent`.
- Captures datées et sourcées : faites (menu Captures, marqueur `[capture:ID]`, voir [le guide d'édition](../fonctionnalites/edition.md)). Reste l'optimisation des images pour mobile et leur mise en cache hors ligne.
- Cache hors ligne : le plan signale que DevDoc garde des cours supprimés et lit tous les contenus au démarrage ; l'IndexedDB contient encore les magasins `exoMenus`/`exoContents` inutilisés.
- `LanguageQCM` (langage de programmation d'un QCM) : à renommer en thème ou à retirer avec le modèle pédagogique.
- Tests de bout en bout Playwright de DevDoc (`e2e/`) : à reprendre lors de la vérification mobile et hors ligne.
