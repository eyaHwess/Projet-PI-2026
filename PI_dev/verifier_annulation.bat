@echo off
echo ========================================
echo VERIFICATION DE L'ANNULATION
echo ========================================
echo.

echo [1/7] Verification Git Status...
git status --short
if %ERRORLEVEL% EQU 0 (
    echo ✓ Git Status: OK
) else (
    echo ✗ Git Status: ERREUR
)
echo.

echo [2/7] Verification CoachingRequest.php...
findstr /C:"STATUS_SCHEDULED" src\Entity\CoachingRequest.php >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo ✗ STATUS_SCHEDULED trouve - PAS ANNULE
) else (
    echo ✓ STATUS_SCHEDULED absent - OK
)
echo.

echo [3/7] Verification CoachingRequestController.php...
findstr /C:"scheduled" src\Controller\CoachingRequestController.php >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo ✗ Variable scheduled trouvee - PAS ANNULE
) else (
    echo ✓ Variable scheduled absente - OK
)
echo.

echo [4/7] Verification template index.html.twig...
findstr /C:"grid-cols-6" templates\coaching_request\index.html.twig >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo ✗ grid-cols-6 trouve - PAS ANNULE
) else (
    echo ✓ grid-cols-6 absent - OK
)
echo.

echo [5/7] Verification fichiers documentation...
if exist "FILTRES_DEMANDES_COACH.md" (
    echo ✗ Fichiers doc presents - PAS ANNULE
) else (
    echo ✓ Fichiers doc supprimes - OK
)
echo.

echo [6/7] Verification migration...
if exist "migrations\Version20260218152625.php" (
    echo ✗ Migration presente - PAS ANNULE
) else (
    echo ✓ Migration supprimee - OK
)
echo.

echo [7/7] Verification cache...
if exist "var\cache\dev" (
    echo ✓ Cache present
) else (
    echo ✗ Cache absent - Executer: php bin/console cache:clear
)
echo.

echo ========================================
echo VERIFICATION TERMINEE
echo ========================================
echo.
echo Pour voir l'interface:
echo 1. Videz le cache navigateur: Ctrl+Shift+Delete
echo 2. Allez sur: http://127.0.0.1:8000/coach/requests
echo.
pause
