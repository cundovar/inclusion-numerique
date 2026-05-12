<?php

declare(strict_types=1);

namespace App\Application\Note\Handler;

use App\Application\Note\DTO\NoteDTO;
use App\Application\Note\Query\GetNoteByPageQuery;
use App\Domain\Note\Repository\NoteRepositoryInterface;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Domain\Note\Service\NoteEncryptionInterface;

final class GetNoteByPageHandler
{
    public function __construct(
        private NoteRepositoryInterface $noteRepository,
        private PageRepositoryInterface $pageRepository,
        private NoteEncryptionInterface $encryption
    ) {}

    public function handle(GetNoteByPageQuery $query): ?NoteDTO
    {
        $page = $this->pageRepository->findById($query->pageId);

        if (!$page) {
            throw new \InvalidArgumentException('Page non trouvée');
        }

        $note = $this->noteRepository->findByUserAndPage($query->user, $page);

        if (!$note) {
            return null;
        }

        return NoteDTO::fromEntity(
            $note,
            $this->encryption->decrypt($note->getContent())
        );
    }
}
