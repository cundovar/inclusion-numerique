<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class QCMGeneratorService
{
    public function __construct(
        private OpenAIService $openAIService,
        private HttpClientInterface $httpClient,
        private string $mainAppBaseUrl
    ) {}

    public function generateQuestionsFromDatabase(array $options = []): array
    {
        $categoryId = $options['categoryId'] ?? null;
        $difficulty = $options['difficulty'] ?? 'medium';
        $questionsCount = $options['questionsCount'] ?? 10;
        $userId = $options['userId'] ?? null;

        try {
            // Récupérer le contenu de la base de données principale
            $content = $this->fetchContentFromMainDatabase($categoryId);
            
            
            if (empty($content)) {
                throw new \Exception('Aucun contenu trouvé pour générer le QCM');
            }

            // Préparer le contenu pour l'IA
            $combinedContent = $this->prepareContentForAI($content);

            // Générer les questions avec l'IA
            $questions = $this->openAIService->generateQCMQuestions($combinedContent, [
                'questionsCount' => $questionsCount,
                'difficulty' => $difficulty,
                'topic' => $this->getCategoryName($categoryId) ?? 'Programmation'
            ]);

            // Enrichir les questions avec des métadonnées
            return $this->enrichQuestionsWithMetadata($questions, $content, $categoryId);

        } catch (\Exception $e) {
            // En cas d'erreur, utiliser des questions de fallback
            error_log('Erreur génération QCM: ' . $e->getMessage());
            return $this->getFallbackQuestions($questionsCount, $difficulty, $categoryId);
        }
    }

    public function analyzeContentForImprovement(int $pageContentId): array
    {
        try {
            // Récupérer le contenu spécifique
            $pageContent = $this->fetchSpecificPageContent($pageContentId);
            
            if (!$pageContent) {
                throw new \Exception('Contenu non trouvé');
            }

            // Analyser avec l'IA
            $analysis = $this->openAIService->analyzePedagogicalContent($pageContent['content']);

            // Ajouter des métadonnées
            $analysis['page_content_id'] = $pageContentId;
            $analysis['analyzed_at'] = new \DateTime();
            $analysis['content_title'] = $pageContent['title'];

            return $analysis;

        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de l\'analyse du contenu: ' . $e->getMessage());
        }
    }

    private function fetchContentFromMainDatabase(?int $categoryId): array
    {
        try {
            $url = $this->mainAppBaseUrl . '/api/page_contents';
            
            $params = ['pagination' => false];
            if ($categoryId) {
                $params['category'] = $categoryId;
            }

            $response = $this->httpClient->request('GET', $url, [
                'query' => $params,
                'headers' => ['Accept' => 'application/ld+json']
            ]);

            $data = $response->toArray();
            
            // Debug: afficher la structure des données
            error_log('API Response structure: ' . print_r(array_keys($data), true));
            
            // L'API Platform retourne les données dans 'hydra:member'
            if (isset($data['hydra:member'])) {
                error_log('Found hydra:member with ' . count($data['hydra:member']) . ' items');
                return $data['hydra:member'];
            }
            
            // Si pas de hydra:member, c'est probablement un tableau direct
            if (isset($data['member'])) {
                return $data['member'];
            }
            
            // Dernier recours - retourner toutes les données si c'est un tableau d'objets
            return $data ?? [];

        } catch (\Exception $e) {
            // Log l'erreur et retourner un tableau vide
            error_log('Erreur récupération contenu: ' . $e->getMessage());
            return [];
        }
    }

    private function fetchSpecificPageContent(int $pageContentId): ?array
    {
        try {
            $url = $this->mainAppBaseUrl . '/api/page_contents/' . $pageContentId;
            
            $response = $this->httpClient->request('GET', $url, [
                'headers' => ['Accept' => 'application/ld+json']
            ]);

            return $response->toArray();

        } catch (\Exception $e) {
            return null;
        }
    }

    private function prepareContentForAI(array $contentItems): string
    {
        $combinedContent = '';
        $maxLength = 8000; // Limiter pour éviter de dépasser les tokens OpenAI
        
        foreach ($contentItems as $item) {
            if (strlen($combinedContent) > $maxLength) {
                break;
            }
            
            // Debug: vérifier le type de données
            if (!is_array($item)) {
                error_log('Content item in prepareContentForAI is not array: ' . gettype($item));
                continue;
            }
            
            $title = $item['title'] ?? '';
            $content = $item['content'] ?? '';
            $code = $item['code'] ?? '';
            
            $combinedContent .= "\n\n--- {$title} ---\n";
            
            if (!empty($content)) {
                $combinedContent .= strip_tags($content) . "\n";
            }
            
            if (!empty($code)) {
                $combinedContent .= "\nCode :\n{$code}\n";
            }
        }
        
        return trim($combinedContent);
    }

    private function enrichQuestionsWithMetadata(array $questions, array $contentItems, ?int $categoryId): array
    {
        foreach ($questions as &$question) {
            $question['page_content_id'] = $this->findRelevantPageContentId($question, $contentItems);
            $question['category_id'] = $categoryId;
            $question['generated_at'] = new \DateTime();
        }
        
        return $questions;
    }

    private function findRelevantPageContentId(array $question, array $contentItems): ?int
    {
        // Essayer de trouver le contenu le plus pertinent pour cette question
        $questionText = strtolower($question['question'] ?? '');
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($contentItems as $content) {
            // Debug: vérifier le type de données
            if (!is_array($content)) {
                error_log('Content item is not array: ' . gettype($content) . ' - ' . substr($content, 0, 100));
                continue;
            }
            
            $contentText = strtolower($content['title'] . ' ' . strip_tags($content['content'] ?? ''));
            
            // Calculer un score de pertinence simple
            $score = 0;
            $words = explode(' ', $questionText);
            
            foreach ($words as $word) {
                if (strlen($word) > 3 && strpos($contentText, $word) !== false) {
                    $score++;
                }
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $content['id'] ?? null;
            }
        }
        
        return $bestMatch;
    }

    private function getCategoryName(?int $categoryId): ?string
    {
        if (!$categoryId) {
            return null;
        }

        try {
            $url = $this->mainAppBaseUrl . '/api/categories/' . $categoryId;
            
            $response = $this->httpClient->request('GET', $url, [
                'headers' => ['Accept' => 'application/ld+json']
            ]);

            $data = $response->toArray();
            return $data['name'] ?? null;

        } catch (\Exception $e) {
            return null;
        }
    }

    private function getFallbackQuestions(int $count, string $difficulty, ?int $categoryId): array
    {
        // Questions de secours en cas d'échec de l'IA
        $baseQuestions = [
            [
                'question' => 'Quel est le principe fondamental du développement web moderne ?',
                'options' => [
                    'A' => 'La séparation des préoccupations',
                    'B' => 'L\'utilisation exclusive de JavaScript',
                    'C' => 'L\'évitement des frameworks',
                    'D' => 'La programmation procédurale'
                ],
                'correct_answer' => 'A',
                'explanation' => 'La séparation des préoccupations permet une meilleure organisation et maintenance du code.',
                'topic' => 'Développement Web',
                'difficulty' => $difficulty
            ],
            [
                'question' => 'Que signifie API dans le contexte du développement web ?',
                'options' => [
                    'A' => 'Application Programming Interface',
                    'B' => 'Advanced Programming Integration',
                    'C' => 'Automatic Program Installer',
                    'D' => 'Application Process Interpreter'
                ],
                'correct_answer' => 'A',
                'explanation' => 'API signifie Application Programming Interface, une interface qui permet aux applications de communiquer.',
                'topic' => 'Concepts Généraux',
                'difficulty' => $difficulty
            ],
            [
                'question' => 'Quel est l\'avantage principal des frameworks PHP comme Symfony ?',
                'options' => [
                    'A' => 'Ils ralentissent le développement',
                    'B' => 'Ils fournissent une structure et des outils prêts à l\'emploi',
                    'C' => 'Ils remplacent complètement PHP',
                    'D' => 'Ils sont uniquement pour les débutants'
                ],
                'correct_answer' => 'B',
                'explanation' => 'Les frameworks comme Symfony accélèrent le développement en fournissant des composants réutilisables.',
                'topic' => 'Frameworks PHP',
                'difficulty' => $difficulty
            ],
            [
                'question' => 'Quelle balise HTML définit un paragraphe ?',
                'options' => [
                    'A' => '<paragraph>',
                    'B' => '<p>',
                    'C' => '<para>',
                    'D' => '<text>'
                ],
                'correct_answer' => 'B',
                'explanation' => 'La balise <p> définit un paragraphe en HTML.',
                'topic' => 'HTML',
                'difficulty' => $difficulty
            ],
            [
                'question' => 'Comment applique-t-on du CSS à un élément via une classe ?',
                'options' => [
                    'A' => '#ma-classe { }',
                    'B' => '.ma-classe { }',
                    'C' => 'ma-classe { }',
                    'D' => '*ma-classe { }'
                ],
                'correct_answer' => 'B',
                'explanation' => 'Les sélecteurs de classe en CSS commencent par un point (.)',
                'topic' => 'CSS',
                'difficulty' => $difficulty
            ],
            [
                'question' => 'Quel est le rôle de JavaScript dans le développement web ?',
                'options' => [
                    'A' => 'Styliser les pages web',
                    'B' => 'Structurer le contenu',
                    'C' => 'Ajouter de l\'interactivité',
                    'D' => 'Gérer la base de données'
                ],
                'correct_answer' => 'C',
                'explanation' => 'JavaScript permet d\'ajouter de l\'interactivité aux pages web.',
                'topic' => 'JavaScript',
                'difficulty' => $difficulty
            ]
        ];

        // Dupliquer les questions pour atteindre le nombre demandé si nécessaire
        $fallbackQuestions = [];
        $baseCount = count($baseQuestions);
        
        for ($i = 0; $i < $count; $i++) {
            $question = $baseQuestions[$i % $baseCount];
            // Modifier légèrement les questions dupliquées
            if ($i >= $baseCount) {
                $question['question'] = '[Variation] ' . $question['question'];
            }
            $fallbackQuestions[] = $question;
        }

        return $fallbackQuestions;
    }
}