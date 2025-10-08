<?php

namespace App\Controller;

use App\Entity\PageContent;
use App\Entity\PropositionIA;
use App\Repository\CategoryRepository;
use App\Repository\MenusRepository;
use App\Repository\PageContentRepository;
use App\Repository\PropositionIARepository;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Attribute\Route;

final class PropositionIAController extends AbstractController
{
    public function __construct(
        private PropositionIARepository $propositionIARepository,
        private SerializerInterface $serializer,
        private EntityManagerInterface $em,
        private PageContentRepository $pageContentRepository,
        private CategoryRepository $categoryRepository,
        private MenusRepository $menuRepository,
        private PageRepository $pageRepository,
   
    ) {}
    #[Route('api/proposition-ia', name: 'proposition_ia')]
    public function index(): JsonResponse
    {
     $propositions = $this->propositionIARepository->findAll();
     $jsonPropositions = $this->serializer->serialize($propositions, 'json', ['groups' => ['proposition_ia:read']]);

     return new JsonResponse($jsonPropositions, Response::HTTP_OK, [], true);



    }
    #[Route('api/proposition-ia/accept/{id}', name: 'ia_accept', methods: ['POST'])]
    public function accept(PropositionIA $propoIA,EntityManagerInterface $em,PageContentRepository $pageContentRepository) : JsonResponse
    {
        $payload=$propoIA->getPayload();
        $action=$propoIA->getAction();
        $statut=$propoIA->getStatut();

        if($action==='creation_cours')
        {
            $pageContent=new PageContent();
            $pageContent->setTitle($payload['title']);
            $pageContent->setContent($payload['content']);
            $pageContent->setCode($payload['code']);
            $pageContent->setPage($payload['page']);
            $pageContent->setCategory($payload['category']);
            $pageContent->setMenu($payload['menu']);
            $em->persist($pageContent);
            $em->flush();
            $propoIA->setStatut('accepted');
            $em->persist($propoIA);
            $em->flush();
            return new JsonResponse(['message' => 'Proposition acceptée'], Response::HTTP_OK);
        }
        if($action==='analyse_cours')
        {
            $pageContent=$pageContentRepository->find($payload['id']);
            $pageContent->setTitle($payload['title']);
            $pageContent->setContent($payload['content']);
            $pageContent->setCode($payload['code']);
            $pageContent->setPage($payload['page']);
            $pageContent->setCategory($payload['category']);
            $pageContent->setMenu($payload['menu']);
            $em->persist($pageContent);
            $em->flush();
            $propoIA->setStatut('accepted');
            $em->persist($propoIA);
            $em->flush();
            return new JsonResponse(['message' => 'Proposition acceptée'], Response::HTTP_OK);
        }
        return new JsonResponse(['message' => 'Proposition refusée'], Response::HTTP_OK);
    }
    #[Route('api/proposition-ia/reject/{id}', name: 'ia_reject', methods: ['POST'])]
    public function reject(PropositionIA $propoIA,EntityManagerInterface $em) : JsonResponse
    {
        $propoIA->setStatut('rejected');
        $em->persist($propoIA);
        $em->flush();
        return new JsonResponse(['message' => 'Proposition refusée'], Response::HTTP_OK);
    }

    #[Route('api/proposition-ia/review/{id}', name: 'ia_review', methods: ['POST'])]
    public function review(PropositionIA $propoIA,EntityManagerInterface $em) : JsonResponse
    {
        $propoIA->setStatut('review');
        $em->persist($propoIA);
        $em->flush();
        return new JsonResponse(['message' => 'Proposition revue'], Response::HTTP_OK);
    }
    #[Route('api/proposition-ia/delete/{id}', name: 'ia_delete', methods: ['POST'])]
    public function delete(PropositionIA $propoIA,EntityManagerInterface $em) : JsonResponse
    {
        $em->remove($propoIA);
        $em->flush();
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
    public function create(Request $request,EntityManagerInterface $em) : JsonResponse
    {
        $propoIA = new PropositionIA();
        $propoIA->setAction($request->get('action'));
        $propoIA->setStatut($request->get('statut'));
        $propoIA->setPayload($request->get('payload'));
        $em->persist($propoIA);
        $em->flush();
        return new JsonResponse(['message' => 'Proposition créée'], Response::HTTP_OK);
    }
}
