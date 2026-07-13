<?php

declare(strict_types=1);

use App\Application\Note\Handler\GetUserNotesHandler;
use App\Application\Note\Query\GetUserNotesQuery;
use App\Domain\Note\Repository\NoteRepositoryInterface;
use App\Domain\Note\Service\NoteEncryptionInterface;
use App\Entity\Note;
use App\Entity\Page;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class GetUserNotesHandlerTest extends TestCase
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

    private function makePage(int $id, string $slug): Page
    {
        $page = new Page();
        $page->setSlug($slug);
        $this->setEntityId($page, $id);
        return $page;
    }

    public function testReturnsDecryptedNotesAsDtos(): void
    {
        $noteRepository = $this->createMock(NoteRepositoryInterface::class);
        $encryption = $this->createMock(NoteEncryptionInterface::class);

        $user = $this->makeUser(1);

        $pageA = $this->makePage(10, 'slug-10');
        $noteA = new Note();
        $noteA->setUser($user);
        $noteA->setPage($pageA);
        $noteA->setContent('enc-1');
        $this->setEntityId($noteA, 101);

        $pageB = $this->makePage(11, 'slug-11');
        $noteB = new Note();
        $noteB->setUser($user);
        $noteB->setPage($pageB);
        $noteB->setContent('enc-2');
        $this->setEntityId($noteB, 102);

        $noteRepository->expects($this->once())
            ->method('findAllByUser')
            ->with($user)
            ->willReturn([$noteA, $noteB]);

        $encryption->expects($this->exactly(2))
            ->method('decrypt')
            ->willReturnMap([
                ['enc-1', 'plain-1'],
                ['enc-2', 'plain-2'],
            ]);

        $handler = new GetUserNotesHandler($noteRepository, $encryption);
        $dtos = $handler->handle(new GetUserNotesQuery($user));

        $this->assertCount(2, $dtos);
        $this->assertSame(101, $dtos[0]->id);
        $this->assertSame('plain-1', $dtos[0]->content);
        $this->assertSame(102, $dtos[1]->id);
        $this->assertSame('plain-2', $dtos[1]->content);
    }
}
