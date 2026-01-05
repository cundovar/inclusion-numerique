<?php

declare(strict_types=1);

namespace App\Infrastructure\Note\Repository;

use App\Domain\Note\Repository\NoteRepositoryInterface;
use App\Entity\Note;
use App\Entity\Page;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineNoteRepository implements NoteRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?Note
    {
        return $this->em->find(Note::class, $id);
    }

    public function findByUserAndPage(User $user, Page $page): ?Note
    {
        return $this->em->getRepository(Note::class)->findOneBy([
            'user' => $user,
            'page' => $page
        ]);
    }

    /**
     * @return Note[]
     */
    public function findAllByUser(User $user): array
    {
        return $this->em->getRepository(Note::class)->findBy(
            ['user' => $user],
            ['updatedAt' => 'DESC']
        );
    }

    public function save(Note $note): void
    {
        $this->em->persist($note);
        $this->em->flush();
    }

    public function delete(Note $note): void
    {
        $this->em->remove($note);
        $this->em->flush();
    }
}
