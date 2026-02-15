<?php
// ============================================================
// db_config.php - Service Confirmation Module (Module 10)
// ============================================================
// Usa la configuracion centralizada de base de datos.
// ============================================================

require_once __DIR__ . '/../../config/database.php';

/**
 * Add service confirmation columns to requests table if they don't exist
 */
function addServiceConfirmationColumns($pdo) {
    try {
        // Check and add service_status column
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'service_status'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `service_status` ENUM('pending', 'scheduled', 'confirmed', 'in_progress', 'completed', 'not_completed', 'cancelled') DEFAULT 'pending'");
        } else {
            // Update existing ENUM to include all status values
            $colInfo = $stmt->fetch();
            if (strpos($colInfo['Type'], 'scheduled') === false) {
                $pdo->exec("ALTER TABLE `requests` MODIFY COLUMN `service_status` ENUM('pending', 'scheduled', 'confirmed', 'in_progress', 'completed', 'not_completed', 'cancelled') DEFAULT 'pending'");
            }
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

        // Check and add task_tracking column (JSON for task checklist)
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'task_tracking'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `task_tracking` JSON DEFAULT NULL COMMENT 'JSON con estado de tareas: site_visit, quote_sent, contract_signed, staff_assigned, work_started, work_completed, client_approved, invoice_ready'");
        }

        // Check and add task_tracking_updated_at column
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'task_tracking_updated_at'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `task_tracking_updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Ultima actualizacion del tracking'");
        }

        // Check and add admin_notes column
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'admin_notes'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `admin_notes` TEXT DEFAULT NULL COMMENT 'Notas internas del administrador'");
        }

        // Check and add completed_at column
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'completed_at'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `completed_at` TIMESTAMP NULL DEFAULT NULL");
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

// Inicializar columnas del modulo service_confirmation
$pdo = getDBConnection();
addServiceConfirmationColumns($pdo);
?>
