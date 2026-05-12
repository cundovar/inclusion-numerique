# Architecture Hexagonale - Option 1 : Entités dans Entity/

## Vue d'ensemble

Cette architecture garde les entités dans `src/Entity/` organisées par sous-dossiers, tout en appliquant les principes hexagonaux. Basé sur la structure actuelle de la codebase (Note, Favorite, Page).

## Structure actuelle validée

```
src/
├── Entity/                     # Entités Doctrine (avec attributs ORM)
│   └── Note.php                # namespace App\Entity
│
├── Domain/
│   └── Note/
│       ├── Repository/
│       │   └── NoteRepositoryInterface.php
│       ├── Service/
│       │   └── NoteEncryptionInterface.php
│       └── Exception/
│           ├── NoteNotFoundException.php
│           └── UnauthorizedNoteAccessException.php
│
├── Application/
│   └── Note/
│       ├── Command/
│       │   ├── CreateOrUpdateNoteCommand.php
│       │   └── DeleteNoteCommand.php
│       ├── Query/
│       │   ├── GetNoteByPageQuery.php
│       │   └── GetUserNotesQuery.php
│       ├── Handler/
│       │   ├── CreateOrUpdateNoteHandler.php
│       │   ├── DeleteNoteHandler.php
│       │   ├── GetNoteByPageHandler.php
│       │   └── GetUserNotesHandler.php
│       └── DTO/
│           └── NoteDTO.php
│
└── Infrastructure/
    └── Note/
        ├── Repository/
        │   └── DoctrineNoteRepository.php
        └── Service/
            └── NoteEncryptionService.php
```

---

## Arborescence cible complète

```
src/
├── Entity/
│   │
│   ├── Category/
│   │   └── Category.php
│   │
│   ├── Content/
│   │   ├── Page.php
│   │   ├── PageContent.php
│   │   └── PageBlock.php
│   │
│   ├── Menu/
│   │   ├── Menus.php
│   │   ├── SuperMenu.php
│   │   └── PositionMenus.php
│   │
│   ├── User/
│   │   ├── User.php
│   │   └── UserCustomization.php
│   │
│   ├── UserPageVisit/
│   │   └── UserPageVisit.php
│   │
│   ├── Note/
│   │   └── Note.php
│   │
│   ├── Favorite/
│   │   └── Favorite.php
│   │
│   ├── QCM/
│   │   ├── QCM.php
│   │   ├── ChoicesQCM.php
│   │   ├── LanguageQCM.php
│   │   └── NiveauQCM.php
│   │
│   ├── Exercice/
│   │   ├── Exo.php
│   │   ├── ExoContent.php
│   │   ├── ExoBlock.php
│   │   └── ExoMenu.php
│   │
│   ├── Course/
│   │   ├── NiveauCours.php
│   │   └── DocDeCode.php
│   │
│   ├── Seo/
│   │   └── Seo.php
│   │
│   ├── Config/
│   │   ├── SiteConfiguration.php
│   │   └── Logo.php
│   │
│   └── AI/
│       └── PropositionIA.php
│
├── Domain/
│   │
│   ├── Category/
│   │   ├── Repository/
│   │   │   └── CategoryRepositoryInterface.php
│   │   └── Exception/
│   │       └── CategoryNotFoundException.php
│   │
│   ├── Content/
│   │   ├── Repository/
│   │   │   ├── PageRepositoryInterface.php
│   │   │   └── PageContentRepositoryInterface.php
│   │   └── Exception/
│   │       └── PageNotFoundException.php
│   │
│   ├── Menu/
│   │   ├── Repository/
│   │   │   └── MenuRepositoryInterface.php
│   │   └── Exception/
│   │       └── MenuNotFoundException.php
│   │
│   ├── User/
│   │   ├── Repository/
│   │   │   └── UserRepositoryInterface.php
│   │   └── Exception/
│   │       └── UserNotFoundException.php
│   │
│   ├── UserPageVisit/
│   │   ├── Repository/
│   │   │   └── UserPageVisitRepositoryInterface.php
│   │   └── Exception/
│   │       └── VisitNotFoundException.php
│   │
│   ├── Note/
│   │   ├── Repository/
│   │   │   └── NoteRepositoryInterface.php
│   │   ├── Service/
│   │   │   └── NoteEncryptionInterface.php
│   │   └── Exception/
│   │       ├── NoteNotFoundException.php
│   │       └── UnauthorizedNoteAccessException.php
│   │
│   ├── Favorite/
│   │   ├── Repository/
│   │   │   └── FavoriteRepositoryInterface.php
│   │   └── Exception/
│   │       ├── FavoriteNotFoundException.php
│   │       ├── FavoriteAlreadyExistsException.php
│   │       └── UnauthorizedFavoriteAccessException.php
│   │
│   ├── QCM/
│   │   ├── Repository/
│   │   │   └── QCMRepositoryInterface.php
│   │   └── Exception/
│   │       └── QCMNotFoundException.php
│   │
│   ├── Exercice/
│   │   ├── Repository/
│   │   │   └── ExoRepositoryInterface.php
│   │   └── Exception/
│   │       └── ExerciceNotFoundException.php
│   │
│   ├── Seo/
│   │   ├── Repository/
│   │   │   └── SeoRepositoryInterface.php
│   │   └── Exception/
│   │       └── SeoNotFoundException.php
│   │
│   └── AI/
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
│   │   │   └── DeletePageCommand.php
│   │   ├── Query/
│   │   │   ├── GetPageByIdQuery.php
│   │   │   ├── GetPageBySlugQuery.php
│   │   │   └── GetAllPagesQuery.php
│   │   ├── Handler/
│   │   │   ├── CreatePageHandler.php
│   │   │   ├── UpdatePageHandler.php
│   │   │   ├── DeletePageHandler.php
│   │   │   ├── GetPageByIdHandler.php
│   │   │   ├── GetPageBySlugHandler.php
│   │   │   └── GetAllPagesHandler.php
│   │   └── DTO/
│   │       └── PageDTO.php
│   │
│   ├── Menu/
│   │   ├── Command/
│   │   │   ├── CreateMenuCommand.php
│   │   │   ├── UpdateMenuCommand.php
│   │   │   └── DeleteMenuCommand.php
│   │   ├── Query/
│   │   │   ├── GetMenusByCategoryQuery.php
│   │   │   └── GetAllMenusQuery.php
│   │   ├── Handler/
│   │   │   ├── CreateMenuHandler.php
│   │   │   ├── UpdateMenuHandler.php
│   │   │   ├── DeleteMenuHandler.php
│   │   │   ├── GetMenusByCategoryHandler.php
│   │   │   └── GetAllMenusHandler.php
│   │   └── DTO/
│   │       └── MenuDTO.php
│   │
│   ├── UserPageVisit/
│   │   ├── Command/
│   │   │   ├── RecordVisitCommand.php
│   │   │   └── CleanupOldVisitsCommand.php
│   │   ├── Query/
│   │   │   ├── GetRecentVisitsQuery.php
│   │   │   ├── GetMostVisitedPagesQuery.php
│   │   │   └── GetGlobalStatsQuery.php
│   │   ├── Handler/
│   │   │   ├── RecordVisitHandler.php
│   │   │   ├── CleanupOldVisitsHandler.php
│   │   │   ├── GetRecentVisitsHandler.php
│   │   │   ├── GetMostVisitedPagesHandler.php
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
│   │   │   └── ...
│   │   ├── Query/
│   │   │   ├── GetQCMByIdQuery.php
│   │   │   └── GetQCMsByLanguageQuery.php
│   │   ├── Handler/
│   │   │   └── ...
│   │   └── DTO/
│   │       └── QCMDTO.php
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
│   │       └── DoctrinePageContentRepository.php
│   │
│   ├── Menu/
│   │   └── Repository/
│   │       └── DoctrineMenuRepository.php
│   │
│   ├── User/
│   │   └── Repository/
│   │       └── DoctrineUserRepository.php
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
│   │       └── DoctrineQCMRepository.php
│   │
│   ├── Seo/
│   │   └── Repository/
│   │       └── DoctrineSeoRepository.php
│   │
│   └── AI/
│       └── Repository/
│           └── DoctrinePropositionIARepository.php
│
├── Controller/
│   ├── Api/
│   │   └── AdminCrud/
│   │       ├── ApiCategoryController.php
│   │       ├── ApiPageController.php
│   │       ├── ApiPageContentController.php
│   │       ├── ApiMenuController.php
│   │       └── ApiExoContentController.php
│   ├── Admin/
│   │   ├── DashboardController.php
│   │   └── PageVisitStatsController.php
│   ├── SitemapController.php
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
            App:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Entity'
                prefix: 'App\Entity'
                alias: App
```

---

## Exemples de code (basés sur la codebase actuelle)

### Entité (Entity/)

```php
<?php
// src/Entity/Note/Note.php

namespace App\Entity\Note;

use App\Entity\User\User;
use App\Entity\Content\Page;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Note
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['note:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Page::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['note:read'])]
    private ?Page $page = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['note:read', 'note:write'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['note:read', 'note:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['note:read', 'note:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): static
    {
        $this->page = $page;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
```

### Port Repository (Domain/)

```php
<?php
// src/Domain/Note/Repository/NoteRepositoryInterface.php

declare(strict_types=1);

namespace App\Domain\Note\Repository;

use App\Entity\Note\Note;
use App\Entity\User\User;
use App\Entity\Content\Page;

interface NoteRepositoryInterface
{
    public function findById(int $id): ?Note;

    public function findByUserAndPage(User $user, Page $page): ?Note;

    /**
     * @return Note[]
     */
    public function findAllByUser(User $user): array;

    public function save(Note $note): void;

    public function delete(Note $note): void;
}
```

### Port Service (Domain/)

```php
<?php
// src/Domain/Note/Service/NoteEncryptionInterface.php

declare(strict_types=1);

namespace App\Domain\Note\Service;

interface NoteEncryptionInterface
{
    public function encrypt(string $content): string;

    public function decrypt(string $encryptedContent): string;
}
```

### Exception (Domain/)

```php
<?php
// src/Domain/Favorite/Exception/FavoriteAlreadyExistsException.php

declare(strict_types=1);

namespace App\Domain\Favorite\Exception;

class FavoriteAlreadyExistsException extends \DomainException
{
    public static function forUserAndPage(int $userId, int $pageId): self
    {
        return new self(sprintf(
            'Un favori existe déjà pour l\'utilisateur %d et la page %d',
            $userId,
            $pageId
        ));
    }
}
```

### Command (Application/)

```php
<?php
// src/Application/Note/Command/CreateOrUpdateNoteCommand.php

declare(strict_types=1);

namespace App\Application\Note\Command;

use App\Entity\User\User;

final class CreateOrUpdateNoteCommand
{
    public function __construct(
        public readonly User $user,
        public readonly int $pageId,
        public readonly string $content
    ) {}
}
```

### Query (Application/)

```php
<?php
// src/Application/Note/Query/GetNoteByPageQuery.php

declare(strict_types=1);

namespace App\Application\Note\Query;

use App\Entity\User\User;

final class GetNoteByPageQuery
{
    public function __construct(
        public readonly User $user,
        public readonly int $pageId
    ) {}
}
```

### DTO (Application/)

```php
<?php
// src/Application/Note/DTO/NoteDTO.php

declare(strict_types=1);

namespace App\Application\Note\DTO;

use App\Entity\Note\Note;

final class NoteDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $pageId,
        public readonly ?string $pageSlug,
        public readonly string $content,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {}

    public static function fromEntity(Note $note, string $decryptedContent): self
    {
        return new self(
            id: $note->getId(),
            pageId: $note->getPage()->getId(),
            pageSlug: $note->getPage()->getSlug(),
            content: $decryptedContent,
            createdAt: $note->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $note->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'pageId' => $this->pageId,
            'page' => [
                'id' => $this->pageId,
                'slug' => $this->pageSlug
            ],
            'content' => $this->content,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }
}
```

### Handler (Application/)

```php
<?php
// src/Application/Note/Handler/CreateOrUpdateNoteHandler.php

declare(strict_types=1);

namespace App\Application\Note\Handler;

use App\Application\Note\Command\CreateOrUpdateNoteCommand;
use App\Application\Note\DTO\NoteDTO;
use App\Domain\Note\Repository\NoteRepositoryInterface;
use App\Domain\Content\Repository\PageRepositoryInterface;
use App\Domain\Note\Service\NoteEncryptionInterface;
use App\Entity\Note\Note;

final class CreateOrUpdateNoteHandler
{
    public function __construct(
        private NoteRepositoryInterface $noteRepository,
        private PageRepositoryInterface $pageRepository,
        private NoteEncryptionInterface $encryption
    ) {}

    public function handle(CreateOrUpdateNoteCommand $command): NoteDTO
    {
        $page = $this->pageRepository->findById($command->pageId);

        if (!$page) {
            throw new \InvalidArgumentException('Page non trouvée');
        }

        // Chercher une note existante ou en créer une nouvelle
        $note = $this->noteRepository->findByUserAndPage($command->user, $page);

        if (!$note) {
            $note = new Note();
            $note->setUser($command->user);
            $note->setPage($page);
        }

        // Chiffrer et sauvegarder
        $encryptedContent = $this->encryption->encrypt($command->content);
        $note->setContent($encryptedContent);

        $this->noteRepository->save($note);

        return NoteDTO::fromEntity($note, $command->content);
    }
}
```

```php
<?php
// src/Application/Favorite/Handler/AddFavoriteHandler.php

declare(strict_types=1);

namespace App\Application\Favorite\Handler;

use App\Application\Favorite\Command\AddFavoriteCommand;
use App\Application\Favorite\DTO\FavoriteDTO;
use App\Domain\Favorite\Exception\FavoriteAlreadyExistsException;
use App\Domain\Favorite\Repository\FavoriteRepositoryInterface;
use App\Domain\Content\Repository\PageRepositoryInterface;
use App\Entity\Favorite\Favorite;

final class AddFavoriteHandler
{
    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository,
        private PageRepositoryInterface $pageRepository
    ) {}

    public function handle(AddFavoriteCommand $command): FavoriteDTO
    {
        $page = $this->pageRepository->findById($command->pageId);

        if (!$page) {
            throw new \InvalidArgumentException('Page non trouvée');
        }

        // Vérifier si le favori existe déjà
        $existing = $this->favoriteRepository->findByUserAndPage($command->user, $page);
        if ($existing) {
            throw FavoriteAlreadyExistsException::forUserAndPage(
                $command->user->getId(),
                $command->pageId
            );
        }

        $favorite = new Favorite();
        $favorite->setUser($command->user);
        $favorite->setPage($page);

        $this->favoriteRepository->save($favorite);

        return FavoriteDTO::fromEntity($favorite);
    }
}
```

### Implémentation Doctrine (Infrastructure/)

```php
<?php
// src/Infrastructure/Note/Repository/DoctrineNoteRepository.php

declare(strict_types=1);

namespace App\Infrastructure\Note\Repository;

use App\Domain\Note\Repository\NoteRepositoryInterface;
use App\Entity\Note\Note;
use App\Entity\Content\Page;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineNoteRepository implements NoteRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?Note
    {
        return $this->em->find(Note::class, $id);
    }

    public function findByUserAndPage(User $user, Page $page): ?Note
    {
        return $this->em->getRepository(Note::class)->findOneBy([
            'user' => $user,
            'page' => $page
        ]);
    }

    /**
     * @return Note[]
     */
    public function findAllByUser(User $user): array
    {
        return $this->em->getRepository(Note::class)->findBy(
            ['user' => $user],
            ['updatedAt' => 'DESC']
        );
    }

    public function save(Note $note): void
    {
        $this->em->persist($note);
        $this->em->flush();
    }

    public function delete(Note $note): void
    {
        $this->em->remove($note);
        $this->em->flush();
    }
}
```

### Service Implémentation (Infrastructure/)

```php
<?php
// src/Infrastructure/Note/Service/NoteEncryptionService.php

declare(strict_types=1);

namespace App\Infrastructure\Note\Service;

use App\Domain\Note\Service\NoteEncryptionInterface;

final class NoteEncryptionService implements NoteEncryptionInterface
{
    private string $encryptionKey;

    public function __construct(string $encryptionKey)
    {
        $this->encryptionKey = $encryptionKey;
    }

    public function encrypt(string $content): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt(
            $content,
            'AES-256-CBC',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        return base64_encode($iv . $encrypted);
    }

    public function decrypt(string $encryptedContent): string
    {
        $data = base64_decode($encryptedContent);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        return openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );
    }
}
```

---

## Mapping des namespaces

### Entités

| Avant | Après |
|-------|-------|
| `App\Entity\Category` | `App\Entity\Category\Category` |
| `App\Entity\Page` | `App\Entity\Content\Page` |
| `App\Entity\PageContent` | `App\Entity\Content\PageContent` |
| `App\Entity\Menus` | `App\Entity\Menu\Menus` |
| `App\Entity\User` | `App\Entity\User\User` |
| `App\Entity\UserPageVisit` | `App\Entity\UserPageVisit\UserPageVisit` |
| `App\Entity\Note` | `App\Entity\Note\Note` |
| `App\Entity\Favorite` | `App\Entity\Favorite\Favorite` |
| `App\Entity\QCM` | `App\Entity\QCM\QCM` |
| `App\Entity\Seo` | `App\Entity\Seo\Seo` |
| `App\Entity\PropositionIA` | `App\Entity\AI\PropositionIA` |

### Ports → Implémentations

| Interface (Domain) | Implémentation (Infrastructure) |
|--------------------|--------------------------------|
| `NoteRepositoryInterface` | `DoctrineNoteRepository` |
| `NoteEncryptionInterface` | `NoteEncryptionService` |
| `FavoriteRepositoryInterface` | `DoctrineFavoriteRepository` |
| `PageRepositoryInterface` | `DoctrinePageRepository` |
| `CategoryRepositoryInterface` | `DoctrineCategoryRepository` |
| `MenuRepositoryInterface` | `DoctrineMenuRepository` |
| `UserPageVisitRepositoryInterface` | `DoctrineUserPageVisitRepository` |
| `QCMRepositoryInterface` | `DoctrineQCMRepository` |
| `SeoRepositoryInterface` | `DoctrineSeoRepository` |

---

## Configuration services.yaml

```yaml
# config/services.yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    # Application handlers
    App\Application\:
        resource: '../src/Application/'

    # Infrastructure
    App\Infrastructure\:
        resource: '../src/Infrastructure/'

    # Controllers
    App\Controller\:
        resource: '../src/Controller/'
        tags: ['controller.service_arguments']

    # Binding des interfaces aux implémentations
    App\Domain\Note\Repository\NoteRepositoryInterface:
        class: App\Infrastructure\Note\Repository\DoctrineNoteRepository

    App\Domain\Note\Service\NoteEncryptionInterface:
        class: App\Infrastructure\Note\Service\NoteEncryptionService
        arguments:
            $encryptionKey: '%env(NOTE_ENCRYPTION_KEY)%'

    App\Domain\Favorite\Repository\FavoriteRepositoryInterface:
        class: App\Infrastructure\Favorite\Repository\DoctrineFavoriteRepository

    App\Domain\Content\Repository\PageRepositoryInterface:
        class: App\Infrastructure\Content\Repository\DoctrinePageRepository

    App\Domain\Category\Repository\CategoryRepositoryInterface:
        class: App\Infrastructure\Category\Repository\DoctrineCategoryRepository

    App\Domain\Menu\Repository\MenuRepositoryInterface:
        class: App\Infrastructure\Menu\Repository\DoctrineMenuRepository

    App\Domain\UserPageVisit\Repository\UserPageVisitRepositoryInterface:
        class: App\Infrastructure\UserPageVisit\Repository\DoctrineUserPageVisitRepository

    App\Domain\QCM\Repository\QCMRepositoryInterface:
        class: App\Infrastructure\QCM\Repository\DoctrineQCMRepository

    App\Domain\Seo\Repository\SeoRepositoryInterface:
        class: App\Infrastructure\Seo\Repository\DoctrineSeoRepository

    App\Domain\AI\Repository\PropositionIARepositoryInterface:
        class: App\Infrastructure\AI\Repository\DoctrinePropositionIARepository
```

---

## Conventions de la codebase

### Classes

| Type | Convention |
|------|------------|
| Handler | `final class` |
| Command | `final class` avec propriétés `public readonly` |
| Query | `final class` avec propriétés `public readonly` |
| DTO | `final class` avec `fromEntity()` et `toArray()` |
| Exception | `class` avec méthodes factory statiques |
| Repository Doctrine | `final class implements Interface` |

### Nommage

| Type | Pattern |
|------|---------|
| Command | `{Action}{Context}Command` (CreateOrUpdateNoteCommand) |
| Query | `Get{What}Query` (GetNoteByPageQuery) |
| Handler | `{Action}{Context}Handler` (CreateOrUpdateNoteHandler) |
| DTO | `{Context}DTO` (NoteDTO) |
| Exception | `{Context}{Error}Exception` (FavoriteAlreadyExistsException) |
| Repository Interface | `{Context}RepositoryInterface` |
| Repository Doctrine | `Doctrine{Context}Repository` |

### Méthodes factory des Exceptions

```php
// Pattern utilisé
public static function forUserAndPage(int $userId, int $pageId): self
public static function withId(int $id): self
public static function withName(string $name): self
```

---

## Vérification finale

```bash
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

| Ordre | Contexte | Fichiers dépendants | Statut |
|-------|----------|---------------------|--------|
| 1 | Note | - | ✅ Fait |
| 2 | Favorite | - | ✅ Fait |
| 3 | Page | - | ✅ Fait |
| 4 | **Category** | 8 fichiers | À faire |
| 5 | **UserPageVisit** | 6 fichiers | À faire |
| 6 | **Menu** | 5 fichiers | À faire |
| 7 | Seo | 2 fichiers | À faire |
| 8 | QCM | 1 fichier | À faire |
| 9 | Exercice | 2 fichiers | À faire |
| 10 | AI | 1 fichier | À faire |

---

## Schéma visuel

```
┌─────────────────────────────────────────────────────────────────┐
│                           Entity/                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  Note/       │  │  Favorite/   │  │  Category/   │   ...    │
│  │  └─Note.php  │  │  └─Favorite  │  │  └─Category  │          │
│  │   (Doctrine) │  │   (Doctrine) │  │   (Doctrine) │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                           Domain/                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  Note/       │  │  Favorite/   │  │  Category/   │   ...    │
│  │  ├─Repository│  │  ├─Repository│  │  ├─Repository│          │
│  │  │ Interface │  │  │ Interface │  │  │ Interface │          │
│  │  ├─Service/  │  │  └─Exception/│  │  └─Exception/│          │
│  │  │ Interface │  │              │  │              │          │
│  │  └─Exception/│  │              │  │              │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
└─────────────────────────────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                         Application/                            │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  Note/       │  │  Favorite/   │  │  Category/   │   ...    │
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
│  │  Note/       │  │  Favorite/   │  │  Category/   │   ...    │
│  │  ├─Repository│  │  └─Repository│  │  └─Repository│          │
│  │  │ Doctrine  │  │    Doctrine  │  │    Doctrine  │          │
│  │  └─Service/  │  │              │  │              │          │
│  │    Encryption│  │              │  │              │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
└─────────────────────────────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│               Controller/ + Command/ + EventListener/           │
│                        (Adaptateurs minces)                     │
│           Injectent des Handlers, pas des Repositories          │
└─────────────────────────────────────────────────────────────────┘
```
