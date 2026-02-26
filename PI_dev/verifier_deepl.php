#!/usr/bin/env php
<?php

/**
 * Script de vérification de la configuration DeepL
 * Usage: php verifier_deepl.php
 */

echo "🔍 Vérification de la configuration DeepL\n";
echo str_repeat("=", 50) . "\n\n";

// Charger le fichier .env
$envFile = __DIR__ . '/.env';

if (!file_exists($envFile)) {
    echo "❌ ERREUR: Fichier .env introuvable\n";
    exit(1);
}

$envContent = file_get_contents($envFile);
$lines = explode("\n", $envContent);

$provider = null;
$deeplKey = null;

foreach ($lines as $line) {
    $line = trim($line);
    
    // Ignorer les commentaires et lignes vides
    if (empty($line) || str_starts_with($line, '#')) {
        continue;
    }
    
    if (str_starts_with($line, 'TRANSLATION_PROVIDER=')) {
        $provider = trim(str_replace('TRANSLATION_PROVIDER=', '', $line));
    }
    
    if (str_starts_with($line, 'DEEPL_API_KEY=')) {
        $deeplKey = trim(str_replace('DEEPL_API_KEY=', '', $line));
    }
}

echo "📋 Configuration actuelle\n";
echo str_repeat("-", 50) . "\n";

// Vérifier le provider
echo "Provider: ";
if ($provider === 'deepl') {
    echo "✅ deepl\n";
} else {
    echo "❌ $provider (devrait être 'deepl')\n";
}

// Vérifier la clé API
echo "Clé API DeepL: ";
if (empty($deeplKey) || $deeplKey === 'votre_cle_deepl_ici') {
    echo "❌ Non configurée (placeholder détecté)\n";
} else {
    // Vérifier le format de la clé
    if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}:fx$/i', $deeplKey)) {
        echo "✅ Format valide\n";
    } else {
        echo "⚠️  Format suspect (devrait être: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx:fx)\n";
    }
}

echo "\n";

// Résumé
$isConfigured = ($provider === 'deepl') && 
                !empty($deeplKey) && 
                ($deeplKey !== 'votre_cle_deepl_ici');

if ($isConfigured) {
    echo "✅ DeepL est correctement configuré!\n\n";
    echo "🧪 Pour tester:\n";
    echo "   php bin/console app:test-translation \"bonjour\" en\n\n";
} else {
    echo "❌ DeepL n'est PAS configuré\n\n";
    echo "📝 Actions requises:\n";
    
    if ($provider !== 'deepl') {
        echo "   1. Modifier .env: TRANSLATION_PROVIDER=deepl\n";
    }
    
    if (empty($deeplKey) || $deeplKey === 'votre_cle_deepl_ici') {
        echo "   2. Obtenir une clé API: https://www.deepl.com/pro-api\n";
        echo "   3. Modifier .env: DEEPL_API_KEY=votre_vraie_cle\n";
    }
    
    echo "\n📖 Consultez: DEEPL_5_MINUTES.md\n\n";
}

echo str_repeat("=", 50) . "\n";
