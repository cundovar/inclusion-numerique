<?php

declare(strict_types=1);

namespace App\Application\Note\Handler;

use App\Application\Note\DTO\NoteDTO;
use App\Application\Note\Query\GetNoteByPageQuery;
use App\Domain\Note\Repository\NoteRepositoryInterface;
use App\Domain\Note\Service\NoteEncryptionInterface;
use App\Repository\PageRepository;

final class GetNoteByPageHandler
{
    public function __construct(
        private NoteRepositoryInterface $noteRepository,
        private PageRepository $pageRepository,
        private NoteEncryptionInterface $encryption
    ) {}

    public function handle(GetNoteByPageQuery $query): ?NoteDTO
    {
        $page = $this->pageRepository->find($query->pageId);

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
