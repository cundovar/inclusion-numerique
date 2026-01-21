<?php

declare(strict_types=1);

namespace App\Domain\Note\Repository;

use App\Entity\Note;
use App\Entity\User;
use App\Entity\Page;

interface NoteRepositoryInterface
{
    public function findById(int $id): ?Note;

    public function findByUserAndPage(User $user, Page $page): ?Note;

    /**
     * @return Note[]
     */
    public function findAllByUser(User $user): array;

    public function save(Note $note): void;

    public function delete(Note $note): void;
}
