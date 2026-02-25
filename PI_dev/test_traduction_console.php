<?php

echo "=== TEST DE TRADUCTION FORCÉE ===\n\n";

echo "Pour tester la traduction de 'hello' en français, suivez ces étapes:\n\n";

echo "1. DÉMARRER LE SERVEUR WEB\n";
echo "   Commande: symfony server:start\n";
echo "   Ou: php -S localhost:8000 -t public\n";
echo "   Vérifier: http://localhost:8000\n\n";

echo "2. ALLER DANS UN CHATROOM\n";
echo "   - Se connecter à l'application\n";
echo "   - Rejoindre ou créer un goal\n";
echo "   - Aller dans le chatroom\n";
echo "   - Envoyer un message avec le texte 'hello'\n\n";

echo "3. TESTER LA TRADUCTION DANS LA CONSOLE DU NAVIGATEUR\n";
echo "   - Ouvrir les outils de développement (F12)\n";
echo "   - Aller dans l'onglet Console\n";
echo "   - Taper cette commande (remplacer MESSAGE_ID par l'ID réel):\n\n";

echo "   fetch('/message/MESSAGE_ID/translate', {\n";
echo "     method: 'POST',\n";
echo "     headers: {\n";
echo "       'Content-Type': 'application/x-www-form-urlencoded',\n";
echo "       'X-Requested-With': 'XMLHttpRequest'\n";
echo "     },\n";
echo "     body: 'lang=fr'\n";
echo "   })\n";
echo "   .then(response => response.json())\n";
echo "   .then(data => {\n";
echo "     console.log('Traduction:', data.translation);\n";
echo "     console.log('Langue cible:', data.targetLanguage);\n";
echo "   })\n";
echo "   .catch(error => console.error('Erreur:', error));\n\n";

echo "4. RÉSULTAT ATTENDU\n";
echo "   Traduction: 'bonjour' ou 'salut'\n";
echo "   Langue cible: 'Français' ou 'FR'\n\n";

echo "5. TESTER AVEC L'INTERFACE UTILISATEUR\n";
echo "   - Cliquer sur le bouton 'Traduire' sous le message\n";
echo "   - Sélectionner '🇫🇷 Français' dans le menu\n";
echo "   - Vérifier que la traduction s'affiche sous le message\n\n";

echo "6. VÉRIFIER LES FONCTIONS JAVASCRIPT\n";
echo "   Dans la console du navigateur, taper:\n";
echo "   console.log(typeof window.toggleTranslateMenu);\n";
echo "   console.log(typeof window.translateMessage);\n";
echo "   // Doit afficher 'function' pour les deux\n\n";

echo "7. TESTER MANUELLEMENT LA FONCTION\n";
echo "   Dans la console, taper (remplacer 123 par l'ID réel du message):\n";
echo "   translateMessage(123, 'fr');\n";
echo "   // Doit afficher la traduction sous le message\n\n";

echo "=== DIAGNOSTIC EN CAS DE PROBLÈME ===\n\n";

echo "Si la traduction ne fonctionne pas:\n\n";

echo "A. VÉRIFIER QUE LE FICHIER JS EST CHARGÉ\n";
echo "   - F12 > Network > Recharger la page\n";
echo "   - Chercher 'translation.js'\n";
echo "   - Status doit être 200 OK\n\n";

echo "B. VÉRIFIER LES ERREURS JAVASCRIPT\n";
echo "   - F12 > Console\n";
echo "   - Chercher les erreurs en rouge\n";
echo "   - Noter les messages d'erreur\n\n";

echo "C. VÉRIFIER LA ROUTE DE TRADUCTION\n";
echo "   Commande: php bin/console debug:router | grep translate\n";
echo "   Doit afficher: message_translate POST /message/{id}/translate\n\n";

echo "D. VÉRIFIER LES LOGS SYMFONY\n";
echo "   Commande: tail -f var/log/dev.log\n";
echo "   Tester la traduction et voir les erreurs\n\n";

echo "E. NETTOYER LE CACHE\n";
echo "   Commande: php bin/console cache:clear\n";
echo "   Puis recharger la page avec Ctrl+Shift+R\n\n";

echo "=== EXEMPLE COMPLET ===\n\n";

echo "1. Démarrer le serveur: symfony server:start\n";
echo "2. Aller sur: http://localhost:8000/message/chatroom/1\n";
echo "3. Envoyer le message: 'hello'\n";
echo "4. Noter l'ID du message (par exemple: 42)\n";
echo "5. Ouvrir F12 > Console\n";
echo "6. Taper: translateMessage(42, 'fr')\n";
echo "7. Vérifier que 'bonjour' s'affiche sous le message\n\n";

echo "Si tout fonctionne, vous devriez voir:\n";
echo "┌─────────────────────────────────────────────────┐\n";
echo "│ 👤 Utilisateur                     10:30 AM     │\n";
echo "│ hello                                           │\n";
echo "│                                                 │\n";
echo "│ 🌐 FRANÇAIS : bonjour                       ×  │\n";
echo "└─────────────────────────────────────────────────┘\n\n";

echo "=== FIN DU GUIDE ===\n";