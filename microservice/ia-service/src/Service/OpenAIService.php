<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class OpenAIService
{
    private const API_URL = 'https://api.openai.com/v1/chat/completions';
    
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $openaiApiKey
    ) {}

    public function generateQCMQuestions(string $content, array $options = []): array
    {
        $questionsCount = $options['questionsCount'] ?? 10;
        $difficulty = $options['difficulty'] ?? 'medium';
        $topic = $options['topic'] ?? 'général';

        $prompt = $this->buildQCMPrompt($content, $questionsCount, $difficulty, $topic);

        try {
            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openaiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Tu es un expert pédagogique qui crée des QCM de haute qualité. Réponds UNIQUEMENT avec du JSON valide, sans texte supplémentaire.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => 2000,
                    'temperature' => 0.7,
                ]
            ]);

            $data = $response->toArray();
            
            if (!isset($data['choices'][0]['message']['content'])) {
                throw new \Exception('Format de réponse OpenAI invalide');
            }

            $aiResponse = trim($data['choices'][0]['message']['content']);
            
            // Nettoyer la réponse pour extraire le JSON
            $aiResponse = $this->cleanJsonResponse($aiResponse);
            
            $questions = json_decode($aiResponse, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON invalide dans la réponse de l\'IA: ' . json_last_error_msg());
            }

            return $this->validateAndFormatQuestions($questions);

        } catch (TransportExceptionInterface | ClientExceptionInterface | ServerExceptionInterface $e) {
            throw new \Exception('Erreur lors de l\'appel à OpenAI: ' . $e->getMessage());
        }
    }

    public function analyzePedagogicalContent(string $content): array
    {
        $prompt = "Analyse ce contenu pédagogique et propose des améliorations concrètes :

CONTENU À ANALYSER :
{$content}

Réponds UNIQUEMENT avec un JSON dans ce format exact :
{
    \"analysis\": {
        \"strengths\": [\"point fort 1\", \"point fort 2\"],
        \"weaknesses\": [\"faiblesse 1\", \"faiblesse 2\"],
        \"suggestions\": [
            {
                \"type\": \"structure|contenu|exemples|exercices\",
                \"title\": \"Titre de la suggestion\",
                \"description\": \"Description détaillée\",
                \"priority\": \"high|medium|low\"
            }
        ],
        \"difficulty_level\": \"beginner|intermediate|advanced\",
        \"estimated_reading_time\": 15,
        \"key_concepts\": [\"concept1\", \"concept2\"]
    }
}";

        try {
            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openaiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Tu es un expert pédagogique. Analyse le contenu et réponds UNIQUEMENT avec du JSON valide.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => 1500,
                    'temperature' => 0.5,
                ]
            ]);

            $data = $response->toArray();
            $aiResponse = trim($data['choices'][0]['message']['content']);
            $aiResponse = $this->cleanJsonResponse($aiResponse);
            
            $analysis = json_decode($aiResponse, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON invalide dans la réponse de l\'IA: ' . json_last_error_msg());
            }

            return $analysis;

        } catch (TransportExceptionInterface | ClientExceptionInterface | ServerExceptionInterface $e) {
            throw new \Exception('Erreur lors de l\'analyse du contenu: ' . $e->getMessage());
        }
    }

    private function buildQCMPrompt(string $content, int $questionsCount, string $difficulty, string $topic): string
    {
        return "Crée exactement {$questionsCount} questions QCM de niveau {$difficulty} sur le sujet '{$topic}' basées sur ce contenu :

CONTENU :
{$content}

INSTRUCTIONS :
- Questions claires et précises
- 4 options (A, B, C, D) avec UNE seule bonne réponse
- Explication pédagogique pour chaque réponse
- Niveau {$difficulty}

Réponds UNIQUEMENT avec ce JSON exact :
{
    \"questions\": [
        {
            \"question\": \"La question ici?\",
            \"options\": {
                \"A\": \"Option A\",
                \"B\": \"Option B\", 
                \"C\": \"Option C\",
                \"D\": \"Option D\"
            },
            \"correct_answer\": \"A\",
            \"explanation\": \"Explication de pourquoi A est correct\",
            \"topic\": \"Sujet spécifique\",
            \"difficulty\": \"{$difficulty}\"
        }
    ]
}";
    }

    private function cleanJsonResponse(string $response): string
    {
        // Supprimer les blocs de code markdown
        $response = preg_replace('/```json\s*/', '', $response);
        $response = preg_replace('/```\s*$/', '', $response);
        
        // Supprimer les espaces en début et fin
        $response = trim($response);
        
        // Trouver le premier { et le dernier }
        $firstBrace = strpos($response, '{');
        $lastBrace = strrpos($response, '}');
        
        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $response = substr($response, $firstBrace, $lastBrace - $firstBrace + 1);
        }
        
        return $response;
    }

    private function validateAndFormatQuestions(array $data): array
    {
        if (!isset($data['questions']) || !is_array($data['questions'])) {
            throw new \Exception('Format de questions invalide');
        }

        $formattedQuestions = [];
        
        foreach ($data['questions'] as $question) {
            if (!isset($question['question'], $question['options'], $question['correct_answer'])) {
                continue; // Skip invalid questions
            }

            $formattedQuestions[] = [
                'question' => $question['question'],
                'options' => $question['options'],
                'correct_answer' => $question['correct_answer'],
                'explanation' => $question['explanation'] ?? '',
                'topic' => $question['topic'] ?? 'Général',
                'difficulty' => $question['difficulty'] ?? 'medium'
            ];
        }

        if (empty($formattedQuestions)) {
            throw new \Exception('Aucune question valide générée');
        }

        return $formattedQuestions;
    }
}