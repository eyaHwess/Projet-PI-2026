<?php

/**
 * Test de modération pour MessageController
 * Ce script teste que la modération fonctionne correctement
 */

require_once __DIR__.'/vendor/autoload.php';

use App\Service\ModerationService;
use Psr\Log\NullLogger;

// Créer le service de modération
$moderationService = new ModerationService(new NullLogger());

echo "=== TEST DE MODÉRATION POUR MESSAGECONTROLLER ===\n\n";

// Test 1: Message toxique qui DOIT être bloqué
$toxicMessage = "you are a fucking asshole";
echo "Test 1: Message toxique\n";
echo "Message: \"$toxicMessage\"\n";
$result = $moderationService->analyzeMessage($toxicMessage);
echo "Score de toxicité: " . $result['toxicityScore'] . "\n";
echo "Statut: " . $result['moderationStatus'] . "\n";
echo "Raison: " . ($result['moderationReason'] ?? 'Aucune') . "\n";
echo "Résultat: " . ($result['moderationStatus'] === 'blocked' ? '✅ BLOQUÉ' : '❌ PASSÉ (ERREUR!)') . "\n\n";

// Test 2: Message normal qui DOIT passer
$normalMessage = "Hello, how are you today?";
echo "Test 2: Message normal\n";
echo "Message: \"$normalMessage\"\n";
$result = $moderationService->analyzeMessage($normalMessage);
echo "Score de toxicité: " . $result['toxicityScore'] . "\n";
echo "Statut: " . $result['moderationStatus'] . "\n";
echo "Résultat: " . ($result['moderationStatus'] === 'approved' ? '✅ APPROUVÉ' : '❌ BLOQUÉ (ERREUR!)') . "\n\n";

// Test 3: Message avec insulte en français
$frenchToxic = "tu es un connard";
echo "Test 3: Message toxique en français\n";
echo "Message: \"$frenchToxic\"\n";
$result = $moderationService->analyzeMessage($frenchToxic);
echo "Score de toxicité: " . $result['toxicityScore'] . "\n";
echo "Statut: " . $result['moderationStatus'] . "\n";
echo "Résultat: " . ($result['moderationStatus'] === 'blocked' ? '✅ BLOQUÉ' : '❌ PASSÉ (ERREUR!)') . "\n\n";

// Test 4: Message spam avec URL
$spamMessage = "Click here https://spam.com to win money!!!";
echo "Test 4: Message spam\n";
echo "Message: \"$spamMessage\"\n";
$result = $moderationService->analyzeMessage($spamMessage);
echo "Score de spam: " . $result['spamScore'] . "\n";
echo "Statut: " . $result['moderationStatus'] . "\n";
echo "Résultat: " . ($result['moderationStatus'] === 'hidden' ? '✅ MASQUÉ' : '❌ PASSÉ (ERREUR!)') . "\n\n";

echo "=== FIN DES TESTS ===\n";
echo "\n📋 RÉSUMÉ:\n";
echo "- Les messages toxiques doivent avoir le statut 'blocked'\n";
echo "- Les messages spam doivent avoir le statut 'hidden'\n";
echo "- Les messages normaux doivent avoir le statut 'approved'\n";
echo "\n🔧 INTÉGRATION DANS MESSAGECONTROLLER:\n";
echo "- Le service ModerationService est injecté dans le constructeur\n";
echo "- La modération est appliquée AVANT la persistance du message\n";
echo "- Si le statut est 'blocked', le message n'est PAS enregistré\n";
echo "- Un message d'erreur est affiché à l'utilisateur\n";
