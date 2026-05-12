<?php

declare(strict_types=1);

namespace App\Application\AgentCourse\Handler;

use App\Application\AgentCourse\Command\SaveAgentCourseRevisionCommand;
use App\Application\AgentCourse\DTO\AgentCourseRevisionDTO;
use App\Domain\AgentCourseRevision\Repository\AgentCourseRevisionRepositoryInterface;
use App\Domain\PageContent\Repository\PageContentRepositoryInterface;
use App\Entity\AgentCourseRevision;
use InvalidArgumentException;

final class SaveAgentCourseRevisionHandler
{
    public function __construct(
        private AgentCourseRevisionRepositoryInterface $revisionRepository,
        private PageContentRepositoryInterface $pageContentRepository
    ) {}

    public function handle(SaveAgentCourseRevisionCommand $command): AgentCourseRevisionDTO
    {
        $this->assertCommand($command);

        $course = $this->pageContentRepository->findById($command->courseId);
        if ($course === null || $course->getType() !== 'agent-cours') {
            throw new InvalidArgumentException('Le cours n\'a pas été trouvé');
        }

        $revision = $command->id !== null
            ? $this->revisionRepository->findById($command->id)
            : new AgentCourseRevision();

        if ($command->id !== null && $revision === null) {
            throw new InvalidArgumentException('La révision n\'a pas été trouvée');
        }

        $revision->setCourse($course);
        $revision->setTypeRevision($command->typeRevision);
        $revision->setCommentaire($command->commentaire);
        $revision->setAncienCode($command->ancienCode);
        $revision->setNouveauCode($command->nouveauCode);
        $revision->setAppliquee($command->appliquee);
        $revision->setDateRevision(new \DateTime());

        $this->revisionRepository->save($revision);

        return new AgentCourseRevisionDTO(
            id: (int) $revision->getId(),
            courseId: (int) $course->getId(),
            typeRevision: (string) $revision->getTypeRevision(),
            commentaire: (string) ($revision->getCommentaire() ?? ''),
            ancienCode: (string) ($revision->getAncienCode() ?? ''),
            nouveauCode: (string) ($revision->getNouveauCode() ?? ''),
            dateRevision: $revision->getDateRevision()?->format(DATE_ATOM) ?? '',
            appliquee: $revision->isAppliquee()
        );
    }

    private function assertCommand(SaveAgentCourseRevisionCommand $command): void
    {
        $types = ['correction', 'amelioration', 'retour_eleve', 'maj_techno'];

        if (!in_array($command->typeRevision, $types, true)) {
            throw new InvalidArgumentException('Le type de révision est invalide');
        }

        if (trim($command->commentaire) === '') {
            throw new InvalidArgumentException('Le commentaire est requis');
        }

        if (trim($command->ancienCode) === '') {
            throw new InvalidArgumentException('L\'ancien code est requis');
        }

        if (trim($command->nouveauCode) === '') {
            throw new InvalidArgumentException('Le nouveau code est requis');
        }
    }
}
