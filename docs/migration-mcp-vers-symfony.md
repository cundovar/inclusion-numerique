# Migration du MCP vers Symfony

## Objectif

Faire passer `agents-cours-createur` et ensuite `agents-cours-reviseur` par l'API Symfony au lieu d'un accès direct MySQL, sans casser les use cases existants.

## Principe

On garde l'architecture actuelle du MCP :
- `MCP tools`
- `use cases`
- `repository interface`

On remplace seulement l'implémentation infra :
- avant : `MySQLCoursRepository`
- après : `SymfonyApiCoursRepository`

Donc :
- peu de changement dans les tools MCP
- peu de changement dans les use cases
- changement concentré dans l'infrastructure et le container

---

## Étape 1 — Menus

### Côté Symfony

Déjà fait :
- API `GET /api/admin/menus`
- API `GET /api/admin/menus/{id}`
- API `POST /api/admin/menus`
- API `PUT /api/admin/menus/{id}`
- API `DELETE /api/admin/menus/{id}`

Fichiers concernés :
- `src/Controller/Api/AdminCrud/ApiMenuController.php`
- `src/Entity/Menus.php`

### Côté MCP

À faire :
- créer `SymfonyApiCoursRepository.js`
- implémenter :
  - `listerMenus()`
  - `trouverMenuParId()`
  - `creerMenu()`
  - `mettreAJourMenu()`

Puis :
- brancher `Container` sur cette nouvelle implémentation
- conserver `GererMenus` tel quel ou presque

### Résultat attendu

Les tools :
- `lister_menus`
- `voir_menu`
- `creer_menu`
- `editer_menu`

passent par Symfony au lieu de MySQL.

---

## Étape 2 — Technologies et niveaux

### Côté Symfony

Vérifier ou ajouter des endpoints propres pour :
- lister les catégories utilisées comme technologies
- lister les niveaux de cours

Probablement :
- `GET /api/admin/categories`
- `GET /api/.../niveaux`

### Côté MCP

Dans `SymfonyApiCoursRepository` :
- implémenter `listerTechnologies()`
- implémenter `listerNiveaux()`
- implémenter `trouverTechnologieParNom()`
- implémenter `trouverNiveauParNom()`

### Résultat attendu

Les tools :
- `lister_technologies`
- `lister_niveaux`

passent aussi par Symfony.

---

## Étape 3 — Création de cours

### Côté Symfony

Il faut exposer proprement la création coordonnée :
- menu éventuel
- page
- pageContent

Deux options :
1. utiliser plusieurs endpoints existants
2. créer un endpoint Symfony dédié au workflow "créer un cours"

Recommandation :
- créer un endpoint métier dédié
- exemple : `POST /api/admin/agent-cours/creer`

Payload :
- `titre`
- `description`
- `technologie`
- `niveau`
- `duree`
- `menuId` optionnel
- `nouveauMenuLabel` optionnel
- `codeHTML`
- `objectifs`

### Côté MCP

Dans `CreerCours` :
- on garde la logique IA
- on remplace seulement la persistance finale par un appel HTTP Symfony

Méthodes à migrer :
- `sauvegarder()`
- éventuellement une partie de `trouverTechnologieParNom()` / `trouverNiveauParNom()`

### Résultat attendu

Le tool `creer_cours` ne touche plus directement la base.

---

## Étape 4 — Lecture des cours récents

### Côté Symfony

Ajouter ou confirmer un endpoint du type :
- `GET /api/admin/agent-cours/recents`
ou
- `GET /api/admin/page-contents?type=agent-cours`

### Côté MCP

Migrer :
- `listerCoursIA()`
- `trouverParId()`
- `listerParTechnologie()`
- `listerParNiveau()`
- `listerParStatut()`

### Résultat attendu

Le tool `lister_cours_recents` et les lectures de cours passent par Symfony.

---

## Étape 5 — Révisions

### Côté Symfony

Il faudra exposer :
- création de révision
- listing des révisions
- application d'une révision
- lecture d'un cours avec son HTML

Exemples :
- `POST /api/admin/agent-cours/revisions`
- `GET /api/admin/agent-cours/{id}/revisions`
- `POST /api/admin/agent-cours/revisions/{id}/appliquer`
- `GET /api/admin/agent-cours/{id}`

### Côté MCP

Migrer :
- `sauvegarderRevision()`
- `listerRevisions()`
- `trouverParId()` si partagé avec le réviseur

### Résultat attendu

`agents-cours-reviseur` n'utilise plus MySQL direct non plus.

---

## Étape 6 — Container

### Fichier concerné

- `MCP/agents-cours/src/infrastructure/di/container.js`

### Changement

Aujourd'hui :
- `getCoursRepository()` retourne `MySQLCoursRepository`

Demain :
- `getCoursRepository()` retourne `SymfonyApiCoursRepository`

Prévoir éventuellement un switch de config :
- `repositoryDriver=mysql`
- `repositoryDriver=symfony`

Comme ça :
- migration progressive
- rollback simple si besoin

---

## Étape 7 — Configuration

### Ajouter dans le MCP

Dans `config/config.js` :
- `SYMFONY_API_BASE_URL`
- `SYMFONY_API_TOKEN` ou clé d'API si nécessaire

### But

Permettre au serveur Node MCP d'appeler Symfony proprement.

---

## Stratégie recommandée

Ordre conseillé :
1. `menus`
2. `technologies` et `niveaux`
3. `lecture simple des cours`
4. `création de cours`
5. `révisions`

Pourquoi :
- migration progressive
- moins de risque
- validation facile tool par tool

---

## Estimation

### Menus seulement

- petit chantier

### MCP createur complet

- chantier moyen

### Createur + reviseur complets

- moyen à assez large, mais sans refonte totale

---

## Recommandation nette

La bonne approche n'est pas de tout réécrire d'un coup.

Il faut :
- introduire `SymfonyApiCoursRepository`
- migrer les capacités une par une
- garder les use cases et les tools MCP stables

C'est la façon la plus propre et la moins risquée.
