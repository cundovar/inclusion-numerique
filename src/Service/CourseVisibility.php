<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PageContent;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Même règle que l'API publique (PublishedContentExtension), pour un cours déjà chargé : un cours publié
 * est ouvert à tous, sauf s'il est réservé, lui ou son parcours, à d'autres personnes. Un administrateur
 * voit tout, brouillons compris, pour les relire.
 */
final class CourseVisibility
{
    public function __construct(private Security $security) {}

    public function canSee(PageContent $course): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
        $user = $this->security->getUser();
        $user = $user instanceof User ? $user : null;

        return $course->isPublished()
            && $course->isAccessibleTo($user)
            && ($course->getCategory()?->isAccessibleTo($user) ?? true);
    }

    /**
     * Un contenu que tout le monde peut voir peut être gardé en cache par les intermédiaires.
     */
    public function isPublic(PageContent $course): bool
    {
        return $course->isPublished() && !$course->isReserve() && !($course->getCategory()?->isReserve() ?? false);
    }
}
