<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Brouillon de QCM préparé par l'assistant de rédaction. Il n'est pas une ressource de l'API : tant qu'il n'est
 * pas publié, rien n'apparaît dans le catalogue public. La publication crée un QCM et ses ChoicesQCM.
 *
 * Les réponses sont gardées en JSON : liste de {texte, correcte, explication}.
 */
#[ORM\Entity]
#[ORM\Table(name: 'qcm_draft')]
class QcmDraft
{
    public const STATUS_DRAFT = 'brouillon';
    public const STATUS_TO_FIX = 'a_corriger';
    public const STATUS_PUBLISHED = 'publie';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $question = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $explanation = '';

    /** @var list<array{texte: string, correcte: bool, explication: ?string}> */
    #[ORM\Column(type: Types::JSON)]
    private array $answers = [];

    // Thème du catalogue public : l'entité LanguageQCM, filtre « langage » repris de DevDoc
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?LanguageQCM $theme = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?NiveauQCM $level = null;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_DRAFT;

    #[ORM\Column(length: 100)]
    private string $author = '';

    /** @var array{avis: string, resume: string, points: list<array{regle: ?string, constat: string, suggestion: ?string}>}|null */
    #[ORM\Column(name: 'review_report', type: Types::JSON, nullable: true)]
    private ?array $reviewReport = null;

    #[ORM\Column(name: 'reviewed_by', length: 100, nullable: true)]
    private ?string $reviewedBy = null;

    #[ORM\Column(name: 'reviewed_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $reviewedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'published_qcm_id', onDelete: 'SET NULL')]
    private ?QCM $publishedQcm = null;

    #[ORM\Column(name: 'published_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $author)
    {
        $this->author = $author;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getExplanation(): string
    {
        return $this->explanation;
    }

    public function setExplanation(string $explanation): static
    {
        $this->explanation = $explanation;

        return $this;
    }

    /**
     * @return list<array{texte: string, correcte: bool, explication: ?string}>
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function getAnswersForAdmin(): string
    {
        return implode("\n\n", array_map(static function (array $answer): string {
            $text = ($answer['correcte'] ? '✓ ' : '• ').$answer['texte'];
            if (($answer['explication'] ?? null) !== null) {
                $text .= "\n  ".'Explication : '.$answer['explication'];
            }

            return $text;
        }, $this->answers));
    }

    /**
     * @param list<array{texte: string, correcte: bool, explication: ?string}> $answers
     */
    public function setAnswers(array $answers): static
    {
        $this->answers = $answers;

        return $this;
    }

    public function getTheme(): ?LanguageQCM
    {
        return $this->theme;
    }

    public function setTheme(?LanguageQCM $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getLevel(): ?NiveauQCM
    {
        return $this->level;
    }

    public function setLevel(?NiveauQCM $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param array{avis: string, resume: string, points: list<array{regle: ?string, constat: string, suggestion: ?string}>} $report
     */
    public function recordReview(array $report, string $reviewer): static
    {
        $this->reviewReport = $report;
        $this->reviewedBy = $reviewer;
        $this->reviewedAt = new \DateTimeImmutable();

        return $this;
    }

    public function clearReview(): static
    {
        $this->reviewReport = null;
        $this->reviewedBy = null;
        $this->reviewedAt = null;

        return $this;
    }

    /**
     * @return array{avis: string, resume: string, points: list<array{regle: ?string, constat: string, suggestion: ?string}>}|null
     */
    public function getReviewReport(): ?array
    {
        return $this->reviewReport;
    }

    public function getReviewSummaryForAdmin(): string
    {
        if ($this->reviewReport === null) {
            return 'Aucune relecture enregistrée.';
        }

        $lines = [$this->reviewReport['avis'].': '.$this->reviewReport['resume']];
        foreach ($this->reviewReport['points'] as $point) {
            $lines[] = sprintf('%s — %s%s', $point['regle'] ?? 'Sans règle', $point['constat'], isset($point['suggestion']) ? ' Suggestion : '.$point['suggestion'] : '');
        }

        return implode("\n", $lines);
    }

    public function getReviewedBy(): ?string
    {
        return $this->reviewedBy;
    }

    public function getReviewedAt(): ?\DateTimeImmutable
    {
        return $this->reviewedAt;
    }

    public function markPublished(QCM $qcm): static
    {
        $this->status = self::STATUS_PUBLISHED;
        $this->publishedQcm = $qcm;
        $this->publishedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getPublishedQcm(): ?QCM
    {
        return $this->publishedQcm;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
