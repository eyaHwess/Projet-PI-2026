<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class ModerationService
{
    // Seuils de détection
    private const TOXICITY_THRESHOLD = 0.5;  // Abaissé de 0.6 à 0.5 pour bloquer plus de messages
    private const SPAM_THRESHOLD = 0.5;      // Maintenu à 0.5

    // Mots toxiques - Liste enrichie et catégorisée
    private const TOXIC_WORDS = [
        // Insultes directes (score élevé: 0.5)
        'insulte', 'idiot', 'con', 'connard', 'salaud', 'merde', 'putain',
        'imbécile', 'crétin', 'débile', 'abruti', 'enculé', 'connasse',
        'salope', 'pute', 'ordure', 'déchet', 'raclure', 'fumier',
        
        // Insultes modérées (score moyen: 0.4)
        'stupide', 'bête', 'nul', 'pourri', 'minable', 'pathétique',
        'ridicule', 'lamentable', 'pitoyable', 'médiocre', 'incompétent',
        'incapable', 'inutile', 'loser', 'raté', 'naze',
        
        // Mots agressifs (score moyen: 0.4)
        'ferme ta gueule', 'ta gueule', 'dégage', 'casse-toi', 'va te faire',
        'chier', 'foutre', 'bordel', 'merdique', 'dégueulasse',
        
        // Anglais - Insultes directes (score élevé: 0.5)
        'fuck', 'fucking', 'fucker', 'motherfucker', 'bitch', 'asshole',
        'bastard', 'cunt', 'dick', 'pussy', 'shit', 'bullshit',
        'damn', 'dumbass', 'jackass', 'moron', 'retard',
        
        // Anglais - Insultes modérées (score moyen: 0.4)
        'stupid', 'dumb', 'idiot', 'fool', 'loser', 'pathetic',
        'ridiculous', 'lame', 'suck', 'sucks', 'useless', 'worthless',
        
        // Arabe (score élevé: 0.5)
        'كلب', 'حمار', 'غبي', 'أحمق', 'حقير', 'وسخ',
        
        // Expressions toxiques communes
        'va mourir', 'crève', 'suicide', 'tue-toi', 'kill yourself',
        'go die', 'kys', 'neck yourself',
    ];

    // Patterns toxiques (expressions contextuelles)
    private const TOXIC_PATTERNS = [
        // Expressions avec "vraiment" ou "tellement" (intensificateurs)
        '/\b(vraiment|tellement|très|super|hyper)\s+(stupide|bête|con|nul|débile|idiot|pathétique|ridicule)\b/i',
        '/\b(c\'est|t\'es|vous êtes|tu es)\s+(vraiment|tellement|très)?\s*(stupide|bête|con|nul|débile|idiot|pathétique|ridicule)\b/i',
        
        // Expressions dégradantes
        '/\b(espèce de|sale|putain de|foutu|fucking)\s+\w+\b/i',
        '/\b(tu|vous|t\'|vous)\s+(me|nous)\s+(fais|faites)\s+chier\b/i',
        '/\b(va|allez)\s+(te|vous)\s+faire\s+(foutre|enculer)\b/i',
        
        // Menaces
        '/\b(je vais|on va|je te|je vais te)\s+(tuer|buter|défoncer|péter|casser)\b/i',
        '/\b(ferme|fermez)\s+(ta|votre)\s+(gueule|bouche)\b/i',
        
        // Harcèlement
        '/\b(personne|nobody|no one)\s+(t\'|te|vous)\s+(aime|like|want)\b/i',
        '/\b(tu|you)\s+(devrais|should)\s+(mourir|die|disparaître)\b/i',
    ];

    // Patterns de spam
    private const SPAM_PATTERNS = [
        '/https?:\/\/[^\s]+/i', // URLs
        '/www\.[^\s]+/i', // WWW
        '/\b(viagra|casino|lottery|winner|prize|click here|buy now)\b/i', // Mots-clés spam
        '/(.)\1{4,}/', // Caractères répétés (aaaaa)
        '/\b(\w+)\s+\1\b/i', // Mots répétés
    ];

    public function __construct(
        private LoggerInterface $logger
    ) {}

    /**
     * Analyse un message et retourne les résultats de modération
     */
    public function analyzeMessage(string $content): array
    {
        $toxicityResult = $this->detectToxicity($content);
        $spamResult = $this->detectSpam($content);

        $isToxic = $toxicityResult['score'] >= self::TOXICITY_THRESHOLD;
        $isSpam = $spamResult['score'] >= self::SPAM_THRESHOLD;

        // Déterminer le statut de modération
        $status = 'approved';
        $reason = null;

        if ($isToxic) {
            $status = 'blocked';
            $reason = 'Ce message viole les règles de la communauté';
        } elseif ($isSpam) {
            $status = 'hidden';
            $reason = 'Ce message est considéré comme spam';
        }

        return [
            'isToxic' => $isToxic,
            'isSpam' => $isSpam,
            'toxicityScore' => $toxicityResult['score'],
            'spamScore' => $spamResult['score'],
            'moderationStatus' => $status,
            'moderationReason' => $reason,
            'details' => [
                'toxicWords' => $toxicityResult['words'],
                'spamPatterns' => $spamResult['patterns'],
            ]
        ];
    }

    /**
     * Détecte la toxicité dans un message
     */
    private function detectToxicity(string $content): array
    {
        $originalContent = $content;
        $content = strtolower($content);
        $foundWords = [];
        $score = 0.0;

        // 1. Vérifier les patterns toxiques (expressions contextuelles)
        foreach (self::TOXIC_PATTERNS as $pattern) {
            if (preg_match($pattern, $originalContent)) {
                $foundWords[] = 'PATTERN_TOXIQUE';
                $score += 0.5; // Score élevé pour les patterns contextuels
            }
        }

        // 2. Vérifier les mots toxiques individuels
        // Catégoriser les mots par niveau de gravité
        $highSeverityWords = [
            'fuck', 'fucking', 'fucker', 'motherfucker', 'bitch', 'asshole',
            'bastard', 'cunt', 'dick', 'pussy', 'connard', 'enculé', 'connasse',
            'salope', 'pute', 'ordure', 'déchet', 'raclure', 'fumier',
            'va mourir', 'crève', 'suicide', 'tue-toi', 'kill yourself',
        ];

        $mediumSeverityWords = [
            'stupide', 'bête', 'nul', 'pourri', 'minable', 'pathétique',
            'ridicule', 'lamentable', 'pitoyable', 'idiot', 'crétin', 'débile',
            'stupid', 'dumb', 'fool', 'loser', 'pathetic', 'ridiculous',
        ];

        foreach (self::TOXIC_WORDS as $word) {
            $word = strtolower($word);
            if (str_contains($content, $word)) {
                $foundWords[] = $word;
                
                // Score selon la gravité
                if (in_array($word, $highSeverityWords)) {
                    $score += 0.5; // Insultes graves
                } elseif (in_array($word, $mediumSeverityWords)) {
                    $score += 0.4; // Insultes modérées
                } else {
                    $score += 0.3; // Autres mots toxiques
                }
            }
        }

        // 3. Vérifier les majuscules excessives (CRIER)
        $upperCount = preg_match_all('/[A-ZÀ-Ÿ]/', $originalContent);
        $totalChars = strlen(preg_replace('/[^a-zA-ZÀ-ÿ]/', '', $originalContent));
        if ($totalChars > 10 && $totalChars > 0 && $upperCount / $totalChars > 0.6) {
            $score += 0.3;
            $foundWords[] = 'MAJUSCULES_EXCESSIVES';
        }

        // 4. Vérifier les points d'exclamation excessifs
        $exclamationCount = substr_count($originalContent, '!');
        if ($exclamationCount > 3) {
            $score += 0.2;
            $foundWords[] = 'EXCLAMATIONS_EXCESSIVES';
        }

        // 5. Détecter les répétitions de caractères agressifs (!!!! ou ????)
        if (preg_match('/[!?]{4,}/', $originalContent)) {
            $score += 0.2;
            $foundWords[] = 'PONCTUATION_AGGRESSIVE';
        }

        // Limiter le score à 1.0
        $score = min(1.0, $score);

        return [
            'score' => $score,
            'words' => $foundWords
        ];
    }

    /**
     * Détecte le spam dans un message
     */
    private function detectSpam(string $content): array
    {
        $foundPatterns = [];
        $score = 0.0;

        // Vérifier les patterns de spam
        foreach (self::SPAM_PATTERNS as $pattern) {
            if (preg_match($pattern, $content)) {
                $foundPatterns[] = $pattern;
                $score += 0.4; // Augmenté de 0.3 à 0.4
            }
        }

        // Message trop court répété (moins de 5 caractères)
        if (strlen(trim($content)) < 5 && strlen(trim($content)) > 0) {
            $score += 0.3; // Augmenté de 0.2 à 0.3
            $foundPatterns[] = 'MESSAGE_TROP_COURT';
        }

        // Message entièrement en majuscules
        $cleanContent = preg_replace('/[^a-zA-ZÀ-ÿ]/', '', $content);
        if (strlen($cleanContent) > 10 && $cleanContent === strtoupper($cleanContent)) {
            $score += 0.3; // Augmenté de 0.2 à 0.3
            $foundPatterns[] = 'TOUT_EN_MAJUSCULES';
        }

        // Trop de liens
        $linkCount = preg_match_all('/https?:\/\//', $content);
        if ($linkCount > 2) {
            $score += 0.4; // Augmenté de 0.3 à 0.4
            $foundPatterns[] = 'TROP_DE_LIENS';
        }

        // Limiter le score à 1.0
        $score = min(1.0, $score);

        return [
            'score' => $score,
            'patterns' => $foundPatterns
        ];
    }

    /**
     * Vérifie si un utilisateur spam (messages répétitifs)
     */
    public function checkUserSpamming(array $recentMessages, string $newMessage): bool
    {
        if (count($recentMessages) < 3) {
            return false;
        }

        // Vérifier si les 3 derniers messages sont identiques
        $lastThree = array_slice($recentMessages, -3);
        $identical = true;
        foreach ($lastThree as $msg) {
            if ($msg !== $newMessage) {
                $identical = false;
                break;
            }
        }

        if ($identical) {
            $this->logger->warning('Spam détecté: messages identiques répétés');
            return true;
        }

        // Vérifier si trop de messages en peu de temps (plus de 5 en 10 secondes)
        if (count($recentMessages) > 5) {
            $this->logger->warning('Spam détecté: trop de messages rapides');
            return true;
        }

        return false;
    }

    /**
     * Retourne un message d'avertissement pour l'utilisateur
     */
    public function getModerationMessage(string $status, ?string $reason = null): string
    {
        return match($status) {
            'blocked' => $reason ?? '⚠️ Ce message viole les règles de la communauté',
            'hidden' => $reason ?? '🚫 Ce message est considéré comme spam',
            'pending' => '⏳ Ce message est en attente de modération',
            default => ''
        };
    }
}
