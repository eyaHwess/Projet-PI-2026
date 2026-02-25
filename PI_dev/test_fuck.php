<?php

/**
 * Test spécifique pour le mot "fuck"
 */

require_once __DIR__.'/vendor/autoload.php';

use App\Service\ModerationService;
use Psr\Log\NullLogger;

$moderationService = new ModerationService(new NullLogger());

echo "=== TEST DU MOT 'FUCK' ===\n\n";

$testMessages = [
    "fuck",
    "Fuck",
    "FUCK",
    "fuck you",
    "what the fuck",
    "fucking hell",
    "this is fucked",
    "fuck off",
    "go fuck yourself",
    "Hello everyone", // Message normal pour comparaison
];

foreach ($testMessages as $message) {
    echo "Message: \"$message\"\n";
    $result = $moderationService->analyzeMessage($message);
    echo "Score: {$result['toxicityScore']}\n";
    echo "Statut: {$result['moderationStatus']}\n";
    
    if ($result['moderationStatus'] === 'blocked') {
        echo "✅ BLOQUÉ\n";
    } else {
        echo "❌ PASSÉ (ERREUR!)\n";
    }
    
    if (!empty($result['details']['toxicWords'])) {
        echo "Mots détectés: " . implode(', ', $result['details']['toxicWords']) . "\n";
    }
    echo "\n";
}

echo "=== CONCLUSION ===\n";
echo "Le mot 'fuck' et ses variantes DOIVENT être bloqués.\n";
echo "Si un message avec 'fuck' passe, il y a un problème.\n";
