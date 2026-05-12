<?php

namespace App\EventListener;

use App\Domain\UserPageVisit\Repository\UserPageVisitRepositoryInterface;
use App\Entity\UserPageVisit;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UserPageVisitListener
{
    private const MAX_VISITS = 200;

    public function __construct(
        private UserPageVisitRepositoryInterface $visitRepository
    ) {}

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // Vérifier que c'est bien une visite de page
        if (!$entity instanceof UserPageVisit) {
            return;
        }

        // Compter le nombre total de visites
        $totalVisits = $this->visitRepository->countAll();

        // Si on dépasse la limite, supprimer les plus anciennes
        if ($totalVisits > self::MAX_VISITS) {
            $toDelete = $totalVisits - self::MAX_VISITS;

            // Récupérer les visites les plus anciennes
            $oldestVisits = $this->visitRepository->findOldest($toDelete);

            foreach ($oldestVisits as $visit) {
                $this->visitRepository->delete($visit);
            }
        }
    }
}
