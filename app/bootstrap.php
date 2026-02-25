<?php
/**
 * Bootstrap - Application initialization
 *
 * Include this file at the top of any entry point that needs authentication.
 * It handles: session config, class loading, and database connection.
 */

// ─── Secure session configuration ──────────────────────────
ini_set('session.use_strict_mode', '1');      // Reject uninitialized session IDs
ini_set('session.use_only_cookies', '1');      // No session ID in URLs
ini_set('session.cookie_httponly', '1');        // JS can't access session cookie
ini_set('session.cookie_samesite', 'Lax');     // CSRF protection at cookie level
ini_set('session.cookie_secure', '1');         // Cookie only sent over HTTPS

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Security headers ────────────────────────────────────────
header('X-Frame-Options: SAMEORIGIN');              // Prevent clickjacking
header('X-Content-Type-Options: nosniff');          // Prevent MIME-type sniffing
header('X-XSS-Protection: 1; mode=block');          // Legacy XSS filter
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data:;");

// ─── Detect application base path ─────────────────────────
// Auto-detect the URL prefix when the app lives in a subdirectory.
// e.g. if deployed at http://localhost/sales/, BASE_PATH = '/sales'
// If deployed at root http://localhost/, BASE_PATH = ''
//
// This makes the entire system portable: you can move the project
// to any folder (or to root) and all URLs will work automatically.
if (!defined('BASE_PATH')) {
    $projectRoot = realpath(__DIR__ . '/..');
    $docRoot     = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');

    if ($docRoot && $projectRoot && str_starts_with($projectRoot, $docRoot)) {
        $detected = str_replace('\\', '/', substr($projectRoot, strlen($docRoot)));
        define('BASE_PATH', rtrim($detected, '/'));
    } else {
        // Fallback: try to infer from SCRIPT_NAME
        // e.g. SCRIPT_NAME = '/sales/public/index.php' → BASE_PATH = '/sales'
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        // Walk up to find the app root (where /app/bootstrap.php lives)
        $parts = explode('/', trim($scriptDir, '/'));
        // The first segment is typically the project folder
        define('BASE_PATH', !empty($parts[0]) ? '/' . $parts[0] : '');
    }
}

// ─── URL helper function ──────────────────────────────────
// Generates an absolute URL path relative to the application root.
//
// Usage:
//   url('/public/index.php?action=login')  → '/sales/public/index.php?action=login'
//   url('/billing/')                       → '/sales/billing/'
//   url('/')                               → '/sales/'
//
// When the app moves to root, the same calls produce:
//   url('/public/index.php?action=login')  → '/public/index.php?action=login'
//   url('/billing/')                       → '/billing/'
//
// This is the ONLY way URLs should be built in the application.
if (!function_exists('url')) {
    function url(string $path = '/'): string
    {
        return BASE_PATH . $path;
    }
}

// ─── Load core classes ─────────────────────────────────────
require_once __DIR__ . '/Core/Auth.php';
require_once __DIR__ . '/Core/Csrf.php';
require_once __DIR__ . '/Core/Gate.php';
require_once __DIR__ . '/Core/Middleware.php';
require_once __DIR__ . '/Core/FileStorageService.php';
require_once __DIR__ . '/Controllers/AuthController.php';

// ─── Database connection (centralizada) ────────────────────
require_once __DIR__ . '/../config/database.php';
$pdo = getDBConnection();

// ─── Initialize Auth & Gate ────────────────────────────────
Auth::init($pdo);
Gate::init($pdo);

// ─── Ensure users table exists ─────────────────────────────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `users` (
        `user_id`       INT AUTO_INCREMENT PRIMARY KEY,
        `username`      VARCHAR(100) NOT NULL UNIQUE,
        `email`         VARCHAR(200) NOT NULL UNIQUE,
        `password_hash` VARCHAR(255) NOT NULL,
        `full_name`     VARCHAR(200) NOT NULL,
        `timezone`      VARCHAR(50)  DEFAULT 'America/Chicago',
        `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
        `role_id`       INT          DEFAULT 1,
        INDEX `idx_email` (`email`),
        INDEX `idx_role`  (`role_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// ─── Add role_id column if missing (table created before role support) ──
$columns = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'role_id'")->fetchAll();
if (empty($columns)) {
    $pdo->exec("ALTER TABLE `users` ADD COLUMN `role_id` INT DEFAULT 1");
    $pdo->exec("ALTER TABLE `users` ADD INDEX `idx_role` (`role_id`)");
}

// ─── Seed a default admin user if table is empty ───────────
$count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
if ((int)$count === 0) {
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, full_name, role_id)
        VALUES (:username, :email, :password_hash, :full_name, :role_id)
    ");
    $stmt->execute([
        ':username'      => 'admin',
        ':email'         => 'admin@primefacility.com',
        ':password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
        ':full_name'     => 'Administrator',
        ':role_id'       => 1,
    ]);
}

// ─── Authorization tables: roles, modules, role_module ─────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `roles` (
        `role_id`   INT AUTO_INCREMENT PRIMARY KEY,
        `name`      VARCHAR(50) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS `modules` (
        `module_id` INT AUTO_INCREMENT PRIMARY KEY,
        `name`      VARCHAR(100) NOT NULL,
        `slug`      VARCHAR(100) NOT NULL UNIQUE,
        `icon`      VARCHAR(50)  DEFAULT '',
        `url`       VARCHAR(255) DEFAULT ''
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS `role_module` (
        `role_id`   INT NOT NULL,
        `module_id` INT NOT NULL,
        PRIMARY KEY (`role_id`, `module_id`),
        FOREIGN KEY (`role_id`)   REFERENCES `roles`(`role_id`)   ON DELETE CASCADE,
        FOREIGN KEY (`module_id`) REFERENCES `modules`(`module_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// ─── Seed roles ────────────────────────────────────────────
$rolesCount = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
if ((int)$rolesCount === 0) {
    $pdo->exec("
        INSERT INTO `roles` (`role_id`, `name`) VALUES
        (1, 'Admin'),
        (2, 'Leader'),
        (3, 'Vendedor'),
        (4, 'Empleado'),
        (5, 'Contabilidad')
    ");
}

// ─── Seed modules ──────────────────────────────────────────
$modulesCount = $pdo->query("SELECT COUNT(*) FROM modules")->fetchColumn();
if ((int)$modulesCount === 0) {
    $pdo->exec("
        INSERT INTO `modules` (`module_id`, `name`, `slug`, `icon`, `url`) VALUES
        (1, 'Form for Contract',     'contracts',     '&#x1F4BC;', 'form_contract/'),
        (2, 'Contract Generator',    'generator',     '&#x1F4DD;', 'contract_generator/'),
        (3, 'Employee Work Report',  'work_report',   '&#x1F9F9;', 'employee_work_report/'),
        (4, 'Reports',              'reports',        '&#x1F4CB;', 'reports/'),
        (5, 'Billing / Accounting', 'billing',        '&#x1F4B0;', 'billing/'),
        (6, 'Admin Panel',          'admin_panel',    '&#x2699;&#xFE0F;',  'modules/admin/'),
        (7, 'Calendar',             'calendar',       '&#x1F4C5;', 'calendar/')
    ");
}

// ─── Ensure contract_generator URL is always correct ─────────────────
try {
    $pdo->exec("
        UPDATE `modules`
        SET `url` = 'contract_generator/'
        WHERE `slug` = 'generator'
          AND `url` != 'contract_generator/'
    ");
    // Clear session-cached modules so the corrected URL takes effect
    foreach (array_keys($_SESSION ?? []) as $k) {
        if (str_starts_with($k, 'gate_modules_')) {
            unset($_SESSION[$k]);
        }
    }
} catch (PDOException $e) {
    // skip if table not ready
}

// ─── Seed role_module permissions ──────────────────────────
$rmCount = $pdo->query("SELECT COUNT(*) FROM role_module")->fetchColumn();
if ((int)$rmCount === 0) {
    $pdo->exec("
        INSERT INTO `role_module` (`role_id`, `module_id`) VALUES
        -- contracts (1): Admin, Leader, Vendedor
        (1, 1), (2, 1), (3, 1),
        -- generator (2): Admin, Leader
        (1, 2), (2, 2),
        -- work_report (3): Admin, Leader, Empleado
        (1, 3), (2, 3), (4, 3),
        -- reports (4): Admin, Leader
        (1, 4), (2, 4),
        -- billing (5): Admin, Leader, Contabilidad
        (1, 5), (2, 5), (5, 5),
        -- admin_panel (6): Admin
        (1, 6),
        -- calendar (7): Admin, Leader
        (1, 7), (2, 7)
    ");
}

// ─── Ensure Vendedor has calendar module access ─────────────────
try {
    $calExists = $pdo->query(
        "SELECT COUNT(*) FROM role_module WHERE role_id = 3 AND module_id = 7"
    )->fetchColumn();
    if ((int)$calExists === 0) {
        $pdo->exec("INSERT INTO role_module (role_id, module_id) VALUES (3, 7)");
    }
} catch (PDOException $e) {
    // skip if already present or table not ready
}

// ─── Add foreign key from users.role_id → roles if not present ──
try {
    $fkCheck = $pdo->query("
        SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'role_id'
          AND REFERENCED_TABLE_NAME = 'roles'
    ")->fetchColumn();
    if ((int)$fkCheck === 0) {
        $pdo->exec("ALTER TABLE `users` ADD FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`) ON DELETE SET NULL");
    }
} catch (PDOException $e) {
    // Silently skip if FK already exists or constraint fails
}

// ─── RBAC: Add description & created_at to roles if missing ──
try {
    $cols = $pdo->query("SHOW COLUMNS FROM `roles` LIKE 'description'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE `roles` ADD COLUMN `description` VARCHAR(255) DEFAULT '' AFTER `name`");
    }
    $cols = $pdo->query("SHOW COLUMNS FROM `roles` LIKE 'created_at'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE `roles` ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `description`");
    }
} catch (PDOException $e) {
    // skip
}

// ─── RBAC: Permissions table ──────────────────────────────────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `permissions` (
        `permission_id` INT AUTO_INCREMENT PRIMARY KEY,
        `name`          VARCHAR(100) NOT NULL,
        `description`   VARCHAR(255) DEFAULT '',
        `perm_key`      VARCHAR(100) NOT NULL UNIQUE,
        `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// ─── RBAC: Role ↔ Permission junction ─────────────────────────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `role_permission` (
        `role_id`       INT NOT NULL,
        `permission_id` INT NOT NULL,
        PRIMARY KEY (`role_id`, `permission_id`),
        FOREIGN KEY (`role_id`)       REFERENCES `roles`(`role_id`)             ON DELETE CASCADE,
        FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`permission_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// ─── Add photo column to users if missing ─────────────────────
try {
    $cols = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'photo'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `photo` VARCHAR(255) DEFAULT NULL AFTER `full_name`");
    }
} catch (PDOException $e) {
    // skip
}

// ─── Seed default permissions ─────────────────────────────────
$permCount = $pdo->query("SELECT COUNT(*) FROM permissions")->fetchColumn();
if ((int)$permCount === 0) {
    $pdo->exec("
        INSERT INTO `permissions` (`name`, `description`, `perm_key`) VALUES
        ('Manage Users',       'Create, edit and list users',       'manage_users'),
        ('Manage Roles',       'Create, edit and list roles',       'manage_roles'),
        ('Manage Permissions', 'Create, edit and list permissions', 'manage_permissions'),
        ('View Reports',       'Access the reports module',         'view_reports'),
        ('Manage Billing',     'Access billing / accounting',       'manage_billing'),
        ('Manage Calendar',    'Access the calendar module',        'manage_calendar')
    ");

    // Assign all permissions to Admin role
    $pdo->exec("
        INSERT IGNORE INTO `role_permission` (`role_id`, `permission_id`)
        SELECT 1, `permission_id` FROM `permissions`
    ");
}

// ─── Login rate-limiting table ──────────────────────────────────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `login_attempts` (
        `id`         INT AUTO_INCREMENT PRIMARY KEY,
        `ip_address` VARCHAR(45) NOT NULL,
        `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_ip_time` (`ip_address`, `attempted_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// ─── Add must_change_password column to users if missing ──────────
try {
    $cols = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'must_change_password'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `must_change_password` TINYINT(1) DEFAULT 0 AFTER `role_id`");
        // Mark the seeded admin user so they are forced to change on first login
        $pdo->exec("UPDATE `users` SET `must_change_password` = 1 WHERE `username` = 'admin' AND `password_hash` != ''");
    }
} catch (PDOException $e) {
    // skip
}
