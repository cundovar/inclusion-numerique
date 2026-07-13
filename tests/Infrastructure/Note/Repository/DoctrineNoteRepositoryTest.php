<?php

declare(strict_types=1);

use App\Entity\Note;
use App\Entity\Page;
use App\Entity\User;
use App\Infrastructure\Note\Repository\DoctrineNoteRepository;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;

final class DoctrineNoteRepositoryTest extends TestCase
{
    private EntityManager $entityManager;
    private DoctrineNoteRepository $repository;

    protected function setUp(): void
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            [dirname(__DIR__, 4) . '/src/Entity'],
            true
        );

        $connection = DriverManager::getConnection(
            ['driver' => 'pdo_sqlite', 'memory' => true],
            $config
        );
        $this->entityManager = new EntityManager($connection, $config);

        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->createSchema(
            $this->entityManager->getMetadataFactory()->getAllMetadata()
        );

        $this->repository = new DoctrineNoteRepository($this->entityManager);
    }

    public function testSaveAndFindById(): void
    {
        $user = $this->createUser('alice');
        $page = $this->createPage('page-a');

        $note = new Note();
        $note->setUser($user);
        $note->setPage($page);
        $note->setContent('enc-1');

        $this->repository->save($note);

        $found = $this->repository->findById($note->getId());

        $this->assertInstanceOf(Note::class, $found);
        $this->assertSame($note->getId(), $found->getId());
        $this->assertSame('enc-1', $found->getContent());
    }

    public function testFindByUserAndPage(): void
    {
        $user = $this->createUser('alice');
        $pageA = $this->createPage('page-a');
        $pageB = $this->createPage('page-b');

        $noteA = new Note();
        $noteA->setUser($user);
        $noteA->setPage($pageA);
        $noteA->setContent('enc-a');

        $noteB = new Note();
        $noteB->setUser($user);
        $noteB->setPage($pageB);
        $noteB->setContent('enc-b');

        $this->repository->save($noteA);
        $this->repository->save($noteB);

        $found = $this->repository->findByUserAndPage($user, $pageB);

        $this->assertInstanceOf(Note::class, $found);
        $this->assertSame($noteB->getId(), $found->getId());
        $this->assertSame('enc-b', $found->getContent());
    }

    public function testFindAllByUserOrdersByUpdatedAtDesc(): void
    {
        $user = $this->createUser('alice');
        $pageA = $this->createPage('page-a');
        $pageB = $this->createPage('page-b');

        $older = new Note();
        $older->setUser($user);
        $older->setPage($pageA);
        $older->setContent('enc-old');
        $older->setUpdatedAt(new \DateTimeImmutable('2020-01-01 10:00:00'));

        $newer = new Note();
        $newer->setUser($user);
        $newer->setPage($pageB);
        $newer->setContent('enc-new');
        $newer->setUpdatedAt(new \DateTimeImmutable('2021-01-01 10:00:00'));

        $this->repository->save($older);
        $this->repository->save($newer);

        $notes = $this->repository->findAllByUser($user);

        $this->assertCount(2, $notes);
        $this->assertSame($newer->getId(), $notes[0]->getId());
        $this->assertSame($older->getId(), $notes[1]->getId());
    }

    public function testDeleteRemovesNote(): void
    {
        $user = $this->createUser('alice');
        $page = $this->createPage('page-a');

        $note = new Note();
        $note->setUser($user);
        $note->setPage($page);
        $note->setContent('enc');

        $this->repository->save($note);
        $noteId = $note->getId();

        $this->repository->delete($note);

        $this->assertNull($this->repository->findById($noteId));
    }

    private function createUser(string $username): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setPassword('hashed');
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createPage(string $slug): Page
    {
        $page = new Page();
        $page->setSlug($slug);

        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }
}
