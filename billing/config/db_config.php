<?php
/**
 * Database Configuration - Billing Module
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'form');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

date_default_timezone_set('America/Chicago');

/**
 * Initialize billing_documents table
 */
function initializeBillingTable($pdo) {
    $sql = "
    CREATE TABLE IF NOT EXISTS `billing_documents` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `request_id` INT DEFAULT NULL,
        `order_number` VARCHAR(100) NOT NULL,
        `client_name` VARCHAR(200) DEFAULT NULL,
        `company_name` VARCHAR(200) DEFAULT NULL,
        `document_type` VARCHAR(50) DEFAULT NULL,
        `pdf_path` VARCHAR(500) DEFAULT NULL,
        `status` ENUM('pending', 'completed') DEFAULT 'pending',
        `notes` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `completed_at` TIMESTAMP NULL DEFAULT NULL,
        INDEX `idx_status` (`status`),
        INDEX `idx_order` (`order_number`),
        INDEX `idx_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql);
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    initializeBillingTable($pdo);
} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}
?>
