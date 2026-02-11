#!/bin/bash
# Start development environment for Sales Management System
# Usage: bash start.sh

set -e

echo "=== Sales Management System - Dev Server ==="

# 1. Start MariaDB if not running
if ! mariadb -u root -e "SELECT 1" &>/dev/null 2>&1; then
    echo "[*] Starting MariaDB..."
    mkdir -p /run/mysqld && chown mysql:mysql /run/mysqld
    mysqld --user=mysql --datadir=/var/lib/mysql --socket=/run/mysqld/mysqld.sock &>/tmp/mysql.log &
    sleep 3

    if mariadb -u root -e "SELECT 1" &>/dev/null 2>&1; then
        echo "[OK] MariaDB started."
    else
        echo "[ERROR] Could not start MariaDB. Check /tmp/mysql.log"
        exit 1
    fi
else
    echo "[OK] MariaDB already running."
fi

# 2. Create database if it doesn't exist
mariadb -u root -e "CREATE DATABASE IF NOT EXISTS \`form\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
echo "[OK] Database 'form' ready."

# 3. Start PHP built-in server
PORT=${1:-8080}
echo "[*] Starting PHP server on http://localhost:$PORT ..."
echo ""
echo "  Login page: http://localhost:$PORT/public/index.php?action=login"
echo "  Default credentials: admin / admin123"
echo ""
echo "  Press Ctrl+C to stop the server."
echo ""

cd /home/user/sales
php -S localhost:$PORT
