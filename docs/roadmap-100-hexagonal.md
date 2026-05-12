# Roadmap vers une architecture 100% hexagonale

## Objectif

Amener l'application vers une architecture où:

- `Domain` ne dépend d'aucun détail technique Symfony ou Doctrine.
- `Application` ne dépend que de ports, DTO, commandes, queries et services métier.
- `Infrastructure` contient les implémentations Doctrine, sécurité, chiffrement et intégrations techniques.
- `Controller`, `Command`, `EventListener` et interfaces HTTP jouent le rôle d'adaptateurs d'entrée.

## Ce qui est déjà en place

- `Note` est passé sur un port de repository et un service de chiffrement côté domaine.
- `Favorite` est passé sur un port de repository.
- `Page` dispose d'un port `PageRepositoryInterface` et d'une implémentation Doctrine dédiée.
- Les repositories Doctrine legacy supprimés:
  - `NoteRepository`
  - `FavoriteRepository`
  - `PageRepository`

## Définition du "100% hexagonal"

Pour considérer un module comme migré:

- aucune classe dans `src/Application/<Context>` ne doit importer `App\Repository\*`
- aucune classe dans `src/Application/<Context>` ne doit dépendre de `EntityManagerInterface`
- les contrôleurs ne doivent plus porter la logique métier
- les accès aux entités persistées passent par des ports explicites
- les effets techniques sont délégués à `Infrastructure`

## Règles de refactor

1. Migrer par contexte métier, pas fichier par fichier.
2. Commencer par les lectures/écritures les plus utilisées.
3. Créer un port par besoin métier réel, pas un port générique anémique.
4. Ne pas injecter `EntityManagerInterface` dans `Application`.
5. Ne pas remettre de `repositoryClass` Doctrine dans les entités déjà migrées.
6. Laisser les contrôleurs minces: validation d'entrée, appel handler, réponse HTTP.

## Ordre recommandé

### 1. Category

Pourquoi:

- `CategoryRepository` est encore injecté dans plusieurs contrôleurs et services.
- `SitemapController`, `SeoService`, `ApiCategoryController`, `ApiPageContentController`, `PropositionIAController` en dépendent.

À créer:

- `src/Domain/Category/Repository/CategoryRepositoryInterface.php`
- `src/Infrastructure/Category/Repository/DoctrineCategoryRepository.php`

Cas d'usage minimaux:

- `findById`
- `findAll`
- `findByName`
- `save`
- `delete`

### 2. UserPageVisit

Pourquoi:

- encore présent dans `PageVisitController`, `UserPageVisitController`, `CleanupPageVisitsCommand`, `UserPageVisitListener`, `PageVisitStatsController`
- beaucoup de logique transverse, bon candidat pour clarifier le domaine

À créer:

- `src/Domain/UserPageVisit/Repository/UserPageVisitRepositoryInterface.php`
- services métier si nécessaire pour nettoyage, agrégation, purge

Cas d'usage minimaux:

- enregistrer une visite
- retrouver les visites d'un utilisateur
- compter
- supprimer les anciennes visites
- récupérer les stats nécessaires au dashboard

### 3. SEO

Pourquoi:

- `SeoService` reste hybride
- mélange lecture de catégorie, lecture de page, fallback SEO, logique de génération

But:

- garder la logique de génération dans un service applicatif ou domaine
- sortir les accès persistence derrière ports `SeoRepositoryInterface` et `CategoryRepositoryInterface`

### 4. QCM

Pourquoi:

- contexte métier identifiable
- API dédiée déjà isolée côté contrôleur

À créer:

- port repository QCM
- handlers de lecture
- éventuels services métier de notation ou agrégation

### 5. Admin CRUD restants

Pourquoi:

- beaucoup de contrôleurs CRUD parlent encore directement à Doctrine
- ce n'est pas le plus prioritaire métier, mais nécessaire pour la cohérence architecturale

Approche:

- traiter chaque ressource admin comme un mini contexte applicatif
- créer commandes/handlers pour `create`, `update`, `delete`
- réserver `Infrastructure` aux opérations Doctrine

## Cibles restantes connues

### Encore couplé à `App\Repository`

- `src/Controller/Api/AdminCrud/ApiCategoryController.php`
- `src/Controller/Api/AdminCrud/ApiExoContentController.php`
- `src/Controller/Api/AdminCrud/ApiMenuController.php`
- `src/Controller/Api/AdminCrud/ApiPageContentController.php`
- `src/Controller/Api/CustomizationController.php`
- `src/Controller/Api/PagesApiController.php` pour `MenusRepository`
- `src/Controller/Api/QCMApiController.php`
- `src/Controller/Api/UserPageVisitController.php`
- `src/Controller/Admin/DashboardController.php`
- `src/Controller/Admin/PageVisitStatsController.php`
- `src/Controller/PageVisitController.php`
- `src/Controller/PropositionIAController.php`
- `src/Controller/SitemapController.php`
- `src/Service/SeoService.php`
- `src/EventListener/UserPageVisitListener.php`
- `src/Command/CleanupPageVisitsCommand.php`

### Encore couplé directement à Doctrine

- contrôleurs avec `EntityManagerInterface`
- commandes Symfony avec `EntityManagerInterface`
- listeners Doctrine
- services transverses qui font eux-mêmes `persist/remove/flush`

## Méthode de migration par contexte

Pour chaque contexte:

1. Lister les contrôleurs, services, listeners et commandes concernés.
2. Identifier les opérations métier réellement utilisées.
3. Créer les ports côté `Domain`.
4. Implémenter les adaptateurs Doctrine côté `Infrastructure`.
5. Créer les handlers `Application`.
6. Réécrire les contrôleurs pour appeler ces handlers.
7. Supprimer les injections directes de repository legacy.
8. Linter le conteneur et vérifier les routes concernées.

## Check de fin de migration

Avant de considérer l'app comme "100% hexagonal", vérifier:

- `rg -n 'use App\\Repository\\' src/Application src/Domain` ne retourne rien
- `rg -n 'EntityManagerInterface' src/Application src/Domain` ne retourne rien
- les entités migrées n'ont plus de `repositoryClass` legacy
- chaque contexte métier principal a ses ports et ses adaptateurs
- les contrôleurs HTTP n'embarquent plus de logique métier significative

## Prochaine action

Commencer par `Category`, puis enchaîner sur `UserPageVisit`.

Ce sont les deux contextes qui réduiront le plus vite le couplage restant tout en préparant `SEO`, `Sitemap` et plusieurs endpoints admin.
