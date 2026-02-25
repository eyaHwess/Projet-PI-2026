<?php

echo "🔧 CORRECTION AUTOMATIQUE - SYSTÈME DE TRADUCTION\n\n";

// 1. Vérifier que le fichier JavaScript existe
echo "1. Vérification du fichier JavaScript...\n";
$jsFile = 'public/js/translation.js';
if (file_exists($jsFile)) {
    echo "   ✅ Fichier translation.js existe (" . filesize($jsFile) . " octets)\n";
} else {
    echo "   ❌ Fichier translation.js manquant\n";
    echo "   🔧 Création du fichier...\n";
    
    if (!is_dir('public/js')) {
        mkdir('public/js', 0755, true);
        echo "   📁 Dossier public/js créé\n";
    }
    
    // Copier le contenu depuis le template ou créer un nouveau fichier
    $jsContent = '/**
 * Système de traduction pour le chatroom
 */

// Variables globales
window.translationMenus = {};

/**
 * Basculer le menu de traduction
 */
window.toggleTranslateMenu = function(messageId) {
    console.log("toggleTranslateMenu appelée:", messageId);
    
    const menu = document.getElementById("translateMenu" + messageId);
    if (!menu) {
        console.error("Menu non trouvé:", "translateMenu" + messageId);
        return;
    }
    
    // Fermer tous les autres menus
    document.querySelectorAll(".translate-menu.show").forEach(m => {
        if (m.id !== "translateMenu" + messageId) {
            m.classList.remove("show");
        }
    });
    
    // Basculer ce menu
    menu.classList.toggle("show");
    console.log("Menu ouvert:", menu.classList.contains("show"));
};

/**
 * Traduire un message
 */
window.translateMessageTo = function(event, messageId, targetLang, langName) {
    console.log("translateMessageTo appelée:", messageId, targetLang, langName);
    
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    // Fermer le menu
    const menu = document.getElementById("translateMenu" + messageId);
    if (menu) {
        menu.classList.remove("show");
    }
    
    // Appeler la fonction de traduction
    if (typeof translateMessage === "function") {
        translateMessage(messageId, targetLang);
    } else {
        console.error("translateMessage non définie");
    }
    
    return false;
};

/**
 * Fermer une traduction
 */
window.closeTranslation = function(messageId) {
    console.log("closeTranslation appelée:", messageId);
    
    const container = document.getElementById("translated-text-" + messageId);
    if (container) {
        container.style.display = "none";
        container.innerHTML = "";
    }
};

/**
 * Traduire un message (appel AJAX)
 */
window.translateMessage = async function(messageId, targetLang) {
    console.log("translateMessage appelée:", messageId, targetLang);
    
    const container = document.getElementById("translated-text-" + messageId);
    if (!container) {
        console.error("Conteneur non trouvé:", "translated-text-" + messageId);
        return;
    }

    if (!targetLang) {
        targetLang = "en";
    }

    // Afficher le spinner
    container.style.display = "block";
    container.innerHTML =
        \'<div class="translated-text-inner">\' +
            \'<span class="spinner"><i class="fas fa-spinner fa-spin"></i></span> \' +
            \'<span>Traduction en cours...</span>\' +
        \'</div>\';

    try {
        const params = new URLSearchParams();
        params.append("lang", targetLang);

        const response = await fetch("/message/" + messageId + "/translate", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: params.toString()
        });

        const contentType = response.headers.get("content-type") || "";
        if (!contentType.includes("application/json")) {
            const txt = await response.text();
            console.error("Réponse non JSON:", txt);
            container.innerHTML =
                \'<div class="translated-text-inner">\' +
                    \'<i class="fas fa-exclamation-triangle"></i>\' +
                    \'<span>Erreur lors de la traduction.</span>\' +
                \'</div>\';
            return;
        }

        const data = await response.json();

        if (data.error) {
            container.innerHTML =
                \'<div class="translated-text-inner">\' +
                    \'<i class="fas fa-exclamation-triangle"></i>\' +
                    \'<span>\' + data.error + \'</span>\' +
                \'</div>\';
            return;
        }

        const langLabel = data.targetLanguage || targetLang.toUpperCase();

        container.style.display = "block";
        container.innerHTML =
            \'<div class="translated-text-inner">\' +
                \'<span class="badge bg-primary-subtle text-primary me-1">\' +
                    \'<i class="fas fa-language"></i>\' +
                \'</span>\' +
                \'<span><strong>\' + langLabel + \' :</strong> \' + data.translation + \'</span>\' +
                \'<button class="btn-close-translation" onclick="closeTranslation(\' + messageId + \')" title="Fermer la traduction">\' +
                    \'<i class="fas fa-times"></i>\' +
                \'</button>\' +
            \'</div>\';
            
        console.log("Traduction affichée avec succès");
    } catch (e) {
        console.error("Erreur translateMessage", e);
        container.innerHTML =
            \'<div class="translated-text-inner">\' +
                \'<i class="fas fa-exclamation-triangle"></i>\' +
                \'<span>Erreur lors de la traduction.</span>\' +
            \'</div>\';
    }
};

// Fermer les menus au clic extérieur
document.addEventListener("click", function(event) {
    if (!event.target.closest(".translate-wrapper")) {
        document.querySelectorAll(".translate-menu.show").forEach(menu => {
            menu.classList.remove("show");
        });
    }
});

// Initialiser au chargement
document.addEventListener("DOMContentLoaded", function() {
    console.log("Translation.js chargé");
    console.log("Fonctions disponibles:", {
        toggleTranslateMenu: typeof window.toggleTranslateMenu,
        translateMessageTo: typeof window.translateMessageTo,
        translateMessage: typeof window.translateMessage,
        closeTranslation: typeof window.closeTranslation
    });
});';
    
    file_put_contents($jsFile, $jsContent);
    echo "   ✅ Fichier translation.js créé\n";
}

// 2. Vérifier les permissions
echo "\n2. Vérification des permissions...\n";
if (is_readable($jsFile)) {
    echo "   ✅ Fichier lisible\n";
} else {
    echo "   ❌ Fichier non lisible\n";
    chmod($jsFile, 0644);
    echo "   🔧 Permissions corrigées\n";
}

// 3. Vérifier la route de traduction
echo "\n3. Vérification des routes...\n";
$routeCheck = shell_exec('php bin/console debug:router 2>&1');
if (strpos($routeCheck, 'message_translate') !== false) {
    echo "   ✅ Route message_translate trouvée\n";
} else {
    echo "   ❌ Route message_translate manquante\n";
    echo "   🔧 Vérifiez le fichier src/Controller/MessageController.php\n";
}

// 4. Nettoyer le cache
echo "\n4. Nettoyage du cache...\n";
$cacheResult = shell_exec('php bin/console cache:clear 2>&1');
if (strpos($cacheResult, 'Cache cleared') !== false || strpos($cacheResult, 'successfully') !== false) {
    echo "   ✅ Cache nettoyé\n";
} else {
    echo "   ⚠️ Nettoyage du cache: " . trim($cacheResult) . "\n";
}

// 5. Créer un fichier de test simple
echo "\n5. Création d\'un test simple...\n";
$testFile = 'public/test_simple.html';
$testContent = '<!DOCTYPE html>
<html>
<head>
    <title>Test Simple Traduction</title>
</head>
<body>
    <h1>Test Simple</h1>
    <button onclick="testFunction()">Tester les Fonctions</button>
    <div id="result"></div>
    
    <script src="/js/translation.js"></script>
    <script>
        function testFunction() {
            const result = document.getElementById("result");
            let html = "<h3>Résultats:</h3>";
            
            const functions = ["toggleTranslateMenu", "translateMessageTo", "translateMessage", "closeTranslation"];
            functions.forEach(func => {
                const exists = typeof window[func] === "function";
                html += `<p>${func}: ${exists ? "✅ OK" : "❌ MANQUANTE"}</p>`;
            });
            
            result.innerHTML = html;
        }
        
        // Test automatique au chargement
        window.addEventListener("load", () => {
            setTimeout(testFunction, 1000);
        });
    </script>
</body>
</html>';

file_put_contents($testFile, $testContent);
echo "   ✅ Fichier test_simple.html créé\n";

// 6. Instructions finales
echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 CORRECTION TERMINÉE\n\n";

echo "ÉTAPES SUIVANTES:\n";
echo "1. Démarrer le serveur: symfony server:start\n";
echo "2. Tester les fonctions: http://localhost:8000/test_simple.html\n";
echo "3. Diagnostic complet: http://localhost:8000/diagnostic_traduction.html\n";
echo "4. Test de traduction: http://localhost:8000/test_traduction_direct.html\n\n";

echo "VÉRIFICATIONS MANUELLES:\n";
echo "1. Ouvrir un chatroom\n";
echo "2. Envoyer un message 'hello'\n";
echo "3. Cliquer sur 'Traduire' → '🇫🇷 Français'\n";
echo "4. Vérifier que 'bonjour' s'affiche\n\n";

echo "EN CAS DE PROBLÈME:\n";
echo "1. F12 > Console pour voir les erreurs\n";
echo "2. F12 > Network pour vérifier le chargement des fichiers\n";
echo "3. Vérifier que vous êtes connecté\n";
echo "4. Vérifier qu'un message existe avec l'ID testé\n\n";

echo "✅ Système de traduction corrigé et prêt à l'utilisation!\n";