# Architecture Hexagonale - Option 2 : Entités dans Domain/

## Vue d'ensemble

Cette architecture place les entités directement dans le dossier `Domain/` par contexte métier, offrant une cohésion maximale entre entités, ports et exceptions.

## Principes

| Couche | Responsabilité | Dépendances autorisées |
|--------|----------------|------------------------|
| **Domain/** | Entités, Ports (interfaces), Exceptions | Aucune (pur PHP) |
| **Application/** | Commands, Queries, Handlers, DTOs | Domain uniquement |
| **Infrastructure/** | Implémentations Doctrine | Domain + Doctrine |
| **Controller/** | Adaptateurs HTTP | Application (handlers) |

---

## Arborescence cible

```
src/
├── Domain/
│   │
│   ├── Category/
│   │   ├── Entity/
│   │   │   └── Category.php
│   │   ├── Repository/
│   │   │   └── CategoryRepositoryInterface.php
│   │   └── Exception/
│   │       └── CategoryNotFoundException.php
│   │
│   ├── Content/
│   │   ├── Entity/
│   │   │   ├── Page.php
│   │   │   ├── PageContent.php
│   │   │   └── PageBlock.php
│   │   ├── Repository/
│   │   │   ├── PageRepositoryInterface.php
│   │   │   ├── PageContentRepositoryInterface.php
│   │   │   └── PageBlockRepositoryInterface.php
│   │   └── Exception/
│   │       ├── PageNotFoundException.php
│   │       └── PageContentNotFoundException.php
│   │
│   ├── Menu/
│   │   ├── Entity/
│   │   │   ├── Menus.php
│   │   │   ├── SuperMenu.php
│   │   │   └── PositionMenus.php
│   │   ├── Repository/
│   │   │   ├── MenuRepositoryInterface.php
│   │   │   ├── SuperMenuRepositoryInterface.php
│   │   │   └── PositionMenusRepositoryInterface.php
│   │   └── Exception/
│   │       └── MenuNotFoundException.php
│   │
│   ├── User/
│   │   ├── Entity/
│   │   │   ├── User.php
│   │   │   └── UserCustomization.php
│   │   ├── Repository/
│   │   │   ├── UserRepositoryInterface.php
│   │   │   └── UserCustomizationRepositoryInterface.php
│   │   └── Exception/
│   │       └── UserNotFoundException.php
│   │
│   ├── UserPageVisit/
│   │   ├── Entity/
│   │   │   └── UserPageVisit.php
│   │   ├── Repository/
│   │   │   └── UserPageVisitRepositoryInterface.php
│   │   └── Exception/
│   │       └── VisitNotFoundException.php
│   │
│   ├── Note/
│   │   ├── Entity/
│   │   │   └── Note.php
│   │   ├── Repository/
│   │   │   └── NoteRepositoryInterface.php
│   │   ├── Service/
│   │   │   └── NoteEncryptionInterface.php
│   │   └── Exception/
│   │       ├── NoteNotFoundException.php
│   │       └── UnauthorizedNoteAccessException.php
│   │
│   ├── Favorite/
│   │   ├── Entity/
│   │   │   └── Favorite.php
│   │   ├── Repository/
│   │   │   └── FavoriteRepositoryInterface.php
│   │   └── Exception/
│   │       ├── FavoriteNotFoundException.php
│   │       ├── FavoriteAlreadyExistsException.php
│   │       └── UnauthorizedFavoriteAccessException.php
│   │
│   ├── QCM/
│   │   ├── Entity/
│   │   │   ├── QCM.php
│   │   │   ├── ChoicesQCM.php
│   │   │   ├── LanguageQCM.php
│   │   │   └── NiveauQCM.php
│   │   ├── Repository/
│   │   │   ├── QCMRepositoryInterface.php
│   │   │   ├── ChoicesQCMRepositoryInterface.php
│   │   │   ├── LanguageQCMRepositoryInterface.php
│   │   │   └── NiveauQCMRepositoryInterface.php
│   │   └── Exception/
│   │       └── QCMNotFoundException.php
│   │
│   ├── Exercice/
│   │   ├── Entity/
│   │   │   ├── Exo.php
│   │   │   ├── ExoContent.php
│   │   │   ├── ExoBlock.php
│   │   │   └── ExoMenu.php
│   │   ├── Repository/
│   │   │   ├── ExoRepositoryInterface.php
│   │   │   ├── ExoContentRepositoryInterface.php
│   │   │   ├── ExoBlockRepositoryInterface.php
│   │   │   └── ExoMenuRepositoryInterface.php
│   │   └── Exception/
│   │       └── ExerciceNotFoundException.php
│   │
│   ├── Course/
│   │   ├── Entity/
│   │   │   ├── NiveauCours.php
│   │   │   └── DocDeCode.php
│   │   ├── Repository/
│   │   │   ├── NiveauCoursRepositoryInterface.php
│   │   │   └── DocDeCodeRepositoryInterface.php
│   │   └── Exception/
│   │       └── CourseNotFoundException.php
│   │
│   ├── Seo/
│   │   ├── Entity/
│   │   │   └── Seo.php
│   │   ├── Repository/
│   │   │   └── SeoRepositoryInterface.php
│   │   └── Exception/
│   │       └── SeoNotFoundException.php
│   │
│   ├── Config/
│   │   ├── Entity/
│   │   │   ├── SiteConfiguration.php
│   │   │   └── Logo.php
│   │   └── Repository/
│   │       ├── SiteConfigurationRepositoryInterface.php
│   │       └── LogoRepositoryInterface.php
│   │
│   └── AI/
│       ├── Entity/
│       │   └── PropositionIA.php
│       ├── Repository/
│       │   └── PropositionIARepositoryInterface.php
│       └── Exception/
│           └── PropositionNotFoundException.php
│
├── Application/
│   │
│   ├── Category/
│   │   ├── Command/
│   │   │   ├── CreateCategoryCommand.php
│   │   │   ├── UpdateCategoryCommand.php
│   │   │   └── DeleteCategoryCommand.php
│   │   ├── Query/
│   │   │   ├── GetCategoryByIdQuery.php
│   │   │   ├── GetCategoryByNameQuery.php
│   │   │   └── GetAllCategoriesQuery.php
│   │   ├── Handler/
│   │   │   ├── CreateCategoryHandler.php
│   │   │   ├── UpdateCategoryHandler.php
│   │   │   ├── DeleteCategoryHandler.php
│   │   │   ├── GetCategoryByIdHandler.php
│   │   │   ├── GetCategoryByNameHandler.php
│   │   │   └── GetAllCategoriesHandler.php
│   │   └── DTO/
│   │       └── CategoryDTO.php
│   │
│   ├── Content/
│   │   ├── Command/
│   │   │   ├── CreatePageCommand.php
│   │   │   ├── UpdatePageCommand.php
│   │   │   ├── DeletePageCommand.php
│   │   │   ├── CreatePageContentCommand.php
│   │   │   └── UpdatePageContentCommand.php
│   │   ├── Query/
│   │   │   ├── GetPageByIdQuery.php
│   │   │   ├── GetPageBySlugQuery.php
│   │   │   ├── GetAllPagesQuery.php
│   │   │   └── GetPageContentQuery.php
│   │   ├── Handler/
│   │   │   ├── CreatePageHandler.php
│   │   │   ├── UpdatePageHandler.php
│   │   │   ├── DeletePageHandler.php
│   │   │   ├── GetPageByIdHandler.php
│   │   │   ├── GetPageBySlugHandler.php
│   │   │   ├── GetAllPagesHandler.php
│   │   │   └── GetPageContentHandler.php
│   │   └── DTO/
│   │       ├── PageDTO.php
│   │       └── PageContentDTO.php
│   │
│   ├── Menu/
│   │   ├── Command/
│   │   │   ├── CreateMenuCommand.php
│   │   │   ├── UpdateMenuCommand.php
│   │   │   └── DeleteMenuCommand.php
│   │   ├── Query/
│   │   │   ├── GetMenusByCategoryQuery.php
│   │   │   ├── GetAllMenusQuery.php
│   │   │   └── GetMenuHierarchyQuery.php
│   │   ├── Handler/
│   │   │   ├── CreateMenuHandler.php
│   │   │   ├── UpdateMenuHandler.php
│   │   │   ├── DeleteMenuHandler.php
│   │   │   ├── GetMenusByCategoryHandler.php
│   │   │   ├── GetAllMenusHandler.php
│   │   │   └── GetMenuHierarchyHandler.php
│   │   └── DTO/
│   │       └── MenuDTO.php
│   │
│   ├── User/
│   │   ├── Command/
│   │   │   ├── RegisterUserCommand.php
│   │   │   └── UpdateCustomizationCommand.php
│   │   ├── Query/
│   │   │   ├── GetUserByIdQuery.php
│   │   │   └── GetUserCustomizationQuery.php
│   │   ├── Handler/
│   │   │   ├── RegisterUserHandler.php
│   │   │   ├── UpdateCustomizationHandler.php
│   │   │   ├── GetUserByIdHandler.php
│   │   │   └── GetUserCustomizationHandler.php
│   │   └── DTO/
│   │       ├── UserDTO.php
│   │       └── UserCustomizationDTO.php
│   │
│   ├── UserPageVisit/
│   │   ├── Command/
│   │   │   ├── RecordVisitCommand.php
│   │   │   └── CleanupOldVisitsCommand.php
│   │   ├── Query/
│   │   │   ├── GetRecentVisitsQuery.php
│   │   │   ├── GetMostVisitedPagesQuery.php
│   │   │   ├── GetVisitStatsQuery.php
│   │   │   └── GetGlobalStatsQuery.php
│   │   ├── Handler/
│   │   │   ├── RecordVisitHandler.php
│   │   │   ├── CleanupOldVisitsHandler.php
│   │   │   ├── GetRecentVisitsHandler.php
│   │   │   ├── GetMostVisitedPagesHandler.php
│   │   │   ├── GetVisitStatsHandler.php
│   │   │   └── GetGlobalStatsHandler.php
│   │   └── DTO/
│   │       ├── VisitDTO.php
│   │       └── VisitStatsDTO.php
│   │
│   ├── Note/
│   │   ├── Command/
│   │   │   ├── CreateOrUpdateNoteCommand.php
│   │   │   └── DeleteNoteCommand.php
│   │   ├── Query/
│   │   │   ├── GetNoteByPageQuery.php
│   │   │   └── GetUserNotesQuery.php
│   │   ├── Handler/
│   │   │   ├── CreateOrUpdateNoteHandler.php
│   │   │   ├── DeleteNoteHandler.php
│   │   │   ├── GetNoteByPageHandler.php
│   │   │   └── GetUserNotesHandler.php
│   │   └── DTO/
│   │       └── NoteDTO.php
│   │
│   ├── Favorite/
│   │   ├── Command/
│   │   │   ├── AddFavoriteCommand.php
│   │   │   ├── RemoveFavoriteCommand.php
│   │   │   └── ToggleFavoriteCommand.php
│   │   ├── Query/
│   │   │   ├── CheckFavoriteQuery.php
│   │   │   ├── GetFavoriteByIdQuery.php
│   │   │   └── GetUserFavoritesQuery.php
│   │   ├── Handler/
│   │   │   ├── AddFavoriteHandler.php
│   │   │   ├── RemoveFavoriteHandler.php
│   │   │   ├── ToggleFavoriteHandler.php
│   │   │   ├── CheckFavoriteHandler.php
│   │   │   ├── GetFavoriteByIdHandler.php
│   │   │   └── GetUserFavoritesHandler.php
│   │   └── DTO/
│   │       └── FavoriteDTO.php
│   │
│   ├── QCM/
│   │   ├── Command/
│   │   │   ├── CreateQCMCommand.php
│   │   │   ├── UpdateQCMCommand.php
│   │   │   └── DeleteQCMCommand.php
│   │   ├── Query/
│   │   │   ├── GetQCMByIdQuery.php
│   │   │   ├── GetQCMsByLanguageQuery.php
│   │   │   └── GetQCMsByNiveauQuery.php
│   │   ├── Handler/
│   │   │   ├── CreateQCMHandler.php
│   │   │   ├── UpdateQCMHandler.php
│   │   │   ├── DeleteQCMHandler.php
│   │   │   ├── GetQCMByIdHandler.php
│   │   │   ├── GetQCMsByLanguageHandler.php
│   │   │   └── GetQCMsByNiveauHandler.php
│   │   └── DTO/
│   │       ├── QCMDTO.php
│   │       └── ChoiceDTO.php
│   │
│   ├── Exercice/
│   │   ├── Command/
│   │   │   ├── CreateExoCommand.php
│   │   │   ├── UpdateExoCommand.php
│   │   │   └── DeleteExoCommand.php
│   │   ├── Query/
│   │   │   ├── GetExoByIdQuery.php
│   │   │   └── GetExosByCategoryQuery.php
│   │   ├── Handler/
│   │   │   ├── CreateExoHandler.php
│   │   │   ├── UpdateExoHandler.php
│   │   │   ├── DeleteExoHandler.php
│   │   │   ├── GetExoByIdHandler.php
│   │   │   └── GetExosByCategoryHandler.php
│   │   └── DTO/
│   │       └── ExerciceDTO.php
│   │
│   ├── Seo/
│   │   ├── Query/
│   │   │   ├── GetSeoForPageQuery.php
│   │   │   ├── GetSeoForCategoryQuery.php
│   │   │   └── GetSeoForSlugQuery.php
│   │   ├── Handler/
│   │   │   ├── GetSeoForPageHandler.php
│   │   │   ├── GetSeoForCategoryHandler.php
│   │   │   └── GetSeoForSlugHandler.php
│   │   └── DTO/
│   │       └── SeoDTO.php
│   │
│   └── AI/
│       ├── Command/
│       │   ├── CreatePropositionCommand.php
│       │   ├── AcceptPropositionCommand.php
│       │   ├── RejectPropositionCommand.php
│       │   └── DeletePropositionCommand.php
│       ├── Query/
│       │   └── GetPropositionsQuery.php
│       ├── Handler/
│       │   ├── CreatePropositionHandler.php
│       │   ├── AcceptPropositionHandler.php
│       │   ├── RejectPropositionHandler.php
│       │   ├── DeletePropositionHandler.php
│       │   └── GetPropositionsHandler.php
│       └── DTO/
│           └── PropositionDTO.php
│
├── Infrastructure/
│   │
│   ├── Category/
│   │   └── Repository/
│   │       └── DoctrineCategoryRepository.php
│   │
│   ├── Content/
│   │   └── Repository/
│   │       ├── DoctrinePageRepository.php
│   │       ├── DoctrinePageContentRepository.php
│   │       └── DoctrinePageBlockRepository.php
│   │
│   ├── Menu/
│   │   └── Repository/
│   │       ├── DoctrineMenuRepository.php
│   │       ├── DoctrineSuperMenuRepository.php
│   │       └── DoctrinePositionMenusRepository.php
│   │
│   ├── User/
│   │   └── Repository/
│   │       ├── DoctrineUserRepository.php
│   │       └── DoctrineUserCustomizationRepository.php
│   │
│   ├── UserPageVisit/
│   │   └── Repository/
│   │       └── DoctrineUserPageVisitRepository.php
│   │
│   ├── Note/
│   │   ├── Repository/
│   │   │   └── DoctrineNoteRepository.php
│   │   └── Service/
│   │       └── NoteEncryptionService.php
│   │
│   ├── Favorite/
│   │   └── Repository/
│   │       └── DoctrineFavoriteRepository.php
│   │
│   ├── QCM/
│   │   └── Repository/
│   │       ├── DoctrineQCMRepository.php
│   │       ├── DoctrineChoicesQCMRepository.php
│   │       ├── DoctrineLanguageQCMRepository.php
│   │       └── DoctrineNiveauQCMRepository.php
│   │
│   ├── Exercice/
│   │   └── Repository/
│   │       ├── DoctrineExoRepository.php
│   │       ├── DoctrineExoContentRepository.php
│   │       ├── DoctrineExoBlockRepository.php
│   │       └── DoctrineExoMenuRepository.php
│   │
│   ├── Course/
│   │   └── Repository/
│   │       ├── DoctrineNiveauCoursRepository.php
│   │       └── DoctrineDocDeCodeRepository.php
│   │
│   ├── Seo/
│   │   └── Repository/
│   │       └── DoctrineSeoRepository.php
│   │
│   ├── Config/
│   │   └── Repository/
│   │       ├── DoctrineSiteConfigurationRepository.php
│   │       └── DoctrineLogoRepository.php
│   │
│   └── AI/
│       └── Repository/
│           └── DoctrinePropositionIARepository.php
│
├── Controller/
│   ├── Api/
│   │   ├── CategoryApiController.php
│   │   ├── ContentApiController.php
│   │   ├── MenuApiController.php
│   │   ├── FavoriteApiController.php
│   │   ├── NoteApiController.php
│   │   ├── QCMApiController.php
│   │   ├── UserPageVisitApiController.php
│   │   ├── CustomizationController.php
│   │   └── AdminCrud/
│   │       ├── ApiCategoryController.php
│   │       ├── ApiPageController.php
│   │       ├── ApiPageContentController.php
│   │       ├── ApiMenuController.php
│   │       └── ApiExoContentController.php
│   ├── Admin/
│   │   ├── DashboardController.php
│   │   ├── PageVisitStatsController.php
│   │   └── SiteConfigurationCrudController.php
│   ├── SitemapController.php
│   ├── SeoController.php
│   ├── PropositionIAController.php
│   ├── PageVisitController.php
│   └── RegistrationController.php
│
├── Command/
│   └── CleanupPageVisitsCommand.php
│
├── EventListener/
│   ├── UserPageVisitListener.php
│   └── PageContentSanitizerListener.php
│
├── Security/
│   └── ApiKeyAuthenticator.php
│
└── Repository/
    └── (VIDE - supprimé après migration)
```

---

## Configuration Doctrine

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'

    orm:
        auto_generate_proxy_classes: true
        enable_lazy_ghost_objects: true
        report_fields_where_declared: true
        validate_xml_mapping: true
        naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
        auto_mapping: false

        mappings:
            Category:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Domain/Category/Entity'
                prefix: 'App\Domain\Category\Entity'
                alias: Category

            Content:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Domain/Content/Entity'
                prefix: 'App\Domain\Content\Entity'
                alias: Content

            Menu:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Domain/Menu/Entity'
                prefix: 'App\Domain\Menu\Entity'
                alias: Menu

            User:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Domain/User/Entity'
                prefix: 'App\Domain\User\Entity'
                alias: User

            UserPageVisit:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Domain/UserPageVisit/Entity'
                prefix: 'App\Domain\UserPageVisit\Entity'
                alias: UserPageVisit

            Note:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Domain/Note/Entity'
                prefix: 'App\Domain\Note\Entity'
                alias: Note

            Favorite:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Domain/Favorite/Entity'
                prefix: 'App\Domain\Favorite\Entity'
                alias: Favorite

            QCM:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Domain/QCM/Entity'
                prefix: 'App\Domain\QCM\Entity'
                alias: QCM

            Exercice:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Domain/Exercice/Entity'
                prefix: 'App\Domain\Exercice\Entity'
                alias: Exercice

            Course:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Domain/Course/Entity'
                prefix: 'App\Domain\Course\Entity'
                alias: Course

            Seo:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Domain/Seo/Entity'
                prefix: 'App\Domain\Seo\Entity'
                alias: Seo

            Config:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Domain/Config/Entity'
                prefix: 'App\Domain\Config\Entity'
                alias: Config

            AI:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Domain/AI/Entity'
                prefix: 'App\Domain\AI\Entity'
                alias: AI
```

---

## Mapping des namespaces

### Entités

| Avant | Après |
|-------|-------|
| `App\Entity\Category` | `App\Domain\Category\Entity\Category` |
| `App\Entity\Page` | `App\Domain\Content\Entity\Page` |
| `App\Entity\PageContent` | `App\Domain\Content\Entity\PageContent` |
| `App\Entity\PageBlock` | `App\Domain\Content\Entity\PageBlock` |
| `App\Entity\Menus` | `App\Domain\Menu\Entity\Menus` |
| `App\Entity\SuperMenu` | `App\Domain\Menu\Entity\SuperMenu` |
| `App\Entity\PositionMenus` | `App\Domain\Menu\Entity\PositionMenus` |
| `App\Entity\User` | `App\Domain\User\Entity\User` |
| `App\Entity\UserCustomization` | `App\Domain\User\Entity\UserCustomization` |
| `App\Entity\UserPageVisit` | `App\Domain\UserPageVisit\Entity\UserPageVisit` |
| `App\Entity\Note` | `App\Domain\Note\Entity\Note` |
| `App\Entity\Favorite` | `App\Domain\Favorite\Entity\Favorite` |
| `App\Entity\QCM` | `App\Domain\QCM\Entity\QCM` |
| `App\Entity\ChoicesQCM` | `App\Domain\QCM\Entity\ChoicesQCM` |
| `App\Entity\LanguageQCM` | `App\Domain\QCM\Entity\LanguageQCM` |
| `App\Entity\NiveauQCM` | `App\Domain\QCM\Entity\NiveauQCM` |
| `App\Entity\Exo` | `App\Domain\Exercice\Entity\Exo` |
| `App\Entity\ExoContent` | `App\Domain\Exercice\Entity\ExoContent` |
| `App\Entity\ExoBlock` | `App\Domain\Exercice\Entity\ExoBlock` |
| `App\Entity\ExoMenu` | `App\Domain\Exercice\Entity\ExoMenu` |
| `App\Entity\NiveauCours` | `App\Domain\Course\Entity\NiveauCours` |
| `App\Entity\DocDeCode` | `App\Domain\Course\Entity\DocDeCode` |
| `App\Entity\Seo` | `App\Domain\Seo\Entity\Seo` |
| `App\Entity\SiteConfiguration` | `App\Domain\Config\Entity\SiteConfiguration` |
| `App\Entity\Logo` | `App\Domain\Config\Entity\Logo` |
| `App\Entity\PropositionIA` | `App\Domain\AI\Entity\PropositionIA` |

### Repositories (ports)

| Contexte | Interface |
|----------|-----------|
| Category | `App\Domain\Category\Repository\CategoryRepositoryInterface` |
| Content | `App\Domain\Content\Repository\PageRepositoryInterface` |
| Content | `App\Domain\Content\Repository\PageContentRepositoryInterface` |
| Menu | `App\Domain\Menu\Repository\MenuRepositoryInterface` |
| User | `App\Domain\User\Repository\UserRepositoryInterface` |
| UserPageVisit | `App\Domain\UserPageVisit\Repository\UserPageVisitRepositoryInterface` |
| Note | `App\Domain\Note\Repository\NoteRepositoryInterface` |
| Favorite | `App\Domain\Favorite\Repository\FavoriteRepositoryInterface` |
| QCM | `App\Domain\QCM\Repository\QCMRepositoryInterface` |
| Exercice | `App\Domain\Exercice\Repository\ExoRepositoryInterface` |
| Seo | `App\Domain\Seo\Repository\SeoRepositoryInterface` |
| AI | `App\Domain\AI\Repository\PropositionIARepositoryInterface` |

### Repositories (implémentations)

| Port | Implémentation |
|------|----------------|
| `CategoryRepositoryInterface` | `App\Infrastructure\Category\Repository\DoctrineCategoryRepository` |
| `PageRepositoryInterface` | `App\Infrastructure\Content\Repository\DoctrinePageRepository` |
| `MenuRepositoryInterface` | `App\Infrastructure\Menu\Repository\DoctrineMenuRepository` |
| `UserRepositoryInterface` | `App\Infrastructure\User\Repository\DoctrineUserRepository` |
| `UserPageVisitRepositoryInterface` | `App\Infrastructure\UserPageVisit\Repository\DoctrineUserPageVisitRepository` |
| `NoteRepositoryInterface` | `App\Infrastructure\Note\Repository\DoctrineNoteRepository` |
| `FavoriteRepositoryInterface` | `App\Infrastructure\Favorite\Repository\DoctrineFavoriteRepository` |
| `QCMRepositoryInterface` | `App\Infrastructure\QCM\Repository\DoctrineQCMRepository` |
| `ExoRepositoryInterface` | `App\Infrastructure\Exercice\Repository\DoctrineExoRepository` |
| `SeoRepositoryInterface` | `App\Infrastructure\Seo\Repository\DoctrineSeoRepository` |
| `PropositionIARepositoryInterface` | `App\Infrastructure\AI\Repository\DoctrinePropositionIARepository` |

---

## Exemples de code

### Entité (Domain)

```php
<?php
// src/Domain/Category/Entity/Category.php

declare(strict_types=1);

namespace App\Domain\Category\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'category')]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    // Getters/Setters...
}
```

### Port (Domain)

```php
<?php
// src/Domain/Category/Repository/CategoryRepositoryInterface.php

declare(strict_types=1);

namespace App\Domain\Category\Repository;

use App\Domain\Category\Entity\Category;

interface CategoryRepositoryInterface
{
    public function findById(int $id): ?Category;

    public function findByName(string $name): ?Category;

    /** @return Category[] */
    public function findAll(): array;

    public function save(Category $category): void;

    public function delete(Category $category): void;
}
```

### Exception (Domain)

```php
<?php
// src/Domain/Category/Exception/CategoryNotFoundException.php

declare(strict_types=1);

namespace App\Domain\Category\Exception;

class CategoryNotFoundException extends \DomainException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Category with ID %d not found', $id));
    }

    public static function withName(string $name): self
    {
        return new self(sprintf('Category "%s" not found', $name));
    }
}
```

### Implémentation Doctrine (Infrastructure)

```php
<?php
// src/Infrastructure/Category/Repository/DoctrineCategoryRepository.php

declare(strict_types=1);

namespace App\Infrastructure\Category\Repository;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineCategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?Category
    {
        return $this->em->find(Category::class, $id);
    }

    public function findByName(string $name): ?Category
    {
        return $this->em->getRepository(Category::class)
            ->findOneBy(['name' => $name]);
    }

    public function findAll(): array
    {
        return $this->em->getRepository(Category::class)->findAll();
    }

    public function save(Category $category): void
    {
        $this->em->persist($category);
        $this->em->flush();
    }

    public function delete(Category $category): void
    {
        $this->em->remove($category);
        $this->em->flush();
    }
}
```

### Handler (Application)

```php
<?php
// src/Application/Category/Handler/GetCategoryByNameHandler.php

declare(strict_types=1);

namespace App\Application\Category\Handler;

use App\Application\Category\Query\GetCategoryByNameQuery;
use App\Application\Category\DTO\CategoryDTO;
use App\Domain\Category\Repository\CategoryRepositoryInterface;
use App\Domain\Category\Exception\CategoryNotFoundException;

class GetCategoryByNameHandler
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function handle(GetCategoryByNameQuery $query): CategoryDTO
    {
        $category = $this->repository->findByName($query->name);

        if (!$category) {
            throw CategoryNotFoundException::withName($query->name);
        }

        return CategoryDTO::fromEntity($category);
    }
}
```

### Controller (mince)

```php
<?php
// src/Controller/Api/CategoryApiController.php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\Category\Handler\GetCategoryByNameHandler;
use App\Application\Category\Handler\GetAllCategoriesHandler;
use App\Application\Category\Query\GetCategoryByNameQuery;
use App\Application\Category\Query\GetAllCategoriesQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/categories')]
class CategoryApiController extends AbstractController
{
    public function __construct(
        private GetCategoryByNameHandler $getCategoryByNameHandler,
        private GetAllCategoriesHandler $getAllCategoriesHandler
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $categories = $this->getAllCategoriesHandler->handle(new GetAllCategoriesQuery());
        return $this->json($categories);
    }

    #[Route('/{name}', methods: ['GET'])]
    public function show(string $name): JsonResponse
    {
        $category = $this->getCategoryByNameHandler->handle(new GetCategoryByNameQuery($name));
        return $this->json($category);
    }
}
```

---

## Configuration services.yaml

```yaml
# config/services.yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    # Domain - pas de config nécessaire (interfaces)

    # Application - Handlers auto-découverts
    App\Application\:
        resource: '../src/Application/'

    # Infrastructure - Binding des interfaces aux implémentations
    App\Domain\Category\Repository\CategoryRepositoryInterface:
        class: App\Infrastructure\Category\Repository\DoctrineCategoryRepository

    App\Domain\Content\Repository\PageRepositoryInterface:
        class: App\Infrastructure\Content\Repository\DoctrinePageRepository

    App\Domain\Content\Repository\PageContentRepositoryInterface:
        class: App\Infrastructure\Content\Repository\DoctrinePageContentRepository

    App\Domain\Menu\Repository\MenuRepositoryInterface:
        class: App\Infrastructure\Menu\Repository\DoctrineMenuRepository

    App\Domain\User\Repository\UserRepositoryInterface:
        class: App\Infrastructure\User\Repository\DoctrineUserRepository

    App\Domain\UserPageVisit\Repository\UserPageVisitRepositoryInterface:
        class: App\Infrastructure\UserPageVisit\Repository\DoctrineUserPageVisitRepository

    App\Domain\Note\Repository\NoteRepositoryInterface:
        class: App\Infrastructure\Note\Repository\DoctrineNoteRepository

    App\Domain\Note\Service\NoteEncryptionInterface:
        class: App\Infrastructure\Note\Service\NoteEncryptionService

    App\Domain\Favorite\Repository\FavoriteRepositoryInterface:
        class: App\Infrastructure\Favorite\Repository\DoctrineFavoriteRepository

    App\Domain\QCM\Repository\QCMRepositoryInterface:
        class: App\Infrastructure\QCM\Repository\DoctrineQCMRepository

    App\Domain\Exercice\Repository\ExoRepositoryInterface:
        class: App\Infrastructure\Exercice\Repository\DoctrineExoRepository

    App\Domain\Seo\Repository\SeoRepositoryInterface:
        class: App\Infrastructure\Seo\Repository\DoctrineSeoRepository

    App\Domain\AI\Repository\PropositionIARepositoryInterface:
        class: App\Infrastructure\AI\Repository\DoctrinePropositionIARepository

    # Controllers
    App\Controller\:
        resource: '../src/Controller/'
        tags: ['controller.service_arguments']

    # Infrastructure auto-découverte
    App\Infrastructure\:
        resource: '../src/Infrastructure/'
```

---

## Vérification finale

```bash
# Ces commandes doivent retourner 0 résultats :

# Aucun import de repository legacy dans Application ou Domain
rg -n 'use App\\Repository\\' src/Application src/Domain

# Aucun EntityManagerInterface dans Application ou Domain
rg -n 'EntityManagerInterface' src/Application src/Domain

# Le dossier src/Repository doit être vide
ls src/Repository/

# Vérifier que Doctrine trouve toutes les entités
php bin/console doctrine:mapping:info

# Vérifier le schéma
php bin/console doctrine:schema:validate
```

---

## Ordre de migration recommandé

1. **Category** - 8 fichiers dépendants
2. **UserPageVisit** - 6 fichiers dépendants
3. **Menu** - 5 fichiers dépendants
4. **Content (Page/PageContent)** - déjà partiellement fait
5. **Seo** - dépend de Category
6. **QCM** - contexte isolé
7. **Exercice** - contexte isolé
8. **User** - authentification
9. **Config** - administration
10. **AI** - propositions IA

---

## Schéma visuel

```
┌─────────────────────────────────────────────────────────────────┐
│                           Domain/                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  Category/   │  │   Content/   │  │    Menu/     │   ...    │
│  │  ├─Entity/   │  │  ├─Entity/   │  │  ├─Entity/   │          │
│  │  │ Category  │  │  │ Page      │  │  │ Menus     │          │
│  │  ├─Repository│  │  │ PageContent│ │  ├─Repository│          │
│  │  │ Interface │  │  ├─Repository│  │  │ Interface │          │
│  │  └─Exception/│  │  └─Exception/│  │  └─Exception/│          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
└─────────────────────────────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                         Application/                            │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  Category/   │  │   Content/   │  │    Menu/     │   ...    │
│  │  ├─Command/  │  │  ├─Command/  │  │  ├─Command/  │          │
│  │  ├─Query/    │  │  ├─Query/    │  │  ├─Query/    │          │
│  │  ├─Handler/  │  │  ├─Handler/  │  │  ├─Handler/  │          │
│  │  └─DTO/      │  │  └─DTO/      │  │  └─DTO/      │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
└─────────────────────────────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                        Infrastructure/                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  Category/   │  │   Content/   │  │    Menu/     │   ...    │
│  │  └─Repository│  │  └─Repository│  │  └─Repository│          │
│  │    Doctrine  │  │    Doctrine  │  │    Doctrine  │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
└─────────────────────────────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│               Controller/ + Command/ + EventListener/           │
│                        (Adaptateurs minces)                     │
│           Injectent des Handlers, jamais des Repositories       │
└─────────────────────────────────────────────────────────────────┘
```
