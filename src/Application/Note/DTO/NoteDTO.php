<?php

declare(strict_types=1);

namespace App\Application\Note\DTO;

use App\Entity\Note;

final class NoteDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $pageId,
        public readonly ?string $pageSlug,
        public readonly string $content,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {}

    public static function fromEntity(Note $note, string $decryptedContent): self
    {
        return new self(
            id: $note->getId(),
            pageId: $note->getPage()->getId(),
            pageSlug: $note->getPage()->getSlug(),
            content: $decryptedContent,
            createdAt: $note->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $note->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'pageId' => $this->pageId,
            'page' => [
                'id' => $this->pageId,
                'slug' => $this->pageSlug
            ],
            'content' => $this->content,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }
}
