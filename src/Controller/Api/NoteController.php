<?php

namespace App\Controller\Api;

use App\Entity\Note;
use App\Entity\Page;
use App\Repository\NoteRepository;
use App\Repository\PageRepository;
use App\Service\NoteEncryptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/notes')]
#[IsGranted('ROLE_USER')]
class NoteController extends AbstractController
{
    public function __construct(
        private NoteRepository $noteRepository,
        private PageRepository $pageRepository,
        private EntityManagerInterface $em,
        private NoteEncryptionService $encryptionService
    ) {}

    /**
     * Créer ou mettre à jour une note pour une page
     */
    #[Route('', name: 'api_note_create', methods: ['POST'])]
    public function createOrUpdate(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (!isset($data['pageId']) || !isset($data['content'])) {
            return $this->json(['error' => 'pageId et content requis'], Response::HTTP_BAD_REQUEST);
        }

        $page = $this->pageRepository->find($data['pageId']);
        if (!$page) {
            return $this->json(['error' => 'Page non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier si une note existe déjà pour cette page et cet utilisateur
        $note = $this->noteRepository->findOneBy([
            'user' => $user,
            'page' => $page
        ]);

        if (!$note) {
            $note = new Note();
            $note->setUser($user);
            $note->setPage($page);
        }

        // Chiffrer le contenu avant de le sauvegarder
        $encryptedContent = $this->encryptionService->encrypt($data['content']);
        $note->setContent($encryptedContent);

        $this->em->persist($note);
        $this->em->flush();

        return $this->json([
            'id' => $note->getId(),
            'pageId' => $note->getPage()->getId(),
            'content' => $data['content'], // Retourner le contenu non chiffré
            'createdAt' => $note->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $note->getUpdatedAt()->format('Y-m-d H:i:s')
        ], Response::HTTP_CREATED);
    }

    /**
     * Récupérer toutes les notes de l'utilisateur connecté
     */
    #[Route('/my-notes', name: 'api_note_my_notes', methods: ['GET'])]
    public function getMyNotes(): JsonResponse
    {
        $user = $this->getUser();
        $notes = $this->noteRepository->findBy(
            ['user' => $user],
            ['updatedAt' => 'DESC']
        );

        $data = array_map(function($note) {
            // Déchiffrer le contenu avant de le renvoyer
            $decryptedContent = $this->encryptionService->decrypt($note->getContent());

            return [
                'id' => $note->getId(),
                'page' => [
                    'id' => $note->getPage()->getId(),
                    'slug' => $note->getPage()->getSlug()
                ],
                'content' => $decryptedContent,
                'createdAt' => $note->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $note->getUpdatedAt()->format('Y-m-d H:i:s')
            ];
        }, $notes);

        return $this->json($data);
    }

    /**
     * Récupérer la note pour une page spécifique
     */
    #[Route('/page/{pageId}', name: 'api_note_by_page', methods: ['GET'])]
    public function getNoteByPage(int $pageId): JsonResponse
    {
        $user = $this->getUser();
        $page = $this->pageRepository->find($pageId);

        if (!$page) {
            return $this->json(['error' => 'Page non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $note = $this->noteRepository->findOneBy([
            'user' => $user,
            'page' => $page
        ]);

        if (!$note) {
            return $this->json(null, Response::HTTP_NO_CONTENT);
        }

        // Déchiffrer le contenu avant de le renvoyer
        $decryptedContent = $this->encryptionService->decrypt($note->getContent());

        return $this->json([
            'id' => $note->getId(),
            'pageId' => $note->getPage()->getId(),
            'content' => $decryptedContent,
            'createdAt' => $note->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $note->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Supprimer une note
     */
    #[Route('/{id}', name: 'api_note_delete', methods: ['DELETE'])]
    public function deleteNote(int $id): JsonResponse
    {
        $user = $this->getUser();
        $note = $this->noteRepository->find($id);

        if (!$note) {
            return $this->json(['error' => 'Note non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que la note appartient à l'utilisateur
        if ($note->getUser() !== $user) {
            return $this->json(['error' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $this->em->remove($note);
        $this->em->flush();

        return $this->json(['message' => 'Note supprimée'], Response::HTTP_OK);
    }
}
