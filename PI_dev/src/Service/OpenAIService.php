<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class OpenAIService
{
    private const MODEL = 'gpt-4o-mini';
    private const TEMPERATURE = 0.2;
    private const TIMEOUT = 15;
    private const MAX_MESSAGE_LENGTH = 1000;
    private const ALLOWED_CATEGORIES = ['fitness', 'nutrition', 'mental'];
    private const ALLOWED_EMOTIONS = ['stress', 'urgence', 'motivation'];

    public function __construct(
        private HttpClientInterface $httpClient,
        #[\Symfony\Component\DependencyInjection\Attribute\Autowire(param: 'openAiApiKey')]
        private string $apiKey,
    ) {
    }

    /**
     * Analyse le message utilisateur et retourne catégories + émotion.
     *
     * @return array{categories: string[], emotion: string|null}
     */
    public function analyzeMessage(string $message): array
    {
        $message = $this->sanitizeMessage($message);

        if (empty($message) || empty($this->apiKey)) {
            return $this->fallbackResponse();
        }

        try {
            $response = $this->httpClient->request(
                'POST',
                'https://api.openai.com/v1/chat/completions',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => self::MODEL,
                        'temperature' => self::TEMPERATURE,
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'Tu es un assistant qui analyse des messages. Réponds UNIQUEMENT avec un JSON valide, sans texte avant ou après.',
                            ],
                            [
                                'role' => 'user',
                                'content' => $this->buildPrompt($message),
                            ],
                        ],
                        'response_format' => ['type' => 'json_object'],
                    ],
                    'timeout' => self::TIMEOUT,
                ]
            );

            $data = $response->toArray();
            $content = $data['choices'][0]['message']['content'] ?? '';

            return $this->parseResponse($content);
        } catch (ExceptionInterface $e) {
            return $this->fallbackResponse();
        } catch (\Throwable $e) {
            return $this->fallbackResponse();
        }
    }

    private function sanitizeMessage(string $message): string
    {
        $message = trim($message);
        if (\strlen($message) > self::MAX_MESSAGE_LENGTH) {
            $message = substr($message, 0, self::MAX_MESSAGE_LENGTH);
        }
        return $message;
    }

    private function buildPrompt(string $message): string
    {
        $categoriesStr = implode('", "', self::ALLOWED_CATEGORIES);
        $emotionsStr = implode('" | "', self::ALLOWED_EMOTIONS);

        return <<<PROMPT
Analyse le message utilisateur et retourne STRICTEMENT un JSON valide avec cette structure :
{
  "categories": ["fitness", "nutrition", "mental"],
  "emotion": "stress" | "urgence" | "motivation" | null
}
Les catégories autorisées sont uniquement : {$categoriesStr}.
L'émotion autorisée est : {$emotionsStr} ou null.
Ne retourne rien d'autre que du JSON valide.
Message : {$message}
PROMPT;
    }

    private function parseResponse(string $content): array
    {
        $content = trim($content);

        // Nettoyer un éventuel markdown
        if (str_starts_with($content, '```json')) {
            $content = substr($content, 7);
        }
        if (str_starts_with($content, '```')) {
            $content = substr($content, 3);
        }
        if (str_ends_with($content, '```')) {
            $content = substr($content, 0, -3);
        }
        $content = trim($content);

        $decoded = json_decode($content, true);
        if (!\is_array($decoded)) {
            return $this->fallbackResponse();
        }

        $categories = $decoded['categories'] ?? [];
        if (!\is_array($categories)) {
            $categories = [];
        }
        $categories = array_values(array_filter(
            array_map('strtolower', $categories),
            fn($c) => \in_array($c, self::ALLOWED_CATEGORIES, true)
        ));

        $emotion = $decoded['emotion'] ?? null;
        if ($emotion !== null && !\in_array(strtolower((string) $emotion), self::ALLOWED_EMOTIONS, true)) {
            $emotion = null;
        }
        if ($emotion !== null) {
            $emotion = strtolower((string) $emotion);
        }

        return [
            'categories' => array_unique($categories),
            'emotion' => $emotion,
        ];
    }

    private function fallbackResponse(): array
    {
        return [
            'categories' => [],
            'emotion' => null,
        ];
    }
}
