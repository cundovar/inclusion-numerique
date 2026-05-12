<?php

namespace App\Controller\Api\AdminCrud;

use App\Domain\Category\Repository\CategoryRepositoryInterface;
use App\Domain\CourseLevel\Repository\CourseLevelRepositoryInterface;
use App\Domain\Menu\Repository\MenuRepositoryInterface;
use App\Domain\PositionMenu\Repository\PositionMenuRepositoryInterface;
use App\Entity\Menus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/menus')]
final class ApiMenuController extends AbstractController
{
    public function __construct(
        private MenuRepositoryInterface $menusRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private PositionMenuRepositoryInterface $positionMenusRepository,
        private CourseLevelRepositoryInterface $niveauCoursRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'api_menus_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $criteria = [];

        if ($request->query->has('categoryId')) {
            $criteria['category'] = $request->query->getInt('categoryId');
        }

        if ($request->query->has('niveauCoursId')) {
            $criteria['niveauCours'] = $request->query->getInt('niveauCoursId');
        }

        if ($request->query->has('positionMenusId')) {
            $criteria['positionMenus'] = $request->query->getInt('positionMenusId');
        }

        $menus = $this->menusRepository->findByCriteria($criteria, ['id' => 'ASC']);
        $jsonMenus = $this->serializer->serialize($menus, 'json', [
            'groups' => ['menu:list', 'menu:read', 'page_content:read', 'position_menus:read', 'niveau_cours:read']
        ]);

        return new JsonResponse($jsonMenus, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_menus_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $menu = $this->menusRepository->findById($id);

        if (!$menu) {
            return new JsonResponse(['error' => 'Menu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $jsonMenu = $this->serializer->serialize($menu, 'json', [
            'groups' => ['menu:read', 'page_content:read', 'position_menus:read', 'niveau_cours:read']
        ]);

        return new JsonResponse($jsonMenu, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'api_menus_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        $menu = new Menus();

        if (isset($data['label'])) {
            $menu->setLabel($data['label']);
        } else {
            return new JsonResponse(['error' => 'Le champ label est requis'], Response::HTTP_BAD_REQUEST);
        }

        $relationError = $this->assignRelations($menu, $data);
        if ($relationError instanceof JsonResponse) {
            return $relationError;
        }

        $errors = $this->validator->validate($menu);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->menusRepository->save($menu);

        $jsonMenu = $this->serializer->serialize($menu, 'json', [
            'groups' => ['menu:read', 'page_content:read', 'position_menus:read', 'niveau_cours:read']
        ]);

        return new JsonResponse($jsonMenu, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'api_menus_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $menu = $this->menusRepository->findById($id);

        if (!$menu) {
            return new JsonResponse(['error' => 'Menu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['label'])) {
            $menu->setLabel($data['label']);
        }

        $relationError = $this->assignRelations($menu, $data, true);
        if ($relationError instanceof JsonResponse) {
            return $relationError;
        }

        $errors = $this->validator->validate($menu);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->menusRepository->save($menu);

        $jsonMenu = $this->serializer->serialize($menu, 'json', [
            'groups' => ['menu:read', 'page_content:read', 'position_menus:read', 'niveau_cours:read']
        ]);

        return new JsonResponse($jsonMenu, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_menus_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $menu = $this->menusRepository->findById($id);

        if (!$menu) {
            return new JsonResponse(['error' => 'Menu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($menu->getPages()->count() > 0) {
            return new JsonResponse([
                'error' => 'Impossible de supprimer le menu car il contient des pages associées'
            ], Response::HTTP_CONFLICT);
        }

        if ($menu->getPageContents()->count() > 0) {
            return new JsonResponse([
                'error' => 'Impossible de supprimer le menu car il contient des contenus de page associés'
            ], Response::HTTP_CONFLICT);
        }

        $this->menusRepository->delete($menu);

        return new JsonResponse(['message' => 'Menu supprimé avec succès'], Response::HTTP_OK);
    }

    private function assignRelations(Menus $menu, array $data, bool $allowNull = false): ?JsonResponse
    {
        if (array_key_exists('categoryId', $data)) {
            if ($allowNull && $data['categoryId'] === null) {
                $menu->setCategory(null);
            } else {
                $category = $this->categoryRepository->findById((int) $data['categoryId']);
                if (!$category) {
                    return new JsonResponse(['error' => 'Catégorie non trouvée'], Response::HTTP_BAD_REQUEST);
                }

                $menu->setCategory($category);
            }
        }

        if (array_key_exists('positionMenusId', $data)) {
            if ($allowNull && $data['positionMenusId'] === null) {
                $menu->setPositionMenus(null);
            } else {
                $positionMenus = $this->positionMenusRepository->findById((int) $data['positionMenusId']);
                if (!$positionMenus) {
                    return new JsonResponse(['error' => 'Position de menu non trouvée'], Response::HTTP_BAD_REQUEST);
                }

                $menu->setPositionMenus($positionMenus);
            }
        }

        if (array_key_exists('niveauCoursId', $data)) {
            if ($allowNull && $data['niveauCoursId'] === null) {
                $menu->setNiveauCours(null);
            } else {
                $niveauCours = $this->niveauCoursRepository->findById((int) $data['niveauCoursId']);
                if (!$niveauCours) {
                    return new JsonResponse(['error' => 'Niveau de cours non trouvé'], Response::HTTP_BAD_REQUEST);
                }

                $menu->setNiveauCours($niveauCours);
            }
        }

        return null;
    }
}
