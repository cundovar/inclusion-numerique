<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** Passage de recherche extrait d'un cours ; il n'est jamais une ressource publique directe. */
#[ORM\Entity]
#[ORM\Table(name: 'rag_passage')]
#[ORM\UniqueConstraint(name: 'uniq_rag_course_position', columns: ['page_content_id', 'position'])]
#[ORM\Index(name: 'IDX_RAG_PASSAGE_COURSE', columns: ['page_content_id'])]
#[ORM\Index(name: 'rag_passage_content_fulltext', columns: ['content'], flags: ['fulltext'])]
class RagPassage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'page_content_id', nullable: false, onDelete: 'CASCADE')]
    private PageContent $course;

    #[ORM\Column]
    private int $position;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(length: 64)]
    private string $checksum;

    public function __construct(PageContent $course, int $position, string $content, string $checksum)
    {
        $this->course = $course;
        $this->position = $position;
        $this->content = $content;
        $this->checksum = $checksum;
    }

    public function getId(): ?int { return $this->id; }

    public function getCourse(): PageContent { return $this->course; }

    public function getPosition(): int { return $this->position; }

    public function getContent(): string { return $this->content; }

    public function getChecksum(): string { return $this->checksum; }

    public function replace(string $content, string $checksum): void
    {
        $this->content = $content;
        $this->checksum = $checksum;
    }
}
