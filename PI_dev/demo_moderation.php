<?php

/**
 * Script de Démonstration - Modération Intelligente
 * 
 * Ce script teste rapidement le service de modération sans passer par Symfony
 * Usage: php demo_moderation.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Service\ModerationService;
use Psr\Log\NullLogger;

// Créer le service avec un logger null
$moderationService = new ModerationService(new NullLogger());

// Messages de test
$testMessages = [
    // Messages normaux
    ['content' => 'Bonjour tout le monde!', 'expected' => 'approved', 'type' => 'Normal'],
    ['content' => 'Comment allez-vous aujourd\'hui?', 'expected' => 'approved', 'type' => 'Normal'],
    ['content' => 'Merci pour votre aide 😊', 'expected' => 'approved', 'type' => 'Normal avec émoji'],
    
    // Messages toxiques
    ['content' => 'Tu es un idiot', 'expected' => 'blocked', 'type' => 'Toxique (FR)'],
    ['content' => 'Espèce de con', 'expected' => 'blocked', 'type' => 'Toxique (FR)'],
    ['content' => 'You are a fucking asshole', 'expected' => 'blocked', 'type' => 'Toxique (EN)'],
    ['content' => 'أنت كلب', 'expected' => 'blocked', 'type' => 'Toxique (AR)'],
    
    // Messages spam
    ['content' => 'Visitez https://www.spam.com maintenant!', 'expected' => 'hidden', 'type' => 'Spam (URL)'],
    ['content' => 'aaaaaaaaaa', 'expected' => 'hidden', 'type' => 'Spam (répétition)'],
    ['content' => 'ok', 'expected' => 'hidden', 'type' => 'Spam (trop court)'],
    ['content' => 'Click here to win!', 'expected' => 'hidden', 'type' => 'Spam (mots-clés)'],
];

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║         DÉMONSTRATION - MODÉRATION INTELLIGENTE                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$totalTests = count($testMessages);
$passed = 0;
$failed = 0;

foreach ($testMessages as $index => $test) {
    $num = $index + 1;
    $content = $test['content'];
    $expected = $test['expected'];
    $type = $test['type'];
    
    // Analyser le message
    $result = $moderationService->analyzeMessage($content);
    
    // Vérifier le résultat
    $status = $result['moderationStatus'];
    $success = ($status === $expected);
    
    if ($success) {
        $passed++;
        $icon = '✅';
        $color = "\033[32m"; // Vert
    } else {
        $failed++;
        $icon = '❌';
        $color = "\033[31m"; // Rouge
    }
    
    $reset = "\033[0m";
    
    echo "Test #{$num} - {$type}\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    echo "Message: \"{$content}\"\n";
    echo "Attendu: {$expected} | Obtenu: {$color}{$status}{$reset} {$icon}\n";
    
    if ($result['isToxic']) {
        echo "🔴 Toxique (score: " . number_format($result['toxicityScore'], 2) . ")\n";
        if (!empty($result['details']['toxicWords'])) {
            echo "   Mots détectés: " . implode(', ', $result['details']['toxicWords']) . "\n";
        }
    }
    
    if ($result['isSpam']) {
        echo "🟠 Spam (score: " . number_format($result['spamScore'], 2) . ")\n";
        if (!empty($result['details']['spamPatterns'])) {
            echo "   Patterns détectés: " . count($result['details']['spamPatterns']) . "\n";
        }
    }
    
    if ($result['moderationReason']) {
        echo "💬 Raison: {$result['moderationReason']}\n";
    }
    
    echo "\n";
}

// Résumé
echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║                          RÉSUMÉ                                    ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "Total de tests: {$totalTests}\n";
echo "\033[32m✅ Réussis: {$passed}\033[0m\n";
echo "\033[31m❌ Échoués: {$failed}\033[0m\n";
echo "Taux de réussite: " . number_format(($passed / $totalTests) * 100, 1) . "%\n";
echo "\n";

// Test de spam utilisateur
echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║                  TEST SPAM UTILISATEUR                             ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$recentMessages = ['Bonjour', 'Bonjour', 'Bonjour'];
$newMessage = 'Bonjour';
$isSpamming = $moderationService->checkUserSpamming($recentMessages, $newMessage);

echo "Messages récents: " . implode(', ', $recentMessages) . "\n";
echo "Nouveau message: {$newMessage}\n";
echo "Résultat: " . ($isSpamming ? "\033[31m❌ SPAM DÉTECTÉ\033[0m" : "\033[32m✅ OK\033[0m") . "\n";
echo "\n";

// Statistiques
echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║                       STATISTIQUES                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$toxicCount = 0;
$spamCount = 0;
$approvedCount = 0;

foreach ($testMessages as $test) {
    $result = $moderationService->analyzeMessage($test['content']);
    if ($result['moderationStatus'] === 'blocked') $toxicCount++;
    if ($result['moderationStatus'] === 'hidden') $spamCount++;
    if ($result['moderationStatus'] === 'approved') $approvedCount++;
}

echo "Messages approuvés: {$approvedCount}\n";
echo "Messages toxiques bloqués: {$toxicCount}\n";
echo "Messages spam masqués: {$spamCount}\n";
echo "\n";

echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║                    FIN DE LA DÉMONSTRATION                         ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Code de sortie
exit($failed > 0 ? 1 : 0);
