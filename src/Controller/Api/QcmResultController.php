<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\QcmResult\Command\RecordQcmAttemptCommand;
use App\Application\QcmResult\Handler\RecordQcmAttemptHandler;
use App\Domain\QCM\Repository\QCMRepositoryInterface;
use App\Domain\QcmResult\Exception\InvalidQcmAttemptException;
use App\Domain\QcmResult\Exception\QcmResultsUnavailableException;
use App\Domain\QcmResult\QcmResultsGatewayInterface;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Résultats de QCM de l'utilisateur connecté. Symfony authentifie, corrige les réponses à partir de MySQL,
 * puis délègue le stockage au micro-service MongoDB. Si ce service est indisponible : 503, le reste du site fonctionne.
 */
#[Route('/api/qcm-results')]
final class QcmResultController extends AbstractController
{
    public function __construct(
        private QcmResultsGatewayInterface $gateway
    ) {}

    #[Route('', name: 'api_qcm_results_record', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function record(Request $request, RecordQcmAttemptHandler $handler): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!\is_array($data) || !\is_string($data['language'] ?? null) || !\is_string($data['level'] ?? null)
            || !\is_array($data['answers'] ?? null)) {
            return $this->json(['error' => 'Attendu : language, level et answers'], Response::HTTP_BAD_REQUEST);
        }

        $answers = [];
        foreach ($data['answers'] as $answer) {
            $questionId = $answer['questionId'] ?? null;
            $choiceId = $answer['choiceId'] ?? null;
            if (!\is_int($questionId) || !\is_int($choiceId)) {
                return $this->json(['error' => 'Chaque réponse attend questionId et choiceId entiers'], Response::HTTP_BAD_REQUEST);
            }
            if (isset($answers[$questionId])) {
                return $this->json(['error' => InvalidQcmAttemptException::duplicateQuestion($questionId)->getMessage()], Response::HTTP_BAD_REQUEST);
            }
            $answers[$questionId] = $choiceId;
        }

        try {
            $attempt = $handler->handle(new RecordQcmAttemptCommand($this->currentUser(), $data['language'], $data['level'], $answers));
        } catch (InvalidQcmAttemptException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (QcmResultsUnavailableException) {
            return $this->unavailable();
        }

        return $this->json($attempt, Response::HTTP_CREATED);
    }

    #[Route('/me', name: 'api_qcm_results_history', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function history(Request $request): JsonResponse
    {
        $limit = max(1, min(100, $request->query->getInt('limit', 20)));

        return $this->fromGateway(fn () => $this->gateway->findUserAttempts($this->currentUserId(), $limit));
    }

    #[Route('/me/progress', name: 'api_qcm_results_progress', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function progress(): JsonResponse
    {
        return $this->fromGateway(fn () => $this->gateway->getUserProgress($this->currentUserId()));
    }

    #[Route('/me', name: 'api_qcm_results_clear', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function clear(): JsonResponse
    {
        return $this->fromGateway(fn () => ['deleted' => $this->gateway->deleteUserAttempts($this->currentUserId())]);
    }

    /**
     * Questions les plus ratées, avec leur intitulé lu dans MySQL.
     */
    #[Route('/stats/questions', name: 'api_qcm_results_question_stats', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function questionStats(Request $request, QCMRepositoryInterface $qcmRepository): JsonResponse
    {
        $language = $request->query->get('language');
        $level = $request->query->get('level');

        return $this->fromGateway(function () use ($qcmRepository, $language, $level): array {
            $stats = $this->gateway->getQuestionStats(
                \is_string($language) ? $language : null,
                \is_string($level) ? $level : null
            );

            return array_map(static function (array $stat) use ($qcmRepository): array {
                $stat['title'] = $qcmRepository->findById($stat['questionId'])?->getTitre();

                return $stat;
            }, $stats);
        });
    }

    /**
     * @param callable(): array<mixed> $call
     */
    private function fromGateway(callable $call): JsonResponse
    {
        try {
            return $this->json($call());
        } catch (QcmResultsUnavailableException) {
            return $this->unavailable();
        }
    }

    private function unavailable(): JsonResponse
    {
        return $this->json(['error' => 'Les résultats de QCM sont momentanément indisponibles.'], Response::HTTP_SERVICE_UNAVAILABLE);
    }

    private function currentUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }

    private function currentUserId(): int
    {
        return (int) $this->currentUser()->getId();
    }
}
