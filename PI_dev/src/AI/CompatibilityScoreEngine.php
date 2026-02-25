<?php

namespace App\AI;

use App\Entity\User;

class CompatibilityScoreEngine
{
    private const POINTS_PER_MATCHING_CATEGORY = 10;
    private const BONUS_STRESS_MENTAL = 3;
    private const BONUS_URGENCE = 1;

    /**
     * Calcule le score de compatibilité entre un coach et les catégories/émotion extraites du message.
     */
    public function calculate(User $coach, array $categories, ?string $emotion): int
    {
        $score = 0;
        $coachCategories = $this->resolveCoachCategories($coach);

        if (empty($categories)) {
            return 0;
        }

        foreach ($categories as $category) {
            if (\in_array($category, $coachCategories, true)) {
                $score += self::POINTS_PER_MATCHING_CATEGORY;
            }
        }

        if ($emotion === 'stress' && \in_array('mental', $coachCategories, true)) {
            $score += self::BONUS_STRESS_MENTAL;
        }

        if ($emotion === 'urgence') {
            $score += self::BONUS_URGENCE;
        }

        return $score;
    }

    /**
     * Retourne les catégories du coach (fitness, nutrition, mental).
     */
    public function getCoachCategories(User $coach): array
    {
        return $this->resolveCoachCategories($coach);
    }

    /**
     * Récupère les catégories du coach (fitness, nutrition, mental).
     * Utilise specialities si présent, sinon dérive de speciality.
     */
    private function resolveCoachCategories(User $coach): array
    {
        $specialities = $coach->getSpecialities();

        if (!empty($specialities)) {
            return array_map('strtolower', $specialities);
        }

        return $this->deriveCategoriesFromSpeciality($coach->getSpeciality());
    }

    private function deriveCategoriesFromSpeciality(?string $speciality): array
    {
        if (empty($speciality)) {
            return [];
        }

        $s = strtolower($speciality);
        $categories = [];

        $fitnessKeywords = ['fitness', 'musculation', 'cardio', 'crossfit', 'sport', 'remise en forme', 'perte de poids', 'prise de masse'];
        $nutritionKeywords = ['nutrition', 'alimentation', 'régime', 'diète', 'alimentaire'];
        $mentalKeywords = ['mental', 'yoga', 'méditation', 'relaxation', 'stress', 'bien-être', 'développement personnel', 'coaching'];

        foreach ($fitnessKeywords as $kw) {
            if (str_contains($s, $kw)) {
                $categories[] = 'fitness';
                break;
            }
        }
        foreach ($nutritionKeywords as $kw) {
            if (str_contains($s, $kw)) {
                $categories[] = 'nutrition';
                break;
            }
        }
        foreach ($mentalKeywords as $kw) {
            if (str_contains($s, $kw)) {
                $categories[] = 'mental';
                break;
            }
        }

        return array_unique($categories);
    }
}
