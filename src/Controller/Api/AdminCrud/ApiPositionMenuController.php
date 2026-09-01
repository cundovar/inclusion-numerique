<?php

declare(strict_types=1);

namespace App\Controller\Api\AdminCrud;

use App\Domain\PositionMenu\Repository\PositionMenuRepositoryInterface;
use App\Entity\PositionMenus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/positions-menus')]
final class ApiPositionMenuController extends AbstractController
{
    public function __construct(private PositionMenuRepositoryInterface $repository) {}

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return new JsonResponse(array_map(fn (PositionMenus $item) => $this->map($item), $this->repository->findAll()));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $position = $this->position($request);
        if ($position === '') return new JsonResponse(['error' => 'Le champ position est requis'], Response::HTTP_BAD_REQUEST);
        if ($this->repository->findByPosition($position)) return new JsonResponse(['error' => 'Cette position existe déjà'], Response::HTTP_CONFLICT);
        $item = (new PositionMenus())->setPosition($position);
        $this->repository->save($item);
        return new JsonResponse($this->map($item), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $item = $this->repository->findById($id);
        if (!$item) return new JsonResponse(['error' => 'Position non trouvée'], Response::HTTP_NOT_FOUND);
        $position = $this->position($request);
        if ($position === '') return new JsonResponse(['error' => 'Le champ position est requis'], Response::HTTP_BAD_REQUEST);
        $same = $this->repository->findByPosition($position);
        if ($same && $same->getId() !== $item->getId()) return new JsonResponse(['error' => 'Cette position existe déjà'], Response::HTTP_CONFLICT);
        $item->setPosition($position); $this->repository->save($item);
        return new JsonResponse($this->map($item));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $item = $this->repository->findById($id);
        if (!$item) return new JsonResponse(['error' => 'Position non trouvée'], Response::HTTP_NOT_FOUND);
        if (!$item->getMenus()->isEmpty() || !$item->getCategorie()->isEmpty()) return new JsonResponse(['error' => 'Impossible de supprimer une position liée'], Response::HTTP_CONFLICT);
        $this->repository->remove($item);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function position(Request $request): string
    {
        $payload = json_decode($request->getContent(), true);
        return trim((string) (is_array($payload) ? ($payload['position'] ?? '') : ''));
    }

    private function map(PositionMenus $item): array
    {
        return ['id' => $item->getId(), 'position' => $item->getPosition()];
    }
}
