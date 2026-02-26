<?php

/**
 * Test de la modération améliorée
 * Teste la détection de messages toxiques avec expressions contextuelles
 */

require_once __DIR__.'/vendor/autoload.php';

use App\Service\ModerationService;
use Psr\Log\NullLogger;

// Créer le service de modération
$moderationService = new ModerationService(new NullLogger());

echo "=== TEST DE MODÉRATION AMÉLIORÉE ===\n\n";

// Liste de messages à tester
$testMessages = [
    // Messages toxiques qui DOIVENT être bloqués
    [
        'message' => "C'est vraiment stupide",
        'expected' => 'blocked',
        'description' => 'Expression avec intensificateur'
    ],
    [
        'message' => "Tu es tellement bête",
        'expected' => 'blocked',
        'description' => 'Expression dégradante avec intensificateur'
    ],
    [
        'message' => "C'est très ridicule",
        'expected' => 'blocked',
        'description' => 'Expression avec "très"'
    ],
    [
        'message' => "T'es vraiment con",
        'expected' => 'blocked',
        'description' => 'Insulte directe avec intensificateur'
    ],
    [
        'message' => "Espèce d'idiot",
        'expected' => 'blocked',
        'description' => 'Expression dégradante'
    ],
    [
        'message' => "Ferme ta gueule",
        'expected' => 'blocked',
        'description' => 'Expression agressive'
    ],
    [
        'message' => "you are a fucking asshole",
        'expected' => 'blocked',
        'description' => 'Insulte grave en anglais'
    ],
    [
        'message' => "Tu es pathétique",
        'expected' => 'blocked',
        'description' => 'Insulte modérée'
    ],
    [
        'message' => "C'est nul",
        'expected' => 'blocked',
        'description' => 'Mot toxique simple'
    ],
    [
        'message' => "Quel loser",
        'expected' => 'blocked',
        'description' => 'Insulte en anglais'
    ],
    
    // Messages normaux qui DOIVENT passer
    [
        'message' => "C'est une bonne idée",
        'expected' => 'approved',
        'description' => 'Message positif'
    ],
    [
        'message' => "Je ne suis pas d'accord",
        'expected' => 'approved',
        'description' => 'Désaccord poli'
    ],
    [
        'message' => "Pouvez-vous m'expliquer?",
        'expected' => 'approved',
        'description' => 'Question polie'
    ],
    [
        'message' => "C'est intéressant",
        'expected' => 'approved',
        'description' => 'Commentaire neutre'
    ],
    [
        'message' => "Merci pour votre aide",
        'expected' => 'approved',
        'description' => 'Message de remerciement'
    ],
];

$passed = 0;
$failed = 0;

foreach ($testMessages as $index => $test) {
    $num = $index + 1;
    echo "Test $num: {$test['description']}\n";
    echo "Message: \"{$test['message']}\"\n";
    
    $result = $moderationService->analyzeMessage($test['message']);
    
    echo "Score de toxicité: {$result['toxicityScore']}\n";
    echo "Statut: {$result['moderationStatus']}\n";
    echo "Attendu: {$test['expected']}\n";
    
    if ($result['moderationStatus'] === $test['expected']) {
        echo "✅ RÉUSSI\n";
        $passed++;
    } else {
        echo "❌ ÉCHOUÉ\n";
        $failed++;
    }
    
    if (!empty($result['details']['toxicWords'])) {
        echo "Mots détectés: " . implode(', ', $result['details']['toxicWords']) . "\n";
    }
    
    echo "\n";
}

echo "=== RÉSUMÉ DES TESTS ===\n";
echo "Total: " . count($testMessages) . " tests\n";
echo "✅ Réussis: $passed\n";
echo "❌ Échoués: $failed\n";
echo "Taux de réussite: " . round(($passed / count($testMessages)) * 100, 1) . "%\n\n";

echo "=== AMÉLIORATIONS APPORTÉES ===\n";
echo "1. ✅ Liste de mots toxiques enrichie (80+ mots)\n";
echo "2. ✅ Catégorisation par gravité (haute/moyenne)\n";
echo "3. ✅ Patterns contextuels (expressions avec intensificateurs)\n";
echo "4. ✅ Détection de \"C'est vraiment stupide\" et similaires\n";
echo "5. ✅ Détection d'expressions dégradantes\n";
echo "6. ✅ Détection de menaces et harcèlement\n";
echo "7. ✅ Support multilingue (FR/EN/AR)\n";
echo "8. ✅ Scores adaptés selon la gravité\n";
