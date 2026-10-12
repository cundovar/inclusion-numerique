# Plan de tests — DevDoc

> Plan rédigé le 2026-10-12 et exécuté le même jour, en local, en CI et en production. Exigences de référence : [`docs/conception/besoins.md`](../conception/besoins.md) (US = user story, ENF = exigence non fonctionnelle).

## 1. Objectifs et périmètre

- Vérifier que les exigences fonctionnelles et non fonctionnelles sont satisfaites.
- Empêcher les régressions à chaque modification, en local avant commit et en CI avant déploiement.
- Vérifier la production après chaque déploiement, sans modifier ses données.

Dans le périmètre : le backend Symfony (domaine, application, infrastructure, HTTP), la logique du frontend Vue (composant de sécurité, gardes du routeur, store, utilitaires), le build du frontend et les parcours utilisateur publics.
Hors périmètre à ce jour : les tests de bout en bout du navigateur (E2E) et les tests de charge (voir §8).

## 2. Stratégie : niveaux de test

| Niveau | Objet | Outil | Où | Bloquant |
|---|---|---|---|---|
| Analyse statique | Syntaxe PHP, typage et erreurs | `php -l`, PHPStan niveau 5 (`make lint-php`, `make analyse`) | Local, CI | Local oui ; CI informatif (PHPStan) |
| Qualité front | Règles Vue et JS | ESLint (`make lint-front`) | Local | Local oui ; absent de la CI |
| Unitaire front | Composant `SafeHtml`, gardes du routeur, store QCM, utilitaires, accès IndexedDB | Vitest + Vue Test Utils + jsdom (`make test-front`, `front/tests/unit/`) | Local, CI (job « Frontend quality ») | Oui |
| Unitaire | Entités, handlers CQRS, services | PHPUnit (`tests/Entity`, `tests/Application`, `tests/Infrastructure/Note/Service`) | Local, CI | Oui |
| Intégration | Repository Doctrine sur une vraie base | PHPUnit + SQLite en mémoire (`tests/Infrastructure/Note/Repository`) | Local, CI | Oui |
| Fonctionnel HTTP | Contrôles d'accès de bout en bout (routage, pare-feu, API Platform, EasyAdmin) | PHPUnit `WebTestCase` + SQLite jetable (`tests/Functional`) | Local, CI | Oui |
| Build | Compilation Vite et PWA | `make build-front`, job « Build release » | Local, CI | Oui |
| Sécurité des dépendances | Vulnérabilités connues | `composer audit`, `npm audit` | CI | Informatif |
| Recette fonctionnelle | Parcours utilisateur réels | Navigateur (Chrome DevTools) et requêtes HTTP non destructives | Local, production | Manuel |

Les tests automatisés ne touchent jamais la base MySQL. `phpunit.xml.dist` force `DATABASE_URL` vers une base SQLite jetable (`var/test.db`), et le schéma est recréé avant chaque test fonctionnel.

## 3. Environnements et critères

| Environnement | Commande / déclencheur | Critère de passage |
|---|---|---|
| Local (Docker) | `make check-fast` pendant le développement, `make check` avant push | Toutes les étapes vertes ; un hook de fin de tâche le rappelle |
| CI GitHub Actions | Push ou PR sur `main` / `deploy` (`.github/workflows/ci.yml`) | Jobs qualité, tests, build et rapport verts ; le déploiement en dépend |
| Production | Push sur `main` → job « Deploy production » | Recette non destructive après déploiement (§5) |

Critère de sortie d'une livraison : `make check` vert, CI verte, aucune anomalie critique ou majeure ouverte sur le périmètre modifié.

## 4. Cas de test automatisés

85 tests : 49 tests PHPUnit (121 assertions) et 36 tests Vitest. Le nombre de tests de chaque ligne figure entre parenthèses après le nom du fichier.

| Exigence | Fichier de test | Ce qui est vérifié |
|---|---|---|
| US-09 | `tests/Application/Note/Handler/CreateOrUpdateNoteHandlerTest.php` (3) | Création ou mise à jour d'une note ; page inexistante rejetée |
| US-09 | `tests/Application/Note/Handler/DeleteNoteHandlerTest.php` (3) | Suppression par le propriétaire ; refus pour un autre utilisateur ; note inexistante |
| US-09 | `tests/Application/Note/Handler/GetNoteByPageHandlerTest.php` (3) | Lecture de la note d'une page ; absence de note ; page inexistante |
| US-09, ENF-04 | `tests/Application/Note/Handler/GetUserNotesHandlerTest.php` (1) | Notes renvoyées déchiffrées |
| ENF-04 | `tests/Infrastructure/Note/Service/NoteEncryptionServiceTest.php` (1) | Chiffrement puis déchiffrement sans perte |
| US-09 | `tests/Entity/NoteTest.php` (1) | Horodatage mis à jour à la modification |
| US-09 | `tests/Infrastructure/Note/Repository/DoctrineNoteRepositoryTest.php` (4) | Persistance, recherche par utilisateur et page, tri, suppression (SQLite) |
| US-02, US-14 | `tests/Entity/PositionMenusTest.php` (2) | Cohérence des deux côtés de la relation menu ↔ position (test de régression) |
| US-15 | `tests/Entity/AgentCourseGenerationTest.php` (5) | Cycle de vie d'une génération de cours IA : file d'attente, échec, nouvel essai, validation |
| US-13, US-14 | `tests/Functional/Security/AccessControlTest.php` (16) | Back-office réservé aux admins et absent hors de `/admin` ; écritures API refusées aux anonymes (401) et aux utilisateurs (403), autorisées aux admins ; lecture publique conservée ; un utilisateur ne modifie que son propre nom, jamais ses rôles ni son mot de passe |
| Intégrité des données | `tests/Integration/Doctrine/MappingTest.php` (10) | Mapping Doctrine valide ; les colonnes `snake_case` créées par la migration `Version20260901103000` correspondent aux entités `CourseMedia` et `AgentCourseGeneration` |
| ENF-03 | `front/tests/unit/components/SafeHtml.spec.js` (6) | Balises `script` et `iframe` supprimées ; gestionnaires d'événements et styles en ligne retirés ; liens `javascript:` et `data:` neutralisés ; `rel="noopener noreferrer"` sur les liens `_blank` ; contenu de cours conservé |
| US-13, US-14 | `front/tests/unit/router/guards.spec.js` (6) | `authGuard` redirige un visiteur et mémorise la destination ; `adminGuard` refuse sans `ROLE_ADMIN` ; `guestGuard` renvoie un utilisateur connecté |
| US-06, US-07 | `front/tests/unit/stores/qcm.spec.js` (4) | Réponses enregistrées ; questions et choix mélangés sans perte ; remise à zéro |
| US-02, US-04 | `front/tests/unit/utils/utils.spec.js` (5) | Mélange de Fisher-Yates sans perte ; majuscule et slug ; tri des menus par numéro |
| ENF-02 | `front/tests/unit/utils/offlineDb.spec.js` (12) | Base IndexedDB `spa-db` (simulée par `fake-indexeddb`) : stores et clés ; migration depuis les versions 1 à 4 sans perte ; insertion ou remplacement par clé ; objets réactifs Vue ; effacement de l'utilisateur ; expiration du cache API à 1 heure |
| ENF-02, US-13 | `front/tests/unit/utils/fetchDataPwa.spec.js` (3) | Repli hors ligne : menus visibles copiés en ligne puis relus sans réseau après redémarrage ; utilisateur restauré hors ligne ; effacé sur réponse 401 |

## 5. Recette fonctionnelle exécutée

Production (`https://devdoc.varascundo.com`), 2026-10-12, après le déploiement du run [`38246023750`](https://github.com/cundovar/vue-symfony/actions/runs/38246023750), en visiteur non connecté et sans aucune écriture de données.

| ID | Exigence | Scénario | Résultat attendu | Résultat |
|---|---|---|---|---|
| R-01 | US-01 | Ouvrir l'accueil | Message d'accueil, section « Cours », menus de catégories | OK : 49 liens de catégorie, menus présents |
| R-02 | US-03 | Ouvrir `/spa/pages/flexbox` | Cours affiché, sommaire cliquable, code coloré | OK : 9 ancres de sommaire, coloration Prism |
| R-03 | US-04 | Rechercher « flex » | Le cours Flexbox apparaît dans les résultats | OK |
| R-04 | US-06 | Ouvrir `/spa/qcm`, puis charger les questions CSS de niveau « basique » (requête du front `/api/q_c_ms`) | Formulaire affiché ; questions filtrées avec leurs choix | OK : 41 questions CSS, 4 choix chacune |
| R-05 | US-13 | Ouvrir `/spa/profile` sans être connecté | Redirection vers l'accueil | OK : redirigé vers `/spa/` |
| R-06 | US-10 | Ouvrir un cours sans être connecté | Bouton favori masqué | OK (`v-if="isAuthenticated"`) |
| R-07 | US-14 | `GET /admin`, `/admin/category/new` | Redirection vers `/login` | OK : 302 vers `/login` |
| R-08 | Sécurité | `GET /category/new` ; `POST /api/pages` ; `PATCH /api/users/999999` ; `DELETE /api/pages/999999` ; `GET /api/users` | 404 puis 401 partout | OK après déploiement ; KO avant (voir AN-01) |
| R-09 | ENF-01 | Cours sur mobile (390 × 844) | Pas de défilement horizontal ; barre QCM / Exos | OK : largeur de page = 390 px |
| R-10 | ENF-02 | Vérifier l'enregistrement du service worker | Service worker actif sur `/spa/` | **KO** (AN-03) ; corrigé, voir R-11 |

Après correction d'AN-03 (branche `fix/pwa-service-worker-registration`), en local avec les fichiers compilés comme en CI (service `front-build`), le 2026-10-12 :

| ID | Exigence | Scénario | Résultat attendu | Résultat |
|---|---|---|---|---|
| R-11 | ENF-02 | Charger `/spa/pages/flexbox` en ligne, puis couper le réseau (Chrome DevTools, mode hors ligne) et recharger | Service worker `/spa/sw.js` actif ; cours affiché depuis le cache ; une URL non mise en cache échoue | OK : caches `spa-pages`, `build-assets`, `api-public` et pré-cache remplis ; cours, menus et code coloré affichés hors ligne ; requête non mise en cache en échec réseau. Limite : panneau Sommaire non affiché hors ligne |

Non exécutés en production, faute de compte de test dédié (aucune création de compte ni écriture en production) : US-05 (exercices), US-07 (résultat de QCM), US-08 (inscription et connexion), US-09 à US-12 (espace connecté), US-15 et US-16 (administration).

Après le déploiement du run [`38249883993`](https://github.com/cundovar/vue-symfony/actions/runs/38249883993) (fusion de la PR #41), en production, le 2026-10-12 :

| ID | Exigence | Scénario | Résultat attendu | Résultat |
|---|---|---|---|---|
| R-12 | ENF-02 | Même scénario que R-11 sur `https://devdoc.varascundo.com/spa/pages/flexbox` | Service worker actif ; cours affiché hors ligne | OK : `/spa/sw.js` actif, portée `/spa/`, page contrôlée ; caches remplis (pré-cache 3, `spa-pages` 1, `build-assets` 9, `api-public` 6) ; hors ligne, cours, menus et code coloré affichés ; requête non mise en cache en échec réseau |

## 6. Résultats

| Exécution | Date | Résultat |
|---|---|---|
| `make check` local (`fix/secure-admin-and-api`) | 2026-10-12 | Syntaxe PHP OK, PHPStan `[OK] No errors`, ESLint 0 erreur, PHPUnit `OK (39 tests, 111 assertions)`, build Vite OK |
| Tests fonctionnels sans le correctif de sécurité | 2026-10-12 | 9 échecs sur 16 : les tests détectent bien la faille |
| CI [run `38246023750`](https://github.com/cundovar/vue-symfony/actions/runs/38246023750) (`8d320ed1`, fusion PR #39) | 2026-10-12 | Tous les jobs réussis ; PHPUnit `OK (39 tests, 111 assertions)` ; déploiement réussi |
| `make check` local (`fix/doctrine-schema-drift`) | 2026-10-12 | PHPStan `[OK] No errors`, PHPUnit `OK (49 tests, 121 assertions)`, build Vite OK ; `MappingTest` : 10 échecs sur 10 sans le correctif |
| CI [run `38252131258`](https://github.com/cundovar/vue-symfony/actions/runs/38252131258) (fusion des PR #42 à #44) | 2026-10-12 | Tous les jobs réussis ; PHPUnit `OK (49 tests, 121 assertions)` ; déploiement réussi |
| Vitest local (`test/front-vitest`) | 2026-10-12 | `Tests 21 passed (21)` ; `make check` réussi (49 tests PHPUnit, 21 tests Vitest, build Vite) |
| Vitest local (`feat/nosql-indexeddb`) | 2026-10-12 | `Tests 36 passed (36)` ; `make check` réussi (49 tests PHPUnit, 36 tests Vitest, build Vite) ; dans le navigateur en local, `spa-db` v5 remplie par l'application (281 menus, 22 catégories, 42 exercices) |
| Défauts introduits volontairement dans `offlineDb.js` et `fetchDataPwa.js` | 2026-10-12 | Conversion des objets réactifs retirée : 2 tests échouent ; expiration du cache décalée : 1 test échoue ; création de `docDeCodes` avancée en version 4 : 1 test échoue (non détecté avant l'ajout des migrations depuis chaque version) ; copie des menus retirée : 1 test échoue ; effacement sur 401 retiré : 1 test échoue |
| Défauts introduits volontairement dans le code front | 2026-10-12 | `authGuard` inversé : 2 tests échouent ; ajout de `rel="noopener noreferrer"` supprimé dans `SafeHtml` : 1 test échoue. Le filtre `javascript:` maison de `SafeHtml` peut être retiré sans échec, car DOMPurify bloque déjà ces liens par défaut (filtre redondant) |
| Recette en production (§5) | 2026-10-12 | 9 OK, 1 KO |

**Couverture du code** (rapports Clover des runs `38246023750` et `38252131258`) : **7,5 % des lignes** (285 sur 3 800) et 95 méthodes sur 904. Le contexte Notes est presque entièrement couvert ; contrôleurs, services et entités hors Notes le sont très peu. Au run précédent (`38236154178`, avant les tests fonctionnels), la couverture était de 4,79 %.

## 7. Anomalies

| ID | Gravité | Description | Détection | Statut |
|---|---|---|---|---|
| AN-01 | Critique | Back-office EasyAdmin servi hors de `/admin` sans authentification ; écritures API Platform anonymes sur 17 ressources, dont la modification des rôles et du mot de passe via `PATCH /api/users/{id}` | Recette HTTP en local | Corrigée (PR #39) ; couverte par `AccessControlTest` ; vérifiée en production (R-08) |
| AN-02 | Majeure | Modification du profil impossible : le front envoie `application/json`, API Platform attend `application/merge-patch+json` (415) | Analyse lors de la correction d'AN-01 | Corrigée (PR #39) ; couverte par test fonctionnel ; non revérifiée en production (compte requis) |
| AN-03 | Majeure | Service worker jamais enregistré en production : le front demande `/build/sw.js` (500), alors que le fichier est généré dans `/spa/sw.js`. Cause probable : `base: '/build/'` dans `front/vite.config.js` | Recette R-10 | Corrigée (branche `fix/pwa-service-worker-registration`) : `base` et `scope` `/spa/`, mise en cache des pages, des fichiers compilés et de l'API publique ; vérifiée en local (R-11) puis en production (R-12) |
| AN-04 | Mineure | `GET /api/qcm/language/{language}` renvoie 500 : `QCMApiController` appelle `ChoicesQCM::isCorrect()`, qui n'existe pas. Le front utilise `/api/q_c_ms` et n'est pas touché | Recette R-04 | Ouverte |
| AN-05 | Mineure | En visiteur, le front appelle des routes réservées (`/user-api/me`, `/api/customization`) : 401 et erreurs dans la console | Recette, console navigateur | Ouverte |

## 8. Limites et suite

- **Tests du frontend limités** : 36 tests unitaires Vitest sur la logique critique et l'accès IndexedDB ; aucun test de bout en bout. Ajouter un parcours E2E (Playwright) sur US-01, US-03, US-04 et US-06, puis des tests de composants de pages.
- **Recette de l'espace connecté** non exécutée : créer un compte de recette dédié pour US-08 à US-12, en local ou en préproduction.
- **Couverture faible (7,5 %)** : prioriser les contrôleurs d'API maison (notes, favoris, historique) et `HtmlSanitizerService` (ENF-03, non testé).
- **Contrôles non bloquants en CI** : PHPStan et les audits de dépendances sont informatifs, et ESLint est absent de la CI.
