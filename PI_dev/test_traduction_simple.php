<?php

echo "=== TEST SIMPLE DE TRADUCTION VIA HTTP ===\n\n";

// Test 1: Créer un message de test avec "hello"
echo "1. Test de traduction via HTTP\n";

// Simuler une requête POST vers la route de traduction
$messageId = 1; // ID d'un message existant (à adapter)
$url = "http://localhost/message/$messageId/translate";

$postData = http_build_query([
    'lang' => 'fr'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                   "X-Requested-With: XMLHttpRequest\r\n",
        'content' => $postData
    ]
]);

echo "   URL: $url\n";
echo "   Données: lang=fr\n";

try {
    $result = file_get_contents($url, false, $context);
    
    if ($result === false) {
        echo "   Status: ❌ ÉCHEC - Impossible de contacter le serveur\n";
        echo "   Vérifiez que le serveur web est démarré\n";
    } else {
        echo "   Réponse brute: $result\n";
        
        $json = json_decode($result, true);
        if ($json) {
            if (isset($json['translation'])) {
                echo "   Traduction: " . $json['translation'] . "\n";
                echo "   Status: ✅ SUCCÈS\n";
            } elseif (isset($json['error'])) {
                echo "   Erreur: " . $json['error'] . "\n";
                echo "   Status: ⚠️ ERREUR MÉTIER\n";
            } else {
                echo "   Status: ❓ RÉPONSE INATTENDUE\n";
            }
        } else {
            echo "   Status: ❌ RÉPONSE NON-JSON\n";
        }
    }
} catch (Exception $e) {
    echo "   Exception: " . $e->getMessage() . "\n";
    echo "   Status: ❌ ÉCHEC\n";
}

echo "\n";

// Test 2: Instructions pour test manuel
echo "2. Test manuel dans le navigateur\n";
echo "   1. Ouvrir le chatroom: http://localhost/message/chatroom/{goalId}\n";
echo "   2. Ouvrir la console (F12)\n";
echo "   3. Taper: translateMessage(1, 'fr')\n";
echo "   4. Vérifier la réponse\n";

echo "\n";

// Test 3: Vérifier que le fichier JavaScript existe
echo "3. Vérification du fichier JavaScript\n";
$jsFile = 'public/js/translation.js';
if (file_exists($jsFile)) {
    echo "   Fichier: ✅ EXISTE\n";
    echo "   Taille: " . filesize($jsFile) . " octets\n";
    
    // Vérifier que les fonctions sont définies
    $content = file_get_contents($jsFile);
    $functions = ['toggleTranslateMenu', 'translateMessageTo', 'translateMessage', 'closeTranslation'];
    
    foreach ($functions as $func) {
        if (strpos($content, "window.$func") !== false) {
            echo "   Fonction $func: ✅ DÉFINIE\n";
        } else {
            echo "   Fonction $func: ❌ MANQUANTE\n";
        }
    }
} else {
    echo "   Fichier: ❌ MANQUANT\n";
    echo "   Créer le fichier: public/js/translation.js\n";
}

echo "\n=== FIN DES TESTS ===\n";

// Test 4: Afficher les instructions de débogage
echo "\n=== INSTRUCTIONS DE DÉBOGAGE ===\n";
echo "1. Vérifier que le serveur web fonctionne:\n";
echo "   - Ouvrir: http://localhost\n";
echo "   - Doit afficher la page d'accueil\n\n";

echo "2. Vérifier qu'un message existe:\n";
echo "   - Aller dans un chatroom\n";
echo "   - Envoyer un message avec le texte 'hello'\n";
echo "   - Noter l'ID du message\n\n";

echo "3. Tester la traduction manuellement:\n";
echo "   - Ouvrir la console (F12)\n";
echo "   - Taper: fetch('/message/ID_DU_MESSAGE/translate', {\n";
echo "       method: 'POST',\n";
echo "       headers: {'Content-Type': 'application/x-www-form-urlencoded'},\n";
echo "       body: 'lang=fr'\n";
echo "     }).then(r => r.json()).then(console.log)\n\n";

echo "4. Vérifier les logs d'erreur:\n";
echo "   - Fichier: var/log/dev.log\n";
echo "   - Commande: tail -f var/log/dev.log\n\n";