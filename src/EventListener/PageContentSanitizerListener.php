<?php

namespace App\EventListener;

use App\Entity\PageContent;
use App\Service\HtmlSanitizerService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * Listener Doctrine qui nettoie automatiquement le HTML
 * des entités PageContent avant leur sauvegarde en BDD
 *
 * Protège contre les attaques XSS en sanitisant les champs 'code' et 'content'
 */
#[AsEntityListener(event: Events::prePersist, entity: PageContent::class)]
#[AsEntityListener(event: Events::preUpdate, entity: PageContent::class)]
class PageContentSanitizerListener
{
    public function __construct(
        private HtmlSanitizerService $sanitizer
    ) {}

    /**
     * Appelé avant la création d'une nouvelle entité
     */
    public function prePersist(PageContent $entity, PrePersistEventArgs $event): void
    {
        $this->sanitizeEntity($entity);
    }

    /**
     * Appelé avant la mise à jour d'une entité existante
     */
    public function preUpdate(PageContent $entity, PreUpdateEventArgs $event): void
    {
        $this->sanitizeEntity($entity);
    }

    /**
     * Nettoie les champs HTML de l'entité
     * Les contenus générés par l'agent IA sont exclus du sanitizer
     */
    private function sanitizeEntity(PageContent $entity): void
    {
        if ($entity->getType() === 'agent-cours') {
            return;
        }

        // Nettoyer le champ 'code' (contenu HTML principal)
        if ($entity->getCode() !== null) {
            $entity->setCode(
                $this->sanitizer->sanitize($entity->getCode())
            );
        }

        // Nettoyer le champ 'content' si utilisé
        if ($entity->getContent() !== null) {
            $entity->setContent(
                $this->sanitizer->sanitize($entity->getContent())
            );
        }
    }
}
