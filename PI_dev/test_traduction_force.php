<?php

require_once 'vendor/autoload.php';

use App\Service\TranslationService;

// Créer une instance du service de traduction
$translationService = new TranslationService();

echo "=== TEST FORCÉ DE TRADUCTION ===\n\n";

// Test 1: Traduire "hello" en français
echo "1. Test: 'hello' → français\n";
try {
    $result = $translationService->translate('hello', 'fr');
    echo "   Résultat: '$result'\n";
    echo "   Status: ✅ SUCCÈS\n";
} catch (Exception $e) {
    echo "   Erreur: " . $e->getMessage() . "\n";
    echo "   Status: ❌ ÉCHEC\n";
}

echo "\n";

// Test 2: Traduire "Hello, how are you?" en français
echo "2. Test: 'Hello, how are you?' → français\n";
try {
    $result = $translationService->translate('Hello, how are you?', 'fr');
    echo "   Résultat: '$result'\n";
    echo "   Status: ✅ SUCCÈS\n";
} catch (Exception $e) {
    echo "   Erreur: " . $e->getMessage() . "\n";
    echo "   Status: ❌ ÉCHEC\n";
}

echo "\n";

// Test 3: Traduire "Bonjour" en anglais
echo "3. Test: 'Bonjour' → anglais\n";
try {
    $result = $translationService->translate('Bonjour', 'en');
    echo "   Résultat: '$result'\n";
    echo "   Status: ✅ SUCCÈS\n";
} catch (Exception $e) {
    echo "   Erreur: " . $e->getMessage() . "\n";
    echo "   Status: ❌ ÉCHEC\n";
}

echo "\n";

// Test 4: Vérifier le fournisseur utilisé
echo "4. Fournisseur de traduction utilisé\n";
try {
    $provider = $translationService->getProvider();
    echo "   Fournisseur: $provider\n";
    echo "   Status: ✅ SUCCÈS\n";
} catch (Exception $e) {
    echo "   Erreur: " . $e->getMessage() . "\n";
    echo "   Status: ❌ ÉCHEC\n";
}

echo "\n";

// Test 5: Langues supportées
echo "5. Langues supportées\n";
try {
    $languages = $translationService->getSupportedLanguages();
    echo "   Nombre de langues: " . count($languages) . "\n";
    echo "   Français disponible: " . (isset($languages['fr']) ? '✅ OUI' : '❌ NON') . "\n";
    echo "   Anglais disponible: " . (isset($languages['en']) ? '✅ OUI' : '❌ NON') . "\n";
    echo "   Arabe disponible: " . (isset($languages['ar']) ? '✅ OUI' : '❌ NON') . "\n";
} catch (Exception $e) {
    echo "   Erreur: " . $e->getMessage() . "\n";
    echo "   Status: ❌ ÉCHEC\n";
}

echo "\n=== FIN DES TESTS ===\n";