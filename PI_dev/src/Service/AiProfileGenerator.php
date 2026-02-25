<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Generates a personal growth archetype profile from onboarding answers via OpenAI.
 */
class AiProfileGenerator
{
    private const OPENAI_URL = 'https://api.openai.com/v1/chat/completions';

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
        private bool $useMock = false,
    ) {
    }

    /**
     * @param array{goals?: string, challenges?: string, motivationStyle?: string, planningStyle?: string, interests?: string} $answers
     * @return array{archetypeName: string, description: string, strengths: array, growthAreas: array, habitSuggestions: array, shortBio: string}|null
     */
    public function generateProfile(array $answers): ?array
    {
        if ($this->useMock) {
            return $this->generateMockProfile($answers);
        }

        if ($this->apiKey === '') {
            return null;
        }

        $goals = $answers['goals'] ?? '';
        $challenges = $answers['challenges'] ?? '';
        $motivationStyle = $answers['motivationStyle'] ?? '';
        $planningStyle = $answers['planningStyle'] ?? '';
        $interests = $answers['interests'] ?? '';

        $userMessage = <<<PROMPT
Based on the following user answers, generate a personal growth archetype profile.

Goals: {$goals}
Challenges: {$challenges}
Motivation style: {$motivationStyle}
Planning style: {$planningStyle}
Interests: {$interests}

Return ONLY valid JSON with this structure (no markdown, no code block):

{
  "archetypeName": "string",
  "description": "string",
  "strengths": ["string", "string", "string"],
  "growthAreas": ["string", "string"],
  "habitSuggestions": ["string", "string", "string"],
  "shortBio": "string"
}

- archetypeName: a short, memorable name for this archetype (e.g. "The Structured Achiever")
- description: 2–3 sentences describing this profile
- strengths: exactly 3 strengths
- growthAreas: exactly 2 growth areas
- habitSuggestions: exactly 3 personalized habit suggestions
- shortBio: a short professional-style bio (2–3 sentences)
PROMPT;

        try {
            $response = $this->httpClient->request('POST', self::OPENAI_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a personal growth expert. You MUST respond with valid JSON only. No markdown, no explanations, no code fences. Output a single JSON object.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $userMessage,
                        ],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 800,
                ],
                'timeout' => 30,
            ]);

            $data = $response->toArray();
            $content = $data['choices'][0]['message']['content'] ?? null;

            if ($content === null || $content === '') {
                return null;
            }

            $content = trim($content);
            if (preg_match('/^```\w*\s*(.*?)```/s', $content, $m)) {
                $content = trim($m[1]);
            }

            $decoded = json_decode($content, true);
            if (!\is_array($decoded)) {
                return null;
            }

            return $this->normalizeProfile($decoded);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return array{archetypeName: string, description: string, strengths: array, growthAreas: array, habitSuggestions: array, shortBio: string}|null
     */
    private function normalizeProfile(array $data): ?array
    {
        $archetypeName = isset($data['archetypeName']) ? (string) $data['archetypeName'] : 'Explorer';
        $description = isset($data['description']) ? (string) $data['description'] : '';
        $strengths = \is_array($data['strengths'] ?? null) ? $data['strengths'] : [];
        $growthAreas = \is_array($data['growthAreas'] ?? null) ? $data['growthAreas'] : [];
        $habitSuggestions = \is_array($data['habitSuggestions'] ?? null) ? $data['habitSuggestions'] : [];
        $shortBio = isset($data['shortBio']) ? (string) $data['shortBio'] : '';

        return [
            'archetypeName' => $archetypeName,
            'description' => $description,
            'strengths' => array_values($strengths),
            'growthAreas' => array_values($growthAreas),
            'habitSuggestions' => array_values($habitSuggestions),
            'shortBio' => $shortBio,
        ];
    }

    /**
     * Profil simulé basé sur les réponses, utilisé quand OPENAI_USE_MOCK=true ou sans connexion.
     * @param array<string, string> $answers
     * @return array{archetypeName: string, description: string, strengths: array, growthAreas: array, habitSuggestions: array, shortBio: string}
     */
    private function generateMockProfile(array $answers): array
    {
        $motivation = $answers['motivationStyle'] ?? 'discipline';
        $planning = $answers['planningStyle'] ?? 'structured';
        $goals = $answers['goals'] ?? '';

        $archetypeMap = [
            'discipline' => 'The Disciplined Achiever',
            'inspiration' => 'The Inspired Visionary',
            'flexibility' => 'The Adaptive Explorer',
            'structure' => 'The Strategic Builder',
        ];
        $archetypeName = $archetypeMap[$motivation] ?? 'The Motivated Grower';

        return [
            'archetypeName' => $archetypeName,
            'description' => "You are someone who values {$motivation} as your core driver. "
                . "With a {$planning} approach to planning, you naturally build momentum by staying consistent with your commitments. "
                . "Your goals reflect a genuine desire for growth and self-improvement.",
            'strengths' => [
                ucfirst($motivation) . ' and self-awareness',
                'Clear sense of personal direction',
                'Ability to commit to long-term goals',
            ],
            'growthAreas' => [
                'Balancing ambition with rest and recovery',
                'Staying flexible when plans change unexpectedly',
            ],
            'habitSuggestions' => [
                'Start each morning with a 5-minute intention-setting ritual',
                'End each day with a brief review of what you accomplished',
                'Block 30 minutes weekly to reassess your goals and progress',
            ],
            'shortBio' => "A {$motivation}-driven individual committed to structured growth. "
                . ($goals ? "Currently focused on: " . mb_substr($goals, 0, 100) . "." : ''),
        ];
    }
}
