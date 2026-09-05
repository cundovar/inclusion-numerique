<?php

declare(strict_types=1);

namespace App\Controller\Api\AdminCrud;

use App\Application\AgentCourse\Command\CreateAgentCourseCommand;
use App\Application\AgentCourse\Command\SaveAgentCourseRevisionCommand;
use App\Application\AgentCourse\DTO\AgentCourseRevisionDTO;
use App\Application\AgentCourse\Handler\CreateAgentCourseHandler;
use App\Application\AgentCourse\Handler\SaveAgentCourseRevisionHandler;
use App\Domain\AgentCourseRevision\Repository\AgentCourseRevisionRepositoryInterface;
use App\Domain\PageContent\Repository\PageContentRepositoryInterface;
use App\Entity\AgentCourseRevision;
use App\Entity\PageContent;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/agent-cours')]
final class ApiAgentCourseController extends AbstractController
{
    public function __construct(
        private CreateAgentCourseHandler $createAgentCourseHandler,
        private SaveAgentCourseRevisionHandler $saveAgentCourseRevisionHandler,
        private PageContentRepositoryInterface $pageContentRepository,
        private AgentCourseRevisionRepositoryInterface $revisionRepository
    ) {}

    #[Route('/creer', name: 'api_agent_course_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $course = $this->createAgentCourseHandler->handle(
                new CreateAgentCourseCommand(
                    title: (string) ($data['titre'] ?? ''),
                    technology: (string) ($data['technologie'] ?? ''),
                    level: (string) ($data['niveau'] ?? ''),
                    duration: (string) ($data['duree'] ?? ''),
                    codeHtml: (string) ($data['codeHTML'] ?? ''),
                    description: isset($data['description']) ? (string) $data['description'] : null,
                    objectives: isset($data['objectifs']) ? (string) $data['objectifs'] : null,
                    menuId: isset($data['menuId']) ? (int) $data['menuId'] : null,
                    newMenuLabel: isset($data['nouveauMenuLabel']) ? (string) $data['nouveauMenuLabel'] : null,
                    status: isset($data['statut']) ? (string) $data['statut'] : 'brouillon'
                )
            );
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'id' => $course->id,
            'pageId' => $course->pageId,
            'menuId' => $course->menuId,
            'slug' => $course->slug,
            'title' => $course->title,
            'technology' => $course->technology,
            'level' => $course->level,
            'status' => $course->status,
            'visible' => $course->visible,
        ], Response::HTTP_CREATED);
    }

    #[Route('', name: 'api_agent_course_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $criteria = [
            'type' => (string) $request->query->get('type', 'agent-cours'),
        ];

        if ($request->query->has('categoryId')) {
            $criteria['category'] = $request->query->getInt('categoryId');
        }

        if ($request->query->has('niveauCoursId')) {
            $criteria['niveauCours'] = $request->query->getInt('niveauCoursId');
        }

        if ($request->query->has('statut')) {
            $criteria['visible'] = $request->query->get('statut') === 'publie';
        }

        $courses = $this->pageContentRepository->findByCriteria($criteria, ['id' => 'DESC']);

        return new JsonResponse(array_map(fn (PageContent $course) => $this->mapCourse($course), $courses), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'api_agent_course_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $course = $this->pageContentRepository->findById($id);

        if (!$course || $course->getType() !== 'agent-cours') {
            return new JsonResponse(['error' => 'Cours non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->mapCourse($course), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'api_agent_course_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $course = $this->pageContentRepository->findById($id);

        if (!$course || $course->getType() !== 'agent-cours') {
            return new JsonResponse(['error' => 'Cours non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['title'])) {
            $course->setTitle((string) $data['title']);
        }

        if (isset($data['description'])) {
            $course->setContent((string) $data['description']);
        }

        if (isset($data['codeHTML'])) {
            $course->setCode((string) $data['codeHTML']);
        }

        if (isset($data['statut'])) {
            $course->setVisible((string) $data['statut'] === 'publie');
        }

        $this->pageContentRepository->save($course);

        return new JsonResponse($this->mapCourse($course), Response::HTTP_OK);
    }

    #[Route('/revisions', name: 'api_agent_course_revision_create', methods: ['POST'])]
    public function createRevision(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $revision = $this->saveAgentCourseRevisionHandler->handle(
                new SaveAgentCourseRevisionCommand(
                    id: null,
                    courseId: (int) ($data['courseId'] ?? 0),
                    typeRevision: (string) ($data['typeRevision'] ?? ''),
                    commentaire: (string) ($data['commentaire'] ?? ''),
                    ancienCode: (string) ($data['ancienCode'] ?? ''),
                    nouveauCode: (string) ($data['nouveauCode'] ?? ''),
                    appliquee: (bool) ($data['appliquee'] ?? false)
                )
            );
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->mapRevisionDto($revision), Response::HTTP_CREATED);
    }

    #[Route('/revisions', name: 'api_agent_course_revision_list_all', methods: ['GET'])]
    public function listAllRevisions(): JsonResponse
    {
        $revisions = $this->revisionRepository->findAll();

        return new JsonResponse(array_map(fn (AgentCourseRevision $revision) => $this->mapRevision($revision), $revisions), Response::HTTP_OK);
    }

    #[Route('/revisions/{id}', name: 'api_agent_course_revision_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function updateRevision(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $revision = $this->saveAgentCourseRevisionHandler->handle(
                new SaveAgentCourseRevisionCommand(
                    id: $id,
                    courseId: (int) ($data['courseId'] ?? 0),
                    typeRevision: (string) ($data['typeRevision'] ?? ''),
                    commentaire: (string) ($data['commentaire'] ?? ''),
                    ancienCode: (string) ($data['ancienCode'] ?? ''),
                    nouveauCode: (string) ($data['nouveauCode'] ?? ''),
                    appliquee: (bool) ($data['appliquee'] ?? false)
                )
            );
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->mapRevisionDto($revision), Response::HTTP_OK);
    }

    #[Route('/{id}/revisions', name: 'api_agent_course_revision_list', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function listRevisions(int $id): JsonResponse
    {
        $course = $this->pageContentRepository->findById($id);

        if (!$course || $course->getType() !== 'agent-cours') {
            return new JsonResponse(['error' => 'Cours non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $revisions = $this->revisionRepository->findByCourse($course);

        return new JsonResponse(array_map(fn (AgentCourseRevision $revision) => $this->mapRevision($revision), $revisions), Response::HTTP_OK);
    }

    #[Route('/revisions/{id}/appliquer', name: 'api_agent_course_revision_apply', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function applyRevision(int $id): JsonResponse
    {
        $revision = $this->revisionRepository->findById($id);

        if (!$revision) {
            return new JsonResponse(['error' => 'La révision n\'a pas été trouvée'], Response::HTTP_NOT_FOUND);
        }

        $course = $revision->getCourse();
        if (!$course || $course->getType() !== 'agent-cours') {
            return new JsonResponse(['error' => 'Cours non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $course->setCode($revision->getNouveauCode());
        $this->pageContentRepository->save($course);

        $revision->setAppliquee(true);
        $revision->setDateRevision(new \DateTime());
        $this->revisionRepository->save($revision);

        return new JsonResponse([
            'course' => $this->mapCourse($course),
            'revision' => $this->mapRevision($revision),
        ], Response::HTTP_OK);
    }

    private function mapCourse(PageContent $course): array
    {
        return [
            'id' => $course->getId(),
            'title' => $course->getTitle(),
            'description' => $course->getContent(),
            'code' => $course->getCode(),
            'duration' => $course->getDuration() ?: 'N/A',
            'status' => $course->isVisible() ? 'publie' : 'brouillon',
            'genereParIA' => $course->getType() === 'agent-cours',
            'technology' => $course->getCategory() ? [
                'id' => $course->getCategory()->getId(),
                'name' => $course->getCategory()->getName(),
            ] : null,
            'level' => $course->getNiveauCours() ? [
                'id' => $course->getNiveauCours()->getId(),
                'name' => $course->getNiveauCours()->getName(),
            ] : null,
            'page' => $course->getPage() ? [
                'id' => $course->getPage()->getId(),
                'slug' => $course->getPage()->getSlug(),
            ] : null,
            'menu' => $course->getMenu() ? [
                'id' => $course->getMenu()->getId(),
                'label' => $course->getMenu()->getLabel(),
            ] : null,
        ];
    }

    private function mapRevision(AgentCourseRevision $revision): array
    {
        return [
            'id' => $revision->getId(),
            'courseId' => $revision->getCourse()?->getId(),
            'typeRevision' => $revision->getTypeRevision(),
            'commentaire' => $revision->getCommentaire(),
            'ancienCode' => $revision->getAncienCode(),
            'nouveauCode' => $revision->getNouveauCode(),
            'dateRevision' => $revision->getDateRevision()?->format(DATE_ATOM),
            'appliquee' => $revision->isAppliquee(),
        ];
    }

    private function mapRevisionDto(AgentCourseRevisionDTO $revision): array
    {
        return [
            'id' => $revision->id,
            'courseId' => $revision->courseId,
            'typeRevision' => $revision->typeRevision,
            'commentaire' => $revision->commentaire,
            'ancienCode' => $revision->ancienCode,
            'nouveauCode' => $revision->nouveauCode,
            'dateRevision' => $revision->dateRevision,
            'appliquee' => $revision->appliquee,
        ];
    }
}
