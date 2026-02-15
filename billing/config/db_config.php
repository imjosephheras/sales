<?php
/**
 * Database Configuration - Billing Module
 * Usa la configuracion centralizada de base de datos.
 */

require_once __DIR__ . '/../../config/database.php';

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
        `service_name` VARCHAR(200) DEFAULT NULL,
        `total_amount` VARCHAR(100) DEFAULT NULL,
        `pdf_path` VARCHAR(500) DEFAULT NULL,
        `status` ENUM('pending', 'completed') DEFAULT 'pending',
        `notes` TEXT DEFAULT NULL,
        `completed_by` INT DEFAULT NULL,
        `completed_by_name` VARCHAR(200) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `completed_at` TIMESTAMP NULL DEFAULT NULL,
        INDEX `idx_status` (`status`),
        INDEX `idx_order` (`order_number`),
        INDEX `idx_created` (`created_at`),
        INDEX `idx_request_id` (`request_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql);

    // Add missing columns to existing table
    $columnsToAdd = [
        'service_name' => "ALTER TABLE `billing_documents` ADD COLUMN `service_name` VARCHAR(200) DEFAULT NULL",
        'total_amount' => "ALTER TABLE `billing_documents` ADD COLUMN `total_amount` VARCHAR(100) DEFAULT NULL",
        'completed_by' => "ALTER TABLE `billing_documents` ADD COLUMN `completed_by` INT DEFAULT NULL",
        'completed_by_name' => "ALTER TABLE `billing_documents` ADD COLUMN `completed_by_name` VARCHAR(200) DEFAULT NULL",
    ];

    foreach ($columnsToAdd as $col => $alterSql) {
        try {
            $pdo->query("SELECT `$col` FROM `billing_documents` LIMIT 1");
        } catch (Exception $e) {
            $pdo->exec($alterSql);
        }
    }
}

$pdo = getDBConnection();
initializeBillingTable($pdo);
?>
