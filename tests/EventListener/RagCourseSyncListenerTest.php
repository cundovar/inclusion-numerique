<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Entity\PageContent;
use App\EventListener\RagCourseSyncListener;
use App\Service\Rag\RagIndex;
use App\Service\Rag\VectorStore;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class RagCourseSyncListenerTest extends TestCase
{
    /** @var list<string> */
    private array $requests = [];
    private int $status = 200;

    public function testPublishedCourseIsIndexedAfterTheResponse(): void
    {
        $course = $this->course(4, PageContent::STATUS_PUBLISHED);
        [$listener, $entityManager] = $this->listener([4 => $course]);

        $listener->postPersist($course, new PostPersistEventArgs($course, $entityManager));
        self::assertSame([], $this->requests);

        $listener->flush();
        self::assertSame(['DELETE /courses/4', 'POST /index'], $this->requests);

        $this->requests = [];
        $listener->flush();
        self::assertSame([], $this->requests, 'Un cours n\'est envoyé qu\'une fois');
    }

    public function testOnlyTextOrStatusChangesTriggerIndexing(): void
    {
        $course = $this->course(5, PageContent::STATUS_PUBLISHED);
        [$listener, $entityManager] = $this->listener([5 => $course]);
        $changes = ['title' => ['Ancien', 'Nouveau']];

        $listener->preUpdate($course, new PreUpdateEventArgs($course, $entityManager, $changes));
        $listener->flush();
        self::assertSame([], $this->requests);

        $changes = ['code' => ['<p>Ancien</p>', '<p>Nouveau</p>']];
        $listener->preUpdate($course, new PreUpdateEventArgs($course, $entityManager, $changes));
        $listener->flush();
        self::assertSame(['DELETE /courses/5', 'POST /index'], $this->requests);
    }

    public function testUnpublishedOrRemovedCourseLeavesTheIndex(): void
    {
        $draft = $this->course(6, PageContent::STATUS_DRAFT);
        $removed = $this->course(7, PageContent::STATUS_PUBLISHED);
        [$listener, $entityManager] = $this->listener([6 => $draft]);
        $changes = ['status' => [PageContent::STATUS_PUBLISHED, PageContent::STATUS_DRAFT]];

        $listener->preUpdate($draft, new PreUpdateEventArgs($draft, $entityManager, $changes));
        $listener->preRemove($removed, new PreRemoveEventArgs($removed, $entityManager));
        $listener->flush();

        self::assertSame(['DELETE /courses/7', 'DELETE /courses/6'], $this->requests);
    }

    public function testServiceFailureIsLoggedWithoutError(): void
    {
        $this->status = 500;
        $course = $this->course(8, PageContent::STATUS_PUBLISHED);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning')->with(self::stringContains('app:rag:reindex'), ['id' => 8]);
        [$listener, $entityManager] = $this->listener([8 => $course], $logger);

        $listener->postPersist($course, new PostPersistEventArgs($course, $entityManager));
        $listener->flush();
    }

    public function testNothingIsSentWithoutVectorService(): void
    {
        $course = $this->course(9, PageContent::STATUS_PUBLISHED);
        [$listener, $entityManager] = $this->listener([9 => $course], null, '');

        $listener->postPersist($course, new PostPersistEventArgs($course, $entityManager));
        $listener->flush();
        self::assertSame([], $this->requests);
    }

    /**
     * @param array<int, PageContent> $courses
     * @return array{RagCourseSyncListener, EntityManagerInterface}
     */
    private function listener(array $courses, ?LoggerInterface $logger = null, string $url = 'https://rag.example.test'): array
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('find')->willReturnCallback(static fn (string $class, int $id) => $courses[$id] ?? null);
        $http = new MockHttpClient(function (string $method, string $url) {
            $this->requests[] = $method.' '.parse_url($url, PHP_URL_PATH);

            return new MockResponse('{}', ['http_code' => $this->status]);
        });
        $vectors = new VectorStore($http, $url, 'jeton-de-test');

        return [new RagCourseSyncListener($entityManager, new RagIndex($entityManager, $vectors), $vectors, $logger ?? $this->createMock(LoggerInterface::class)), $entityManager];
    }

    private function course(int $id, string $status): PageContent
    {
        $course = (new PageContent())->setCode('<p>Un cours sur les workflows.</p>')->setStatus($status);
        (new \ReflectionProperty(PageContent::class, 'id'))->setValue($course, $id);

        return $course;
    }
}
