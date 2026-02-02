<?php
// ============================================================
// db_config.php - Service Confirmation Module (Module 10)
// Database Configuration
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'form');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Get database connection
 */
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );

        // Ensure service confirmation columns exist
        addServiceConfirmationColumns($pdo);

        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Database connection error: ' . $e->getMessage()
            ]);
            exit;
        }

        die("Database connection failed. Please check your configuration.");
    }
}

/**
 * Add service confirmation columns to requests table if they don't exist
 */
function addServiceConfirmationColumns($pdo) {
    try {
        // Check and add service_status column
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'service_status'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `service_status` ENUM('pending', 'completed', 'not_completed') DEFAULT 'pending'");
        }

        // Check and add service_completed_at column
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'service_completed_at'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `service_completed_at` TIMESTAMP NULL DEFAULT NULL");
        }

        // Check and add ready_to_invoice column
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'ready_to_invoice'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `ready_to_invoice` TINYINT(1) DEFAULT 0");
        }

        // Check and add final_pdf_path column
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'final_pdf_path'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `final_pdf_path` VARCHAR(500) DEFAULT NULL");
        }

        // Check and add Document_Date column (Section 9)
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'Document_Date'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `Document_Date` DATE DEFAULT NULL COMMENT 'Fecha del documento (Q30)'");
        }

        // Check and add Work_Date column (Section 9)
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'Work_Date'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `Work_Date` DATE DEFAULT NULL COMMENT 'Fecha del trabajo (Q31)'");
        }

        // Check and add order_number column (Section 9)
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'order_number'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `order_number` INT DEFAULT NULL COMMENT 'Order number 1000-9999, reusable'");
        }

        // Check and add Order_Nomenclature column (Section 9)
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'Order_Nomenclature'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `Order_Nomenclature` VARCHAR(50) DEFAULT NULL COMMENT 'Auto-generated nomenclature'");
        }

        // Add indexes if they don't exist
        $stmt = $pdo->query("SHOW INDEX FROM `requests` WHERE Key_name = 'idx_service_status'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD INDEX `idx_service_status` (`service_status`)");
        }

        $stmt = $pdo->query("SHOW INDEX FROM `requests` WHERE Key_name = 'idx_ready_to_invoice'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD INDEX `idx_ready_to_invoice` (`ready_to_invoice`)");
        }

    } catch (Exception $e) {
        error_log("Error adding service confirmation columns: " . $e->getMessage());
    }
}
?>
