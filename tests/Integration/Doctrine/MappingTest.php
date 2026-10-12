<?php

declare(strict_types=1);

namespace App\Tests\Integration\Doctrine;

use App\Entity\AgentCourseGeneration;
use App\Entity\CourseMedia;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaValidator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Garde-fous du mapping Doctrine : les entités doivent rester cohérentes entre elles
 * et avec les colonnes réellement créées par les migrations.
 */
final class MappingTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testMappingIsValid(): void
    {
        $errors = (new SchemaValidator($this->entityManager))->validateMapping();

        self::assertSame([], $errors);
    }

    /**
     * Colonnes créées par la migration Version20260901103000 (snake_case).
     *
     * @dataProvider migratedColumns
     */
    public function testColumnNamesMatchMigration(string $entity, string $property, string $column): void
    {
        $metadata = $this->entityManager->getClassMetadata($entity);

        self::assertSame($column, $metadata->getColumnName($property));
    }

    /**
     * @return iterable<string, array{class-string, string, string}>
     */
    public static function migratedColumns(): iterable
    {
        yield 'CourseMedia::mimeType' => [CourseMedia::class, 'mimeType', 'mime_type'];
        yield 'CourseMedia::altText' => [CourseMedia::class, 'altText', 'alt_text'];
        yield 'CourseMedia::createdAt' => [CourseMedia::class, 'createdAt', 'created_at'];
        yield 'AgentCourseGeneration::verificationAttempts' => [AgentCourseGeneration::class, 'verificationAttempts', 'verification_attempts'];
        yield 'AgentCourseGeneration::verificationReport' => [AgentCourseGeneration::class, 'verificationReport', 'verification_report'];
        yield 'AgentCourseGeneration::technicalError' => [AgentCourseGeneration::class, 'technicalError', 'technical_error'];
        yield 'AgentCourseGeneration::createdAt' => [AgentCourseGeneration::class, 'createdAt', 'created_at'];
        yield 'AgentCourseGeneration::updatedAt' => [AgentCourseGeneration::class, 'updatedAt', 'updated_at'];
        yield 'AgentCourseGeneration::finishedAt' => [AgentCourseGeneration::class, 'finishedAt', 'finished_at'];
    }
}
