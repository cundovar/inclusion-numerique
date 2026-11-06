<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Capture\CaptureFailed;
use App\Service\Capture\ImageDepositService;
use App\Service\Capture\InvalidCaptureRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Point d'envoi d'une image annoncée par le connecteur (outil preparer_depot_image).
 * Le jeton à usage unique tient lieu d'autorisation : aucune session ni mot de passe.
 */
final class ImageDepositController extends AbstractController
{
    public function __construct(private ImageDepositService $deposits) {}

    // Priorité : passer avant la route attrape-tout de l'application Vue
    #[Route('/depot-image/{token}', name: 'image_deposit', methods: ['POST'], priority: 10)]
    public function __invoke(string $token, Request $request): JsonResponse
    {
        try {
            $media = $this->deposits->deposit($token, $request->files->get('image'));
        } catch (InvalidCaptureRequest $exception) {
            return $this->json(['erreur' => 'Image refusée.', 'champs' => $exception->errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (CaptureFailed $exception) {
            $status = $exception->codeName === 'JETON_INVALIDE' ? Response::HTTP_NOT_FOUND : Response::HTTP_CONFLICT;

            return $this->json(['erreur' => $exception->getMessage(), 'code' => $exception->codeName] + ($exception->details ?? []), $status);
        }

        return $this->json([
            'id' => $media->getId(),
            'marqueur' => $media->getMarker(),
            'type' => $media->getKind(),
            'url' => $media->getUrl(),
            'largeur' => $media->getWidth(),
            'hauteur' => $media->getHeight(),
        ], Response::HTTP_CREATED);
    }
}
