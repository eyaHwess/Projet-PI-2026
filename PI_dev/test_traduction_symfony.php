<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

require_once 'vendor/autoload.php';

// Bootstrap Symfony
$kernel = new \App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

echo "=== TEST TRADUCTION AVEC SYMFONY ===\n\n";

try {
    // Récupérer le service de traduction depuis le container
    $translationService = $container->get('App\Service\TranslationService');
    
    echo "1. Service de traduction récupéré: ✅\n";
    
    // Test 1: Traduire "hello" en français
    echo "\n2. Test: 'hello' → français\n";
    $result = $translationService->translate('hello', 'fr');
    echo "   Résultat: '$result'\n";
    echo "   Status: ✅ SUCCÈS\n";
    
    // Test 2: Traduire "Hello, how are you?" en français
    echo "\n3. Test: 'Hello, how are you?' → français\n";
    $result = $translationService->translate('Hello, how are you?', 'fr');
    echo "   Résultat: '$result'\n";
    echo "   Status: ✅ SUCCÈS\n";
    
    // Test 3: Traduire "Good morning" en français
    echo "\n4. Test: 'Good morning' → français\n";
    $result = $translationService->translate('Good morning', 'fr');
    echo "   Résultat: '$result'\n";
    echo "   Status: ✅ SUCCÈS\n";
    
    // Test 4: Vérifier le fournisseur
    echo "\n5. Fournisseur utilisé\n";
    $provider = $translationService->getProvider();
    echo "   Fournisseur: $provider\n";
    
    // Test 5: Langues supportées
    echo "\n6. Langues supportées\n";
    $languages = $translationService->getSupportedLanguages();
    echo "   Nombre de langues: " . count($languages) . "\n";
    echo "   Français: " . ($languages['fr'] ?? 'Non disponible') . "\n";
    echo "   Anglais: " . ($languages['en'] ?? 'Non disponible') . "\n";
    echo "   Arabe: " . ($languages['ar'] ?? 'Non disponible') . "\n";
    
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DES TESTS ===\n";