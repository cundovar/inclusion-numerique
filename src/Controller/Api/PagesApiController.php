<?php

namespace App\Controller\Api;

use App\Domain\Menu\Repository\MenuRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Domain\Page\Repository\PageRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
final class PagesApiController extends AbstractController
{

    public function __construct(
        private PageRepositoryInterface $pagesRepository,
        private MenuRepositoryInterface $menuRepository,
        private SerializerInterface $serializer
    ) {}


    
    #[Route('/', name: 'list',methods: ['GET'])]
    public function list(): JsonResponse
    {
        $pages = $this->pagesRepository->findAll();
        $jsonPages = $this->serializer->serialize($pages, 'json');

        return new JsonResponse($jsonPages, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $page = $this->pagesRepository->findById($id);

        if (!$page) {
            return new JsonResponse(['message' => 'Page non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $jsonPage = $this->serializer->serialize($page, 'json');

        return new JsonResponse($jsonPage, Response::HTTP_OK, [], true);
    }

    #[Route('/menus', name: 'menus',methods: ['GET'])]
    public function menus(): JsonResponse
    {
        $menus = $this->menuRepository->findAll();
        $jsonMenus = $this->serializer->serialize($menus, 'json', ['groups' => 'menu_list']);
    
        return new JsonResponse($jsonMenus, Response::HTTP_OK, [], true);
    }


}
