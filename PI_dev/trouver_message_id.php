<?php

require_once 'vendor/autoload.php';

use Doctrine\ORM\EntityManagerInterface;

// Bootstrap Symfony
$kernel = new \App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

echo "🔍 RECHERCHE D'UN MESSAGE EXISTANT\n\n";

try {
    // Récupérer l'EntityManager
    $entityManager = $container->get('doctrine')->getManager();
    
    // Trouver le premier message
    $query = $entityManager->createQuery(
        'SELECT m.id, m.content, m.createdAt, u.firstName, u.lastName 
         FROM App\Entity\Message m 
         JOIN m.author u 
         WHERE m.content IS NOT NULL 
         ORDER BY m.id DESC'
    );
    $query->setMaxResults(5);
    
    $messages = $query->getResult();
    
    if (empty($messages)) {
        echo "❌ AUCUN MESSAGE TROUVÉ\n\n";
        echo "🔧 SOLUTION:\n";
        echo "   1. Aller sur : http://localhost:8000/goals\n";
        echo "   2. Choisir un goal\n";
        echo "   3. Aller dans le chatroom\n";
        echo "   4. Envoyer un message : 'hello'\n";
        echo "   5. Revenir ici et relancer ce script\n\n";
        exit(1);
    }
    
    echo "✅ MESSAGES TROUVÉS : " . count($messages) . "\n\n";
    
    echo "Liste des messages disponibles :\n";
    echo str_repeat("-", 70) . "\n";
    
    foreach ($messages as $message) {
        $content = strlen($message['content']) > 50 
            ? substr($message['content'], 0, 50) . '...' 
            : $message['content'];
        
        echo sprintf(
            "ID: %d | Auteur: %s %s | Contenu: %s\n",
            $message['id'],
            $message['firstName'],
            $message['lastName'],
            $content
        );
    }
    
    echo str_repeat("-", 70) . "\n\n";
    
    // Prendre le premier message
    $firstMessage = $messages[0];
    $messageId = $firstMessage['id'];
    
    echo "🎯 MESSAGE SÉLECTIONNÉ POUR LE TEST:\n";
    echo "   ID: $messageId\n";
    echo "   Contenu: " . $firstMessage['content'] . "\n";
    echo "   Auteur: {$firstMessage['firstName']} {$firstMessage['lastName']}\n\n";
    
    // Créer une page de test avec cet ID
    echo "📄 CRÉATION D'UNE PAGE DE TEST AVEC CET ID...\n";
    
    $testHTML = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test Traduction - ID Réel</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .success { background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #0056b3; }
        .message-info { background: #fff; padding: 15px; border-radius: 5px; border: 1px solid #ddd; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🌍 Test de Traduction avec ID Réel</h1>
    
    <div class="info">
        <strong>Message trouvé dans la base de données:</strong><br>
        ID: ' . $messageId . '<br>
        Contenu: "' . htmlspecialchars($firstMessage['content']) . '"<br>
        Auteur: ' . htmlspecialchars($firstMessage['firstName'] . ' ' . $firstMessage['lastName']) . '
    </div>
    
    <div class="message-info">
        <h3>Test Automatique</h3>
        <button onclick="testTranslation()">🔄 Tester la Traduction</button>
        <div id="result"></div>
    </div>
    
    <div class="message-info">
        <h3>Instructions</h3>
        <p>Ce test utilise un message réel de votre base de données.</p>
        <ol>
            <li>Cliquez sur "Tester la Traduction"</li>
            <li>La traduction devrait s\'afficher ci-dessous</li>
            <li>Si erreur 401/302 : <a href="http://localhost:8000/login" target="_blank">Se connecter d\'abord</a></li>
        </ol>
    </div>
    
    <script src="http://localhost:8000/js/translation.js"></script>
    <script>
        async function testTranslation() {
            const messageId = ' . $messageId . ';
            const resultDiv = document.getElementById("result");
            
            resultDiv.innerHTML = "<div class=\"info\">⏳ Test en cours...</div>";
            
            try {
                const response = await fetch(`http://localhost:8000/message/${messageId}/translate`, {
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
                            ✅ <strong>Traduction réussie!</strong><br><br>
                            <strong>Texte original:</strong> "' . htmlspecialchars($firstMessage['content']) . '"<br>
                            <strong>Traduction:</strong> "${data.translation}"<br>
                            <strong>Langue:</strong> ${data.targetLanguage}
                        </div>`;
                    } else if (data.error) {
                        resultDiv.innerHTML = `<div class="error">
                            ❌ <strong>Erreur:</strong> ${data.error}<br><br>
                            <strong>Solutions possibles:</strong><br>
                            1. <a href="http://localhost:8000/login" target="_blank">Se connecter</a><br>
                            2. Vérifier que vous avez accès à ce message
                        </div>`;
                    }
                } else {
                    resultDiv.innerHTML = `<div class="error">
                        ❌ <strong>Erreur:</strong> Réponse non-JSON<br>
                        <strong>Status:</strong> ${response.status}<br><br>
                        ${response.status === 401 || response.status === 302 ? 
                            \'<a href="http://localhost:8000/login" target="_blank">→ Se connecter maintenant</a>\' : 
                            \'Vérifiez que le serveur fonctionne correctement\'}
                    </div>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="error">
                    ❌ <strong>Erreur réseau:</strong> ${error.message}<br><br>
                    <strong>Vérifications:</strong><br>
                    1. Le serveur est-il démarré? (http://localhost:8000)<br>
                    2. Êtes-vous connecté? <a href="http://localhost:8000/login" target="_blank">Se connecter</a>
                </div>`;
            }
        }
        
        // Test automatique au chargement
        window.addEventListener("load", () => {
            console.log("Page chargée, prêt pour le test");
            console.log("ID du message:", ' . $messageId . ');
            console.log("Contenu:", "' . addslashes($firstMessage['content']) . '");
        });
    </script>
</body>
</html>';
    
    file_put_contents('public/test_id_reel.html', $testHTML);
    echo "   ✅ Page créée : public/test_id_reel.html\n\n";
    
    // Mettre à jour la configuration
    $config = json_decode(file_get_contents('config_serveur.json'), true);
    $config['test_message_id'] = $messageId;
    $config['test_message_content'] = $firstMessage['content'];
    file_put_contents('config_serveur.json', json_encode($config, JSON_PRETTY_PRINT));
    echo "   ✅ Configuration mise à jour\n\n";
    
    echo "🚀 POUR TESTER:\n";
    echo "   1. Ouvrir : http://localhost:8000/test_id_reel.html\n";
    echo "   2. Cliquer sur 'Tester la Traduction'\n";
    echo "   3. Voir le résultat\n\n";
    
    echo "💡 OU TESTER DIRECTEMENT DANS L'INTERFACE:\n";
    echo "   1. Ouvrir : http://localhost:8000/goals\n";
    echo "   2. Aller dans un chatroom\n";
    echo "   3. Trouver le message ID $messageId\n";
    echo "   4. Cliquer sur 'Traduire' sous le message\n\n";
    
    echo "✅ SUCCÈS! Un message existant a été trouvé.\n";
    
} catch (\Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n\n";
    echo "🔧 SOLUTION:\n";
    echo "   1. Vérifier que la base de données est accessible\n";
    echo "   2. Vérifier qu'il y a des messages dans la table 'message'\n";
    echo "   3. Créer un message via l'interface si nécessaire\n\n";
}
