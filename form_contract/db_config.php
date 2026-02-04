<?php
// ============================================================
// db_config.php - Configuración de Base de Datos
// ============================================================

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'form');  // ← CAMBIAR SEGÚN TU BASE DE DATOS
define('DB_USER', 'root');  // ← CAMBIAR SEGÚN TU USUARIO
define('DB_PASS', '');      // ← CAMBIAR SEGÚN TU CONTRASEÑA

// Configuración del calendario (para sincronización)
define('CALENDAR_DB_NAME', 'calendar_system');

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
        // Add missing columns to existing forms table
    addMissingColumnsToForms($pdo);
}
 
/**
 * Add missing columns to existing forms table (handles upgrades)
 */
function addMissingColumnsToForms($pdo) {
    $columnsToAdd = [
        'Document_Date'       => 'DATE DEFAULT NULL',
        'Work_Date'           => 'DATE DEFAULT NULL',
        'order_number'        => 'INT DEFAULT NULL',
        'Order_Nomenclature'  => 'VARCHAR(100) DEFAULT NULL',
        'payment_terms'       => 'VARCHAR(100) DEFAULT NULL',
        'contact_name'        => 'VARCHAR(100) DEFAULT NULL',
        'city'                => 'VARCHAR(100) DEFAULT NULL',
        'state'               => 'VARCHAR(100) DEFAULT NULL',
        'submitted_by'        => 'VARCHAR(100) DEFAULT NULL',
        'include_staff'       => 'VARCHAR(10) DEFAULT NULL',
        'service_status'      => "ENUM('pending', 'completed', 'not_completed') DEFAULT 'pending'",
        'service_completed_at' => 'TIMESTAMP NULL DEFAULT NULL',
    ];
 
    try {
        foreach ($columnsToAdd as $col => $definition) {
            $stmt = $pdo->query("SHOW COLUMNS FROM `forms` LIKE '$col'");
            if ($stmt->rowCount() == 0) {
                $pdo->exec("ALTER TABLE `forms` ADD COLUMN `$col` $definition");
            }
        }
    } catch (Exception $e) {
        error_log("Error adding missing columns to forms: " . $e->getMessage());
    }
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

        // Check and add form_id column for linking with forms table
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'form_id'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `form_id` INT DEFAULT NULL");
            $pdo->exec("ALTER TABLE `requests` ADD INDEX `idx_form_id` (`form_id`)");
        }

        // Check and add Work_Date column
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'Work_Date'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `Work_Date` DATE DEFAULT NULL");
        }

        // Check and add Document_Date column
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'Document_Date'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `Document_Date` DATE DEFAULT NULL");
        }

        // Check and add City column
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'City'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `City` VARCHAR(100) DEFAULT NULL");
        }

        // Check and add State column
        $stmt = $pdo->query("SHOW COLUMNS FROM `requests` LIKE 'State'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `requests` ADD COLUMN `State` VARCHAR(100) DEFAULT NULL");
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

/**
 * Get connection to calendar database for syncing events
 */
function getCalendarDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . CALENDAR_DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );

        // Ensure form_id column exists in events table for linking
        ensureCalendarFormIdColumn($pdo);

        return $pdo;
    } catch (PDOException $e) {
        error_log("Calendar database connection error: " . $e->getMessage());
        return null;
    }
}

/**
 * Add form_id column to calendar events table if it doesn't exist
 */
function ensureCalendarFormIdColumn($pdo) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `events` LIKE 'form_id'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `events` ADD COLUMN `form_id` INT DEFAULT NULL");
            $pdo->exec("ALTER TABLE `events` ADD INDEX `idx_form_id` (`form_id`)");
        }
    } catch (Exception $e) {
        error_log("Error adding form_id column to calendar events: " . $e->getMessage());
    }
}

/**
 * Create or update calendar event from form data
 *
 * @param int $formId - The form_id from forms table
 * @param array $formData - Form data including Work_Date, Document_Date, Order_Nomenclature, etc.
 * @return int|false - Returns event_id on success, false on failure
 */
function syncFormToCalendar($formId, $formData) {
    $calendarPdo = getCalendarDBConnection();
    if (!$calendarPdo) {
        error_log("Could not connect to calendar database for sync");
        return false;
    }

    try {
        // Check if Work_Date is set - required for calendar sync
        $workDate = $formData['Work_Date'] ?? null;
        if (empty($workDate)) {
            // No work date, nothing to sync
            return false;
        }

        // Check if event already exists for this form
        $stmt = $calendarPdo->prepare("SELECT event_id FROM events WHERE form_id = ? AND is_active = 1");
        $stmt->execute([$formId]);
        $existingEvent = $stmt->fetch();

        // Prepare event data
        $title = $formData['Order_Nomenclature'] ?? ('Service Order #' . $formId);
        $client = $formData['Company_Name'] ?? '';
        $documentDate = $formData['Document_Date'] ?? null;
        $description = buildEventDescription($formData);
        $location = trim(($formData['Company_Address'] ?? '') . ', ' . ($formData['City'] ?? '') . ', ' . ($formData['State'] ?? ''));
        $location = trim($location, ', ');
        $priority = strtolower($formData['Priority'] ?? 'medium');

        // Map form status to calendar status
        $status = 'pending';
        if (($formData['status'] ?? '') === 'submitted') {
            $status = 'confirmed';
        }

        // Determine category based on Request_Type
        $requestType = strtoupper($formData['Request_Type'] ?? 'JWO');

        // Map request types to category names
        $categoryMap = [
            'JWO' => ['name' => 'JWO', 'color' => '#3b82f6', 'icon' => '📋'],
            'CONTRACT' => ['name' => 'Contract', 'color' => '#10b981', 'icon' => '📄'],
            'PROPOSAL' => ['name' => 'Proposal', 'color' => '#f59e0b', 'icon' => '📊'],
            'HOODVENT' => ['name' => 'Hoodvent', 'color' => '#ef4444', 'icon' => '🔥'],
            'JANITORIAL' => ['name' => 'Janitorial', 'color' => '#8b5cf6', 'icon' => '🧹'],
        ];

        // Default to JWO if request type not found
        $categoryInfo = $categoryMap[$requestType] ?? $categoryMap['JWO'];
        $categoryName = $categoryInfo['name'];

        // Try to find existing category by name (case-insensitive)
        $stmt = $calendarPdo->prepare("SELECT category_id FROM event_categories WHERE UPPER(category_name) = :name LIMIT 1");
        $stmt->execute([':name' => strtoupper($categoryName)]);
        $category = $stmt->fetch();

        if ($category) {
            $categoryId = $category['category_id'];
        } else {
            // Category doesn't exist - create it
            $insertStmt = $calendarPdo->prepare(
                "INSERT INTO event_categories (user_id, category_name, color_hex, icon, is_default)
                 VALUES (:user_id, :name, :color, :icon, :is_default)"
            );
            $insertStmt->execute([
                ':user_id' => 1, // Default user
                ':name' => $categoryName,
                ':color' => $categoryInfo['color'],
                ':icon' => $categoryInfo['icon'],
                ':is_default' => ($categoryName === 'JWO') ? 1 : 0
            ]);
            $categoryId = $calendarPdo->lastInsertId();
            error_log("Created new calendar category '$categoryName' with ID $categoryId");
        }

        if ($existingEvent) {
            // UPDATE existing event
            $sql = "UPDATE events SET
                category_id = :category_id,
                title = :title,
                description = :description,
                location = :location,
                client = :client,
                start_date = :start_date,
                end_date = :end_date,
                document_date = :document_date,
                status = :status,
                priority = :priority,
                updated_at = NOW()
            WHERE event_id = :event_id";

            $stmt = $calendarPdo->prepare($sql);
            $stmt->execute([
                ':category_id' => $categoryId,
                ':title' => $title,
                ':description' => $description,
                ':location' => $location,
                ':client' => $client,
                ':start_date' => $workDate,
                ':end_date' => $workDate,
                ':document_date' => $documentDate,
                ':status' => $status,
                ':priority' => $priority,
                ':event_id' => $existingEvent['event_id']
            ]);

            return $existingEvent['event_id'];
        } else {
            // INSERT new event
            $sql = "INSERT INTO events (
                user_id, category_id, title, description, location, client,
                start_date, end_date, start_time, end_time,
                is_all_day, is_recurring, status, priority,
                document_date, original_date, form_id,
                is_active, created_at
            ) VALUES (
                :user_id, :category_id, :title, :description, :location, :client,
                :start_date, :end_date, :start_time, :end_time,
                :is_all_day, :is_recurring, :status, :priority,
                :document_date, :original_date, :form_id,
                1, NOW()
            )";

            $stmt = $calendarPdo->prepare($sql);
            $stmt->execute([
                ':user_id' => 1, // Default user, adjust as needed
                ':category_id' => $categoryId,
                ':title' => $title,
                ':description' => $description,
                ':location' => $location,
                ':client' => $client,
                ':start_date' => $workDate,
                ':end_date' => $workDate,
                ':start_time' => '09:00:00',
                ':end_time' => '17:00:00',
                ':is_all_day' => 0,
                ':is_recurring' => 0,
                ':status' => $status,
                ':priority' => $priority,
                ':document_date' => $documentDate,
                ':original_date' => $workDate,
                ':form_id' => $formId
            ]);

            return $calendarPdo->lastInsertId();
        }
    } catch (Exception $e) {
        error_log("Error syncing form to calendar: " . $e->getMessage());
        return false;
    }
}

/**
 * Build event description from form data
 */
function buildEventDescription($formData) {
    $parts = [];

    if (!empty($formData['Order_Nomenclature'])) {
        $parts[] = "Order: " . $formData['Order_Nomenclature'];
    }
    if (!empty($formData['order_number'])) {
        $parts[] = "Order #: " . $formData['order_number'];
    }
    if (!empty($formData['Company_Name'])) {
        $parts[] = "Client: " . $formData['Company_Name'];
    }
    if (!empty($formData['Requested_Service'])) {
        $parts[] = "Service: " . $formData['Requested_Service'];
    }
    if (!empty($formData['Service_Type'])) {
        $parts[] = "Type: " . $formData['Service_Type'];
    }
    if (!empty($formData['Request_Type'])) {
        $parts[] = "Request: " . $formData['Request_Type'];
    }
    if (!empty($formData['Document_Date'])) {
        $parts[] = "Document Date: " . $formData['Document_Date'];
    }

    return implode("\n", $parts);
}

/**
 * Sync form data to requests table (for Contract Generator)
 * Maps fields from forms table format to requests table format
 *
 * @param PDO $pdo - Database connection
 * @param int $formId - The form_id from forms table
 * @param array $formData - Form data from POST
 * @return int|false - Returns request_id on success, false on failure
 */
function syncFormToRequests($pdo, $formId, $formData) {
    try {
        // Check if request already exists for this form (using form_id or docnum)
        $docnum = $formData['Order_Nomenclature'] ?? null;
        $existingRequest = null;

        // First try to find by form_id (more reliable)
        if ($formId) {
            $stmt = $pdo->prepare("SELECT id FROM requests WHERE form_id = ?");
            $stmt->execute([$formId]);
            $existingRequest = $stmt->fetch();
        }

        // If not found by form_id, try by docnum
        if (!$existingRequest && $docnum) {
            $stmt = $pdo->prepare("SELECT id FROM requests WHERE docnum = ?");
            $stmt->execute([$docnum]);
            $existingRequest = $stmt->fetch();
        }

        // Prepare scope of work as JSON
        $scopeOfWork = null;
        if (isset($formData['Scope_Of_Work']) && is_array($formData['Scope_Of_Work'])) {
            $scopeOfWork = json_encode($formData['Scope_Of_Work']);
        }

        // Prepare janitorial arrays as JSON
        $type18 = isset($formData['type18']) && is_array($formData['type18']) ? json_encode($formData['type18']) : null;
        $write18 = isset($formData['write18']) && is_array($formData['write18']) ? json_encode($formData['write18']) : null;
        $time18 = isset($formData['time18']) && is_array($formData['time18']) ? json_encode($formData['time18']) : null;
        $freq18 = isset($formData['freq18']) && is_array($formData['freq18']) ? json_encode($formData['freq18']) : null;
        $desc18 = isset($formData['desc18']) && is_array($formData['desc18']) ? json_encode($formData['desc18']) : null;
        $subtotal18 = isset($formData['subtotal18']) && is_array($formData['subtotal18']) ? json_encode($formData['subtotal18']) : null;

        // Prepare kitchen/hoodvent arrays as JSON
        $type19 = isset($formData['type19']) && is_array($formData['type19']) ? json_encode($formData['type19']) : null;
        $time19 = isset($formData['time19']) && is_array($formData['time19']) ? json_encode($formData['time19']) : null;
        $freq19 = isset($formData['freq19']) && is_array($formData['freq19']) ? json_encode($formData['freq19']) : null;
        $desc19 = isset($formData['desc19']) && is_array($formData['desc19']) ? json_encode($formData['desc19']) : null;
        $subtotal19 = isset($formData['subtotal19']) && is_array($formData['subtotal19']) ? json_encode($formData['subtotal19']) : null;

        // Prepare staff arrays as JSON
        $baseStaff = isset($formData['base_staff']) && is_array($formData['base_staff']) ? json_encode($formData['base_staff']) : null;
        $increaseStaff = isset($formData['increase_staff']) && is_array($formData['increase_staff']) ? json_encode($formData['increase_staff']) : null;
        $billStaff = isset($formData['bill_staff']) && is_array($formData['bill_staff']) ? json_encode($formData['bill_staff']) : null;

        // Prepare week_days as JSON if it's an array
        $weekDays = isset($formData['week_days']) && is_array($formData['week_days']) ? json_encode($formData['week_days']) : ($formData['week_days'] ?? null);

        if ($existingRequest) {
            // UPDATE existing request
            $sql = "UPDATE requests SET
                Service_Type = :service_type,
                Request_Type = :request_type,
                Priority = :priority,
                Requested_Service = :requested_service,
                client_name = :client_name,
                Client_Title = :client_title,
                Email = :email,
                Number_Phone = :number_phone,
                Company_Name = :company_name,
                Company_Address = :company_address,
                City = :city,
                State = :state,
                Is_New_Client = :is_new_client,
                Site_Visit_Conducted = :site_visit_conducted,
                frequency_period = :frequency_period,
                week_days = :week_days,
                one_time = :one_time,
                Invoice_Frequency = :invoice_frequency,
                Contract_Duration = :contract_duration,
                Seller = :seller,
                PriceInput = :price_input,
                Prime_Quoted_Price = :prime_quoted_price,
                includeJanitorial = :include_janitorial,
                type18 = :type18, write18 = :write18, time18 = :time18,
                freq18 = :freq18, desc18 = :desc18, subtotal18 = :subtotal18,
                total18 = :total18, taxes18 = :taxes18, grand18 = :grand18,
                includeKitchen = :include_kitchen,
                type19 = :type19, time19 = :time19,
                freq19 = :freq19, desc19 = :desc19, subtotal19 = :subtotal19,
                total19 = :total19, taxes19 = :taxes19, grand19 = :grand19,
                includeStaff = :include_staff,
                base_staff = :base_staff, increase_staff = :increase_staff, bill_staff = :bill_staff,
                inflationAdjustment = :inflation_adjustment,
                totalArea = :total_area,
                buildingsIncluded = :buildings_included,
                startDateServices = :start_date_services,
                Site_Observation = :site_observation,
                Additional_Comments = :additional_comments,
                Scope_Of_Work = :scope_of_work,
                Work_Date = :work_date,
                Document_Date = :document_date,
                docnum = :docnum,
                form_id = :form_id,
                status = :status,
                updated_at = NOW()
            WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $existingRequest['id']);
            $stmt->bindValue(':docnum', $docnum);
            $stmt->bindValue(':form_id', $formId);
        } else {
            // INSERT new request
            $sql = "INSERT INTO requests (
                Service_Type, Request_Type, Priority, Requested_Service,
                client_name, Client_Title, Email, Number_Phone, Company_Name, Company_Address, City, State, Is_New_Client,
                Site_Visit_Conducted, frequency_period, week_days, one_time, Invoice_Frequency, Contract_Duration,
                Seller, PriceInput, Prime_Quoted_Price,
                includeJanitorial, type18, write18, time18, freq18, desc18, subtotal18, total18, taxes18, grand18,
                includeKitchen, type19, time19, freq19, desc19, subtotal19, total19, taxes19, grand19,
                includeStaff, base_staff, increase_staff, bill_staff,
                inflationAdjustment, totalArea, buildingsIncluded, startDateServices,
                Site_Observation, Additional_Comments, Scope_Of_Work,
                Work_Date, Document_Date,
                status, docnum, form_id, created_at
            ) VALUES (
                :service_type, :request_type, :priority, :requested_service,
                :client_name, :client_title, :email, :number_phone, :company_name, :company_address, :city, :state, :is_new_client,
                :site_visit_conducted, :frequency_period, :week_days, :one_time, :invoice_frequency, :contract_duration,
                :seller, :price_input, :prime_quoted_price,
                :include_janitorial, :type18, :write18, :time18, :freq18, :desc18, :subtotal18, :total18, :taxes18, :grand18,
                :include_kitchen, :type19, :time19, :freq19, :desc19, :subtotal19, :total19, :taxes19, :grand19,
                :include_staff, :base_staff, :increase_staff, :bill_staff,
                :inflation_adjustment, :total_area, :buildings_included, :start_date_services,
                :site_observation, :additional_comments, :scope_of_work,
                :work_date, :document_date,
                :status, :docnum, :form_id, NOW()
            )";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':docnum', $docnum);
            $stmt->bindValue(':form_id', $formId);
        }

        // Bind common parameters
        $stmt->bindValue(':service_type', $formData['Service_Type'] ?? null);
        $stmt->bindValue(':request_type', $formData['Request_Type'] ?? null);
        $stmt->bindValue(':priority', $formData['Priority'] ?? 'Normal');
        $stmt->bindValue(':requested_service', $formData['Requested_Service'] ?? null);
        $stmt->bindValue(':client_name', $formData['Client_Name'] ?? null);
        $stmt->bindValue(':client_title', $formData['Client_Title'] ?? null);
        $stmt->bindValue(':email', $formData['Email'] ?? null);
        $stmt->bindValue(':number_phone', $formData['Number_Phone'] ?? null);
        $stmt->bindValue(':company_name', $formData['Company_Name'] ?? null);
        $stmt->bindValue(':company_address', $formData['Company_Address'] ?? null);
        $stmt->bindValue(':city', $formData['City'] ?? null);
        $stmt->bindValue(':state', $formData['State'] ?? null);
        $stmt->bindValue(':is_new_client', $formData['Is_New_Client'] ?? null);
        $stmt->bindValue(':site_visit_conducted', $formData['Site_Visit_Conducted'] ?? null);
        $stmt->bindValue(':frequency_period', $formData['frequency_period'] ?? null);
        $stmt->bindValue(':week_days', $weekDays);
        $stmt->bindValue(':one_time', $formData['one_time'] ?? null);
        $stmt->bindValue(':invoice_frequency', $formData['Invoice_Frequency'] ?? null);
        $stmt->bindValue(':contract_duration', $formData['Contract_Duration'] ?? null);
        $stmt->bindValue(':seller', $formData['Seller'] ?? null);
        $stmt->bindValue(':price_input', $formData['PriceInput'] ?? null);
        $stmt->bindValue(':prime_quoted_price', $formData['Prime_Quoted_Price'] ?? null);
        $stmt->bindValue(':include_janitorial', $formData['includeJanitorial'] ?? null);
        $stmt->bindValue(':type18', $type18);
        $stmt->bindValue(':write18', $write18);
        $stmt->bindValue(':time18', $time18);
        $stmt->bindValue(':freq18', $freq18);
        $stmt->bindValue(':desc18', $desc18);
        $stmt->bindValue(':subtotal18', $subtotal18);
        $stmt->bindValue(':total18', $formData['total18'] ?? null);
        $stmt->bindValue(':taxes18', $formData['taxes18'] ?? null);
        $stmt->bindValue(':grand18', $formData['grand18'] ?? null);
        $stmt->bindValue(':include_kitchen', $formData['includeKitchen'] ?? null);
        $stmt->bindValue(':type19', $type19);
        $stmt->bindValue(':time19', $time19);
        $stmt->bindValue(':freq19', $freq19);
        $stmt->bindValue(':desc19', $desc19);
        $stmt->bindValue(':subtotal19', $subtotal19);
        $stmt->bindValue(':total19', $formData['total19'] ?? null);
        $stmt->bindValue(':taxes19', $formData['taxes19'] ?? null);
        $stmt->bindValue(':grand19', $formData['grand19'] ?? null);
        $stmt->bindValue(':include_staff', $formData['includeStaff'] ?? null);
        $stmt->bindValue(':base_staff', $baseStaff);
        $stmt->bindValue(':increase_staff', $increaseStaff);
        $stmt->bindValue(':bill_staff', $billStaff);
        $stmt->bindValue(':inflation_adjustment', $formData['inflationAdjustment'] ?? null);
        $stmt->bindValue(':total_area', $formData['totalArea'] ?? null);
        $stmt->bindValue(':buildings_included', $formData['buildingsIncluded'] ?? null);
        $stmt->bindValue(':start_date_services', !empty($formData['startDateServices']) ? $formData['startDateServices'] : null);
        $stmt->bindValue(':site_observation', $formData['Site_Observation'] ?? null);
        $stmt->bindValue(':additional_comments', $formData['Additional_Comments'] ?? null);
        $stmt->bindValue(':scope_of_work', $scopeOfWork);
        $stmt->bindValue(':work_date', !empty($formData['Work_Date']) ? $formData['Work_Date'] : null);
        $stmt->bindValue(':document_date', !empty($formData['Document_Date']) ? $formData['Document_Date'] : null);
        $stmt->bindValue(':status', $formData['status'] ?? 'pending');

        $stmt->execute();

        if ($existingRequest) {
            return $existingRequest['id'];
        } else {
            return $pdo->lastInsertId();
        }

    } catch (Exception $e) {
        error_log("Error syncing form to requests: " . $e->getMessage());
        return false;
    }
}
?>