<?php

declare(strict_types=1);

namespace App\Application\Note\Handler;

use App\Application\Note\DTO\NoteDTO;
use App\Application\Note\Query\GetUserNotesQuery;
use App\Domain\Note\Repository\NoteRepositoryInterface;
use App\Domain\Note\Service\NoteEncryptionInterface;

final class GetUserNotesHandler
{
    public function __construct(
        private NoteRepositoryInterface $noteRepository,
        private NoteEncryptionInterface $encryption
    ) {}

    /**
     * @return NoteDTO[]
     */
    public function handle(GetUserNotesQuery $query): array
    {
        $notes = $this->noteRepository->findAllByUser($query->user);

        return array_map(
            fn($note) => NoteDTO::fromEntity(
                $note,
                $this->encryption->decrypt($note->getContent())
            ),
            $notes
        );
    }
}
