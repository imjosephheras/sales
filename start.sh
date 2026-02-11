#!/bin/bash
# Start development environment for Sales Management System
# Usage: bash start.sh

set -e

echo "=== Sales Management System - Dev Server ==="

# 0. Ensure /tmp is writable (required by InnoDB for temp files)
chmod 1777 /tmp 2>/dev/null || true

# 1. Start MariaDB if not running
if ! mariadb -u root -e "SELECT 1" &>/dev/null 2>&1; then
    echo "[*] Starting MariaDB..."
    mkdir -p /run/mysqld && chown mysql:mysql /run/mysqld

    # Initialize data directory if system tables are missing
    if [ ! -f /var/lib/mysql/mysql/user.frm ] && [ ! -f /var/lib/mysql/mysql/global_priv.MAD ]; then
        echo "[*] Initializing MariaDB data directory..."
        mysql_install_db --user=mysql --datadir=/var/lib/mysql &>/dev/null
    fi

    mysqld --user=mysql --datadir=/var/lib/mysql --socket=/run/mysqld/mysqld.sock &>/tmp/mysql.log &
    sleep 3

    # If root can't connect, fix authentication via skip-grant-tables
    if ! mariadb -u root -e "SELECT 1" &>/dev/null 2>&1; then
        echo "[*] Fixing root authentication..."
        kill $(pgrep mysqld) 2>/dev/null; sleep 2
        mysqld --user=mysql --datadir=/var/lib/mysql --socket=/run/mysqld/mysqld.sock --skip-grant-tables &>/tmp/mysql.log &
        sleep 3
        mariadb -u root --socket=/run/mysqld/mysqld.sock -e "FLUSH PRIVILEGES; ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING ''; FLUSH PRIVILEGES;" &>/dev/null
        kill $(pgrep mysqld) 2>/dev/null; sleep 2
        mysqld --user=mysql --datadir=/var/lib/mysql --socket=/run/mysqld/mysqld.sock &>/tmp/mysql.log &
        sleep 3
    fi

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
