<?php

namespace App\Controller;

use App\Domain\Category\Repository\CategoryRepositoryInterface;
use App\Domain\Menu\Repository\MenuRepositoryInterface;
use App\Domain\PageContent\Repository\PageContentRepositoryInterface;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Domain\PropositionIA\Repository\PropositionIARepositoryInterface;
use App\Entity\PageContent;
use App\Entity\PropositionIA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Attribute\Route;

final class PropositionIAController extends AbstractController
{
    public function __construct(
        private PropositionIARepositoryInterface $propositionIARepository,
        private SerializerInterface $serializer,
        private PageContentRepositoryInterface $pageContentRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private MenuRepositoryInterface $menuRepository,
        private PageRepositoryInterface $pageRepository,
   
    ) {}
    #[Route('api/proposition-ia', name: 'proposition_ia')]
    public function index(): JsonResponse
    {
     $propositions = $this->propositionIARepository->findAll();
     $jsonPropositions = $this->serializer->serialize($propositions, 'json', ['groups' => ['proposition_ia:read']]);

     return new JsonResponse($jsonPropositions, Response::HTTP_OK, [], true);



    }
    #[Route('api/proposition-ia/accept/{id}', name: 'ia_accept', methods: ['POST'])]
    public function accept(PropositionIA $propoIA): JsonResponse
    {
        $payload = $propoIA->getPayload();
        $action = $propoIA->getAction();

        if ($action === 'creation_cours' || $action === 'analyse_cours') {

            // Récupérer les entités depuis la base de données
            $page = isset($payload['pageId'])
                ? $this->pageRepository->findById((int) $payload['pageId'])
                : null;

            $category = isset($payload['categoryId'])
                ? $this->categoryRepository->findById((int) $payload['categoryId'])
                : null;

            $menu = isset($payload['menuId'])
                ? $this->menuRepository->findById((int) $payload['menuId'])
                : null;

            // Créer ou mettre à jour PageContent
            if ($action === 'creation_cours') {
                $pageContent = new PageContent();
            } else {
                $pageContent = $this->pageContentRepository->findById((int) $payload['id']);
                if (!$pageContent) {
                    return new JsonResponse(['error' => 'PageContent not found'], Response::HTTP_NOT_FOUND);
                }
            }

            // Assigner les champs texte
            $pageContent->setTitle($payload['title'] ?? '');
            $pageContent->setContent($payload['content'] ?? null);
            $pageContent->setCode($payload['code'] ?? null);
            $pageContent->setType($payload['type'] ?? null);

            // Assigner les relations (objets Entity)
            if ($page) $pageContent->setPage($page);
            if ($category) $pageContent->setCategory($category);
            if ($menu) $pageContent->setMenu($menu);

            $this->pageContentRepository->save($pageContent);

            $propoIA->setStatut('accepted');
            $this->propositionIARepository->save($propoIA);

            return new JsonResponse([
                'message' => 'Proposition acceptée',
                'pageContentId' => $pageContent->getId()
            ], Response::HTTP_OK);
        }

        return new JsonResponse(['error' => 'Action non supportée'], Response::HTTP_BAD_REQUEST);
    }
    #[Route('api/proposition-ia/reject/{id}', name: 'ia_reject', methods: ['POST'])]
    public function reject(PropositionIA $propoIA) : JsonResponse
    {
        $propoIA->setStatut('rejected');
        $this->propositionIARepository->save($propoIA);
        return new JsonResponse(['message' => 'Proposition refusée'], Response::HTTP_OK);
    }

    #[Route('api/proposition-ia/review/{id}', name: 'ia_review', methods: ['POST'])]
    public function review(PropositionIA $propoIA) : JsonResponse
    {
        $propoIA->setStatut('review');
        $this->propositionIARepository->save($propoIA);
        return new JsonResponse(['message' => 'Proposition revue'], Response::HTTP_OK);
    }
    #[Route('api/proposition-ia/delete/{id}', name: 'ia_delete', methods: ['POST'])]
    public function delete(PropositionIA $propoIA) : JsonResponse
    {
        $this->propositionIARepository->delete($propoIA);
        return new JsonResponse(['message' => 'Proposition supprimée'], Response::HTTP_OK);
    }
    #[Route('api/proposition-ia/list', name: 'ia_list', methods: ['GET'])]
    public function list() : JsonResponse
    {
        $propositions = $this->propositionIARepository->findAll();
        $jsonPropositions = $this->serializer->serialize($propositions, 'json', ['groups' => ['proposition_ia:read']]);
        return new JsonResponse($jsonPropositions, Response::HTTP_OK, [], true);
    }
    #[Route('api/proposition-ia/show/{id}', name: 'ia_show', methods: ['GET'])]
    public function show(PropositionIA $propoIA) : JsonResponse
    {
        $jsonProposition = $this->serializer->serialize($propoIA, 'json', ['groups' => ['proposition_ia:read']]);
        return new JsonResponse($jsonProposition, Response::HTTP_OK, [], true);
    }
    #[Route('api/proposition-ia/create', name: 'ia_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // Récupérer correctement les données JSON
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $propoIA = new PropositionIA();
        $propoIA->setAction($data['action'] ?? null);
        $propoIA->setStatut($data['statut'] ?? 'pending');
        $propoIA->setPayload($data['payload'] ?? []);
        $propoIA->setCreatedAt(new \DateTimeImmutable());

        $this->propositionIARepository->save($propoIA);

        return new JsonResponse([
            'message' => 'Proposition créée',
            'id' => $propoIA->getId()
        ], Response::HTTP_CREATED);
    }
}
