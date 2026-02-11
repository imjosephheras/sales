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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Load core classes ─────────────────────────────────────
require_once __DIR__ . '/Core/Auth.php';
require_once __DIR__ . '/Core/Csrf.php';
require_once __DIR__ . '/Core/Middleware.php';
require_once __DIR__ . '/Controllers/AuthController.php';

// ─── Database connection ───────────────────────────────────
// Reuse existing DB config constants
$dbHost = 'localhost';
$dbName = 'form';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Auth DB connection failed: ' . $e->getMessage());
    die('Database connection failed.');
}

// ─── Initialize Auth ───────────────────────────────────────
Auth::init($pdo);

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
