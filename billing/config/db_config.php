<?php
/**
 * Database Configuration - Billing Module
 * Uses centralized database configuration.
 * Reads from forms + contract_items (single source of truth).
 */

require_once __DIR__ . '/../../config/database.php';

// Ensure form_contract db_config is loaded for table initialization
require_once __DIR__ . '/../../form_contract/db_config.php';

/**
 * Initialize billing_documents table
 */
function initializeBillingDocuments($pdo) {
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `billing_documents` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `form_id` INT DEFAULT NULL,
      `order_number` VARCHAR(100) DEFAULT NULL,
      `client_name` VARCHAR(200) DEFAULT NULL,
      `company_name` VARCHAR(200) DEFAULT NULL,
      `document_type` VARCHAR(50) DEFAULT NULL,
      `pdf_path` VARCHAR(500) DEFAULT NULL,
      `service_name` VARCHAR(200) DEFAULT NULL,
      `total_amount` VARCHAR(100) DEFAULT NULL,
      `notes` TEXT DEFAULT NULL,
      `status` VARCHAR(50) DEFAULT 'pending',
      `completed_by` INT DEFAULT NULL,
      `completed_by_name` VARCHAR(200) DEFAULT NULL,
      `completed_at` TIMESTAMP NULL DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX `idx_form_id` (`form_id`),
      INDEX `idx_status` (`status`),
      INDEX `idx_order` (`order_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Add form_id column if it doesn't exist (migration from request_id)
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `billing_documents` LIKE 'form_id'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `billing_documents` ADD COLUMN `form_id` INT DEFAULT NULL AFTER `id`");
            $pdo->exec("ALTER TABLE `billing_documents` ADD INDEX `idx_form_id` (`form_id`)");
        }
    } catch (Exception $e) {
        // Column already exists
    }

    // Add missing columns
    $columnsToCheck = [
        'service_name'       => 'VARCHAR(200) DEFAULT NULL',
        'total_amount'       => 'VARCHAR(100) DEFAULT NULL',
        'completed_by'       => 'INT DEFAULT NULL',
        'completed_by_name'  => 'VARCHAR(200) DEFAULT NULL',
        'completed_at'       => 'TIMESTAMP NULL DEFAULT NULL',
    ];

    foreach ($columnsToCheck as $col => $def) {
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM `billing_documents` LIKE '$col'");
            if ($stmt->rowCount() == 0) {
                $pdo->exec("ALTER TABLE `billing_documents` ADD COLUMN `$col` $def");
            }
        } catch (Exception $e) {
            // Ignore
        }
    }
}

/**
 * Initialize document_attachments table
 * Stores additional file attachments for any billing document (JWO, Contract, Proposal, etc.)
 */
function initializeDocumentAttachments($pdo) {
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `document_attachments` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `document_id` INT NOT NULL,
      `document_type` VARCHAR(50) NOT NULL COMMENT 'JWO, Contract, Proposal, Quote, etc.',
      `file_type` VARCHAR(50) NOT NULL COMMENT 'timesheet, invoice, po, other',
      `file_name` VARCHAR(255) NOT NULL,
      `file_path` VARCHAR(500) NOT NULL,
      `uploaded_by` VARCHAR(200) DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX `idx_document` (`document_id`, `document_type`),
      INDEX `idx_file_type` (`file_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
}

$pdo = getDBConnection();
initializeBillingDocuments($pdo);
initializeDocumentAttachments($pdo);
?>
