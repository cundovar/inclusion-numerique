<?php

namespace App\Controller\Api;

use App\Application\Favorite\Command\AddFavoriteCommand;
use App\Application\Favorite\Command\RemoveFavoriteCommand;
use App\Application\Favorite\Command\ToggleFavoriteCommand;
use App\Application\Favorite\Handler\AddFavoriteHandler;
use App\Application\Favorite\Handler\CheckFavoriteHandler;
use App\Application\Favorite\Handler\GetFavoriteByIdHandler;
use App\Application\Favorite\Handler\GetUserFavoritesHandler;
use App\Application\Favorite\Handler\RemoveFavoriteHandler;
use App\Application\Favorite\Handler\ToggleFavoriteHandler;
use App\Application\Favorite\Query\CheckFavoriteQuery;
use App\Application\Favorite\Query\GetFavoriteByIdQuery;
use App\Application\Favorite\Query\GetUserFavoritesQuery;
use App\Domain\Favorite\Exception\FavoriteAlreadyExistsException;
use App\Domain\Favorite\Exception\FavoriteNotFoundException;
use App\Domain\Favorite\Exception\UnauthorizedFavoriteAccessException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/me-favorites')]
#[IsGranted('ROLE_USER')]
class FavoriteController extends AbstractController
{
    public function __construct(
        private GetUserFavoritesHandler $getUserFavoritesHandler,
        private GetFavoriteByIdHandler $getFavoriteByIdHandler,
        private AddFavoriteHandler $addFavoriteHandler,
        private RemoveFavoriteHandler $removeFavoriteHandler,
        private ToggleFavoriteHandler $toggleFavoriteHandler,
        private CheckFavoriteHandler $checkFavoriteHandler
    ) {}

    #[Route('/list-my-favorites', name: 'my-favorites-list', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $query = new GetUserFavoritesQuery(user: $this->getUser());
        $favorites = $this->getUserFavoritesHandler->handle($query);

        $data = array_map(fn($dto) => $dto->toArray(), $favorites);

        return $this->json($data);
    }

    #[Route('/show-my-favorite/{id}', name: 'my-favorites-show', methods: ['GET'])]
    public function showFavorite(int $id): JsonResponse
    {
        try {
            $query = new GetFavoriteByIdQuery(
                user: $this->getUser(),
                favoriteId: $id
            );

            $favoriteDTO = $this->getFavoriteByIdHandler->handle($query);

            return $this->json($favoriteDTO->toArray());
        } catch (FavoriteNotFoundException $e) {
            return $this->json(['error' => 'Favori non trouvé'], Response::HTTP_NOT_FOUND);
        } catch (UnauthorizedFavoriteAccessException $e) {
            return $this->json(['error' => 'Favori non trouvé'], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['pageId'])) {
            return $this->json(['error' => 'pageId requis'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $command = new AddFavoriteCommand(
                user: $this->getUser(),
                pageId: (int) $data['pageId']
            );

            $favoriteDTO = $this->addFavoriteHandler->handle($command);

            return $this->json($favoriteDTO->toArray(), Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (FavoriteAlreadyExistsException $e) {
            return $this->json(['error' => 'Cette page est déjà dans vos favoris'], Response::HTTP_CONFLICT);
        }
    }

    #[Route('/{id<\d+>}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $command = new RemoveFavoriteCommand(
                user: $this->getUser(),
                favoriteId: $id
            );

            $this->removeFavoriteHandler->handle($command);

            return $this->json(['message' => 'Favori supprimé avec succès']);
        } catch (FavoriteNotFoundException $e) {
            return $this->json(['error' => 'Favori non trouvé'], Response::HTTP_NOT_FOUND);
        } catch (UnauthorizedFavoriteAccessException $e) {
            return $this->json(['error' => 'Favori non trouvé'], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/toggle/{pageId}', name: 'toggle', methods: ['POST'])]
    public function toggle(int $pageId): JsonResponse
    {
        try {
            $command = new ToggleFavoriteCommand(
                user: $this->getUser(),
                pageId: $pageId
            );

            $result = $this->toggleFavoriteHandler->handle($command);

            return $this->json($result);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/check/{pageId}', name: 'check', methods: ['GET'])]
    public function check(int $pageId): JsonResponse
    {
        try {
            $query = new CheckFavoriteQuery(
                user: $this->getUser(),
                pageId: $pageId
            );

            $result = $this->checkFavoriteHandler->handle($query);

            return $this->json($result);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
}
