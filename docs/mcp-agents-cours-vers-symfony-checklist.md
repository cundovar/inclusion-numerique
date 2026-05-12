# Checklist MCP agents-cours vers Symfony

## Objectif

Faire passer [`MCP/agents-cours`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours) par l'API Symfony au lieu d'un accès direct MySQL, sans casser les use cases métier existants.

## Principe

On garde:

- les use cases MCP
- les entités MCP
- le port [`ICoursRepository.js`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/src/domain/ports/ICoursRepository.js)

On remplace:

- [`MySQLCoursRepository.js`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/src/infrastructure/database/MySQLCoursRepository.js)

par:

- `SymfonyApiCoursRepository.js`

## Fichiers MCP à modifier

### À créer

- `MCP/agents-cours/src/infrastructure/api/SymfonyApiCoursRepository.js`

### À modifier

- [`MCP/agents-cours/src/infrastructure/di/container.js`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/src/infrastructure/di/container.js)
- [`MCP/agents-cours/config/config.js`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/config/config.js)
- [`MCP/agents-cours/.env.example`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/.env.example)
- [`MCP/agents-cours/README.md`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/README.md)
- [`MCP/agents-cours/docs/QUICKSTART.md`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/docs/QUICKSTART.md)
- [`MCP/agents-cours/docs/API-VUEJS.md`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/docs/API-VUEJS.md)

### À débrancher ensuite

- [`MCP/agents-cours/src/infrastructure/database/MySQLCoursRepository.js`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/src/infrastructure/database/MySQLCoursRepository.js)
- [`MCP/agents-cours/src/infrastructure/database/connection.js`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/src/infrastructure/database/connection.js)
- `MCP/agents-cours/scripts/migrate.js`
- `MCP/agents-cours/scripts/test-config.js`

## Fichiers MCP à conserver

Ne pas refondre ces fichiers tant que le port reste stable:

- [`MCP/agents-cours/src/domain/ports/ICoursRepository.js`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/src/domain/ports/ICoursRepository.js)
- [`MCP/agents-cours/src/domain/use-cases/CreerCours.js`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/src/domain/use-cases/CreerCours.js)
- [`MCP/agents-cours/src/domain/use-cases/ListerCours.js`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/src/domain/use-cases/ListerCours.js)
- [`MCP/agents-cours/src/domain/use-cases/ReviserCours.js`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/src/domain/use-cases/ReviserCours.js)
- [`MCP/agents-cours/src/domain/use-cases/GererMenus.js`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/src/domain/use-cases/GererMenus.js)

## Méthodes à implémenter dans SymfonyApiCoursRepository

Le nouveau repository HTTP doit couvrir tout le port actuel:

- `sauvegarder`
- `trouverParId`
- `listerParTechnologie`
- `listerParNiveau`
- `listerParStatut`
- `listerCoursIA`
- `sauvegarderRevision`
- `listerRevisions`
- `trouverTechnologieParNom`
- `trouverNiveauParNom`
- `listerTechnologies`
- `listerNiveaux`
- `listerMenus`
- `trouverMenuParId`
- `creerMenu`
- `mettreAJourMenu`

## Endpoints Symfony à réutiliser

### Menus

- `GET /api/admin/menus`
- `GET /api/admin/menus/{id}`
- `POST /api/admin/menus`
- `PUT /api/admin/menus/{id}`

### Référentiels

- `GET /api/admin/categories`
- `GET /api/admin/niveau-cours` ou route équivalente exacte

## Endpoints Symfony à confirmer ou compléter

- `GET /api/admin/page-contents/{id}`
- `GET /api/admin/page-contents` avec filtres utiles
- lecture des cours IA
- lecture par technologie
- lecture par niveau
- lecture par statut

## Endpoints Symfony à créer

### Workflow métier cours

- `POST /api/admin/agent-cours/creer`
- `GET /api/admin/agent-cours/{id}`
- `GET /api/admin/agent-cours`
- `PUT /api/admin/agent-cours/{id}`

### Révisions

- `POST /api/admin/agent-cours/revisions`
- `GET /api/admin/agent-cours/{id}/revisions`
- `POST /api/admin/agent-cours/revisions/{id}/appliquer`

## Mapping méthode MCP vers endpoint Symfony

- `listerMenus()` -> `GET /api/admin/menus`
- `trouverMenuParId(id)` -> `GET /api/admin/menus/{id}`
- `creerMenu(data)` -> `POST /api/admin/menus`
- `mettreAJourMenu(id, data)` -> `PUT /api/admin/menus/{id}`
- `listerTechnologies()` -> `GET /api/admin/categories`
- `trouverTechnologieParNom(nom)` -> filtre API ou filtrage local après liste
- `listerNiveaux()` -> `GET /api/admin/niveau-cours`
- `trouverNiveauParNom(nom)` -> filtre API ou filtrage local après liste
- `sauvegarder(cours, options)` -> `POST /api/admin/agent-cours/creer` ou `PUT /api/admin/agent-cours/{id}`
- `trouverParId(id)` -> `GET /api/admin/agent-cours/{id}`
- `listerParTechnologie(id)` -> `GET /api/admin/agent-cours?categoryId=...`
- `listerParNiveau(id)` -> `GET /api/admin/agent-cours?niveauCoursId=...`
- `listerParStatut(statut)` -> `GET /api/admin/agent-cours?statut=...`
- `listerCoursIA()` -> `GET /api/admin/agent-cours?type=agent-cours`
- `sauvegarderRevision(revision)` -> `POST /api/admin/agent-cours/revisions`
- `listerRevisions(courseId)` -> `GET /api/admin/agent-cours/{id}/revisions`

## Point critique

Le vrai point non trivial est [`MySQLCoursRepository.sauvegarder()`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/src/infrastructure/database/MySQLCoursRepository.js).

Aujourd'hui cette méthode orchestre:

- création optionnelle d'un menu
- création d'une page
- création d'un page content
- mise à jour éventuelle d'un cours existant

Cette orchestration doit devenir un use case Symfony dédié et atomique.

Il ne faut pas remplacer ça par une série d'appels HTTP dispersés dans le MCP.

## Configuration MCP cible

Dans [`MCP/agents-cours/config/config.js`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/MCP/agents-cours/config/config.js), prévoir:

- `repositoryDriver`
- `symfonyApi.baseUrl`
- `symfonyApi.apiKey`

Exemple logique:

```env
REPOSITORY_DRIVER=symfony
SYMFONY_API_BASE_URL=http://localhost:8080
SYMFONY_API_KEY=...
```

## Ordre recommandé

1. Basculer `menus`
2. Basculer `catégories` et `niveaux`
3. Créer `POST /api/admin/agent-cours/creer`
4. Basculer la lecture des cours
5. Créer et brancher les endpoints de révision
6. Basculer le container MCP sur Symfony
7. Retirer définitivement MySQL du MCP

## Verdict

Le chantier est raisonnable si vous gardez les use cases MCP et que vous limitez les changements à l'infrastructure.

Le plus gros morceau n'est pas la lecture, mais le workflow coordonné de création de cours et la gestion des révisions.
