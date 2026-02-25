Write-Host "Test de Configuration - Fonctionnalites de Presence" -ForegroundColor Cyan
Write-Host "====================================================" -ForegroundColor Cyan
Write-Host ""

$PASSED = 0
$FAILED = 0

Write-Host "Verification des Fichiers" -ForegroundColor Cyan
Write-Host "-------------------------"

$files = @(
    "src/Entity/MessageReadReceipt.php",
    "src/Entity/UserPresence.php",
    "src/Repository/MessageReadReceiptRepository.php",
    "src/Repository/UserPresenceRepository.php",
    "src/Controller/UserPresenceController.php",
    "public/presence_manager.js"
)

foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "[OK] $file" -ForegroundColor Green
        $PASSED++
    } else {
        Write-Host "[ERREUR] $file manquant" -ForegroundColor Red
        $FAILED++
    }
}

Write-Host ""
Write-Host "Verification des Routes" -ForegroundColor Cyan
Write-Host "-----------------------"

$routes = php bin/console debug:router 2>&1 | Out-String

$routesToCheck = @("presence_heartbeat", "presence_typing", "presence_online_users", "message_mark_read")

foreach ($route in $routesToCheck) {
    if ($routes -match $route) {
        Write-Host "[OK] Route $route" -ForegroundColor Green
        $PASSED++
    } else {
        Write-Host "[ERREUR] Route $route manquante" -ForegroundColor Red
        $FAILED++
    }
}

Write-Host ""
Write-Host "Resultats" -ForegroundColor Cyan
Write-Host "---------"
$TOTAL = $PASSED + $FAILED
Write-Host "Tests reussis: $PASSED/$TOTAL" -ForegroundColor Green

if ($FAILED -gt 0) {
    Write-Host "Tests echoues: $FAILED/$TOTAL" -ForegroundColor Red
    Write-Host ""
    Write-Host "Certains elements sont manquants!" -ForegroundColor Yellow
    Write-Host "Consultez GUIDE_TEST_PRESENCE_FEATURES.md pour plus de details."
} else {
    Write-Host ""
    Write-Host "Tous les tests sont passes!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Prochaines etapes:"
    Write-Host "1. Integrer le script dans le template du chatroom"
    Write-Host "2. Suivre le guide: GUIDE_TEST_PRESENCE_FEATURES.md"
    Write-Host "3. Tester avec 2 navigateurs differents"
}
