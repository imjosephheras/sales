#!/bin/bash

# ============================================
# QUICK START: MySQL/MariaDB Service
# ============================================

echo "🔍 Verificando estado de MySQL..."
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if MySQL process is running
if ps aux | grep -v grep | grep -E 'mysqld|mariadb' > /dev/null; then
    echo -e "${GREEN}✅ MySQL ya está corriendo${NC}"
    ps aux | grep -v grep | grep -E 'mysqld|mariadb' | head -3
    echo ""
else
    echo -e "${YELLOW}⚠️  MySQL NO está corriendo. Intentando iniciar...${NC}"
    echo ""

    # Fix /tmp permissions if needed
    chmod 1777 /tmp 2>/dev/null

    # Try different methods to start MySQL
    if command -v systemctl &> /dev/null; then
        echo "Intentando: systemctl start mysql"
        systemctl start mysql 2>&1 || true
        sleep 2

        if ! ps aux | grep -v grep | grep mysqld > /dev/null; then
            echo "Intentando: systemctl start mariadb"
            systemctl start mariadb 2>&1 || true
            sleep 2
        fi
    fi

    # If still not running, try direct mysqld start
    if ! ps aux | grep -v grep | grep mysqld > /dev/null; then
        echo "Intentando: mysqld --user=mysql (en background)"
        mysqld --user=mysql > /tmp/mysql.log 2>&1 &
        sleep 3
    fi
fi

echo ""
echo "🔍 Verificando conexión..."
echo ""

# Test connection
php -r "
try {
    \$pdo = new PDO('mysql:host=localhost;dbname=form', 'root', '');
    echo '✅ Conexión exitosa a la base de datos \"form\"' . PHP_EOL;

    // Count requests
    \$stmt = \$pdo->query('SELECT COUNT(*) as total FROM requests');
    \$total = \$stmt->fetch()['total'];
    echo '📊 Total de solicitudes en BD: ' . \$total . PHP_EOL;

    // Count pending
    \$stmt = \$pdo->query(\"SELECT COUNT(*) as count FROM requests WHERE status IN ('pending', 'in_progress')\");
    \$pending = \$stmt->fetch()['count'];
    echo '📥 Solicitudes pendientes: ' . \$pending . PHP_EOL;

} catch (Exception \$e) {
    echo '❌ Error de conexión: ' . \$e->getMessage() . PHP_EOL;
    echo PHP_EOL;
    echo 'Posibles soluciones:' . PHP_EOL;
    echo '1. Verifica que MySQL esté instalado: mysql --version' . PHP_EOL;
    echo '2. Verifica el estado del servicio: sudo systemctl status mysql' . PHP_EOL;
    echo '3. Lee el archivo FIX_MYSQL_NOT_RUNNING.md para más ayuda' . PHP_EOL;
    exit(1);
}
"

echo ""
echo "🌐 Puedes acceder al Contract Generator en:"
echo "   http://localhost/sales/contract_generator/contract_generator/index.php"
echo ""
