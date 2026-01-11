<?php

declare(strict_types=1);

namespace App\Application\Note\Handler;

use App\Application\Note\Command\DeleteNoteCommand;
use App\Domain\Note\Exception\NoteNotFoundException;
use App\Domain\Note\Exception\UnauthorizedNoteAccessException;
use App\Domain\Note\Repository\NoteRepositoryInterface;

final class DeleteNoteHandler
{
    public function __construct(
        private NoteRepositoryInterface $noteRepository
    ) {}

    public function handle(DeleteNoteCommand $command): void
    {
        $note = $this->noteRepository->findById($command->noteId);

        if (!$note) {
            throw NoteNotFoundException::withId($command->noteId);
        }

        if ($note->getUser() !== $command->user) {
            throw UnauthorizedNoteAccessException::forNote(
                $command->noteId,
                $command->user->getId()
            );
        }

        $this->noteRepository->delete($note);
    }
}
