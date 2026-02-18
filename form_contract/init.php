<?php
// ============================================================
// init.php - Form Contract Module Initialization
// ============================================================
// Inicializa las tablas del modulo form_contract.
// Las funciones de inicializacion y utilidades permanecen aqui
// porque son especificas de este modulo.
// ============================================================
// ARCHITECTURE: forms + contract_items as single source of truth
// No requests table. No separate service cost tables.
// ============================================================

require_once __DIR__ . '/../config/database.php';

/**
 * Initialize the forms table and related tables used by form_contract module
 */
function initializeFormsTable($pdo) {
    // Main forms table
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `forms` (
      `form_id` INT AUTO_INCREMENT PRIMARY KEY,

      -- SECTION 1: Request Information
      `service_type` VARCHAR(100) DEFAULT NULL,
      `request_type` VARCHAR(100) DEFAULT NULL,
      `priority` VARCHAR(50) DEFAULT NULL,
      `requested_service` VARCHAR(200) DEFAULT NULL,

      -- SECTION 2: Client Information
      `client_name` VARCHAR(200) DEFAULT NULL,
      `contact_name` VARCHAR(100) DEFAULT NULL,
      `email` VARCHAR(200) DEFAULT NULL,
      `phone` VARCHAR(50) DEFAULT NULL,
      `company_name` VARCHAR(200) DEFAULT NULL,
      `address` TEXT DEFAULT NULL,
      `city` VARCHAR(100) DEFAULT NULL,
      `state` VARCHAR(100) DEFAULT NULL,
      `is_new_client` VARCHAR(10) DEFAULT NULL,

      -- SECTION 3: Operational Details
      `site_visit_conducted` VARCHAR(10) DEFAULT NULL,
      `invoice_frequency` VARCHAR(50) DEFAULT NULL,
      `contract_duration` VARCHAR(100) DEFAULT NULL,

      -- SECTION 4: Economic Information
      `seller` VARCHAR(100) DEFAULT NULL,
      `total_cost` DECIMAL(10,2) DEFAULT NULL,
      `payment_terms` VARCHAR(100) DEFAULT NULL,
      `include_staff` VARCHAR(10) DEFAULT NULL,

      -- SECTION 5: Contract Information
      `inflation_adjustment` VARCHAR(50) DEFAULT NULL,
      `total_area` VARCHAR(100) DEFAULT NULL,
      `buildings_included` TEXT DEFAULT NULL,
      `start_date_services` DATE DEFAULT NULL,

      -- SECTION 6: Observations
      `site_observation` TEXT DEFAULT NULL,
      `additional_comments` TEXT DEFAULT NULL,
      `email_information_sent` TEXT DEFAULT NULL,

      -- SECTION 9: Document & Work Dates
      `Document_Date` DATE DEFAULT NULL,
      `Work_Date` DATE DEFAULT NULL,
      `order_number` INT DEFAULT NULL,
      `Order_Nomenclature` VARCHAR(100) DEFAULT NULL,
      `docnum` VARCHAR(100) DEFAULT NULL,

      -- Service Tracking
      `service_status` ENUM('pending', 'scheduled', 'confirmed', 'in_progress', 'completed', 'not_completed', 'cancelled') DEFAULT 'pending',
      `service_completed_at` TIMESTAMP NULL DEFAULT NULL,
      `ready_to_invoice` TINYINT(1) DEFAULT 0,
      `final_pdf_path` VARCHAR(500) DEFAULT NULL,
      `task_tracking` JSON DEFAULT NULL,
      `task_tracking_updated_at` TIMESTAMP NULL DEFAULT NULL,
      `admin_notes` TEXT DEFAULT NULL,

      -- Status & Metadata
      `status` VARCHAR(50) DEFAULT 'pending',
      `submitted_by` VARCHAR(100) DEFAULT NULL,
      `completed_at` TIMESTAMP NULL DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

      INDEX `idx_status` (`status`),
      INDEX `idx_company` (`company_name`),
      INDEX `idx_order_number` (`order_number`),
      INDEX `idx_created` (`created_at`),
      INDEX `idx_service_status` (`service_status`),
      INDEX `idx_ready_to_invoice` (`ready_to_invoice`),
      INDEX `idx_docnum` (`docnum`)

    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Unified contract items table (replaces janitorial_services_costs, kitchen_cleaning_costs, hood_vent_costs)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `contract_items` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `form_id` INT NOT NULL,
      `category` VARCHAR(50) NOT NULL COMMENT 'janitorial, kitchen, hood_vent',
      `service_name` VARCHAR(255) DEFAULT NULL,
      `service_type` VARCHAR(100) DEFAULT NULL,
      `service_time` VARCHAR(100) DEFAULT NULL,
      `frequency` VARCHAR(100) DEFAULT NULL,
      `description` TEXT DEFAULT NULL,
      `subtotal` DECIMAL(12,2) DEFAULT NULL,
      `bundle_group` VARCHAR(100) DEFAULT NULL,
      `position` INT DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX `idx_form_id` (`form_id`),
      INDEX `idx_category` (`category`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Scope of work tasks
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `scope_of_work` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `form_id` INT NOT NULL,
      `task_name` VARCHAR(255) DEFAULT NULL,
      INDEX `idx_form_id` (`form_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Calendar events (agendas)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `calendar_events` (
      `event_id` INT AUTO_INCREMENT PRIMARY KEY,
      `form_id` INT NOT NULL,
      `parent_event_id` INT DEFAULT NULL,
      `is_base_event` TINYINT(1) DEFAULT 0,
      `event_date` DATE NOT NULL,
      `description` TEXT DEFAULT NULL,
      `frequency_months` TINYINT UNSIGNED DEFAULT 0,
      `frequency_years` TINYINT UNSIGNED DEFAULT 0,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX `idx_form_id` (`form_id`),
      INDEX `idx_parent_event` (`parent_event_id`),
      INDEX `idx_event_date` (`event_date`),
      INDEX `idx_base_event` (`is_base_event`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Migrate existing forms with Work_Date to calendar_events
    migrateExistingFormsToCalendarEvents($pdo);

    // Scope sections (Section 7 - dynamic blocks)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `scope_sections` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `form_id` INT NOT NULL,
      `section_order` INT NOT NULL DEFAULT 0,
      `title` VARCHAR(255) DEFAULT NULL,
      `scope_content` TEXT DEFAULT NULL,
      INDEX `idx_form_id` (`form_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Contract staff positions
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `contract_staff` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `form_id` INT NOT NULL,
      `position` VARCHAR(150) DEFAULT NULL,
      `base_rate` DECIMAL(12,2) DEFAULT NULL,
      `percent_increase` DECIMAL(5,2) DEFAULT NULL,
      `bill_rate` DECIMAL(12,2) DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX `idx_form_id` (`form_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Form photos (Section 8)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `form_photos` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `form_id` INT NOT NULL,
      `photo_filename` VARCHAR(255) DEFAULT NULL,
      `photo_path` VARCHAR(500) DEFAULT NULL,
      `photo_size` INT DEFAULT NULL,
      `photo_type` VARCHAR(100) DEFAULT NULL,
      INDEX `idx_form_id` (`form_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Add missing columns to existing forms table
    addMissingColumnsToForms($pdo);
}

/**
 * Add missing columns to existing forms table (handles upgrades)
 */
function addMissingColumnsToForms($pdo) {
    $columnsToAdd = [
        'Document_Date'            => 'DATE DEFAULT NULL',
        'Work_Date'                => 'DATE DEFAULT NULL',
        'order_number'             => 'INT DEFAULT NULL',
        'Order_Nomenclature'       => 'VARCHAR(100) DEFAULT NULL',
        'payment_terms'            => 'VARCHAR(100) DEFAULT NULL',
        'contact_name'             => 'VARCHAR(100) DEFAULT NULL',
        'city'                     => 'VARCHAR(100) DEFAULT NULL',
        'state'                    => 'VARCHAR(100) DEFAULT NULL',
        'submitted_by'             => 'VARCHAR(100) DEFAULT NULL',
        'include_staff'            => 'VARCHAR(10) DEFAULT NULL',
        'service_status'           => "ENUM('pending', 'scheduled', 'confirmed', 'in_progress', 'completed', 'not_completed', 'cancelled') DEFAULT 'pending'",
        'service_completed_at'     => 'TIMESTAMP NULL DEFAULT NULL',
        'total_cost'               => 'DECIMAL(10,2) DEFAULT NULL',
        'ready_to_invoice'         => 'TINYINT(1) DEFAULT 0',
        'final_pdf_path'           => 'VARCHAR(500) DEFAULT NULL',
        'task_tracking'            => 'JSON DEFAULT NULL',
        'task_tracking_updated_at' => 'TIMESTAMP NULL DEFAULT NULL',
        'admin_notes'              => 'TEXT DEFAULT NULL',
        'completed_at'             => 'TIMESTAMP NULL DEFAULT NULL',
        'docnum'                   => 'VARCHAR(100) DEFAULT NULL',
    ];

    try {
        foreach ($columnsToAdd as $col => $definition) {
            $stmt = $pdo->query("SHOW COLUMNS FROM `forms` LIKE '$col'");
            if ($stmt->rowCount() == 0) {
                $pdo->exec("ALTER TABLE `forms` ADD COLUMN `$col` $definition");
            }
        }

        // Update service_status ENUM to include new values (for existing installations)
        $stmt = $pdo->query("SHOW COLUMNS FROM `forms` LIKE 'service_status'");
        if ($stmt->rowCount() > 0) {
            $colInfo = $stmt->fetch();
            if (strpos($colInfo['Type'], 'scheduled') === false) {
                $pdo->exec("ALTER TABLE `forms` MODIFY COLUMN `service_status` ENUM('pending', 'scheduled', 'confirmed', 'in_progress', 'completed', 'not_completed', 'cancelled') DEFAULT 'pending'");
            }
        }

        // Add indexes if missing
        $indexesToAdd = [
            'idx_service_status'   => 'service_status',
            'idx_ready_to_invoice' => 'ready_to_invoice',
            'idx_docnum'           => 'docnum',
        ];
        foreach ($indexesToAdd as $idxName => $idxCol) {
            $stmt = $pdo->query("SHOW INDEX FROM `forms` WHERE Key_name = '$idxName'");
            if ($stmt->rowCount() == 0) {
                try {
                    $pdo->exec("ALTER TABLE `forms` ADD INDEX `$idxName` (`$idxCol`)");
                } catch (Exception $e) {
                    // Ignore if column doesn't exist yet
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error adding missing columns to forms: " . $e->getMessage());
    }
}

// Inicializar tablas del modulo form_contract
$pdo = Database::getConnection();
initializeFormsTable($pdo);

/**
 * Migrate existing forms with Work_Date to calendar_events (runs once)
 */
function migrateExistingFormsToCalendarEvents($pdo) {
    try {
        // Check if there are any calendar events already
        $count = $pdo->query("SELECT COUNT(*) FROM calendar_events")->fetchColumn();
        if ($count > 0) return; // Already migrated

        // Find all forms with Work_Date that don't have calendar events
        $stmt = $pdo->query("
            SELECT form_id, Work_Date FROM forms
            WHERE Work_Date IS NOT NULL
        ");
        $forms = $stmt->fetchAll();

        if (empty($forms)) return;

        $insertStmt = $pdo->prepare("
            INSERT INTO calendar_events (form_id, parent_event_id, is_base_event, event_date, frequency_months, frequency_years)
            VALUES (:form_id, NULL, 1, :event_date, 0, 0)
        ");

        foreach ($forms as $form) {
            $insertStmt->execute([
                ':form_id' => $form['form_id'],
                ':event_date' => $form['Work_Date']
            ]);
        }
    } catch (Exception $e) {
        error_log("Calendar events migration: " . $e->getMessage());
    }
}

/**
 * Create or update base calendar event for a form, and generate recurring agendas
 */
function syncCalendarEvent($pdo, $formId, $workDate, $frequencyMonths = 0, $frequencyYears = 0, $description = null) {
    if (empty($workDate)) return false;

    // Clamp values
    $frequencyMonths = max(0, min(6, (int)$frequencyMonths));
    $frequencyYears = max(0, min(5, (int)$frequencyYears));

    try {
        // Check if base event exists for this form
        $stmt = $pdo->prepare("SELECT event_id, description FROM calendar_events WHERE form_id = :fid AND is_base_event = 1 LIMIT 1");
        $stmt->execute([':fid' => $formId]);
        $baseEvent = $stmt->fetch();

        if ($baseEvent) {
            // Update existing base event
            $pdo->prepare("
                UPDATE calendar_events SET
                    event_date = :event_date,
                    frequency_months = :fm,
                    frequency_years = :fy,
                    description = :desc
                WHERE event_id = :eid
            ")->execute([
                ':event_date' => $workDate,
                ':fm' => $frequencyMonths,
                ':fy' => $frequencyYears,
                ':desc' => $description !== null ? $description : $baseEvent['description'],
                ':eid' => $baseEvent['event_id']
            ]);
            $baseEventId = $baseEvent['event_id'];
        } else {
            // Create new base event
            $pdo->prepare("
                INSERT INTO calendar_events (form_id, parent_event_id, is_base_event, event_date, description, frequency_months, frequency_years)
                VALUES (:fid, NULL, 1, :event_date, :desc, :fm, :fy)
            ")->execute([
                ':fid' => $formId,
                ':event_date' => $workDate,
                ':desc' => $description,
                ':fm' => $frequencyMonths,
                ':fy' => $frequencyYears
            ]);
            $baseEventId = $pdo->lastInsertId();
        }

        // Delete all existing recurring agendas for this base event
        $pdo->prepare("DELETE FROM calendar_events WHERE parent_event_id = :pid")->execute([':pid' => $baseEventId]);

        // Generate recurring agendas if frequency > 0
        if ($frequencyMonths > 0 && $frequencyYears > 0) {
            generateRecurringAgendas($pdo, $baseEventId, $formId, $workDate, $frequencyMonths, $frequencyYears);
        }

        return $baseEventId;

    } catch (Exception $e) {
        error_log("syncCalendarEvent error: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate recurring agendas from a base event
 */
function generateRecurringAgendas($pdo, $baseEventId, $formId, $baseDate, $frequencyMonths, $frequencyYears) {
    $totalMonths = $frequencyYears * 12;
    $step = $frequencyMonths;

    if ($step <= 0 || $totalMonths <= 0) return;

    $insertStmt = $pdo->prepare("
        INSERT INTO calendar_events (form_id, parent_event_id, is_base_event, event_date, frequency_months, frequency_years)
        VALUES (:fid, :pid, 0, :event_date, :fm, :fy)
    ");

    $baseDateObj = new DateTime($baseDate);

    for ($m = $step; $m < $totalMonths; $m += $step) {
        $nextDate = clone $baseDateObj;
        $nextDate->modify("+{$m} months");

        $insertStmt->execute([
            ':fid' => $formId,
            ':pid' => $baseEventId,
            ':event_date' => $nextDate->format('Y-m-d'),
            ':fm' => $frequencyMonths,
            ':fy' => $frequencyYears
        ]);
    }
}

/**
 * Calculate and update the total_cost for a form based on its contract_items
 */
function recalculateTotalCost($pdo, $formId) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(subtotal), 0) as total FROM contract_items WHERE form_id = ?");
    $stmt->execute([$formId]);
    $total = $stmt->fetchColumn();
    $totalCost = $total > 0 ? $total : null;

    $pdo->prepare("UPDATE forms SET total_cost = ? WHERE form_id = ?")->execute([$totalCost, $formId]);

    return $totalCost;
}
