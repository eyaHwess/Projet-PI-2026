<?php

/**
 * EXEMPLES DE TESTS - Code PHP
 * 
 * Ce fichier contient des exemples de code pour tester
 * le système de modération de différentes manières
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Service\ModerationService;
use Psr\Log\NullLogger;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║          EXEMPLES DE TESTS - MODÉRATION INTELLIGENTE        ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// EXEMPLE 1: Test Simple d'un Message
// ============================================================================

echo "📝 EXEMPLE 1: Test Simple d'un Message\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$moderationService = new ModerationService(new NullLogger());

$message = "Bonjour tout le monde!";
$result = $moderationService->analyzeMessage($message);

echo "Message: \"{$message}\"\n";
echo "Résultat:\n";
echo "  - Toxique: " . ($result['isToxic'] ? '❌ OUI' : '✅ NON') . "\n";
echo "  - Spam: " . ($result['isSpam'] ? '❌ OUI' : '✅ NON') . "\n";
echo "  - Statut: {$result['moderationStatus']}\n";
echo "  - Score toxicité: " . number_format($result['toxicityScore'], 2) . "\n";
echo "  - Score spam: " . number_format($result['spamScore'], 2) . "\n\n";

// ============================================================================
// EXEMPLE 2: Tester Plusieurs Messages en Boucle
// ============================================================================

echo "📝 EXEMPLE 2: Tester Plusieurs Messages\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$messages = [
    "Bonjour!",
    "You are a fucking asshole",
    "Visitez https://spam.com",
    "Merci beaucoup 😊",
];

foreach ($messages as $index => $msg) {
    $result = $moderationService->analyzeMessage($msg);
    $num = $index + 1;
    
    echo "Test #{$num}: \"{$msg}\"\n";
    echo "  → Statut: {$result['moderationStatus']} ";
    
    if ($result['moderationStatus'] === 'approved') {
        echo "✅\n";
    } elseif ($result['moderationStatus'] === 'blocked') {
        echo "🔴\n";
    } else {
        echo "🟠\n";
    }
    
    echo "\n";
}

// ============================================================================
// EXEMPLE 3: Fonction de Test Réutilisable
// ============================================================================

echo "📝 EXEMPLE 3: Fonction de Test Réutilisable\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

function testMessage(ModerationService $service, string $message, string $expectedStatus): bool
{
    $result = $service->analyzeMessage($message);
    $success = ($result['moderationStatus'] === $expectedStatus);
    
    echo "Message: \"{$message}\"\n";
    echo "Attendu: {$expectedStatus} | Obtenu: {$result['moderationStatus']} ";
    echo $success ? "✅\n" : "❌\n";
    
    return $success;
}

// Utilisation
$tests = [
    ['message' => 'Bonjour!', 'expected' => 'approved'],
    ['message' => 'You are a fucking asshole', 'expected' => 'blocked'],
    ['message' => 'Visitez https://spam.com', 'expected' => 'hidden'],
];

$passed = 0;
foreach ($tests as $test) {
    if (testMessage($moderationService, $test['message'], $test['expected'])) {
        $passed++;
    }
}

echo "\nRésultat: {$passed}/" . count($tests) . " tests réussis\n\n";

// ============================================================================
// EXEMPLE 4: Test avec Assertion (Style PHPUnit)
// ============================================================================

echo "📝 EXEMPLE 4: Test avec Assertion\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

function assertEquals($expected, $actual, $message = '')
{
    if ($expected === $actual) {
        echo "✅ PASS: {$message}\n";
        return true;
    } else {
        echo "❌ FAIL: {$message}\n";
        echo "   Attendu: {$expected}\n";
        echo "   Obtenu: {$actual}\n";
        return false;
    }
}

function assertTrue($condition, $message = '')
{
    return assertEquals(true, $condition, $message);
}

function assertFalse($condition, $message = '')
{
    return assertEquals(false, $condition, $message);
}

// Tests
$result = $moderationService->analyzeMessage("Bonjour!");
assertEquals('approved', $result['moderationStatus'], 'Message normal doit être approuvé');
assertFalse($result['isToxic'], 'Message normal ne doit pas être toxique');
assertFalse($result['isSpam'], 'Message normal ne doit pas être spam');

echo "\n";

$result = $moderationService->analyzeMessage("You are a fucking asshole");
assertEquals('blocked', $result['moderationStatus'], 'Message toxique doit être bloqué');
assertTrue($result['isToxic'], 'Message avec insultes doit être toxique');

echo "\n";

// ============================================================================
// EXEMPLE 5: Test de Spam Utilisateur
// ============================================================================

echo "📝 EXEMPLE 5: Test de Spam Utilisateur\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$recentMessages = ['Bonjour', 'Bonjour', 'Bonjour'];
$newMessage = 'Bonjour';

$isSpamming = $moderationService->checkUserSpamming($recentMessages, $newMessage);

echo "Messages récents: [" . implode(', ', $recentMessages) . "]\n";
echo "Nouveau message: \"{$newMessage}\"\n";
echo "Spam détecté: " . ($isSpamming ? "❌ OUI" : "✅ NON") . "\n\n";

// ============================================================================
// EXEMPLE 6: Statistiques sur un Lot de Messages
// ============================================================================

echo "📝 EXEMPLE 6: Statistiques sur un Lot de Messages\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$testBatch = [
    "Bonjour!",
    "Comment allez-vous?",
    "Merci beaucoup",
    "You are a fucking asshole",
    "Espèce de connard",
    "Visitez https://spam.com",
    "https://site1.com https://site2.com https://site3.com",
    "aaaaaaaaaa",
];

$stats = [
    'total' => count($testBatch),
    'approved' => 0,
    'blocked' => 0,
    'hidden' => 0,
    'avgToxicity' => 0,
    'avgSpam' => 0,
];

$totalToxicity = 0;
$totalSpam = 0;

foreach ($testBatch as $msg) {
    $result = $moderationService->analyzeMessage($msg);
    
    switch ($result['moderationStatus']) {
        case 'approved':
            $stats['approved']++;
            break;
        case 'blocked':
            $stats['blocked']++;
            break;
        case 'hidden':
            $stats['hidden']++;
            break;
    }
    
    $totalToxicity += $result['toxicityScore'];
    $totalSpam += $result['spamScore'];
}

$stats['avgToxicity'] = $totalToxicity / $stats['total'];
$stats['avgSpam'] = $totalSpam / $stats['total'];

echo "Statistiques:\n";
echo "  Total messages: {$stats['total']}\n";
echo "  ✅ Approuvés: {$stats['approved']}\n";
echo "  🔴 Bloqués (toxiques): {$stats['blocked']}\n";
echo "  🟠 Masqués (spam): {$stats['hidden']}\n";
echo "  📊 Score toxicité moyen: " . number_format($stats['avgToxicity'], 2) . "\n";
echo "  📊 Score spam moyen: " . number_format($stats['avgSpam'], 2) . "\n\n";

// ============================================================================
// EXEMPLE 7: Test de Performance
// ============================================================================

echo "📝 EXEMPLE 7: Test de Performance\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$iterations = 100;
$message = "Bonjour tout le monde!";

$startTime = microtime(true);

for ($i = 0; $i < $iterations; $i++) {
    $moderationService->analyzeMessage($message);
}

$endTime = microtime(true);
$duration = $endTime - $startTime;
$avgTime = ($duration / $iterations) * 1000; // en millisecondes

echo "Nombre d'analyses: {$iterations}\n";
echo "Temps total: " . number_format($duration, 3) . " secondes\n";
echo "Temps moyen par analyse: " . number_format($avgTime, 2) . " ms\n";
echo "Analyses par seconde: " . number_format($iterations / $duration, 0) . "\n\n";

// ============================================================================
// EXEMPLE 8: Test avec Différentes Langues
// ============================================================================

echo "📝 EXEMPLE 8: Test Multi-Langues\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$multiLangMessages = [
    ['lang' => 'FR', 'msg' => 'Espèce de connard'],
    ['lang' => 'EN', 'msg' => 'You are a fucking asshole'],
    ['lang' => 'AR', 'msg' => 'أنت كلب'],
];

foreach ($multiLangMessages as $test) {
    $result = $moderationService->analyzeMessage($test['msg']);
    
    echo "[{$test['lang']}] \"{$test['msg']}\"\n";
    echo "  → Toxique: " . ($result['isToxic'] ? '❌ OUI' : '✅ NON');
    echo " (score: " . number_format($result['toxicityScore'], 2) . ")\n\n";
}

// ============================================================================
// EXEMPLE 9: Générer un Rapport HTML
// ============================================================================

echo "📝 EXEMPLE 9: Générer un Rapport HTML\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$html = "<!DOCTYPE html>
<html>
<head>
    <title>Rapport de Modération</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        .approved { background-color: #d4edda; }
        .blocked { background-color: #f8d7da; }
        .hidden { background-color: #fff3cd; }
    </style>
</head>
<body>
    <h1>Rapport de Modération</h1>
    <table>
        <tr>
            <th>Message</th>
            <th>Statut</th>
            <th>Toxicité</th>
            <th>Spam</th>
        </tr>";

foreach ($testBatch as $msg) {
    $result = $moderationService->analyzeMessage($msg);
    $class = $result['moderationStatus'];
    
    $html .= "
        <tr class='{$class}'>
            <td>" . htmlspecialchars($msg) . "</td>
            <td>{$result['moderationStatus']}</td>
            <td>" . number_format($result['toxicityScore'], 2) . "</td>
            <td>" . number_format($result['spamScore'], 2) . "</td>
        </tr>";
}

$html .= "
    </table>
</body>
</html>";

file_put_contents('rapport_moderation.html', $html);
echo "✅ Rapport HTML généré: rapport_moderation.html\n";
echo "   Ouvrez ce fichier dans votre navigateur pour voir les résultats\n\n";

// ============================================================================
// EXEMPLE 10: Test avec Données JSON
// ============================================================================

echo "📝 EXEMPLE 10: Export JSON des Résultats\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$jsonResults = [];

foreach ($testBatch as $msg) {
    $result = $moderationService->analyzeMessage($msg);
    
    $jsonResults[] = [
        'message' => $msg,
        'status' => $result['moderationStatus'],
        'isToxic' => $result['isToxic'],
        'isSpam' => $result['isSpam'],
        'toxicityScore' => round($result['toxicityScore'], 2),
        'spamScore' => round($result['spamScore'], 2),
        'reason' => $result['moderationReason'],
    ];
}

$json = json_encode($jsonResults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents('resultats_moderation.json', $json);

echo "✅ Résultats exportés en JSON: resultats_moderation.json\n";
echo "   Extrait:\n";
echo substr($json, 0, 300) . "...\n\n";

// ============================================================================
// FIN
// ============================================================================

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    FIN DES EXEMPLES                          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "📁 Fichiers générés:\n";
echo "  - rapport_moderation.html\n";
echo "  - resultats_moderation.json\n\n";

echo "💡 Pour utiliser ces exemples dans votre code:\n";
echo "  1. Copiez les fonctions dont vous avez besoin\n";
echo "  2. Adaptez-les à votre contexte\n";
echo "  3. Intégrez-les dans vos tests PHPUnit\n\n";
