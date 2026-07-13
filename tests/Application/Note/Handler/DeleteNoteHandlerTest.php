<?php

declare(strict_types=1);

use App\Application\Note\Command\DeleteNoteCommand;
use App\Application\Note\Handler\DeleteNoteHandler;
use App\Domain\Note\Exception\NoteNotFoundException;
use App\Domain\Note\Exception\UnauthorizedNoteAccessException;
use App\Domain\Note\Repository\NoteRepositoryInterface;
use App\Entity\Note;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class DeleteNoteHandlerTest extends TestCase
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

    public function testThrowsWhenNoteNotFound(): void
    {
        $noteRepository = $this->createMock(NoteRepositoryInterface::class);
        $noteRepository->expects($this->once())
            ->method('findById')
            ->with(10)
            ->willReturn(null);

        $handler = new DeleteNoteHandler($noteRepository);

        $this->expectException(NoteNotFoundException::class);
        $handler->handle(new DeleteNoteCommand($this->makeUser(1), 10));
    }

    public function testThrowsWhenUserIsUnauthorized(): void
    {
        $noteRepository = $this->createMock(NoteRepositoryInterface::class);

        $noteOwner = $this->makeUser(1);
        $commandUser = $this->makeUser(2);

        $note = new Note();
        $note->setUser($noteOwner);
        $this->setEntityId($note, 42);

        $noteRepository->expects($this->once())
            ->method('findById')
            ->with(42)
            ->willReturn($note);

        $handler = new DeleteNoteHandler($noteRepository);

        $this->expectException(UnauthorizedNoteAccessException::class);
        $handler->handle(new DeleteNoteCommand($commandUser, 42));
    }

    public function testDeletesWhenUserIsAuthorized(): void
    {
        $noteRepository = $this->createMock(NoteRepositoryInterface::class);

        $user = $this->makeUser(1);

        $note = new Note();
        $note->setUser($user);
        $this->setEntityId($note, 42);

        $noteRepository->expects($this->once())
            ->method('findById')
            ->with(42)
            ->willReturn($note);

        $noteRepository->expects($this->once())
            ->method('delete')
            ->with($note);

        $handler = new DeleteNoteHandler($noteRepository);
        $handler->handle(new DeleteNoteCommand($user, 42));
    }
}
