<?php

namespace App\Service;

use App\Entity\Reclamation;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AIResponseService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $openaiApiKey
    ) {
    }

    /**
     * Generate AI-suggested response for a reclamation
     */
    public function generateSuggestedResponse(Reclamation $reclamation): string
    {
        try {
            $prompt = $this->buildPrompt($reclamation);
            
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openaiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a professional customer support agent for Buildify, a coaching platform. Generate polite, empathetic, and professional responses to user complaints in French. Keep responses concise (2-3 sentences).'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 200
                ],
                'timeout' => 10
            ]);

            $data = $response->toArray();
            
            if (isset($data['choices'][0]['message']['content'])) {
                return trim($data['choices'][0]['message']['content']);
            }
            
            return $this->getFallbackResponse($reclamation);
            
        } catch (\Exception $e) {
            // Log error and return fallback
            error_log('AI Response Generation Error: ' . $e->getMessage());
            return $this->getFallbackResponse($reclamation);
        }
    }

    /**
     * Build the prompt for AI based on reclamation details
     */
    private function buildPrompt(Reclamation $reclamation): string
    {
        $type = $reclamation->getType()->value;
        $content = $reclamation->getContent();
        $userName = $reclamation->getUser()->getFirstName();

        return "Type de réclamation: {$type}\n\n" .
               "Message de l'utilisateur ({$userName}): {$content}\n\n" .
               "Générez une réponse professionnelle et empathique pour résoudre cette réclamation.";
    }

    /**
     * Get fallback response when AI is unavailable
     */
    private function getFallbackResponse(Reclamation $reclamation): string
    {
        $type = $reclamation->getType()->value;
        
        return match($type) {
            'Bug' => "Nous vous remercions d'avoir signalé ce problème technique. Notre équipe examine la situation et travaille activement à sa résolution. Nous vous tiendrons informé des développements.",
            'Coaching' => "Nous sommes désolés pour cette expérience avec votre coach. Nous allons enquêter immédiatement sur cette situation et prendre les mesures nécessaires pour garantir la qualité de nos services.",
            'Payment' => "Nous comprenons votre préoccupation concernant le paiement. Notre équipe financière va examiner votre dossier en priorité et vous contacter dans les plus brefs délais pour résoudre cette situation.",
            'Other' => "Nous avons bien reçu votre message et nous vous remercions de nous avoir contactés. Notre équipe examine votre demande et vous répondra dans les meilleurs délais.",
            default => "Merci pour votre retour. Nous avons bien pris en compte votre demande et notre équipe vous répondra rapidement."
        };
    }
}
