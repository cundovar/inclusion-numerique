<?php

namespace App\Controller\Api\AdminCrud;

use App\Entity\ExoContent;
use App\Repository\ExoContentRepository;
use App\Repository\ExoRepository;
use App\Repository\CategoryRepository;
use App\Repository\ExoMenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/exo-contents')]
final class ApiExoContentController extends AbstractController
{
    public function __construct(
        private ExoContentRepository $exoContentRepository,
        private ExoRepository $exoRepository,
        private CategoryRepository $categoryRepository,
        private ExoMenuRepository $exoMenuRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'api_exo_contents_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $exoContents = $this->exoContentRepository->findAll();
        $jsonExoContents = $this->serializer->serialize($exoContents, 'json', ['groups' => ['exo_content:read']]);

        return new JsonResponse($jsonExoContents, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_exo_contents_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $exoContent = $this->exoContentRepository->find($id);

        if (!$exoContent) {
            return new JsonResponse(['error' => 'Contenu d\'exercice non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $jsonExoContent = $this->serializer->serialize($exoContent, 'json', ['groups' => ['exo_content:read']]);

        return new JsonResponse($jsonExoContent, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'api_exo_contents_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        $exoContent = new ExoContent();

        // Validation et assignation des champs requis
        if (isset($data['title'])) {
            $exoContent->setTitle($data['title']);
        } else {
            return new JsonResponse(['error' => 'Le champ title est requis'], Response::HTTP_BAD_REQUEST);
        }

        // Champs optionnels
        if (isset($data['type'])) {
            $exoContent->setType($data['type']);
        }

        if (isset($data['content'])) {
            $exoContent->setContent($data['content']);
        }

        if (isset($data['code'])) {
            $exoContent->setCode($data['code']);
        }

        // Assignation des relations
        if (isset($data['exoId'])) {
            $exo = $this->exoRepository->find($data['exoId']);
            if ($exo) {
                $exoContent->setExo($exo);
            } else {
                return new JsonResponse(['error' => 'Exercice non trouvé'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['categoryId'])) {
            $category = $this->categoryRepository->find($data['categoryId']);
            if ($category) {
                $exoContent->setCategory($category);
            } else {
                return new JsonResponse(['error' => 'Catégorie non trouvée'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['exoMenuId'])) {
            $exoMenu = $this->exoMenuRepository->find($data['exoMenuId']);
            if ($exoMenu) {
                $exoContent->setExoMenu($exoMenu);
            } else {
                return new JsonResponse(['error' => 'Menu d\'exercice non trouvé'], Response::HTTP_BAD_REQUEST);
            }
        }

        // Validation de l'entité
        $errors = $this->validator->validate($exoContent);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($exoContent);
        $this->entityManager->flush();

        $jsonExoContent = $this->serializer->serialize($exoContent, 'json', ['groups' => ['exo_content:read']]);

        return new JsonResponse($jsonExoContent, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'api_exo_contents_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $exoContent = $this->exoContentRepository->find($id);

        if (!$exoContent) {
            return new JsonResponse(['error' => 'Contenu d\'exercice non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        // Mise à jour des champs
        if (isset($data['title'])) {
            $exoContent->setTitle($data['title']);
        }

        if (isset($data['type'])) {
            $exoContent->setType($data['type']);
        }

        if (isset($data['content'])) {
            $exoContent->setContent($data['content']);
        }

        if (isset($data['code'])) {
            $exoContent->setCode($data['code']);
        }

        // Mise à jour des relations
        if (isset($data['exoId'])) {
            if ($data['exoId'] === null) {
                $exoContent->setExo(null);
            } else {
                $exo = $this->exoRepository->find($data['exoId']);
                if ($exo) {
                    $exoContent->setExo($exo);
                } else {
                    return new JsonResponse(['error' => 'Exercice non trouvé'], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        if (isset($data['categoryId'])) {
            if ($data['categoryId'] === null) {
                $exoContent->setCategory(null);
            } else {
                $category = $this->categoryRepository->find($data['categoryId']);
                if ($category) {
                    $exoContent->setCategory($category);
                } else {
                    return new JsonResponse(['error' => 'Catégorie non trouvée'], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        if (isset($data['exoMenuId'])) {
            if ($data['exoMenuId'] === null) {
                $exoContent->setExoMenu(null);
            } else {
                $exoMenu = $this->exoMenuRepository->find($data['exoMenuId']);
                if ($exoMenu) {
                    $exoContent->setExoMenu($exoMenu);
                } else {
                    return new JsonResponse(['error' => 'Menu d\'exercice non trouvé'], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        // Validation de l'entité
        $errors = $this->validator->validate($exoContent);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        $jsonExoContent = $this->serializer->serialize($exoContent, 'json', ['groups' => ['exo_content:read']]);

        return new JsonResponse($jsonExoContent, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_exo_contents_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $exoContent = $this->exoContentRepository->find($id);

        if (!$exoContent) {
            return new JsonResponse(['error' => 'Contenu d\'exercice non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($exoContent);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Contenu d\'exercice supprimé avec succès'], Response::HTTP_OK);
    }

    #[Route('/by-exo/{exoId}', name: 'api_exo_contents_by_exo', methods: ['GET'], requirements: ['exoId' => '\d+'])]
    public function getByExo(int $exoId): JsonResponse
    {
        $exo = $this->exoRepository->find($exoId);

        if (!$exo) {
            return new JsonResponse(['error' => 'Exercice non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $exoContents = $this->exoContentRepository->findBy(['exo' => $exo]);
        $jsonExoContents = $this->serializer->serialize($exoContents, 'json', ['groups' => ['exo_content:read']]);

        return new JsonResponse($jsonExoContents, Response::HTTP_OK, [], true);
    }

    #[Route('/by-category/{categoryId}', name: 'api_exo_contents_by_category', methods: ['GET'], requirements: ['categoryId' => '\d+'])]
    public function getByCategory(int $categoryId): JsonResponse
    {
        $category = $this->categoryRepository->find($categoryId);

        if (!$category) {
            return new JsonResponse(['error' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $exoContents = $this->exoContentRepository->findBy(['category' => $category]);
        $jsonExoContents = $this->serializer->serialize($exoContents, 'json', ['groups' => ['exo_content:read']]);

        return new JsonResponse($jsonExoContents, Response::HTTP_OK, [], true);
    }
}
