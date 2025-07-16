<?php

namespace App\Controller\Api;

use App\Entity\Favorite;
use App\Entity\Page;
use App\Repository\FavoriteRepository;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/me-favorites')]
class FavoriteController extends AbstractController
{
    public function __construct(
        private FavoriteRepository $favoriteRepository,
        private PageRepository $pageRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/list-my-favorites', name: 'my-favorites-list', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $favorites = $this->favoriteRepository->findByUser($user);
        
        $favoritesData = [];
        foreach ($favorites as $favorite) {
            $favoritesData[] = [
                'id' => $favorite->getId(),
                'title' => $favorite->getPageContent()->getTitle(),
                'category' => [
                    'id' => $favorite->getPageContent()->getCategory()->getId(),
                    'name' => $favorite->getPageContent()->getCategory()->getName()
                ],
                'page' => [
                    'id' => $favorite->getPage()->getId(),
                    'slug' => $favorite->getPage()->getSlug()
                ],
                'createdAt' => $favorite->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return $this->json($favoritesData);
    }

    #[Route('/show-my-favorite/{id}', name: 'my-favorites-show', methods: ['GET'])]
    public function showFavorite(int $id): JsonResponse
    {
        $user = $this->getUser();
    
        
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $favorite = $this->favoriteRepository->find($id);
        
        if (!$favorite || $favorite->getUser() !== $user) {
            return $this->json(['error' => 'Favori non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $favorite->getId(),
            'title' => $favorite->getPageContent()->getTitle(),
            'category' => [
                'id' => $favorite->getPageContent()->getCategory()->getId(),
                'name' => $favorite->getPageContent()->getCategory()->getName()
            ],
            'page' => [
                'id' => $favorite->getPage()->getId(),
                'slug' => $favorite->getPage()->getSlug()
            ],
            'createdAt' => $favorite->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['pageId'])) {
            return $this->json(['error' => 'pageId requis'], Response::HTTP_BAD_REQUEST);
        }

        $page = $this->pageRepository->find($data['pageId']);
        
        if (!$page) {
            return $this->json(['error' => 'Page non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier si le favori existe déjà
        $existingFavorite = $this->favoriteRepository->findByUserAndPage($user, $page);
        if ($existingFavorite) {
            return $this->json(['error' => 'Cette page est déjà dans vos favoris'], Response::HTTP_CONFLICT);
        }

        $favorite = new Favorite();
        $favorite->setUser($user);
        $favorite->setPage($page);

        $this->entityManager->persist($favorite);
        $this->entityManager->flush();

        return $this->json([
            'id' => $favorite->getId(),
            'title' => $favorite->getPageContent()->getTitle(),
            'category' => [
                'id' => $favorite->getPageContent()->getCategory()->getId(),
                'name' => $favorite->getPageContent()->getCategory()->getName()
            ],
            'page' => [
                'id' => $favorite->getPage()->getId(),
                'slug' => $favorite->getPage()->getSlug()
            ],
            'createdAt' => $favorite->getCreatedAt()->format('Y-m-d H:i:s')
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id<\d+>}', name: 'delete', methods: ['DELETE'])]
//     {id<\d+>} signifie : id doit être un entier.

// Donc Symfony n’interprétera plus /list-my-favorites comme une tentative de correspondance à cette route
    public function delete(int $id): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $favorite = $this->favoriteRepository->find($id);
        
        if (!$favorite || $favorite->getUser() !== $user) {
            return $this->json(['error' => 'Favori non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($favorite);
        $this->entityManager->flush();

        return $this->json(['message' => 'Favori supprimé avec succès']);
    }

    #[Route('/toggle/{pageId}', name: 'toggle', methods: ['POST'])]
    public function toggle(int $pageId): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $page = $this->pageRepository->find($pageId);
        
        if (!$page) {
            return $this->json(['error' => 'Page non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $favorite = $this->favoriteRepository->findByUserAndPage($user, $page);
        
        if ($favorite) {
            // Supprimer le favori
            $this->entityManager->remove($favorite);
            $this->entityManager->flush();
            
            return $this->json([
                'action' => 'removed',
                'message' => 'Retiré des favoris',
                'isFavorite' => false
            ]);
        } else {
            // Ajouter le favori
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setPage($page);
            
            $this->entityManager->persist($favorite);
            $this->entityManager->flush();
            
            return $this->json([
                'action' => 'added',
                'message' => 'Ajouté aux favoris',
                'isFavorite' => true,
                'favoriteId' => $favorite->getId()
            ]);
        }
    }

    #[Route('/check/{pageId}', name: 'check', methods: ['GET'])]
    public function check(int $pageId): JsonResponse
    {
        $user = $this->getUser();
       
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $page = $this->pageRepository->find($pageId);
        
        if (!$page) {
            return $this->json(['error' => 'Page non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $isFavorite = $this->favoriteRepository->isPageFavorite($user, $page);
        
        return $this->json([
            'isFavorite' => $isFavorite,
            'pageId' => $pageId
        ]);
    }
}