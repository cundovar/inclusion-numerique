<?php

namespace App\Controller;

use App\Service\QCMGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/content', name: 'content_')]
class ContentAnalyzeController extends AbstractController
{
    public function __construct(
        private QCMGeneratorService $qcmGeneratorService
    ) {}

    #[Route('/analyze/{id}', name: 'analyze', methods: ['POST'])]
    public function analyzeContent(int $id): JsonResponse
    {
        try {
            $analysis = $this->qcmGeneratorService->analyzeContentForImprovement($id);

            return new JsonResponse([
                'success' => true,
                'analysis' => $analysis
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/suggestions/batch', name: 'batch_analyze', methods: ['POST'])]
    public function batchAnalyzeContent(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $contentIds = $data['contentIds'] ?? [];

        if (empty($contentIds) || !is_array($contentIds)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Liste d\'IDs de contenu requise'
            ], 400);
        }

        $results = [];
        $errors = [];

        foreach ($contentIds as $contentId) {
            try {
                $analysis = $this->qcmGeneratorService->analyzeContentForImprovement($contentId);
                $results[$contentId] = $analysis;
            } catch (\Exception $e) {
                $errors[$contentId] = $e->getMessage();
            }
        }

        return new JsonResponse([
            'success' => true,
            'results' => $results,
            'errors' => $errors,
            'processed' => count($results),
            'failed' => count($errors)
        ]);
    }

    #[Route('/generate-improvement-suggestions', name: 'improvement_suggestions', methods: ['POST'])]
    public function generateImprovementSuggestions(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $contentId = $data['contentId'] ?? null;
        $focus = $data['focus'] ?? 'general'; // general, structure, examples, exercises

        if (!$contentId) {
            return new JsonResponse([
                'success' => false,
                'error' => 'ID de contenu requis'
            ], 400);
        }

        try {
            $analysis = $this->qcmGeneratorService->analyzeContentForImprovement($contentId);

            // Filtrer les suggestions selon le focus demandé
            $filteredSuggestions = [];
            if (isset($analysis['analysis']['suggestions'])) {
                foreach ($analysis['analysis']['suggestions'] as $suggestion) {
                    if ($focus === 'general' || ($suggestion['type'] ?? '') === $focus) {
                        $filteredSuggestions[] = $suggestion;
                    }
                }
            }

            return new JsonResponse([
                'success' => true,
                'content_id' => $contentId,
                'focus' => $focus,
                'suggestions' => $filteredSuggestions,
                'summary' => [
                    'total_suggestions' => count($filteredSuggestions),
                    'high_priority' => count(array_filter($filteredSuggestions, fn($s) => ($s['priority'] ?? '') === 'high')),
                    'difficulty_level' => $analysis['analysis']['difficulty_level'] ?? 'unknown',
                    'estimated_reading_time' => $analysis['analysis']['estimated_reading_time'] ?? 0
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/health', name: 'health', methods: ['GET'])]
    public function healthCheck(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'healthy',
            'service' => 'ia-service',
            'timestamp' => new \DateTime(),
            'version' => '1.0.0'
        ]);
    }
}