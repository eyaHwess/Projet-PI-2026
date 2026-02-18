<?php

namespace App\Service;

use App\Entity\User;

class CoachRecommendationService
{
    /**
     * Analyse le message de l'utilisateur et recommande les coaches les plus adaptés
     * 
     * @param string $userMessage Le message/objectif de l'utilisateur
     * @param array $coaches Liste de tous les coaches disponibles
     * @return array Les 3 coaches les plus recommandés avec leur score
     */
    public function recommendCoaches(string $userMessage, array $coaches): array
    {
        if (empty($coaches)) {
            return [];
        }

        $messageLower = mb_strtolower($userMessage);
        $recommendations = [];

        foreach ($coaches as $coach) {
            $score = $this->calculateCompatibilityScore($messageLower, $coach);
            $recommendations[] = [
                'coach' => $coach,
                'score' => $score,
                'reasons' => $this->getMatchReasons($messageLower, $coach)
            ];
        }

        // Trier par score décroissant
        usort($recommendations, fn($a, $b) => $b['score'] <=> $a['score']);

        // Retourner les 3 meilleurs
        return array_slice($recommendations, 0, 3);
    }

    /**
     * Calcule un score de compatibilité entre le message et le coach
     */
    private function calculateCompatibilityScore(string $messageLower, User $coach): float
    {
        $score = 0.0;

        // Mots-clés par spécialité (pondération: 40 points)
        $specialityKeywords = [
            'yoga' => ['yoga', 'méditation', 'relaxation', 'souplesse', 'zen', 'stress', 'calme'],
            'musculation' => ['muscle', 'force', 'poids', 'haltère', 'bodybuilding', 'masse', 'gonflette', 'salle'],
            'cardio' => ['cardio', 'course', 'running', 'endurance', 'vélo', 'natation', 'marathon'],
            'nutrition' => ['nutrition', 'alimentation', 'régime', 'manger', 'poids', 'maigrir', 'grossir', 'diète'],
            'crossfit' => ['crossfit', 'hiit', 'intense', 'wod', 'fonctionnel', 'explosif'],
            'pilates' => ['pilates', 'posture', 'core', 'gainage', 'dos', 'colonne'],
            'boxe' => ['boxe', 'combat', 'frappe', 'ring', 'punch', 'uppercut'],
            'danse' => ['danse', 'chorégraphie', 'rythme', 'zumba', 'mouvement'],
        ];

        $coachSpeciality = mb_strtolower($coach->getSpeciality() ?? '');
        
        foreach ($specialityKeywords as $speciality => $keywords) {
            if (str_contains($coachSpeciality, $speciality)) {
                foreach ($keywords as $keyword) {
                    if (str_contains($messageLower, $keyword)) {
                        $score += 40;
                        break 2; // Sortir des deux boucles
                    }
                }
            }
        }

        // Objectifs généraux (pondération: 20 points)
        $goalKeywords = [
            'perte de poids' => ['maigrir', 'perdre', 'poids', 'mincir', 'affiner'],
            'prise de masse' => ['muscle', 'masse', 'grossir', 'prendre', 'volume'],
            'remise en forme' => ['forme', 'santé', 'bien-être', 'condition', 'fitness'],
            'performance' => ['performance', 'compétition', 'améliorer', 'progresser', 'record'],
            'rééducation' => ['rééducation', 'blessure', 'récupération', 'douleur', 'kiné'],
        ];

        foreach ($goalKeywords as $goal => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($messageLower, $keyword)) {
                    $score += 20;
                    break 2;
                }
            }
        }

        // Note du coach (pondération: 20 points max)
        $rating = $coach->getRating() ?? 0;
        $score += ($rating / 5) * 20;

        // Prix attractif (pondération: 10 points max)
        $price = $coach->getPricePerSession() ?? 100;
        if ($price <= 30) {
            $score += 10;
        } elseif ($price <= 50) {
            $score += 7;
        } elseif ($price <= 70) {
            $score += 4;
        }

        // Disponibilité mentionnée (pondération: 10 points)
        $availability = mb_strtolower($coach->getAvailability() ?? '');
        $timeKeywords = ['matin', 'soir', 'midi', 'week-end', 'semaine', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
        
        foreach ($timeKeywords as $timeKeyword) {
            if (str_contains($messageLower, $timeKeyword) && str_contains($availability, $timeKeyword)) {
                $score += 10;
                break;
            }
        }

        return $score;
    }

    /**
     * Génère les raisons du match entre le message et le coach
     */
    private function getMatchReasons(string $messageLower, User $coach): array
    {
        $reasons = [];

        // Vérifier la spécialité
        $specialityKeywords = [
            'yoga' => ['yoga', 'méditation', 'relaxation', 'souplesse'],
            'musculation' => ['muscle', 'force', 'poids', 'haltère'],
            'cardio' => ['cardio', 'course', 'running', 'endurance'],
            'nutrition' => ['nutrition', 'alimentation', 'régime', 'manger'],
            'crossfit' => ['crossfit', 'hiit', 'intense'],
            'pilates' => ['pilates', 'posture', 'gainage'],
            'boxe' => ['boxe', 'combat', 'frappe'],
            'danse' => ['danse', 'chorégraphie', 'zumba'],
        ];

        $coachSpeciality = mb_strtolower($coach->getSpeciality() ?? '');
        
        foreach ($specialityKeywords as $speciality => $keywords) {
            if (str_contains($coachSpeciality, $speciality)) {
                foreach ($keywords as $keyword) {
                    if (str_contains($messageLower, $keyword)) {
                        $reasons[] = 'Spécialiste en ' . $coach->getSpeciality();
                        break 2;
                    }
                }
            }
        }

        // Vérifier la note
        $rating = $coach->getRating() ?? 0;
        if ($rating >= 4.5) {
            $reasons[] = 'Excellente note (' . $rating . '/5)';
        } elseif ($rating >= 4) {
            $reasons[] = 'Très bien noté (' . $rating . '/5)';
        }

        // Vérifier le prix
        $price = $coach->getPricePerSession() ?? 0;
        if ($price > 0 && $price <= 40) {
            $reasons[] = 'Prix attractif (' . $price . '€)';
        }

        // Vérifier la disponibilité
        $availability = mb_strtolower($coach->getAvailability() ?? '');
        $timeKeywords = ['matin', 'soir', 'week-end', 'semaine'];
        
        foreach ($timeKeywords as $timeKeyword) {
            if (str_contains($messageLower, $timeKeyword) && str_contains($availability, $timeKeyword)) {
                $reasons[] = 'Disponible ' . $timeKeyword;
                break;
            }
        }

        // Si pas de raisons spécifiques, ajouter des raisons génériques
        if (empty($reasons)) {
            if ($coach->getSpeciality()) {
                $reasons[] = 'Expert en ' . $coach->getSpeciality();
            }
            if ($rating > 0) {
                $reasons[] = 'Note de ' . $rating . '/5';
            }
        }

        return $reasons;
    }
}
