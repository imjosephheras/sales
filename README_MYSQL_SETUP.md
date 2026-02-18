# MySQL Setup for Contract Generator

## Overview

The Contract Generator application requires MySQL to store and retrieve form submissions. This document explains the setup process and troubleshooting steps.

## Quick Start

### 1. Start MySQL

Run the startup script:

```bash
cd /home/user/sales
bash start_mysql.sh
```

This script will:
- Check if MySQL is running
- Start MySQL if not running
- Verify database connection
- Show the count of pending requests

### 2. Access the Application

Once MySQL is running, you can access:

- **Form Submission**: http://localhost/sales/form_contract/
- **Contract Generator**: http://localhost/sales/contract_generator/

## Database Schema

### Database: `form`
### Table: `requests`

The `requests` table stores all form submissions with the following key fields:

- **id**: Auto-increment primary key
- **Service_Type**: Type of service requested
- **Company_Name**: Client company name
- **client_name**: Contact person name
- **Email**: Contact email
- **Priority**: Request priority (Urgent, High, Normal, Low)
- **status**: Request status (pending, in_progress, completed)
- **created_at**: Timestamp of submission
- Plus ~50 additional fields for detailed form data

## Setup Process (One-Time)

If you need to set up MySQL from scratch:

### 1. Install MySQL (if not installed)

```bash
apt-get update
apt-get install -y mysql-server
```

### 2. Fix Permissions

```bash
chmod 1777 /tmp
```

### 3. Initialize MySQL

```bash
rm -rf /var/lib/mysql/*
mysqld --initialize-insecure --user=mysql
```

### 4. Start MySQL

```bash
mysqld --user=mysql > /tmp/mysql.log 2>&1 &
sleep 5
```

### 5. Create Database and Table

```bash
# Create database
php -r "\$pdo = new PDO('mysql:host=localhost', 'root', ''); \$pdo->exec('CREATE DATABASE IF NOT EXISTS form CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');"

# Create table
mysql -u root form < /home/user/sales/create_requests_table.sql
```

### 6. Verify Setup

```bash
php -r "try { \$pdo = new PDO('mysql:host=localhost;dbname=form', 'root', ''); echo 'Connection successful\n'; \$stmt = \$pdo->query('SELECT COUNT(*) FROM requests'); echo 'Table exists with ' . \$stmt->fetchColumn() . ' records\n'; } catch (Exception \$e) { echo 'Error: ' . \$e->getMessage() . '\n'; }"
```

## Common Issues

### Issue: "No such file or directory" Error

**Cause**: MySQL is not running

**Solution**:
```bash
bash /home/user/sales/start_mysql.sh
```

### Issue: "Permission denied" on /tmp

**Cause**: Incorrect /tmp permissions

**Solution**:
```bash
chmod 1777 /tmp
```

### Issue: Forms Submit But Don't Appear

**Symptoms**: Form shows success but Contract Generator inbox is empty

**Cause**: MySQL stopped or database connection failed

**Diagnosis**:
```bash
# Check if MySQL is running
ps aux | grep mysqld

# Check database
php -r "\$pdo = new PDO('mysql:host=localhost;dbname=form', 'root', ''); echo 'OK\n';"
```

**Solution**: Restart MySQL using the startup script

## Application Flow

```
┌─────────────────────────────────────────────┐
│ 1. User fills form at /form_contract/      │
└─────────────────┬───────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────┐
│ 2. Form submits to enviar_correo.php       │
│    - Validates data                         │
│    - Uploads photos                         │
│    - Generates PDF                          │
└─────────────────┬───────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────┐
│ 3. Saves to MySQL database                  │
│    INSERT INTO requests (...) VALUES (...)  │
│    Status: 'pending'                        │
└─────────────────┬───────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────┐
│ 4. User goes to Contract Generator          │
│    /contract_generator/  │
└─────────────────┬───────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────┐
│ 5. Frontend loads pending requests          │
│    JS: fetch('controllers/                  │
│          get_pending_requests.php')         │
└─────────────────┬───────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────┐
│ 6. PHP queries database                     │
│    SELECT * FROM requests                   │
│    WHERE status IN ('pending',              │
│                     'in_progress')          │
└─────────────────┬───────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────┐
│ 7. Displays in inbox sidebar                │
│    User can click to edit and generate      │
│    contract                                 │
└─────────────────────────────────────────────┘
```

## Database Configuration

Configuration files:

1. **Form Submission**: `/home/user/sales/form_contract/db_config.php`
2. **Contract Generator**: `/home/user/sales/contract_generator/config/db_config.php`

Both use:
```php
DB_HOST: localhost
DB_NAME: form
DB_USER: root
DB_PASS: (empty)
```

## Maintenance

### View Pending Requests

```bash
mysql -u root form -e "SELECT id, Company_Name, client_name, Priority, status, created_at FROM requests WHERE status='pending' ORDER BY created_at DESC LIMIT 10;"
```

### Check Request Count by Status

```bash
mysql -u root form -e "SELECT status, COUNT(*) as count FROM requests GROUP BY status;"
```

### Clear Test Data

```bash
mysql -u root form -e "DELETE FROM requests WHERE Company_Name LIKE '%test%';"
```

## Auto-Start MySQL on Boot

To ensure MySQL starts automatically, add to your startup script or crontab:

```bash
# Add to crontab
@reboot /home/user/sales/ensure_mysql_running.sh

# Or create a systemd service (if systemd is available)
systemctl enable mysql
```

## Files Reference

- `start_mysql.sh` - Interactive MySQL startup and diagnostic script
- `ensure_mysql_running.sh` - Simple auto-start script for automation
- `create_requests_table.sql` - Database schema
- `FIX_MYSQL_NOT_RUNNING.md` - Detailed troubleshooting guide
- `README_MYSQL_SETUP.md` - This file

## Support

If you encounter issues:

1. Check `/var/log/mysql/error.log` for MySQL errors
2. Check `/tmp/mysql.log` for startup issues
3. Run `bash start_mysql.sh` for diagnostic information
4. Verify database connection with the test commands above

---

**Last Updated**: 2026-01-23
**Issue**: MySQL setup and data display fix
**Branch**: `claude/fix-data-display-4ppsT`
