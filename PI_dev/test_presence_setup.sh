#!/bin/bash

echo "🧪 Test de Configuration - Fonctionnalités de Présence"
echo "======================================================"
echo ""

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Compteur de tests
PASSED=0
FAILED=0

# Fonction de test
test_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✓${NC} $2"
        ((PASSED++))
    else
        echo -e "${RED}✗${NC} $2"
        echo -e "  ${YELLOW}→${NC} Fichier manquant: $1"
        ((FAILED++))
    fi
}

test_route() {
    ROUTE_EXISTS=$(php bin/console debug:router | grep -c "$1")
    if [ "$ROUTE_EXISTS" -gt 0 ]; then
        echo -e "${GREEN}✓${NC} Route $1 existe"
        ((PASSED++))
    else
        echo -e "${RED}✗${NC} Route $1 manquante"
        ((FAILED++))
    fi
}

echo "📁 Vérification des Fichiers"
echo "----------------------------"
test_file "src/Entity/MessageReadReceipt.php" "Entité MessageReadReceipt"
test_file "src/Entity/UserPresence.php" "Entité UserPresence"
test_file "src/Repository/MessageReadReceiptRepository.php" "Repository MessageReadReceipt"
test_file "src/Repository/UserPresenceRepository.php" "Repository UserPresence"
test_file "src/Controller/UserPresenceController.php" "Contrôleur UserPresence"
test_file "public/presence_manager.js" "Script JavaScript presence_manager.js"
echo ""

echo "🔌 Vérification des Routes"
echo "--------------------------"
test_route "presence_heartbeat"
test_route "presence_typing"
test_route "presence_typing_users"
test_route "presence_online_users"
test_route "message_mark_read"
echo ""

echo "🗄️  Vérification de la Base de Données"
echo "--------------------------------------"
php bin/console doctrine:schema:validate > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Schéma de base de données valide"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} Schéma de base de données invalide"
    echo -e "  ${YELLOW}→${NC} Exécuter: php bin/console doctrine:migrations:migrate"
    ((FAILED++))
fi

# Vérifier si les tables existent
TABLE_EXISTS=$(php bin/console dbal:run-sql "SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'message_read_receipt'" 2>/dev/null | grep -c "1")
if [ "$TABLE_EXISTS" -gt 0 ]; then
    echo -e "${GREEN}✓${NC} Table message_read_receipt existe"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} Table message_read_receipt manquante"
    ((FAILED++))
fi

TABLE_EXISTS=$(php bin/console dbal:run-sql "SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'user_presence'" 2>/dev/null | grep -c "1")
if [ "$TABLE_EXISTS" -gt 0 ]; then
    echo -e "${GREEN}✓${NC} Table user_presence existe"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} Table user_presence manquante"
    ((FAILED++))
fi
echo ""

echo "📊 Résultats"
echo "------------"
TOTAL=$((PASSED + FAILED))
echo -e "Tests réussis: ${GREEN}$PASSED${NC}/$TOTAL"
if [ $FAILED -gt 0 ]; then
    echo -e "Tests échoués: ${RED}$FAILED${NC}/$TOTAL"
    echo ""
    echo -e "${YELLOW}⚠️  Certains éléments sont manquants!${NC}"
    echo "Consultez GUIDE_TEST_PRESENCE_FEATURES.md pour plus de détails."
    exit 1
else
    echo ""
    echo -e "${GREEN}✅ Tous les tests sont passés!${NC}"
    echo ""
    echo "Prochaines étapes:"
    echo "1. Intégrer le script dans le template du chatroom"
    echo "2. Suivre le guide: GUIDE_TEST_PRESENCE_FEATURES.md"
    echo "3. Tester avec 2 navigateurs différents"
    exit 0
fi
