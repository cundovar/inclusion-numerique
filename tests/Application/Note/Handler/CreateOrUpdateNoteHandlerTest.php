<?php

declare(strict_types=1);

use App\Application\Note\Command\CreateOrUpdateNoteCommand;
use App\Application\Note\Handler\CreateOrUpdateNoteHandler;
use App\Domain\Note\Repository\NoteRepositoryInterface;
use App\Domain\Note\Service\NoteEncryptionInterface;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Entity\Note;
use App\Entity\Page;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class CreateOrUpdateNoteHandlerTest extends TestCase
{
    private function setEntityId(object $entity, int $id): void
    {
        // Les entités Doctrine ont souvent un id privé sans setter public.
        // On utilise la réflexion pour simuler un objet déjà persisté.
        $property = new \ReflectionProperty($entity, 'id');
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }

    private function makeUser(int $id): User
    {
        // Helper de test : crée un utilisateur minimal avec un id connu.
        $user = new User();
        $this->setEntityId($user, $id);
        return $user;
    }

    private function makePage(int $id, string $slug = 'page-slug'): Page
    {
        // Helper de test : crée une page minimale avec les données utiles au handler.
        $page = new Page();
        $page->setSlug($slug);
        $this->setEntityId($page, $id);
        return $page;
    }

    public function testThrowsWhenPageNotFound(): void
    {
        // On remplace toutes les dépendances par des mocks pour isoler
        // uniquement la logique du handler.
        $noteRepository = $this->createMock(NoteRepositoryInterface::class);
        $pageRepository = $this->createMock(PageRepositoryInterface::class);
        $encryption = $this->createMock(NoteEncryptionInterface::class);

        // Le handler commence par chercher la page demandée.
        // Ici on simule le cas où cette page n'existe pas.
        $pageRepository->expects($this->once())
            ->method('findById')
            ->with(10)
            ->willReturn(null);

        $handler = new CreateOrUpdateNoteHandler($noteRepository, $pageRepository, $encryption);

        // Si la page est introuvable, le handler doit échouer immédiatement.
        $this->expectException(\InvalidArgumentException::class);
        $handler->handle(new CreateOrUpdateNoteCommand($this->makeUser(1), 10, 'plain'));
    }

    public function testCreatesNewNoteWhenMissing(): void
    {
        // Même principe : on teste le handler seul, avec des dépendances simulées.
        $noteRepository = $this->createMock(NoteRepositoryInterface::class);
        $pageRepository = $this->createMock(PageRepositoryInterface::class);
        $encryption = $this->createMock(NoteEncryptionInterface::class);

        $user = $this->makeUser(1);
        $page = $this->makePage(10, 'slug-10');

        // La page existe bien.
        $pageRepository->expects($this->once())
            ->method('findById')
            ->with(10)
            ->willReturn($page);

        // Aucune note existante pour ce couple utilisateur/page :
        // le handler doit donc créer une nouvelle entité Note.
        $noteRepository->expects($this->once())
            ->method('findByUserAndPage')
            ->with($user, $page)
            ->willReturn(null);

        // Le contenu doit être chiffré avant sauvegarde.
        $encryption->expects($this->once())
            ->method('encrypt')
            ->with('plain')
            ->willReturn('enc');

        // On vérifie l'objet envoyé au repository :
        // user, page et contenu chiffré doivent être correctement posés.
        // On simule aussi ici l'id attribué après persistance.
        $noteRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Note $note) use ($user, $page): bool {
                $this->setEntityId($note, 99);
                $this->assertSame($user, $note->getUser());
                $this->assertSame($page, $note->getPage());
                $this->assertSame('enc', $note->getContent());
                return true;
            }));

        $handler = new CreateOrUpdateNoteHandler($noteRepository, $pageRepository, $encryption);
        $dto = $handler->handle(new CreateOrUpdateNoteCommand($user, 10, 'plain'));

        // On vérifie ensuite le DTO retourné au reste de l'application.
        // Il doit refléter la note sauvegardée, mais avec le contenu en clair.
        $this->assertSame(99, $dto->id);
        $this->assertSame(10, $dto->pageId);
        $this->assertSame('slug-10', $dto->pageSlug);
        $this->assertSame('plain', $dto->content);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $dto->createdAt);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $dto->updatedAt);
    }

    public function testUpdatesExistingNote(): void
    {
        // Ici on couvre le second scénario métier :
        // une note existe déjà, il faut la mettre à jour au lieu d'en créer une nouvelle.
        $noteRepository = $this->createMock(NoteRepositoryInterface::class);
        $pageRepository = $this->createMock(PageRepositoryInterface::class);
        $encryption = $this->createMock(NoteEncryptionInterface::class);

        $user = $this->makeUser(1);
        $page = $this->makePage(10, 'slug-10');

        // Cette entité représente la note déjà présente en base.
        $note = new Note();
        $note->setUser($user);
        $note->setPage($page);
        $note->setContent('old-enc');
        $this->setEntityId($note, 42);

        // La page est trouvée normalement.
        $pageRepository->expects($this->once())
            ->method('findById')
            ->with(10)
            ->willReturn($page);

        // Cette fois, le repository retourne une note existante.
        $noteRepository->expects($this->once())
            ->method('findByUserAndPage')
            ->with($user, $page)
            ->willReturn($note);

        // Le nouveau contenu doit être chiffré avant de remplacer l'ancien.
        $encryption->expects($this->once())
            ->method('encrypt')
            ->with('plain')
            ->willReturn('new-enc');

        // On vérifie qu'on sauvegarde bien la même instance de Note,
        // simplement enrichie avec le nouveau contenu chiffré.
        $noteRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Note $saved) use ($note): bool {
                $this->assertSame($note, $saved);
                $this->assertSame('new-enc', $saved->getContent());
                return true;
            }));

        $handler = new CreateOrUpdateNoteHandler($noteRepository, $pageRepository, $encryption);
        $dto = $handler->handle(new CreateOrUpdateNoteCommand($user, 10, 'plain'));

        // Le DTO final garde l'id de la note existante et expose le contenu en clair.
        $this->assertSame(42, $dto->id);
        $this->assertSame(10, $dto->pageId);
        $this->assertSame('slug-10', $dto->pageSlug);
        $this->assertSame('plain', $dto->content);
    }
}
