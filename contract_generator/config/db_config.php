<?php
/**
 * Database Configuration - Contract Generator Module
 * Uses centralized database configuration.
 * No requests table - reads directly from forms + contract_items.
 */

require_once __DIR__ . '/../../config/database.php';

// Ensure form_contract db_config is loaded for table initialization
require_once __DIR__ . '/../../form_contract/db_config.php';

/**
 * Initialize the docnum_counter table for document number generation
 */
function initializeDocnumCounter($pdo) {
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS `docnum_counter` (
        `id` INT PRIMARY KEY DEFAULT 1,
        `last_number` INT NOT NULL DEFAULT 100000,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($createTableSQL);

    // Insert initial row if not exists
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM `docnum_counter` WHERE id = 1");
    $result = $stmt->fetch();
    if ($result['cnt'] == 0) {
        $pdo->exec("INSERT INTO `docnum_counter` (id, last_number) VALUES (1, 100000)");
    }
}

// Initialize module tables
$pdo = getDBConnection();
initializeDocnumCounter($pdo);
?>
