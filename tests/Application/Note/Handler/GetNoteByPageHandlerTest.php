<?php

declare(strict_types=1);

use App\Application\Note\Handler\GetNoteByPageHandler;
use App\Application\Note\Query\GetNoteByPageQuery;
use App\Domain\Note\Repository\NoteRepositoryInterface;
use App\Domain\Note\Service\NoteEncryptionInterface;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Entity\Note;
use App\Entity\Page;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class GetNoteByPageHandlerTest extends TestCase
{
    private function setEntityId(object $entity, int $id): void
    {
        $property = new \ReflectionProperty($entity, 'id');
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }

    private function makeUser(int $id): User
    {
        $user = new User();
        $this->setEntityId($user, $id);
        return $user;
    }

    private function makePage(int $id, string $slug = 'page-slug'): Page
    {
        $page = new Page();
        $page->setSlug($slug);
        $this->setEntityId($page, $id);
        return $page;
    }

    public function testThrowsWhenPageNotFound(): void
    {
        $noteRepository = $this->createMock(NoteRepositoryInterface::class);
        $pageRepository = $this->createMock(PageRepositoryInterface::class);
        $encryption = $this->createMock(NoteEncryptionInterface::class);

        $pageRepository->expects($this->once())
            ->method('findById')
            ->with(10)
            ->willReturn(null);

        $handler = new GetNoteByPageHandler($noteRepository, $pageRepository, $encryption);

        $this->expectException(\InvalidArgumentException::class);
        $handler->handle(new GetNoteByPageQuery($this->makeUser(1), 10));
    }

    public function testReturnsNullWhenNoNoteFound(): void
    {
        $noteRepository = $this->createMock(NoteRepositoryInterface::class);
        $pageRepository = $this->createMock(PageRepositoryInterface::class);
        $encryption = $this->createMock(NoteEncryptionInterface::class);

        $user = $this->makeUser(1);
        $page = $this->makePage(10, 'slug-10');

        $pageRepository->expects($this->once())
            ->method('findById')
            ->with(10)
            ->willReturn($page);

        $noteRepository->expects($this->once())
            ->method('findByUserAndPage')
            ->with($user, $page)
            ->willReturn(null);

        $handler = new GetNoteByPageHandler($noteRepository, $pageRepository, $encryption);
        $dto = $handler->handle(new GetNoteByPageQuery($user, 10));

        $this->assertNull($dto);
    }

    public function testReturnsDtoWhenNoteFound(): void
    {
        $noteRepository = $this->createMock(NoteRepositoryInterface::class);
        $pageRepository = $this->createMock(PageRepositoryInterface::class);
        $encryption = $this->createMock(NoteEncryptionInterface::class);

        $user = $this->makeUser(1);
        $page = $this->makePage(10, 'slug-10');

        $note = new Note();
        $note->setUser($user);
        $note->setPage($page);
        $note->setContent('enc');
        $this->setEntityId($note, 42);

        $pageRepository->expects($this->once())
            ->method('findById')
            ->with(10)
            ->willReturn($page);

        $noteRepository->expects($this->once())
            ->method('findByUserAndPage')
            ->with($user, $page)
            ->willReturn($note);

        $encryption->expects($this->once())
            ->method('decrypt')
            ->with('enc')
            ->willReturn('plain');

        $handler = new GetNoteByPageHandler($noteRepository, $pageRepository, $encryption);
        $dto = $handler->handle(new GetNoteByPageQuery($user, 10));

        $this->assertSame(42, $dto->id);
        $this->assertSame(10, $dto->pageId);
        $this->assertSame('slug-10', $dto->pageSlug);
        $this->assertSame('plain', $dto->content);
    }
}
