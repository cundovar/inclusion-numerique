<?php

declare(strict_types=1);

namespace App\Tests\Service\Rag;

use App\Entity\PageContent;
use App\Service\Rag\RagIndex;
use App\Service\Rag\VectorStore;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class RagIndexTest extends TestCase
{
    public function testCourseContentIsDeletedThenIndexedInBatches(): void
    {
        $requests = [];
        $index = $this->index([$this->course(7, str_repeat('mot ', 30000))], function (string $method, string $url, array $options) use (&$requests) {
            $requests[] = [$method, parse_url($url, PHP_URL_PATH), json_decode($options['body'] ?? 'null', true)];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        self::assertSame(['indexed' => 1, 'failed' => []], $index->rebuildVectors());
        self::assertSame(['DELETE', '/courses/7'], array_slice($requests[0], 0, 2));
        self::assertGreaterThan(2, count($requests));
        foreach (array_slice($requests, 1) as [$method, $path, $body]) {
            self::assertSame(['POST', '/index'], [$method, $path]);
            self::assertLessThanOrEqual(100, count($body['passages']));
        }
        self::assertSame('7-1', $requests[1][2]['passages'][0]['id']);
    }

    public function testRejectedIndexingIsReportedAsFailure(): void
    {
        $index = $this->index([$this->course(3, 'Un cours publié.')], static fn (string $method) => new MockResponse('{}', ['http_code' => $method === 'POST' ? 500 : 200]));

        self::assertSame(['indexed' => 0, 'failed' => [3]], $index->rebuildVectors());
    }

    /** @param list<PageContent> $courses */
    private function index(array $courses, callable $responder): RagIndex
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findAll')->willReturn($courses);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        return new RagIndex($entityManager, new VectorStore(new MockHttpClient($responder), 'https://rag.example.test', 'jeton-de-test'));
    }

    private function course(int $id, string $code): PageContent
    {
        $course = (new PageContent())->setCode('<p>'.$code.'</p>')->setStatus(PageContent::STATUS_PUBLISHED);
        (new \ReflectionProperty(PageContent::class, 'id'))->setValue($course, $id);

        return $course;
    }
}
