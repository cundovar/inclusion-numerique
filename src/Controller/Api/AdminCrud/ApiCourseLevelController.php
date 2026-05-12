<?php

declare(strict_types=1);

namespace App\Controller\Api\AdminCrud;

use App\Domain\CourseLevel\Repository\CourseLevelRepositoryInterface;
use App\Entity\NiveauCours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/niveau-cours')]
final class ApiCourseLevelController extends AbstractController
{
    public function __construct(
        private CourseLevelRepositoryInterface $courseLevelRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'api_course_levels_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $levels = $this->courseLevelRepository->findAll();
        $json = $this->serializer->serialize($levels, 'json', [
            'groups' => ['niveau_cours:read', 'page_content:read'],
        ]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_course_levels_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $level = $this->courseLevelRepository->findById($id);

        if ($level === null) {
            return new JsonResponse(['error' => 'Niveau de cours non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $json = $this->serializer->serialize($level, 'json', [
            'groups' => ['niveau_cours:read', 'page_content:read'],
        ]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'api_course_levels_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        $level = new NiveauCours();

        if (!isset($data['name']) || trim((string) $data['name']) === '') {
            return new JsonResponse(['error' => 'Le champ name est requis'], Response::HTTP_BAD_REQUEST);
        }

        $level->setName($data['name']);

        if (isset($data['ordre'])) {
            $level->setOrdre((int) $data['ordre']);
        }

        $errors = $this->validator->validate($level);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $messages], Response::HTTP_BAD_REQUEST);
        }

        $this->courseLevelRepository->save($level);

        $json = $this->serializer->serialize($level, 'json', [
            'groups' => ['niveau_cours:read', 'page_content:read'],
        ]);

        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'api_course_levels_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $level = $this->courseLevelRepository->findById($id);

        if ($level === null) {
            return new JsonResponse(['error' => 'Niveau de cours non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['name'])) {
            $level->setName($data['name']);
        }

        if (isset($data['ordre'])) {
            $level->setOrdre((int) $data['ordre']);
        }

        $errors = $this->validator->validate($level);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $messages], Response::HTTP_BAD_REQUEST);
        }

        $this->courseLevelRepository->save($level);

        $json = $this->serializer->serialize($level, 'json', [
            'groups' => ['niveau_cours:read', 'page_content:read'],
        ]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_course_levels_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $level = $this->courseLevelRepository->findById($id);

        if ($level === null) {
            return new JsonResponse(['error' => 'Niveau de cours non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($level->getCategories()->count() > 0 || $level->getMenus()->count() > 0 || $level->getPageContents()->count() > 0) {
            return new JsonResponse([
                'error' => 'Impossible de supprimer le niveau car il est encore utilisé',
            ], Response::HTTP_CONFLICT);
        }

        $this->courseLevelRepository->delete($level);

        return new JsonResponse(['message' => 'Niveau supprimé avec succès'], Response::HTTP_OK);
    }
}
