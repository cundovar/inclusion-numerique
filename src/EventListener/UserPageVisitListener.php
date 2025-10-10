<?php

namespace App\EventListener;

use App\Entity\UserPageVisit;
use App\Repository\UserPageVisitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UserPageVisitListener
{
    private const MAX_VISITS = 200;

    public function __construct(
        private UserPageVisitRepository $visitRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // Vérifier que c'est bien une visite de page
        if (!$entity instanceof UserPageVisit) {
            return;
        }

        // Compter le nombre total de visites
        $totalVisits = $this->visitRepository->count([]);

        // Si on dépasse la limite, supprimer les plus anciennes
        if ($totalVisits > self::MAX_VISITS) {
            $toDelete = $totalVisits - self::MAX_VISITS;

            // Récupérer les visites les plus anciennes
            $oldestVisits = $this->visitRepository->createQueryBuilder('v')
                ->orderBy('v.visitedAt', 'ASC')
                ->setMaxResults($toDelete)
                ->getQuery()
                ->getResult();

            // Supprimer les visites
            foreach ($oldestVisits as $visit) {
                $this->entityManager->remove($visit);
            }

            $this->entityManager->flush();
        }
    }
}
