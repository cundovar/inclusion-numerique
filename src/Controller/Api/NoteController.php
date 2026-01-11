<?php

namespace App\Controller\Api;

use App\Application\Note\Command\CreateOrUpdateNoteCommand;
use App\Application\Note\Command\DeleteNoteCommand;
use App\Application\Note\Handler\CreateOrUpdateNoteHandler;
use App\Application\Note\Handler\DeleteNoteHandler;
use App\Application\Note\Handler\GetNoteByPageHandler;
use App\Application\Note\Handler\GetUserNotesHandler;
use App\Application\Note\Query\GetNoteByPageQuery;
use App\Application\Note\Query\GetUserNotesQuery;
use App\Domain\Note\Exception\NoteNotFoundException;
use App\Domain\Note\Exception\UnauthorizedNoteAccessException;
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
        private CreateOrUpdateNoteHandler $createOrUpdateHandler,
        private DeleteNoteHandler $deleteHandler,
        private GetUserNotesHandler $getUserNotesHandler,
        private GetNoteByPageHandler $getNoteByPageHandler
    ) {}

    #[Route('', name: 'api_note_create', methods: ['POST'])]
    public function createOrUpdate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['pageId']) || !isset($data['content'])) {
            return $this->json(['error' => 'pageId et content requis'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $command = new CreateOrUpdateNoteCommand(
                user: $this->getUser(),
                pageId: (int) $data['pageId'],
                content: $data['content']
            );

            $noteDTO = $this->createOrUpdateHandler->handle($command);

            return $this->json($noteDTO->toArray(), Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/my-notes', name: 'api_note_my_notes', methods: ['GET'])]
    public function getMyNotes(): JsonResponse
    {
        $query = new GetUserNotesQuery(user: $this->getUser());
        $notes = $this->getUserNotesHandler->handle($query);

        $data = array_map(fn($dto) => $dto->toArray(), $notes);

        return $this->json($data);
    }

    #[Route('/page/{pageId}', name: 'api_note_by_page', methods: ['GET'])]
    public function getNoteByPage(int $pageId): JsonResponse
    {
        try {
            $query = new GetNoteByPageQuery(
                user: $this->getUser(),
                pageId: $pageId
            );

            $noteDTO = $this->getNoteByPageHandler->handle($query);

            if (!$noteDTO) {
                return $this->json(null, Response::HTTP_NO_CONTENT);
            }

            return $this->json($noteDTO->toArray());
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/{id}', name: 'api_note_delete', methods: ['DELETE'])]
    public function deleteNote(int $id): JsonResponse
    {
        try {
            $command = new DeleteNoteCommand(
                user: $this->getUser(),
                noteId: $id
            );

            $this->deleteHandler->handle($command);

            return $this->json(['message' => 'Note supprimée'], Response::HTTP_OK);
        } catch (NoteNotFoundException $e) {
            return $this->json(['error' => 'Note non trouvée'], Response::HTTP_NOT_FOUND);
        } catch (UnauthorizedNoteAccessException $e) {
            return $this->json(['error' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }
    }
}
