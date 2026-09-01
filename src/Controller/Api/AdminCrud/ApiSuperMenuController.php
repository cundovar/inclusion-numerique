<?php

declare(strict_types=1);

namespace App\Controller\Api\AdminCrud;

use App\Domain\SuperMenu\Repository\SuperMenuRepositoryInterface;
use App\Entity\SuperMenu;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/super-menus')]
final class ApiSuperMenuController extends AbstractController
{
    public function __construct(private SuperMenuRepositoryInterface $repository) {}

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return new JsonResponse(array_map(fn (SuperMenu $item) => $this->map($item), $this->repository->findAll()));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $name = $this->name($request);
        if ($name === '') return new JsonResponse(['error' => 'Le champ name est requis'], Response::HTTP_BAD_REQUEST);
        if ($this->repository->findByName($name)) return new JsonResponse(['error' => 'Un supermenu de ce nom existe déjà'], Response::HTTP_CONFLICT);

        $superMenu = (new SuperMenu())->setName($name);
        $this->repository->save($superMenu);
        return new JsonResponse($this->map($superMenu), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $superMenu = $this->repository->findById($id);
        if (!$superMenu) return new JsonResponse(['error' => 'Supermenu non trouvé'], Response::HTTP_NOT_FOUND);
        $name = $this->name($request);
        if ($name === '') return new JsonResponse(['error' => 'Le champ name est requis'], Response::HTTP_BAD_REQUEST);
        $same = $this->repository->findByName($name);
        if ($same && $same->getId() !== $superMenu->getId()) return new JsonResponse(['error' => 'Un supermenu de ce nom existe déjà'], Response::HTTP_CONFLICT);

        $superMenu->setName($name);
        $this->repository->save($superMenu);
        return new JsonResponse($this->map($superMenu));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $superMenu = $this->repository->findById($id);
        if (!$superMenu) return new JsonResponse(['error' => 'Supermenu non trouvé'], Response::HTTP_NOT_FOUND);
        if (!$superMenu->getCategory()->isEmpty()) return new JsonResponse(['error' => 'Impossible de supprimer un supermenu lié à des catégories'], Response::HTTP_CONFLICT);
        $this->repository->remove($superMenu);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function name(Request $request): string
    {
        $payload = json_decode($request->getContent(), true);
        return trim((string) (is_array($payload) ? ($payload['name'] ?? '') : ''));
    }

    private function map(SuperMenu $superMenu): array
    {
        return ['id' => $superMenu->getId(), 'name' => $superMenu->getName()];
    }
}
