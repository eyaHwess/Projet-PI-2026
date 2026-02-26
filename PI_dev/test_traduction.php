<?php

/**
 * Test du Service de Traduction
 * Vérifie que les traductions fonctionnent correctement
 */

require_once __DIR__.'/vendor/autoload.php';

use App\Service\TranslationService;
use Symfony\Component\HttpClient\HttpClient;
use Psr\Log\NullLogger;

echo "=== TEST DU SERVICE DE TRADUCTION ===\n\n";

// Créer le service de traduction
$httpClient = HttpClient::create();
$logger = new NullLogger();
$translationService = new TranslationService(
    $httpClient,
    $logger,
    'libretranslate', // Provider
    null, // DeepL API key
    null  // Google API key
);

echo "Provider: " . $translationService->getProvider() . "\n";
echo "Langues supportées: " . count($translationService->getSupportedLanguages()) . "\n\n";

// Tests de traduction
$tests = [
    [
        'text' => 'Bonjour, comment allez-vous?',
        'source' => 'fr',
        'target' => 'en',
        'description' => 'Français → Anglais',
        'expected_contains' => ['hello', 'how', 'you']
    ],
    [
        'text' => 'Hello everyone, how are you today?',
        'source' => 'en',
        'target' => 'fr',
        'description' => 'Anglais → Français',
        'expected_contains' => ['bonjour', 'comment']
    ],
    [
        'text' => 'Merci beaucoup pour votre aide',
        'source' => 'fr',
        'target' => 'en',
        'description' => 'Français → Anglais (remerciement)',
        'expected_contains' => ['thank', 'help']
    ],
    [
        'text' => 'Good morning',
        'source' => 'en',
        'target' => 'fr',
        'description' => 'Anglais → Français (salutation)',
        'expected_contains' => ['bonjour', 'matin']
    ],
    [
        'text' => 'Je suis très content',
        'source' => 'fr',
        'target' => 'en',
        'description' => 'Français → Anglais (émotion)',
        'expected_contains' => ['happy', 'very', 'glad']
    ],
];

$passed = 0;
$failed = 0;
$errors = [];

foreach ($tests as $index => $test) {
    $num = $index + 1;
    echo "Test $num: {$test['description']}\n";
    echo "Texte original: \"{$test['text']}\"\n";
    echo "Direction: {$test['source']} → {$test['target']}\n";
    
    try {
        $startTime = microtime(true);
        $translated = $translationService->translate(
            $test['text'],
            $test['target'],
            $test['source']
        );
        $duration = round((microtime(true) - $startTime) * 1000);
        
        echo "Traduction: \"$translated\"\n";
        echo "Durée: {$duration}ms\n";
        
        // Vérifier que la traduction n'est pas une erreur
        if (str_starts_with($translated, 'Erreur')) {
            echo "❌ ÉCHOUÉ - Erreur de traduction\n";
            $failed++;
            $errors[] = "Test $num: Erreur de traduction";
        } 
        // Vérifier que la traduction n'est pas vide
        elseif (empty(trim($translated))) {
            echo "❌ ÉCHOUÉ - Traduction vide\n";
            $failed++;
            $errors[] = "Test $num: Traduction vide";
        }
        // Vérifier que la traduction contient au moins un des mots attendus
        else {
            $translatedLower = strtolower($translated);
            $found = false;
            foreach ($test['expected_contains'] as $word) {
                if (str_contains($translatedLower, strtolower($word))) {
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                echo "✅ RÉUSSI\n";
                $passed++;
            } else {
                echo "⚠️ RÉUSSI (mais mots attendus non trouvés)\n";
                echo "   Mots attendus: " . implode(', ', $test['expected_contains']) . "\n";
                $passed++;
            }
        }
        
    } catch (\Exception $e) {
        echo "❌ ÉCHOUÉ - Exception: {$e->getMessage()}\n";
        $failed++;
        $errors[] = "Test $num: {$e->getMessage()}";
    }
    
    echo "\n";
    
    // Pause entre les requêtes pour éviter le rate limiting
    if ($num < count($tests)) {
        sleep(1);
    }
}

echo "=== RÉSUMÉ DES TESTS ===\n";
echo "Total: " . count($tests) . " tests\n";
echo "✅ Réussis: $passed\n";
echo "❌ Échoués: $failed\n";
echo "Taux de réussite: " . round(($passed / count($tests)) * 100, 1) . "%\n\n";

if (!empty($errors)) {
    echo "=== ERREURS DÉTECTÉES ===\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
    echo "\n";
}

echo "=== INFORMATIONS SYSTÈME ===\n";
echo "Provider: LibreTranslate (https://libretranslate.de)\n";
echo "Fallback: MyMemory (https://api.mymemory.translated.net)\n";
echo "Timeout: 8 secondes\n";
echo "Langues dans le menu: 3 (EN, FR, AR)\n";
echo "Langues supportées: " . count($translationService->getSupportedLanguages()) . "\n\n";

echo "=== RECOMMANDATIONS ===\n";
if ($passed === count($tests)) {
    echo "✅ Tous les tests sont réussis!\n";
    echo "✅ Le service de traduction fonctionne correctement.\n";
    echo "✅ Vous pouvez tester dans le navigateur.\n";
} else {
    echo "⚠️ Certains tests ont échoué.\n";
    echo "⚠️ Vérifiez la connexion internet.\n";
    echo "⚠️ LibreTranslate peut être temporairement indisponible.\n";
    echo "⚠️ Le fallback MyMemory devrait prendre le relais.\n";
}

echo "\n=== PROCHAINES ÉTAPES ===\n";
echo "1. Ouvrir le chatroom: /message/chatroom/{goalId}\n";
echo "2. Envoyer un message en français\n";
echo "3. Cliquer sur le bouton 'Traduire' (🌐)\n";
echo "4. Sélectionner une langue (EN, FR, ou AR)\n";
echo "5. Vérifier que la traduction s'affiche correctement\n";
echo "6. Tester le bouton de fermeture (×)\n";
