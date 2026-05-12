<?php

namespace App\Controller\Api\AdminCrud;

use App\Entity\Page;
use App\Domain\Menu\Repository\MenuRepositoryInterface;
use App\Domain\Page\Repository\PageRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/pages')]
final class ApiPageController extends AbstractController
{
    public function __construct(
        private PageRepositoryInterface $pageRepository,
        private MenuRepositoryInterface $menuRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'api_pages_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $pages = $this->pageRepository->findAll();
        $jsonPages = $this->serializer->serialize($pages, 'json', ['groups' => ['page_content:read']]);
        
        return new JsonResponse($jsonPages, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_pages_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $page = $this->pageRepository->findById($id);
        
        if (!$page) {
            return new JsonResponse(['error' => 'Page non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $jsonPage = $this->serializer->serialize($page, 'json', ['groups' => ['page_content:read']]);
        
        return new JsonResponse($jsonPage, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'api_pages_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        $page = new Page();
        
        // Validation et assignation des données
        if (isset($data['slug'])) {
            $page->setSlug($data['slug']);
        } else {
            return new JsonResponse(['error' => 'Le champ slug est requis'], Response::HTTP_BAD_REQUEST);
        }

        // Assignation du menu si fourni
        if (isset($data['menuId'])) {
            $menu = $this->menuRepository->findById((int) $data['menuId']);
            if ($menu) {
                $page->setMenus($menu);
            } else {
                return new JsonResponse(['error' => 'Menu non trouvé'], Response::HTTP_BAD_REQUEST);
            }
        }

        // Validation de l'entité
        $errors = $this->validator->validate($page);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->pageRepository->save($page);

        $jsonPage = $this->serializer->serialize($page, 'json', ['groups' => ['page_content:read']]);
        
        return new JsonResponse($jsonPage, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'api_pages_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $page = $this->pageRepository->findById($id);
        
        if (!$page) {
            return new JsonResponse(['error' => 'Page non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        // Mise à jour des champs
        if (isset($data['slug'])) {
            $page->setSlug($data['slug']);
        }

        // Mise à jour du menu
        if (isset($data['menuId'])) {
            if ($data['menuId'] === null) {
                $page->setMenus(null);
            } else {
                $menu = $this->menuRepository->findById((int) $data['menuId']);
                if ($menu) {
                    $page->setMenus($menu);
                } else {
                    return new JsonResponse(['error' => 'Menu non trouvé'], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        // Validation de l'entité
        $errors = $this->validator->validate($page);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->pageRepository->save($page);

        $jsonPage = $this->serializer->serialize($page, 'json', ['groups' => ['page_content:read']]);
        
        return new JsonResponse($jsonPage, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_pages_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $page = $this->pageRepository->findById($id);
        
        if (!$page) {
            return new JsonResponse(['error' => 'Page non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier s'il y a des contenus associés
        if ($page->getPageContents()->count() > 0) {
            return new JsonResponse([
                'error' => 'Impossible de supprimer la page car elle contient des contenus associés'
            ], Response::HTTP_CONFLICT);
        }

        // Vérifier s'il y a des favoris associés
        if ($page->getFavorites()->count() > 0) {
            return new JsonResponse([
                'error' => 'Impossible de supprimer la page car elle est dans des favoris'
            ], Response::HTTP_CONFLICT);
        }

        $this->pageRepository->delete($page);

        return new JsonResponse(['message' => 'Page supprimée avec succès'], Response::HTTP_OK);
    }
}
