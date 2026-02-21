<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiAssistantService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private bool $useMockData;

    public function __construct(HttpClientInterface $client, string $apiKey, bool $useMockData = false)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->useMockData = $useMockData;
    }

    public function generateSuggestion(array $userData): ?string
    {
        // Use mock data if enabled
        if ($this->useMockData) {
            return $this->generateMockSuggestion($userData);
        }

        $prompt = "You are an expert productivity strategist.

Analyze the user statistics and detect performance weaknesses.

User Statistics:
- Total Goals: {$userData['total_goals']}
- Completed Goals: {$userData['completed_goals']}
- Overdue Goals: {$userData['overdue_goals']}
- Completion Rate: {$userData['completion_rate']}%

Generate 5 different goal suggestions that:
- Improve weak areas
- Increase completion consistency
- Reduce overdue goals
- Are realistic and achievable

Each suggestion must:
- Follow SMART principles (Specific, Measurable, Achievable, Relevant, Time-bound)
- Be practical and motivating
- Not repeat similar ideas

Return ONLY valid JSON array with:
[{\"title\": \"...\",\"description\": \"...\",\"priority\": \"low | medium | high\",\"duration_days\": integer}]

No extra text.
No markdown.
Strict JSON only.";

        try {
            $response = $this->client->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert productivity strategist. You MUST respond with valid JSON only. No markdown, no explanations, just pure JSON array.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 800
                ],
                'timeout' => 30
            ]);

            $data = $response->toArray();
            return $data['choices'][0]['message']['content'] ?? null;

        } catch (\Symfony\Contracts\HttpClient\Exception\ClientException $e) {
            // Handle 4xx errors - return null to indicate failure
            // The controller will handle showing appropriate messages
            return null;

        } catch (\Symfony\Contracts\HttpClient\Exception\TransportException $e) {
            // Network errors - return null
            return null;

        } catch (\Exception $e) {
            // Generic fallback - return null
            return null;
        }
    }

    private function generateMockSuggestion(array $userData): string
    {
        $suggestions = [
            [
                "title" => "Complete Overdue Goal First",
                "description" => "Focus on finishing your {$userData['overdue_goals']} overdue goal(s) within the next 7 days to improve completion consistency",
                "priority" => "high",
                "duration_days" => 7
            ],
            [
                "title" => "Daily 15-Minute Goal Review",
                "description" => "Establish a morning routine to review and prioritize your active goals each day",
                "priority" => "high",
                "duration_days" => 30
            ],
            [
                "title" => "Break Large Goals into Milestones",
                "description" => "Divide your remaining goals into smaller, achievable weekly milestones to boost completion rate from {$userData['completion_rate']}%",
                "priority" => "medium",
                "duration_days" => 14
            ],
            [
                "title" => "Weekly Progress Check-in",
                "description" => "Schedule a 30-minute session every Sunday to assess progress and adjust timelines",
                "priority" => "medium",
                "duration_days" => 90
            ],
            [
                "title" => "Celebrate Small Wins",
                "description" => "Create a reward system for completing milestones to maintain motivation and momentum",
                "priority" => "low",
                "duration_days" => 60
            ]
        ];

        return json_encode($suggestions, JSON_PRETTY_PRINT);
    }
}