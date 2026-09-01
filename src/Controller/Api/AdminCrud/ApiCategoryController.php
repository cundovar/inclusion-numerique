<?php

namespace App\Controller\Api\AdminCrud;

use App\Domain\Category\Repository\CategoryRepositoryInterface;
use App\Domain\CourseLevel\Repository\CourseLevelRepositoryInterface;
use App\Domain\PositionMenu\Repository\PositionMenuRepositoryInterface;
use App\Domain\SuperMenu\Repository\SuperMenuRepositoryInterface;
use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/categories')]
final class ApiCategoryController extends AbstractController
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private SuperMenuRepositoryInterface $superMenuRepository,
        private PositionMenuRepositoryInterface $positionMenuRepository,
        private CourseLevelRepositoryInterface $courseLevelRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'api_categories_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        if ($request->query->has('name')) {
            $category = $this->categoryRepository->findByName((string) $request->query->get('name'));
            $categories = $category ? [$category] : [];
        } else {
            $categories = $this->categoryRepository->findAll();
        }

        $jsonCategories = $this->serializer->serialize($categories, 'json', ['groups' => ['page_content:read']]);
        
        return new JsonResponse($jsonCategories, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_categories_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $category = $this->categoryRepository->findById($id);
        
        if (!$category) {
            return new JsonResponse(['error' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $jsonCategory = $this->serializer->serialize($category, 'json', ['groups' => ['page_content:read']]);
        
        return new JsonResponse($jsonCategory, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'api_categories_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        $category = new Category();
        
        // Validation et assignation des données
        if (isset($data['name'])) {
            $category->setName($data['name']);
        } else {
            return new JsonResponse(['error' => 'Le champ name est requis'], Response::HTTP_BAD_REQUEST);
        }

        if ($error = $this->assignRelations($category, $data)) {
            return $error;
        }

        // Validation de l'entité
        $errors = $this->validator->validate($category);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->categoryRepository->save($category);

        $jsonCategory = $this->serializer->serialize($category, 'json', ['groups' => ['page_content:read']]);
        
        return new JsonResponse($jsonCategory, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'api_categories_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $category = $this->categoryRepository->findById($id);
        
        if (!$category) {
            return new JsonResponse(['error' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        // Mise à jour des champs
        if (isset($data['name'])) {
            $category->setName($data['name']);
        }

        if ($error = $this->assignRelations($category, $data, true)) {
            return $error;
        }

        // Validation de l'entité
        $errors = $this->validator->validate($category);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->categoryRepository->save($category);

        $jsonCategory = $this->serializer->serialize($category, 'json', ['groups' => ['page_content:read']]);
        
        return new JsonResponse($jsonCategory, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_categories_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $category = $this->categoryRepository->findById($id);
        
        if (!$category) {
            return new JsonResponse(['error' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier s'il y a des menus associés
        if ($category->getMenus()->count() > 0) {
            return new JsonResponse([
                'error' => 'Impossible de supprimer la catégorie car elle contient des menus associés'
            ], Response::HTTP_CONFLICT);
        }

        // Vérifier s'il y a des contenus de page associés
        if ($category->getPageContents()->count() > 0) {
            return new JsonResponse([
                'error' => 'Impossible de supprimer la catégorie car elle contient des contenus de page associés'
            ], Response::HTTP_CONFLICT);
        }

        $this->categoryRepository->delete($category);

        return new JsonResponse(['message' => 'Catégorie supprimée avec succès'], Response::HTTP_OK);
    }

    #[Route('/{id}/page-contents', name: 'api_categories_page_contents', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getPageContents(int $id): JsonResponse
    {
        $category = $this->categoryRepository->findById($id);
        
        if (!$category) {
            return new JsonResponse(['error' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $pageContents = $category->getPageContents();
        $jsonPageContents = $this->serializer->serialize($pageContents, 'json', ['groups' => ['page_content:read']]);
        
        return new JsonResponse($jsonPageContents, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}/menus', name: 'api_categories_menus', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getMenus(int $id): JsonResponse
    {
        $category = $this->categoryRepository->findById($id);
        
        if (!$category) {
            return new JsonResponse(['error' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $menus = $category->getMenus();
        $jsonMenus = $this->serializer->serialize($menus, 'json', ['groups' => ['menu_list']]);
        
        return new JsonResponse($jsonMenus, Response::HTTP_OK, [], true);
    }

    private function assignRelations(Category $category, array $data, bool $allowNull = false): ?JsonResponse
    {
        if (array_key_exists('superMenuId', $data)) {
            if ($allowNull && $data['superMenuId'] === null) {
                $category->setSuperMenu(null);
            } else {
                $superMenu = $this->superMenuRepository->findById((int) $data['superMenuId']);
                if (!$superMenu) return new JsonResponse(['error' => 'Supermenu non trouvé'], Response::HTTP_BAD_REQUEST);
                $category->setSuperMenu($superMenu);
            }
        }

        if (array_key_exists('positionMenusId', $data)) {
            if ($allowNull && $data['positionMenusId'] === null) {
                $category->setPositionMenus(null);
            } else {
                $position = $this->positionMenuRepository->findById((int) $data['positionMenusId']);
                if (!$position) return new JsonResponse(['error' => 'Position de menu non trouvée'], Response::HTTP_BAD_REQUEST);
                $category->setPositionMenus($position);
            }
        }

        if (array_key_exists('niveauCoursId', $data)) {
            if ($allowNull && $data['niveauCoursId'] === null) {
                $category->setNiveauCours(null);
            } else {
                $level = $this->courseLevelRepository->findById((int) $data['niveauCoursId']);
                if (!$level) return new JsonResponse(['error' => 'Niveau de cours non trouvé'], Response::HTTP_BAD_REQUEST);
                $category->setNiveauCours($level);
            }
        }

        return null;
    }
}
