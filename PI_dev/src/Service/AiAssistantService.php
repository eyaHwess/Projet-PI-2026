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

    public function generateSuggestion(array $userData, string $locale = 'en'): ?string
    {
        if ($this->useMockData) {
            return $this->generateMockSuggestion($userData);
        }

        $languageInstruction = match (substr($locale, 0, 2)) {
            'fr' => 'Write all titles and descriptions in French.',
            'ar' => 'Write all titles and descriptions in Modern Standard Arabic.',
            default => 'Write all titles and descriptions in English.',
        };

        $userGoalsList = '';
        if (!empty($userData['user_goals'])) {
            foreach ($userData['user_goals'] as $g) {
                $userGoalsList .= "- " . ($g['title'] ?? '') . ($g['description'] ? " — " . $g['description'] : "") . "\n";
            }
        } else {
            $userGoalsList = "(The user has no goals yet. Suggest 5 appealing, varied starter goals: e.g. fitness, learning, habits, health, productivity.)\n";
        }

        $prompt = "You are an expert goal coach. The user has the following current goals:

{$userGoalsList}

User stats: {$userData['total_goals']} total goals, {$userData['completed_goals']} completed, {$userData['overdue_goals']} overdue, {$userData['completion_rate']}% completion rate.

Generate exactly 5 NEW goal suggestions that the user could ADD to their list. Requirements:
- Each suggestion must be SIMILAR in theme/topic to the user's existing goals (same areas of interest: e.g. if they have fitness goals, suggest other fitness or health goals; if learning, suggest other learning goals).
- If the user has no goals, suggest 5 varied, concrete starter goals (fitness, learning, habits, etc.).
- Each must be a concrete, achievable GOAL (something to do or achieve), not a meta-tip like \"review your goals\".
- Follow SMART: Specific, Measurable, Achievable, Relevant, Time-bound.
- Do NOT copy or repeat the user's existing goals; suggest NEW goals in the same spirit.
- Include priority (low/medium/high) and duration_days (realistic number).

Language:
- {$languageInstruction}

Return ONLY a valid JSON array, no other text, no markdown:
[{\"title\": \"...\",\"description\": \"...\",\"priority\": \"low | medium | high\",\"duration_days\": integer}]";

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
                            'content' => 'You are an expert goal coach. Suggest new goals similar to the user\'s existing goals. You MUST respond with valid JSON only. No markdown, no explanations, just pure JSON array.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 800,
                ],
                'timeout' => 30,
            ]);

            $data = $response->toArray();
            return $data['choices'][0]['message']['content'] ?? null;
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientException) {
            return null;
        } catch (\Symfony\Contracts\HttpClient\Exception\TransportException) {
            return null;
        } catch (\Exception) {
            return null;
        }
    }

    private function generateMockSuggestion(array $userData): string
    {
        $userGoals = $userData['user_goals'] ?? [];
        $hasGoals = !empty($userGoals);

        // If user has goals, derive themes from first titles and suggest similar ones; otherwise generic starter goals.
        $suggestions = [];
        if ($hasGoals) {
            $themes = array_slice(array_map(fn ($g) => $g['title'] ?? '', $userGoals), 0, 3);
            $suggestions = [
                ['title' => 'Objectif complémentaire – même domaine', 'description' => 'Un objectif concret dans la même thématique que vos objectifs actuels (' . implode(', ', $themes) . '), à définir selon votre envie.', 'priority' => 'medium', 'duration_days' => 30],
                ['title' => 'Version courte d’un de vos objectifs', 'description' => 'Une version sur 2 semaines d’un objectif similaire à ceux que vous suivez déjà, pour renforcer la régularité.', 'priority' => 'high', 'duration_days' => 14],
                ['title' => 'Objectif lié – prochaine étape', 'description' => 'Une suite logique ou une compétence connexe par rapport à vos objectifs existants.', 'priority' => 'medium', 'duration_days' => 60],
                ['title' => 'Habitude quotidienne dans le même thème', 'description' => 'Une petite action quotidienne (5–15 min) en lien avec vos objectifs pour ancrer la discipline.', 'priority' => 'high', 'duration_days' => 21],
                ['title' => 'Objectif de consolidation', 'description' => 'Un objectif qui consolide ce que vous avez déjà commencé dans vos autres objectifs.', 'priority' => 'low', 'duration_days' => 45],
            ];
        } else {
            $suggestions = [
                ['title' => 'Marcher 30 minutes par jour', 'description' => 'Marcher au moins 30 minutes chaque jour pendant 3 semaines pour installer une habitude mouvement.', 'priority' => 'medium', 'duration_days' => 21],
                ['title' => 'Lire 1 livre par mois', 'description' => 'Choisir un livre et le terminer chaque mois pour développer la lecture régulière.', 'priority' => 'low', 'duration_days' => 30],
                ['title' => 'Faire 3 séances de sport par semaine', 'description' => 'Planifier 3 séances de sport ou d’exercice par semaine pendant 2 mois.', 'priority' => 'high', 'duration_days' => 60],
                ['title' => 'Apprendre 10 mots d’une langue par jour', 'description' => 'Réviser ou apprendre 10 nouveaux mots chaque jour pendant 6 semaines.', 'priority' => 'medium', 'duration_days' => 42],
                ['title' => 'Dormir avant 23h pendant 2 semaines', 'description' => 'Se coucher avant 23h tous les soirs pendant 14 jours pour améliorer le sommeil.', 'priority' => 'medium', 'duration_days' => 14],
            ];
        }

        return json_encode($suggestions, JSON_PRETTY_PRINT);
    }
}
