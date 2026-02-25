<?php

echo "🔍 VÉRIFICATION DU SERVEUR ET CORRECTION\n\n";

// Fonction pour tester une URL
function testURL($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 400,
        'code' => $httpCode,
        'error' => $error,
        'response' => $response
    ];
}

// 1. Vérifier les ports communs
echo "1. Recherche du serveur Symfony...\n";
$ports = [8000, 8080, 80, 3000, 9000];
$serverFound = false;
$workingPort = null;

foreach ($ports as $port) {
    $url = "http://localhost:$port";
    echo "   Essai du port $port... ";
    
    $result = testURL($url);
    
    if ($result['success']) {
        echo "✅ TROUVÉ!\n";
        $serverFound = true;
        $workingPort = $port;
        break;
    } else {
        echo "❌ (Code: {$result['code']})\n";
    }
}

if (!$serverFound) {
    echo "\n❌ AUCUN SERVEUR TROUVÉ!\n\n";
    echo "🔧 SOLUTION:\n";
    echo "   Démarrer le serveur avec l'une de ces commandes:\n";
    echo "   1. symfony server:start\n";
    echo "   2. php -S localhost:8000 -t public\n";
    echo "   3. php -S localhost:8080 -t public\n\n";
    exit(1);
}

echo "\n✅ Serveur trouvé sur le port $workingPort\n";

// 2. Vérifier la route de traduction
echo "\n2. Vérification de la route de traduction...\n";
$translateURL = "http://localhost:$workingPort/message/1/translate";
echo "   URL: $translateURL\n";

$result = testURL($translateURL);
echo "   Status: {$result['code']}\n";

if ($result['code'] == 404) {
    echo "   ⚠️ Route non trouvée (404)\n";
    echo "   Causes possibles:\n";
    echo "   - Le message avec l'ID 1 n'existe pas\n";
    echo "   - La route n'est pas configurée\n";
    echo "   - Le cache doit être nettoyé\n";
} elseif ($result['code'] == 401 || $result['code'] == 302) {
    echo "   ⚠️ Authentification requise ($result[code])\n";
    echo "   Vous devez être connecté pour tester\n";
} elseif ($result['code'] == 405) {
    echo "   ⚠️ Méthode non autorisée (405)\n";
    echo "   La route existe mais nécessite POST\n";
} elseif ($result['success']) {
    echo "   ✅ Route accessible\n";
}

// 3. Vérifier le fichier JavaScript
echo "\n3. Vérification du fichier JavaScript...\n";
$jsURL = "http://localhost:$workingPort/js/translation.js";
$result = testURL($jsURL);

if ($result['success']) {
    echo "   ✅ translation.js accessible\n";
    echo "   Taille: " . strlen($result['response']) . " octets\n";
} else {
    echo "   ❌ translation.js non accessible (Code: {$result['code']})\n";
    echo "   🔧 Vérifier que le fichier existe: public/js/translation.js\n";
}

// 4. Créer un fichier de configuration
echo "\n4. Création du fichier de configuration...\n";
$config = [
    'server_url' => "http://localhost:$workingPort",
    'api_base' => "http://localhost:$workingPort/message",
    'js_url' => "http://localhost:$workingPort/js/translation.js",
    'port' => $workingPort
];

file_put_contents('config_serveur.json', json_encode($config, JSON_PRETTY_PRINT));
echo "   ✅ Configuration sauvegardée dans config_serveur.json\n";

// 5. Créer une page de test avec la bonne URL
echo "\n5. Création d'une page de test corrigée...\n";
$testHTML = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test Traduction - Corrigé</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 20px auto; padding: 20px; }
        .success { background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 5px; }
        input { padding: 8px; margin: 5px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>🌍 Test de Traduction (Corrigé)</h1>
    
    <div class="info">
        <strong>Configuration détectée:</strong><br>
        Serveur: ' . $config['server_url'] . '<br>
        Port: ' . $workingPort . '
    </div>
    
    <h3>Test Rapide</h3>
    <p>ID du message: <input type="number" id="msgId" value="1"></p>
    <button onclick="testTranslation()">Tester la Traduction</button>
    <div id="result"></div>
    
    <h3>Instructions</h3>
    <ol>
        <li>Se connecter: <a href="' . $config['server_url'] . '/login" target="_blank">Connexion</a></li>
        <li>Aller dans un chatroom: <a href="' . $config['server_url'] . '/goals" target="_blank">Goals</a></li>
        <li>Envoyer un message "hello"</li>
        <li>Noter l\'ID du message</li>
        <li>Revenir ici et tester</li>
    </ol>
    
    <script src="' . $config['js_url'] . '"></script>
    <script>
        const SERVER_URL = "' . $config['server_url'] . '";
        
        async function testTranslation() {
            const msgId = document.getElementById("msgId").value;
            const resultDiv = document.getElementById("result");
            
            resultDiv.innerHTML = "<div class=\"info\">⏳ Test en cours...</div>";
            
            try {
                const response = await fetch(`${SERVER_URL}/message/${msgId}/translate`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: "lang=fr"
                });
                
                const contentType = response.headers.get("content-type");
                
                if (contentType && contentType.includes("application/json")) {
                    const data = await response.json();
                    
                    if (data.translation) {
                        resultDiv.innerHTML = `<div class="success">
                            ✅ <strong>Traduction réussie!</strong><br>
                            Traduction: ${data.translation}<br>
                            Langue: ${data.targetLanguage}
                        </div>`;
                    } else if (data.error) {
                        resultDiv.innerHTML = `<div class="error">
                            ❌ Erreur: ${data.error}
                        </div>`;
                    }
                } else {
                    const text = await response.text();
                    resultDiv.innerHTML = `<div class="error">
                        ❌ Erreur: Réponse non-JSON<br>
                        Status: ${response.status}<br>
                        <details><summary>Voir la réponse</summary><pre>${text.substring(0, 500)}</pre></details>
                    </div>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="error">
                    ❌ Erreur réseau: ${error.message}<br><br>
                    <strong>Vérifications:</strong><br>
                    1. Le serveur est-il démarré?<br>
                    2. Êtes-vous connecté?<br>
                    3. Le message existe-t-il?
                </div>`;
            }
        }
        
        // Vérifier les fonctions au chargement
        window.addEventListener("load", () => {
            setTimeout(() => {
                const functions = ["toggleTranslateMenu", "translateMessage"];
                const allLoaded = functions.every(f => typeof window[f] === "function");
                
                if (allLoaded) {
                    console.log("✅ Toutes les fonctions sont chargées");
                } else {
                    console.warn("⚠️ Certaines fonctions manquent");
                }
            }, 1000);
        });
    </script>
</body>
</html>';

file_put_contents('public/test_corrige.html', $testHTML);
echo "   ✅ Page de test créée: test_corrige.html\n";

// 6. Instructions finales
echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ VÉRIFICATION TERMINÉE\n\n";

echo "📋 RÉSUMÉ:\n";
echo "   Serveur: http://localhost:$workingPort\n";
echo "   Status: " . ($serverFound ? "✅ En ligne" : "❌ Hors ligne") . "\n\n";

echo "🚀 PROCHAINES ÉTAPES:\n";
echo "   1. Ouvrir: http://localhost:$workingPort/test_corrige.html\n";
echo "   2. Se connecter si nécessaire\n";
echo "   3. Créer un message dans un chatroom\n";
echo "   4. Tester la traduction\n\n";

echo "🔧 COMMANDES UTILES:\n";
echo "   Tester le service: php bin/console app:test-translation hello fr\n";
echo "   Nettoyer le cache: php bin/console cache:clear\n";
echo "   Voir les routes: php bin/console debug:router | grep translate\n\n";

echo "📁 FICHIERS CRÉÉS:\n";
echo "   - config_serveur.json (configuration détectée)\n";
echo "   - public/test_corrige.html (page de test avec bonnes URLs)\n\n";

if ($serverFound) {
    echo "✅ Le serveur fonctionne! Vous pouvez maintenant tester la traduction.\n";
} else {
    echo "❌ Démarrez d'abord le serveur avec: symfony server:start\n";
}
