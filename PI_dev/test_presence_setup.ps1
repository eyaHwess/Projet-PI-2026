# Test de Configuration - Fonctionnalités de Présence
Write-Host "🧪 Test de Configuration - Fonctionnalités de Présence" -ForegroundColor Cyan
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host ""

$PASSED = 0
$FAILED = 0

function Test-FileExists {
    param($Path, $Description)
    
    if (Test-Path $Path) {
        Write-Host "✓ $Description" -ForegroundColor Green
        $script:PASSED++
    } else {
        Write-Host "✗ $Description" -ForegroundColor Red
        Write-Host "  → Fichier manquant: $Path" -ForegroundColor Yellow
        $script:FAILED++
    }
}

function Test-RouteExists {
    param($RouteName)
    
    $routes = php bin/console debug:router 2>&1 | Out-String
    if ($routes -match $RouteName) {
        Write-Host "✓ Route $RouteName existe" -ForegroundColor Green
        $script:PASSED++
    } else {
        Write-Host "✗ Route $RouteName manquante" -ForegroundColor Red
        $script:FAILED++
    }
}

Write-Host "📁 Vérification des Fichiers" -ForegroundColor Cyan
Write-Host "----------------------------"
Test-FileExists "src/Entity/MessageReadReceipt.php" "Entité MessageReadReceipt"
Test-FileExists "src/Entity/UserPresence.php" "Entité UserPresence"
Test-FileExists "src/Repository/MessageReadReceiptRepository.php" "Repository MessageReadReceipt"
Test-FileExists "src/Repository/UserPresenceRepository.php" "Repository UserPresence"
Test-FileExists "src/Controller/UserPresenceController.php" "Contrôleur UserPresence"
Test-FileExists "public/presence_manager.js" "Script JavaScript presence_manager.js"
Write-Host ""

Write-Host "🔌 Vérification des Routes" -ForegroundColor Cyan
Write-Host "--------------------------"
Test-RouteExists "presence_heartbeat"
Test-RouteExists "presence_typing"
Test-RouteExists "presence_typing_users"
Test-RouteExists "presence_online_users"
Test-RouteExists "message_mark_read"
Write-Host ""

Write-Host "🗄️  Vérification de la Base de Données" -ForegroundColor Cyan
Write-Host "--------------------------------------"

$schemaValidation = php bin/console doctrine:schema:validate 2>&1 | Out-String
if ($schemaValidation -match "in sync") {
    Write-Host "✓ Schéma de base de données valide" -ForegroundColor Green
    $script:PASSED++
} else {
    Write-Host "✗ Schéma de base de données invalide" -ForegroundColor Red
    Write-Host "  → Exécuter: php bin/console doctrine:migrations:migrate" -ForegroundColor Yellow
    $script:FAILED++
}

# Vérifier les tables
try {
    $tables = php bin/console dbal:run-sql "SHOW TABLES" 2>&1 | Out-String
    
    if ($tables -match "message_read_receipt") {
        Write-Host "✓ Table message_read_receipt existe" -ForegroundColor Green
        $script:PASSED++
    } else {
        Write-Host "✗ Table message_read_receipt manquante" -ForegroundColor Red
        $script:FAILED++
    }
    
    if ($tables -match "user_presence") {
        Write-Host "✓ Table user_presence existe" -ForegroundColor Green
        $script:PASSED++
    } else {
        Write-Host "✗ Table user_presence manquante" -ForegroundColor Red
        $script:FAILED++
    }
} catch {
    Write-Host "⚠️  Impossible de vérifier les tables" -ForegroundColor Yellow
}

Write-Host ""

Write-Host "📊 Résultats" -ForegroundColor Cyan
Write-Host "------------"
$TOTAL = $PASSED + $FAILED
Write-Host "Tests réussis: " -NoNewline
Write-Host "$PASSED/$TOTAL" -ForegroundColor Green

if ($FAILED -gt 0) {
    Write-Host "Tests échoués: " -NoNewline
    Write-Host "$FAILED/$TOTAL" -ForegroundColor Red
    Write-Host ""
    Write-Host "⚠️  Certains éléments sont manquants!" -ForegroundColor Yellow
    Write-Host "Consultez GUIDE_TEST_PRESENCE_FEATURES.md pour plus de détails."
    exit 1
} else {
    Write-Host ""
    Write-Host "✅ Tous les tests sont passés!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Prochaines étapes:"
    Write-Host "1. Intégrer le script dans le template du chatroom"
    Write-Host "2. Suivre le guide: GUIDE_TEST_PRESENCE_FEATURES.md"
    Write-Host "3. Tester avec 2 navigateurs différents"
    exit 0
}
