<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Capture d'écran d'un service, ou illustration pédagogique fictive, insérée dans un cours
 * avec le marqueur [capture:ID]. Remplacer le fichier met à jour tous les cours qui l'utilisent.
 *
 * L'API publique est en lecture seule et n'expose ni l'auteur ni les dates de suivi éditorial.
 */
#[ORM\Entity]
#[ORM\Table(name: 'course_media')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    shortName: 'Capture',
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['capture:read']],
    paginationEnabled: false,
)]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact'])]
class CourseMedia
{
    public const KIND_CAPTURE = 'capture';
    public const KIND_ILLUSTRATION = 'illustration';
    public const KINDS = [self::KIND_CAPTURE, self::KIND_ILLUSTRATION];

    public const PUBLIC_DIR = '/uploads/course-media';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['capture:read'])]
    private ?int $id = null;

    // Capture réelle d'une page publique, ou illustration fictive d'un écran personnel
    #[ORM\Column(length: 20, options: ['default' => self::KIND_CAPTURE])]
    #[Assert\Choice(choices: self::KINDS)]
    #[Groups(['capture:read'])]
    private string $kind = self::KIND_CAPTURE;

    // Service représenté, par exemple « CAF »
    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    #[Groups(['capture:read'])]
    private ?string $service = null;

    // Page officielle d'où vient la capture
    #[ORM\Column(name: 'source_url', length: 500, nullable: true)]
    #[Assert\Url]
    #[Assert\Length(max: 500)]
    #[Groups(['capture:read'])]
    private ?string $sourceUrl = null;

    #[ORM\Column(name: 'captured_at', type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['capture:read'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    private ?\DateTimeImmutable $capturedAt = null;

    // Personne (ou agent) qui a pris la capture : suivi interne, non publié
    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $author = null;

    #[ORM\Column(name: 'alt_text', length: 500)]
    #[Assert\NotBlank(message: "Le texte alternatif décrit l'image pour les personnes qui ne la voient pas.", normalizer: 'trim')]
    #[Assert\Length(max: 500)]
    #[Groups(['capture:read'])]
    private string $altText = '';

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La légende est obligatoire.', normalizer: 'trim')]
    #[Groups(['capture:read'])]
    private string $caption = '';

    // Date à laquelle comparer de nouveau la capture avec le site officiel
    #[ORM\Column(name: 'next_review_at', type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $nextReviewAt = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    #[Assert\NotBlank(message: 'Ajoutez une image.')]
    private ?string $filename = null;

    #[ORM\Column(name: 'mime_type', length: 100, nullable: true)]
    private ?string $mimeType = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['capture:read'])]
    private ?int $width = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['capture:read'])]
    private ?int $height = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $checksum = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Une capture réelle doit dire d'où elle vient et quand elle a été prise.
     */
    #[Assert\Callback]
    public function validateSource(ExecutionContextInterface $context): void
    {
        if ($this->kind !== self::KIND_CAPTURE) {
            return;
        }
        if ($this->sourceUrl === null || trim($this->sourceUrl) === '') {
            $context->buildViolation("Indiquez l'adresse de la page officielle capturée.")->atPath('sourceUrl')->addViolation();
        }
        if ($this->capturedAt === null) {
            $context->buildViolation('Indiquez la date de la capture.')->atPath('capturedAt')->addViolation();
        }
    }

    public function isDueForReview(\DateTimeImmutable $today): bool
    {
        return $this->nextReviewAt !== null && $this->nextReviewAt <= $today;
    }

    // Marqueur à coller dans le contenu d'un cours
    public function getMarker(): ?string
    {
        return $this->id === null ? null : sprintf('[capture:%d]', $this->id);
    }

    #[Groups(['capture:read'])]
    #[ApiProperty(description: "Adresse publique de l'image")]
    public function getUrl(): ?string
    {
        return $this->filename === null ? null : self::PUBLIC_DIR.'/'.$this->filename;
    }

    public function getId(): ?int { return $this->id; }

    public function getKind(): string { return $this->kind; }

    public function setKind(string $kind): static { $this->kind = $kind; return $this; }

    public function isIllustration(): bool { return $this->kind === self::KIND_ILLUSTRATION; }

    public function getService(): ?string { return $this->service; }

    public function setService(?string $service): static { $this->service = $service; return $this; }

    public function getSourceUrl(): ?string { return $this->sourceUrl; }

    public function setSourceUrl(?string $sourceUrl): static { $this->sourceUrl = $sourceUrl; return $this; }

    public function getCapturedAt(): ?\DateTimeImmutable { return $this->capturedAt; }

    public function setCapturedAt(?\DateTimeImmutable $capturedAt): static { $this->capturedAt = $capturedAt; return $this; }

    public function getAuthor(): ?string { return $this->author; }

    public function setAuthor(?string $author): static { $this->author = $author; return $this; }

    public function getAltText(): string { return $this->altText; }

    public function setAltText(?string $altText): static { $this->altText = (string) $altText; return $this; }

    public function getCaption(): string { return $this->caption; }

    public function setCaption(?string $caption): static { $this->caption = (string) $caption; return $this; }

    public function getNextReviewAt(): ?\DateTimeImmutable { return $this->nextReviewAt; }

    public function setNextReviewAt(?\DateTimeImmutable $nextReviewAt): static { $this->nextReviewAt = $nextReviewAt; return $this; }

    public function getFilename(): ?string { return $this->filename; }

    public function setFilename(?string $filename): static { $this->filename = $filename; return $this; }

    public function getMimeType(): ?string { return $this->mimeType; }

    public function getWidth(): ?int { return $this->width; }

    public function getHeight(): ?int { return $this->height; }

    public function getChecksum(): ?string { return $this->checksum; }

    public function describeFile(string $mimeType, int $width, int $height, string $checksum): static
    {
        $this->mimeType = $mimeType;
        $this->width = $width;
        $this->height = $height;
        $this->checksum = $checksum;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function __toString(): string
    {
        return trim(sprintf('#%s %s — %s', $this->id ?? '?', $this->service ?? '', mb_strimwidth($this->caption, 0, 60, '…')));
    }
}
