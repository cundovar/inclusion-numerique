<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\PageContent;
use App\Service\Rag\RagIndex;
use App\Service\Rag\VectorStore;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Tient la recherche vectorielle (Qdrant) à jour quand un cours est publié, modifié ou retiré.
 * Les cours concernés sont notés pendant l'enregistrement, puis envoyés au service RAG après
 * la réponse (kernel.terminate) : une panne du service ne bloque jamais une publication.
 */
#[AsEntityListener(event: Events::postPersist, entity: PageContent::class)]
#[AsEntityListener(event: Events::preUpdate, entity: PageContent::class)]
#[AsEntityListener(event: Events::preRemove, entity: PageContent::class)]
final class RagCourseSyncListener
{
    // Champs qui changent le texte indexé ou sa présence dans l'index
    private const INDEXED_FIELDS = ['status', 'code', 'content'];

    /** @var array<int, true> */
    private array $toIndex = [];
    /** @var array<int, true> */
    private array $toDelete = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RagIndex $index,
        private VectorStore $vectors,
        private LoggerInterface $logger,
    ) {}

    public function postPersist(PageContent $course, PostPersistEventArgs $event): void
    {
        $this->markForIndexing($course);
    }

    public function preUpdate(PageContent $course, PreUpdateEventArgs $event): void
    {
        foreach (self::INDEXED_FIELDS as $field) {
            if ($event->hasChangedField($field)) {
                $this->markForIndexing($course);

                return;
            }
        }
    }

    public function preRemove(PageContent $course, PreRemoveEventArgs $event): void
    {
        if ($course->getId() !== null) {
            unset($this->toIndex[$course->getId()]);
            $this->toDelete[$course->getId()] = true;
        }
    }

    #[AsEventListener(event: KernelEvents::TERMINATE)]
    #[AsEventListener(event: ConsoleEvents::TERMINATE)]
    public function flush(): void
    {
        $toIndex = array_keys($this->toIndex);
        $toDelete = array_keys($this->toDelete);
        $this->toIndex = $this->toDelete = [];
        if (!$this->vectors->isEnabled()) {
            return;
        }

        foreach ($toDelete as $id) {
            if (!$this->vectors->deleteCourse($id)) {
                $this->warn($id);
            }
        }
        foreach ($toIndex as $id) {
            try {
                $course = $this->entityManager->find(PageContent::class, $id);
                $done = $course instanceof PageContent ? $this->index->indexCourse($course) : $this->vectors->deleteCourse($id);
            } catch (\Throwable) {
                // Base indisponible après la réponse : la réindexation manuelle rattrapera ce cours
                $done = false;
            }
            if (!$done) {
                $this->warn($id);
            }
        }
    }

    private function markForIndexing(PageContent $course): void
    {
        if ($course->getId() !== null) {
            $this->toIndex[$course->getId()] = true;
        }
    }

    private function warn(int $id): void
    {
        $this->logger->warning('Recherche vectorielle non mise à jour pour le cours {id} : lancer app:rag:reindex.', ['id' => $id]);
    }
}
