<?php

namespace App\Controller;

use App\Entity\QCMSession;
use App\Entity\QCMQuestion;
use App\Entity\QCMAnswer;
use App\Repository\QCMSessionRepository;
use App\Repository\QCMQuestionRepository;
use App\Service\QCMGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/qcm', name: 'qcm_')]
class QCMController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private QCMSessionRepository $qcmSessionRepository,
        private QCMQuestionRepository $qcmQuestionRepository,
        private QCMGeneratorService $qcmGeneratorService
    ) {}

    #[Route('/generate', name: 'generate', methods: ['POST'])]
    public function generateQCM(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $categoryId = $data['categoryId'] ?? null;
        $difficulty = $data['difficulty'] ?? 'medium';
        $questionsCount = $data['questionsCount'] ?? 10;
        $userId = $data['userId'] ?? null;

        try {
            $session = new QCMSession();
            $session->setTitle('QCM - ' . date('d/m/Y H:i'));
            $session->setCategoryId($categoryId);
            $session->setDifficulty($difficulty);
            $session->setQuestionsCount($questionsCount);
            $session->setUserId($userId);
            $session->setTotalQuestions($questionsCount);

            $this->entityManager->persist($session);

            // Générer les questions avec l'IA
            $questions = $this->qcmGeneratorService->generateQuestionsFromDatabase([
                'categoryId' => $categoryId,
                'questionsCount' => $questionsCount,
                'difficulty' => $difficulty,
                'userId' => $userId
            ]);
            
            foreach ($questions as $questionData) {
                $question = new QCMQuestion();
                $question->setQuestion($questionData['question']);
                $question->setOptions($questionData['options']);
                $question->setCorrectAnswer($questionData['correct_answer']);
                $question->setExplanation($questionData['explanation'] ?? null);
                $question->setPageContentId($questionData['page_content_id'] ?? null);
                $question->setDifficulty($difficulty);
                $question->setTopic($questionData['topic'] ?? null);
                $session->addQuestion($question);

                $this->entityManager->persist($question);
            }

            $this->entityManager->flush();

            // Retourner les données de session manuellement
            $sessionData = [
                'id' => $session->getId(),
                'title' => $session->getTitle(),
                'difficulty' => $session->getDifficulty(),
                'questions_count' => $session->getQuestionsCount(),
                'total_questions' => $session->getTotalQuestions(),
                'status' => $session->getStatus(),
                'created_at' => $session->getCreatedAt()->format('Y-m-d H:i:s'),
                'questions' => array_map(fn (QCMQuestion $question) => [
                    'id' => $question->getId(),
                    'question' => $question->getQuestion(),
                    'options' => $question->getOptions(),
                    'correct_answer' => $question->getCorrectAnswer(),
                    'explanation' => $question->getExplanation(),
                    'difficulty' => $question->getDifficulty(),
                    'topic' => $question->getTopic()
                ], $session->getQuestions()->toArray())
            ];

            return new JsonResponse($sessionData, 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/session/{id}', name: 'get_session', methods: ['GET'])]
    public function getSession(int $id): JsonResponse
    {
        $session = $this->qcmSessionRepository->find($id);

        if (!$session) {
            return new JsonResponse(['error' => 'Session not found'], 404);
        }

        $sessionData = [
            'id' => $session->getId(),
            'title' => $session->getTitle(),
            'difficulty' => $session->getDifficulty(),
            'questions_count' => $session->getQuestionsCount(),
            'total_questions' => $session->getTotalQuestions(),
            'status' => $session->getStatus(),
            'created_at' => $session->getCreatedAt()->format('Y-m-d H:i:s'),
            'questions' => []
        ];

        foreach ($session->getQuestions() as $question) {
            $sessionData['questions'][] = [
                'id' => $question->getId(),
                'question' => $question->getQuestion(),
                'options' => $question->getOptions(),
                'correct_answer' => $question->getCorrectAnswer(),
                'explanation' => $question->getExplanation(),
                'difficulty' => $question->getDifficulty(),
                'topic' => $question->getTopic(),
                'user_answers' => []
            ];
        }

        return new JsonResponse($sessionData);
    }

    #[Route('/session/{id}/answer', name: 'submit_answer', methods: ['POST'])]
    public function submitAnswer(int $id, Request $request): JsonResponse
    {
        $session = $this->qcmSessionRepository->find($id);
        
        if (!$session) {
            return new JsonResponse(['error' => 'Session not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $questionId = $data['questionId'] ?? null;
        $selectedAnswer = $data['answer'] ?? null;
        $timeSpent = $data['timeSpent'] ?? null;
        $userId = $data['userId'] ?? null;

        if (!$questionId || !$selectedAnswer) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }

        $question = $this->qcmQuestionRepository->find($questionId);
        
        if (!$question || $question->getSession() !== $session) {
            return new JsonResponse(['error' => 'Question not found'], 404);
        }

        $answer = new QCMAnswer();
        $answer->setSelectedAnswer($selectedAnswer);
        $answer->setCorrect($question->isAnswerCorrect($selectedAnswer));
        $answer->setTimeSpent($timeSpent);
        $answer->setUserId($userId);
        $answer->setQuestion($question);

        $this->entityManager->persist($answer);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'is_correct' => $answer->isCorrect(),
            'correct_answer' => $question->getCorrectAnswer(),
            'explanation' => $question->getExplanation()
        ]);
    }

    #[Route('/session/{id}/complete', name: 'complete_session', methods: ['POST'])]
    public function completeSession(int $id): JsonResponse
    {
        $session = $this->qcmSessionRepository->find($id);
        
        if (!$session) {
            return new JsonResponse(['error' => 'Session not found'], 404);
        }

        $totalQuestions = $session->getQuestions()->count();
        $correctAnswers = 0;

        foreach ($session->getQuestions() as $question) {
            foreach ($question->getUserAnswers() as $answer) {
                if ($answer->isCorrect()) {
                    $correctAnswers++;
                }
            }
        }

        $session->setScore($correctAnswers);
        $session->setTotalQuestions($totalQuestions);
        $session->setCompletedAt(new \DateTime());
        $session->setStatus('completed');

        $this->entityManager->flush();

        $percentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;

        $sessionData = [
            'id' => $session->getId(),
            'title' => $session->getTitle(),
            'score' => $session->getScore(),
            'total_questions' => $session->getTotalQuestions(),
            'status' => $session->getStatus(),
            'completed_at' => $session->getCompletedAt() ? $session->getCompletedAt()->format('Y-m-d H:i:s') : null,
            'created_at' => $session->getCreatedAt()->format('Y-m-d H:i:s')
        ];

        return new JsonResponse([
            'success' => true,
            'score' => $correctAnswers,
            'total' => $totalQuestions,
            'percentage' => $percentage,
            'session' => $sessionData
        ]);
    }

    #[Route('/sessions', name: 'list_sessions', methods: ['GET'])]
    public function listSessions(Request $request): JsonResponse
    {
        $userId = $request->query->get('userId');
        $limit = $request->query->get('limit', 10);
        
        $sessions = $this->qcmSessionRepository->findBy(
            $userId ? ['userId' => $userId] : [],
            ['createdAt' => 'DESC'],
            $limit
        );

        $sessionsData = [];
        foreach ($sessions as $session) {
            $sessionsData[] = [
                'id' => $session->getId(),
                'title' => $session->getTitle(),
                'score' => $session->getScore(),
                'total_questions' => $session->getTotalQuestions(),
                'difficulty' => $session->getDifficulty(),
                'category_id' => $session->getCategoryId(),
                'status' => $session->getStatus(),
                'completed_at' => $session->getCompletedAt() ? $session->getCompletedAt()->format('Y-m-d H:i:s') : null,
                'created_at' => $session->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }
        
        return new JsonResponse($sessionsData);
    }

}