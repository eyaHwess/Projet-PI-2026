<?php

/**
 * Script de vérification de la configuration DeepL
 * Usage: php test_deepl_config.php
 */

echo "🔍 Vérification de la configuration DeepL\n";
echo str_repeat("=", 60) . "\n\n";

// 1. Vérifier que le fichier .env existe
echo "1️⃣  Vérification du fichier .env...\n";
if (!file_exists('.env')) {
    echo "   ❌ Fichier .env introuvable\n";
    exit(1);
}
echo "   ✅ Fichier .env trouvé\n\n";

// 2. Lire le contenu du .env
$envContent = file_get_contents('.env');

// 3. Vérifier TRANSLATION_PROVIDER
echo "2️⃣  Vérification du provider de traduction...\n";
if (preg_match('/TRANSLATION_PROVIDER=(\w+)/', $envContent, $matches)) {
    $provider = $matches[1];
    echo "   📌 Provider configuré: $provider\n";
    
    if ($provider === 'deepl') {
        echo "   ✅ DeepL est configuré comme provider\n";
    } else {
        echo "   ⚠️  Provider actuel: $provider (pas DeepL)\n";
        echo "   💡 Pour utiliser DeepL, changez en: TRANSLATION_PROVIDER=deepl\n";
    }
} else {
    echo "   ❌ TRANSLATION_PROVIDER non trouvé dans .env\n";
}
echo "\n";

// 4. Vérifier DEEPL_API_KEY
echo "3️⃣  Vérification de la clé API DeepL...\n";
if (preg_match('/DEEPL_API_KEY=(.+)/', $envContent, $matches)) {
    $apiKey = trim($matches[1]);
    echo "   📌 Clé API trouvée: " . substr($apiKey, 0, 20) . "...\n";
    
    if ($apiKey === 'votre_cle_deepl_ici' || empty($apiKey)) {
        echo "   ❌ Clé API non configurée (valeur par défaut)\n";
        echo "   💡 Action requise:\n";
        echo "      1. Allez sur: https://www.deepl.com/pro-api\n";
        echo "      2. Créez un compte gratuit\n";
        echo "      3. Copiez votre clé API\n";
        echo "      4. Remplacez 'votre_cle_deepl_ici' dans .env\n";
    } else {
        echo "   ✅ Clé API configurée\n";
        
        // Vérifier le format de la clé
        if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}:fx$/i', $apiKey)) {
            echo "   ✅ Format de clé valide (FREE API)\n";
        } else {
            echo "   ⚠️  Format de clé inhabituel\n";
            echo "   💡 Format attendu: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx:fx\n";
        }
    }
} else {
    echo "   ❌ DEEPL_API_KEY non trouvé dans .env\n";
}
echo "\n";

// 5. Vérifier que le service existe
echo "4️⃣  Vérification du service TranslationService...\n";
if (file_exists('src/Service/TranslationService.php')) {
    echo "   ✅ TranslationService.php trouvé\n";
    
    $serviceContent = file_get_contents('src/Service/TranslationService.php');
    if (strpos($serviceContent, 'translateWithDeepL') !== false) {
        echo "   ✅ Méthode translateWithDeepL() présente\n";
    } else {
        echo "   ❌ Méthode translateWithDeepL() manquante\n";
    }
} else {
    echo "   ❌ TranslationService.php introuvable\n";
}
echo "\n";

// 6. Vérifier services.yaml
echo "5️⃣  Vérification de config/services.yaml...\n";
if (file_exists('config/services.yaml')) {
    echo "   ✅ services.yaml trouvé\n";
    
    $servicesContent = file_get_contents('config/services.yaml');
    if (strpos($servicesContent, 'App\Service\TranslationService') !== false) {
        echo "   ✅ TranslationService configuré\n";
    }
    if (strpos($servicesContent, 'translation.deepl_api_key') !== false) {
        echo "   ✅ Paramètre deepl_api_key configuré\n";
    }
} else {
    echo "   ❌ services.yaml introuvable\n";
}
echo "\n";

// 7. Résumé
echo str_repeat("=", 60) . "\n";
echo "📊 RÉSUMÉ\n";
echo str_repeat("=", 60) . "\n\n";

$allGood = true;

if (!preg_match('/TRANSLATION_PROVIDER=deepl/', $envContent)) {
    echo "⚠️  Provider n'est pas DeepL\n";
    $allGood = false;
}

if (preg_match('/DEEPL_API_KEY=(.+)/', $envContent, $matches)) {
    $apiKey = trim($matches[1]);
    if ($apiKey === 'votre_cle_deepl_ici' || empty($apiKey)) {
        echo "❌ Clé API DeepL non configurée\n";
        $allGood = false;
    }
}

if ($allGood) {
    echo "✅ Configuration complète et prête !\n\n";
    echo "🧪 Prochaines étapes:\n";
    echo "   1. Videz le cache: php bin/console cache:clear\n";
    echo "   2. Redémarrez le serveur: symfony server:restart\n";
    echo "   3. Testez: php bin/console app:test-translation \"hello\" fr\n";
} else {
    echo "\n📝 Actions requises:\n";
    echo "   1. Configurez TRANSLATION_PROVIDER=deepl dans .env\n";
    echo "   2. Obtenez une clé API sur https://www.deepl.com/pro-api\n";
    echo "   3. Ajoutez la clé dans .env (DEEPL_API_KEY=...)\n";
    echo "   4. Relancez ce script pour vérifier\n";
}

echo "\n";
