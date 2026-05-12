<?php

namespace App\Controller\Api\AdminCrud;

use App\Domain\Category\Repository\CategoryRepositoryInterface;
use App\Domain\Menu\Repository\MenuRepositoryInterface;
use App\Domain\PageContent\Repository\PageContentRepositoryInterface;
use App\Entity\PageContent;
use App\Domain\Page\Repository\PageRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/page-contents')]
final class ApiPageContentController extends AbstractController
{
    public function __construct(
        private PageContentRepositoryInterface $pageContentRepository,
        private PageRepositoryInterface $pageRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private MenuRepositoryInterface $menuRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'api_page_contents_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $pageContents = $this->pageContentRepository->findAll();
        $jsonPageContents = $this->serializer->serialize($pageContents, 'json', ['groups' => ['page_content:read']]);
        
        return new JsonResponse($jsonPageContents, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_page_contents_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $pageContent = $this->pageContentRepository->findById($id);
        
        if (!$pageContent) {
            return new JsonResponse(['error' => 'Contenu de page non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $jsonPageContent = $this->serializer->serialize($pageContent, 'json', ['groups' => ['page_content:read']]);
        
        return new JsonResponse($jsonPageContent, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'api_page_contents_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        $pageContent = new PageContent();
        
        // Validation et assignation des champs requis
        if (isset($data['title'])) {
            $pageContent->setTitle($data['title']);
        } else {
            return new JsonResponse(['error' => 'Le champ title est requis'], Response::HTTP_BAD_REQUEST);
        }

        // Champs optionnels
        if (isset($data['type'])) {
            $pageContent->setType($data['type']);
        }

        if (isset($data['content'])) {
            $pageContent->setContent($data['content']);
        }

        if (isset($data['code'])) {
            $pageContent->setCode($data['code']);
        }

        // Assignation des relations
        if (isset($data['pageId'])) {
            $page = $this->pageRepository->findById((int) $data['pageId']);
            if ($page) {
                $pageContent->setPage($page);
            } else {
                return new JsonResponse(['error' => 'Page non trouvée'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['categoryId'])) {
            $category = $this->categoryRepository->findById((int) $data['categoryId']);
            if ($category) {
                $pageContent->setCategory($category);
            } else {
                return new JsonResponse(['error' => 'Catégorie non trouvée'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['menuId'])) {
            $menu = $this->menuRepository->findById((int) $data['menuId']);
            if ($menu) {
                $pageContent->setMenu($menu);
            } else {
                return new JsonResponse(['error' => 'Menu non trouvé'], Response::HTTP_BAD_REQUEST);
            }
        }

        // Validation de l'entité
        $errors = $this->validator->validate($pageContent);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->pageContentRepository->save($pageContent);

        $jsonPageContent = $this->serializer->serialize($pageContent, 'json', ['groups' => ['page_content:read']]);
        
        return new JsonResponse($jsonPageContent, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'api_page_contents_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $pageContent = $this->pageContentRepository->findById($id);
        
        if (!$pageContent) {
            return new JsonResponse(['error' => 'Contenu de page non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        // Mise à jour des champs
        if (isset($data['title'])) {
            $pageContent->setTitle($data['title']);
        }

        if (isset($data['type'])) {
            $pageContent->setType($data['type']);
        }

        if (isset($data['content'])) {
            $pageContent->setContent($data['content']);
        }

        if (isset($data['code'])) {
            $pageContent->setCode($data['code']);
        }

        // Mise à jour des relations
        if (isset($data['pageId'])) {
            if ($data['pageId'] === null) {
                $pageContent->setPage(null);
            } else {
                $page = $this->pageRepository->findById((int) $data['pageId']);
                if ($page) {
                    $pageContent->setPage($page);
                } else {
                    return new JsonResponse(['error' => 'Page non trouvée'], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        if (isset($data['categoryId'])) {
            if ($data['categoryId'] === null) {
                $pageContent->setCategory(null);
            } else {
                $category = $this->categoryRepository->findById((int) $data['categoryId']);
                if ($category) {
                    $pageContent->setCategory($category);
                } else {
                    return new JsonResponse(['error' => 'Catégorie non trouvée'], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        if (isset($data['menuId'])) {
            if ($data['menuId'] === null) {
                $pageContent->setMenu(null);
            } else {
                $menu = $this->menuRepository->findById((int) $data['menuId']);
                if ($menu) {
                    $pageContent->setMenu($menu);
                } else {
                    return new JsonResponse(['error' => 'Menu non trouvé'], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        // Validation de l'entité
        $errors = $this->validator->validate($pageContent);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->pageContentRepository->save($pageContent);

        $jsonPageContent = $this->serializer->serialize($pageContent, 'json', ['groups' => ['page_content:read']]);
        
        return new JsonResponse($jsonPageContent, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_page_contents_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $pageContent = $this->pageContentRepository->findById($id);
        
        if (!$pageContent) {
            return new JsonResponse(['error' => 'Contenu de page non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer les blocs de page associés (ils seront supprimés automatiquement grâce à orphanRemoval: true)
        
        $this->pageContentRepository->delete($pageContent);

        return new JsonResponse(['message' => 'Contenu de page supprimé avec succès'], Response::HTTP_OK);
    }

    #[Route('/by-page/{pageId}', name: 'api_page_contents_by_page', methods: ['GET'], requirements: ['pageId' => '\d+'])]
    public function getByPage(int $pageId): JsonResponse
    {
        $page = $this->pageRepository->findById($pageId);
        
        if (!$page) {
            return new JsonResponse(['error' => 'Page non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $pageContents = $this->pageContentRepository->findByPage($page);
        $jsonPageContents = $this->serializer->serialize($pageContents, 'json', ['groups' => ['page_content:read']]);
        
        return new JsonResponse($jsonPageContents, Response::HTTP_OK, [], true);
    }

    #[Route('/by-category/{categoryId}', name: 'api_page_contents_by_category', methods: ['GET'], requirements: ['categoryId' => '\d+'])]
    public function getByCategory(int $categoryId): JsonResponse
    {
        $category = $this->categoryRepository->findById($categoryId);
        
        if (!$category) {
            return new JsonResponse(['error' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $pageContents = $this->pageContentRepository->findByCategory($category);
        $jsonPageContents = $this->serializer->serialize($pageContents, 'json', ['groups' => ['page_content:read']]);
        
        return new JsonResponse($jsonPageContents, Response::HTTP_OK, [], true);
    }
}
