<?php

declare(strict_types=1);

namespace App\Application\Note\Handler;

use App\Application\Note\Command\CreateOrUpdateNoteCommand;
use App\Application\Note\DTO\NoteDTO;
use App\Domain\Note\Repository\NoteRepositoryInterface;
use App\Domain\Note\Service\NoteEncryptionInterface;
use App\Entity\Note;
use App\Repository\PageRepository;

final class CreateOrUpdateNoteHandler
{
    public function __construct(
        private NoteRepositoryInterface $noteRepository,
        private PageRepository $pageRepository,
        private NoteEncryptionInterface $encryption
    ) {}

    public function handle(CreateOrUpdateNoteCommand $command): NoteDTO
    {
        $page = $this->pageRepository->find($command->pageId);

        if (!$page) {
            throw new \InvalidArgumentException('Page non trouvée');
        }

        // Chercher une note existante ou en créer une nouvelle
        $note = $this->noteRepository->findByUserAndPage($command->user, $page);

        if (!$note) {
            $note = new Note();
            $note->setUser($command->user);
            $note->setPage($page);
        }

        // Chiffrer et sauvegarder
        $encryptedContent = $this->encryption->encrypt($command->content);
        $note->setContent($encryptedContent);

        $this->noteRepository->save($note);

        return NoteDTO::fromEntity($note, $command->content);
    }
}
