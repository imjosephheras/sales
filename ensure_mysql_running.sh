#!/bin/bash

# ============================================
# ENSURE MYSQL IS RUNNING
# Auto-start script for MySQL/MariaDB
# ============================================

echo "🔍 Checking MySQL status..."

# Check if MySQL is running
if ps aux | grep -v grep | grep -E 'mysqld|mariadb' > /dev/null; then
    echo "✅ MySQL is already running"
    exit 0
fi

echo "⚠️  MySQL is not running. Starting..."

# Fix /tmp permissions if needed
chmod 1777 /tmp 2>/dev/null

# Start MySQL in background
mysqld --user=mysql > /tmp/mysql.log 2>&1 &

# Wait for MySQL to start
sleep 5

# Verify it started
if ps aux | grep -v grep | grep mysqld > /dev/null; then
    echo "✅ MySQL started successfully"

    # Test connection
    php -r "
    try {
        \$pdo = new PDO('mysql:host=localhost;dbname=form', 'root', '');
        echo '✅ Connection to database \"form\" successful\n';
    } catch (Exception \$e) {
        echo '⚠️  Warning: ' . \$e->getMessage() . '\n';
        echo 'You may need to create the database or import the schema\n';
    }
    " 2>/dev/null
else
    echo "❌ Failed to start MySQL"
    echo "Check logs at /tmp/mysql.log or /var/log/mysql/error.log"
    exit 1
fi
