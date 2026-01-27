<?php
// ============================================================
// db_config.php - Configuración de Base de Datos
// ============================================================

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'form');  // ← CAMBIAR SEGÚN TU BASE DE DATOS
define('DB_USER', 'root');  // ← CAMBIAR SEGÚN TU USUARIO
define('DB_PASS', '');      // ← CAMBIAR SEGÚN TU CONTRASEÑA

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
      `total_cost` DECIMAL(12,2) DEFAULT NULL,
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

      -- Status & Metadata
      `status` VARCHAR(50) DEFAULT 'pending',
      `submitted_by` VARCHAR(100) DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

      INDEX `idx_status` (`status`),
      INDEX `idx_company` (`company_name`),
      INDEX `idx_order_number` (`order_number`),
      INDEX `idx_created` (`created_at`)

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

    // Janitorial services costs (Section 18)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `janitorial_services_costs` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `form_id` INT NOT NULL,
      `service_number` INT DEFAULT NULL,
      `service_type` VARCHAR(200) DEFAULT NULL,
      `service_time` VARCHAR(100) DEFAULT NULL,
      `frequency` VARCHAR(100) DEFAULT NULL,
      `description` TEXT DEFAULT NULL,
      `subtotal` DECIMAL(12,2) DEFAULT NULL,
      INDEX `idx_form_id` (`form_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Kitchen cleaning costs (Section 19)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `kitchen_cleaning_costs` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `form_id` INT NOT NULL,
      `service_number` INT DEFAULT NULL,
      `service_type` VARCHAR(200) DEFAULT NULL,
      `service_time` VARCHAR(100) DEFAULT NULL,
      `frequency` VARCHAR(100) DEFAULT NULL,
      `description` TEXT DEFAULT NULL,
      `subtotal` DECIMAL(12,2) DEFAULT NULL,
      INDEX `idx_form_id` (`form_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Hood vent costs (Section 19)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `hood_vent_costs` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `form_id` INT NOT NULL,
      `service_number` INT DEFAULT NULL,
      `service_type` VARCHAR(200) DEFAULT NULL,
      `service_time` VARCHAR(100) DEFAULT NULL,
      `frequency` VARCHAR(100) DEFAULT NULL,
      `description` TEXT DEFAULT NULL,
      `subtotal` DECIMAL(12,2) DEFAULT NULL,
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
}

/**
 * Initialize the requests table if it doesn't exist
 */
function initializeRequestsTable($pdo) {
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS `requests` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,

      -- SECTION 1: Request Information
      `Service_Type` VARCHAR(100) DEFAULT NULL,
      `Request_Type` VARCHAR(100) DEFAULT NULL,
      `Priority` VARCHAR(50) DEFAULT NULL,
      `Requested_Service` VARCHAR(200) DEFAULT NULL,

      -- SECTION 2: Client Information
      `client_name` VARCHAR(200) DEFAULT NULL,
      `Client_Title` VARCHAR(100) DEFAULT NULL,
      `Email` VARCHAR(200) DEFAULT NULL,
      `Number_Phone` VARCHAR(50) DEFAULT NULL,
      `Company_Name` VARCHAR(200) DEFAULT NULL,
      `Company_Address` TEXT DEFAULT NULL,
      `Is_New_Client` VARCHAR(10) DEFAULT NULL,

      -- SECTION 3: Operational Details
      `Site_Visit_Conducted` VARCHAR(10) DEFAULT NULL,
      `frequency_period` VARCHAR(50) DEFAULT NULL,
      `week_days` TEXT DEFAULT NULL,
      `one_time` VARCHAR(100) DEFAULT NULL,
      `Invoice_Frequency` VARCHAR(50) DEFAULT NULL,
      `Contract_Duration` VARCHAR(100) DEFAULT NULL,

      -- SECTION 4: Economic Information
      `Seller` VARCHAR(100) DEFAULT NULL,
      `PriceInput` VARCHAR(100) DEFAULT NULL,
      `Prime_Quoted_Price` VARCHAR(100) DEFAULT NULL,

      -- Janitorial Services (Section 18)
      `includeJanitorial` VARCHAR(10) DEFAULT NULL,
      `type18` TEXT DEFAULT NULL,
      `write18` TEXT DEFAULT NULL,
      `time18` TEXT DEFAULT NULL,
      `freq18` TEXT DEFAULT NULL,
      `desc18` TEXT DEFAULT NULL,
      `subtotal18` TEXT DEFAULT NULL,
      `total18` VARCHAR(50) DEFAULT NULL,
      `taxes18` VARCHAR(50) DEFAULT NULL,
      `grand18` VARCHAR(50) DEFAULT NULL,

      -- Hoodvent & Kitchen Cleaning (Section 19)
      `includeKitchen` VARCHAR(10) DEFAULT NULL,
      `type19` TEXT DEFAULT NULL,
      `time19` TEXT DEFAULT NULL,
      `freq19` TEXT DEFAULT NULL,
      `desc19` TEXT DEFAULT NULL,
      `subtotal19` TEXT DEFAULT NULL,
      `total19` VARCHAR(50) DEFAULT NULL,
      `taxes19` VARCHAR(50) DEFAULT NULL,
      `grand19` VARCHAR(50) DEFAULT NULL,

      -- Staff (Section 20)
      `includeStaff` VARCHAR(10) DEFAULT NULL,
      `base_staff` TEXT DEFAULT NULL,
      `increase_staff` TEXT DEFAULT NULL,
      `bill_staff` TEXT DEFAULT NULL,

      -- SECTION 5: Contract Information
      `inflationAdjustment` VARCHAR(50) DEFAULT NULL,
      `totalArea` VARCHAR(100) DEFAULT NULL,
      `buildingsIncluded` TEXT DEFAULT NULL,
      `startDateServices` DATE DEFAULT NULL,

      -- SECTION 6: Observations
      `Site_Observation` TEXT DEFAULT NULL,
      `Additional_Comments` TEXT DEFAULT NULL,
      `Email_Information_Sent` TEXT DEFAULT NULL,

      -- SECTION 7: Scope of Work
      `Scope_Of_Work` TEXT DEFAULT NULL,

      -- SECTION 8: Photos
      `photos` TEXT DEFAULT NULL,

      -- Status & Metadata
      `status` VARCHAR(50) DEFAULT 'pending',
      `document_type` VARCHAR(50) DEFAULT NULL,
      `document_number` VARCHAR(50) DEFAULT NULL,
      `docnum` VARCHAR(100) DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `completed_at` TIMESTAMP NULL DEFAULT NULL,

      INDEX `idx_status` (`status`),
      INDEX `idx_company` (`Company_Name`),
      INDEX `idx_service_type` (`Service_Type`),
      INDEX `idx_created` (`created_at`),
      INDEX `idx_docnum` (`docnum`)

    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($createTableSQL);

    // Add missing columns to existing table
    addMissingColumnsFormContract($pdo);
}

/**
 * Add missing columns to existing requests table
 */
function addMissingColumnsFormContract($pdo) {
    try {
        // Check and add docnum column
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'docnum'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `docnum` VARCHAR(100) DEFAULT NULL");
            $pdo->exec("ALTER TABLE `requests` ADD INDEX `idx_docnum` (`docnum`)");
        }

        // Check and add completed_at column
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'completed_at'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `completed_at` TIMESTAMP NULL DEFAULT NULL");
        }
    } catch (Exception $e) {
        error_log("Error adding missing columns: " . $e->getMessage());
    }
}

// Crear conexión PDO
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

        // Initialize tables if they don't exist
        initializeFormsTable($pdo);
        initializeRequestsTable($pdo);

        return $pdo;
    } catch (PDOException $e) {
        // Log error
        error_log("Database connection error: " . $e->getMessage());

        // Return error for JSON responses
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Database connection error: ' . $e->getMessage()
            ]);
            exit;
        }

        // For regular page loads, show error
        die("Database connection failed. Please check your configuration in db_config.php");
    }
}
?>