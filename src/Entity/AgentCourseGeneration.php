<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'agent_course_generation')]
#[ORM\UniqueConstraint(name: 'generation_batch_item_unique', columns: ['batch_id', 'external_id'])]
class AgentCourseGeneration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'batch_id', length: 100)]
    private string $batchId;

    #[ORM\Column(name: 'external_id', length: 100)]
    private string $externalId;

    #[ORM\Column(length: 20)]
    private string $status = 'pending';

    #[ORM\Column(type: Types::INTEGER)]
    private int $verificationAttempts = 0;

    #[ORM\Column(type: Types::JSON)]
    private array $payload = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $candidate = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $verificationReport = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $technicalError = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?PageContent $course = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $finishedAt = null;

    public function __construct(string $batchId, string $externalId, array $payload)
    {
        $this->batchId = $batchId;
        $this->externalId = $externalId;
        $this->payload = $payload;
        $this->createdAt = $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getBatchId(): string { return $this->batchId; }
    public function getExternalId(): string { return $this->externalId; }
    public function getStatus(): string { return $this->status; }
    public function getVerificationAttempts(): int { return $this->verificationAttempts; }
    public function getPayload(): array { return $this->payload; }
    public function getCandidate(): ?array { return $this->candidate; }
    public function getVerificationReport(): ?array { return $this->verificationReport; }
    public function getTechnicalError(): ?string { return $this->technicalError; }
    public function getCourse(): ?PageContent { return $this->course; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function getFinishedAt(): ?\DateTimeImmutable { return $this->finishedAt; }

    public function update(string $status, ?array $candidate, ?array $report, ?string $technicalError): void
    {
        if (!in_array($status, ['pending', 'generating', 'verifying', 'succeeded', 'failed'], true)) throw new \InvalidArgumentException('Statut de génération invalide');
        $this->status = $status;
        if ($candidate !== null) $this->candidate = $candidate;
        if ($report !== null) { $this->verificationReport = $report; $this->verificationAttempts++; }
        $this->technicalError = $technicalError;
        $this->updatedAt = new \DateTimeImmutable();
        if (in_array($status, ['succeeded', 'failed'], true)) $this->finishedAt = $this->updatedAt;
    }

    public function complete(PageContent $course): void
    {
        $this->course = $course;
        $this->status = 'succeeded';
        $this->finishedAt = $this->updatedAt = new \DateTimeImmutable();
    }
}
