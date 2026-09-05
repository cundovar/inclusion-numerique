<?php

declare(strict_types=1);

namespace App\Controller\Api\AdminCrud;

use App\Application\AgentCourse\Command\CreateAgentCourseCommand;
use App\Application\AgentCourse\Handler\CreateAgentCourseHandler;
use App\Domain\PageContent\Repository\PageContentRepositoryInterface;
use App\Domain\Shared\Persistence\TransactionalExecutorInterface;
use App\Entity\AgentCourseGeneration;
use App\Entity\CourseMedia;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/agent-cours/generations')]
final class ApiAgentCourseGenerationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private CreateAgentCourseHandler $createCourse,
        private PageContentRepositoryInterface $courses,
        private TransactionalExecutorInterface $transactional,
        private ParameterBagInterface $parameters,
    ) {}


    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $requested = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) $request->query->get('status', 'pending,generating,verifying,ready'))
        )));
        $allowed = ['pending', 'generating', 'verifying', 'ready', 'succeeded', 'failed'];
        foreach ($requested as $status) {
            if (!in_array($status, $allowed, true)) {
                return new JsonResponse(['error' => 'Statut de génération invalide'], Response::HTTP_BAD_REQUEST);
            }
        }

        $limit = max(1, min(100, $request->query->getInt('limit', 50)));
        $criteria = $requested === [] ? [] : ['status' => $requested];
        $items = $this->em->getRepository(AgentCourseGeneration::class)->findBy(
            $criteria,
            ['updatedAt' => 'ASC'],
            $limit
        );

        return new JsonResponse(array_map(fn (AgentCourseGeneration $item) => $this->map($item), $items));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $this->payload($request);
        $batchId = trim((string) ($data['batchId'] ?? ''));
        $externalId = trim((string) ($data['externalId'] ?? ''));
        $payload = $data['payload'] ?? null;
        if ($batchId === '' || $externalId === '' || !is_array($payload)) {
            return new JsonResponse(['error' => 'batchId, externalId et payload sont requis'], Response::HTTP_BAD_REQUEST);
        }

        $existing = $this->em->getRepository(AgentCourseGeneration::class)->findOneBy(['batchId' => $batchId, 'externalId' => $externalId]);
        if ($existing) return new JsonResponse($this->map($existing), Response::HTTP_OK);

        $generation = new AgentCourseGeneration($batchId, $externalId, $payload);
        $this->em->persist($generation);
        $this->em->flush();
        return new JsonResponse($this->map($generation), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $generation = $this->find($id);
        return $generation ? new JsonResponse($this->map($generation)) : new JsonResponse(['error' => 'Génération non trouvée'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $generation = $this->find($id);
        if (!$generation) return new JsonResponse(['error' => 'Génération non trouvée'], Response::HTTP_NOT_FOUND);
        $data = $this->payload($request);
        try {
            $generation->update(
                (string) ($data['status'] ?? $generation->getStatus()),
                isset($data['candidate']) && is_array($data['candidate']) ? $data['candidate'] : null,
                isset($data['verificationReport']) && is_array($data['verificationReport']) ? $data['verificationReport'] : null,
                isset($data['technicalError']) ? (string) $data['technicalError'] : null,
                isset($data['payload']) && is_array($data['payload']) ? $data['payload'] : null,
            );
            $this->em->flush();
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return new JsonResponse($this->map($generation));
    }

    #[Route('/{id}/finaliser', methods: ['POST'])]
    public function finalize(int $id): JsonResponse
    {
        $generation = $this->find($id);
        if (!$generation) return new JsonResponse(['error' => 'Génération non trouvée'], Response::HTTP_NOT_FOUND);
        if ($generation->getCourse()) return new JsonResponse($this->map($generation));
        $report = $generation->getVerificationReport();
        if (($report['approved'] ?? false) !== true) return new JsonResponse(['error' => 'Le dernier rapport de vérification n’autorise pas la finalisation'], Response::HTTP_CONFLICT);
        $candidate = $generation->getCandidate();
        $payload = $generation->getPayload();
        $html = trim((string) ($candidate['codeHTML'] ?? $candidate['html'] ?? ''));
        if ($html === '') return new JsonResponse(['error' => 'Aucun contenu validé à finaliser'], Response::HTTP_CONFLICT);

        try {
            $this->transactional->run(function () use ($candidate, $payload, $html, $generation): void {
                $course = $this->createCourse->handle(new CreateAgentCourseCommand(
                    title: (string) ($candidate['title'] ?? $payload['title'] ?? $payload['titre'] ?? ''),
                    technology: (string) ($payload['technology'] ?? $payload['technologie'] ?? ''),
                    level: (string) ($payload['level'] ?? $payload['niveau'] ?? ''),
                    duration: (string) ($candidate['duration'] ?? $payload['duration'] ?? $payload['duree'] ?? ''),
                    codeHtml: $html,
                    description: (string) ($candidate['description'] ?? $payload['description'] ?? ''),
                    objectives: (string) ($candidate['objectives'] ?? $payload['objectifs'] ?? ''),
                    menuId: isset($payload['menuId']) ? (int) $payload['menuId'] : null,
                    newMenuLabel: isset($payload['newMenuLabel']) ? (string) $payload['newMenuLabel'] : null,
                    status: 'publie',
                ));
                $pageContent = $this->courses->findById($course->id);
                if (!$pageContent) throw new \LogicException('Cours créé mais introuvable');
                foreach ($this->em->getRepository(CourseMedia::class)->findBy(['generation' => $generation]) as $media) {
                    $media->setCourse($pageContent);
                    $media->setGeneration(null);
                }
                $generation->complete($pageContent);
                $this->em->flush();
            });
        } catch (\Throwable $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return new JsonResponse($this->map($generation));
    }

    #[Route('/{id}/echouer', methods: ['POST'])]
    public function fail(int $id, Request $request): JsonResponse
    {
        $generation = $this->find($id);
        if (!$generation) return new JsonResponse(['error' => 'Génération non trouvée'], Response::HTTP_NOT_FOUND);
        $data = $this->payload($request);
        $generation->fail(isset($data['verificationReport']) && is_array($data['verificationReport']) ? $data['verificationReport'] : null, isset($data['technicalError']) ? (string) $data['technicalError'] : null);
        foreach ($this->em->getRepository(CourseMedia::class)->findBy(['generation' => $generation]) as $media) {
            $file = $this->parameters->get('kernel.project_dir') . '/public' . $media->getPublicPath();
            if (is_file($file)) unlink($file);
            $this->em->remove($media);
        }
        $this->em->flush();
        return new JsonResponse($this->map($generation));
    }

    private function find(int $id): ?AgentCourseGeneration { return $this->em->find(AgentCourseGeneration::class, $id); }
    private function payload(Request $request): array { $data = json_decode($request->getContent(), true); return is_array($data) ? $data : []; }
    private function map(AgentCourseGeneration $item): array
    {
        return ['id' => $item->getId(), 'batchId' => $item->getBatchId(), 'externalId' => $item->getExternalId(), 'status' => $item->getStatus(), 'verificationAttempts' => $item->getVerificationAttempts(), 'payload' => $item->getPayload(), 'candidate' => $item->getCandidate(), 'verificationReport' => $item->getVerificationReport(), 'technicalError' => $item->getTechnicalError(), 'courseId' => $item->getCourse()?->getId(), 'createdAt' => $item->getCreatedAt()->format(DATE_ATOM), 'updatedAt' => $item->getUpdatedAt()->format(DATE_ATOM), 'finishedAt' => $item->getFinishedAt()?->format(DATE_ATOM)];
    }
}
