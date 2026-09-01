<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'course_media')]
class CourseMedia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $filename;

    #[ORM\Column(length: 100)]
    private string $mimeType;

    #[ORM\Column(type: Types::INTEGER)]
    private int $width;

    #[ORM\Column(type: Types::INTEGER)]
    private int $height;

    #[ORM\Column(length: 64)]
    private string $checksum;

    #[ORM\Column(length: 500)]
    private string $altText;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $caption = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $prompt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?PageContent $course = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?AgentCourseGeneration $generation = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $filename, string $mimeType, int $width, int $height, string $checksum, string $altText)
    {
        $this->filename = $filename;
        $this->mimeType = $mimeType;
        $this->width = $width;
        $this->height = $height;
        $this->checksum = $checksum;
        $this->altText = $altText;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getFilename(): string { return $this->filename; }
    public function getMimeType(): string { return $this->mimeType; }
    public function getWidth(): int { return $this->width; }
    public function getHeight(): int { return $this->height; }
    public function getChecksum(): string { return $this->checksum; }
    public function getAltText(): string { return $this->altText; }
    public function getCaption(): ?string { return $this->caption; }
    public function getPrompt(): ?string { return $this->prompt; }
    public function getCourse(): ?PageContent { return $this->course; }
    public function getGeneration(): ?AgentCourseGeneration { return $this->generation; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getPublicPath(): string { return '/uploads/course-media/' . $this->filename; }
    public function setCaption(?string $caption): void { $this->caption = $caption; }
    public function setPrompt(?string $prompt): void { $this->prompt = $prompt; }
    public function setCourse(?PageContent $course): void { $this->course = $course; }
    public function setGeneration(?AgentCourseGeneration $generation): void { $this->generation = $generation; }
}
