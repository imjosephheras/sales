<?php
/**
 * Get services ready to invoice from requests table
 * These are services marked as completed in Module 10 (Service Confirmation)
 */
require_once __DIR__ . '/../config/db_config.php';
header('Content-Type: application/json');

try {
    $search = $_GET['search'] ?? '';

    // Add ready_to_invoice column if it doesn't exist
    try {
        $pdo->query("SELECT ready_to_invoice FROM requests LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE requests ADD COLUMN ready_to_invoice TINYINT(1) DEFAULT 0");
        $pdo->exec("ALTER TABLE requests ADD COLUMN final_pdf_path VARCHAR(500) DEFAULT NULL");
        $pdo->exec("ALTER TABLE requests ADD COLUMN service_status ENUM('pending', 'completed', 'not_completed') DEFAULT 'pending'");
        $pdo->exec("ALTER TABLE requests ADD COLUMN service_completed_at TIMESTAMP NULL DEFAULT NULL");
    }

    $sql = "SELECT
                id,
                Order_Nomenclature as order_number,
                Company_Name as company_name,
                client_name,
                Service_Type as service_type,
                Request_Type as request_type,
                Seller as seller,
                PriceInput as price,
                Work_Date as work_date,
                service_completed_at as completed_at,
                final_pdf_path as pdf_path,
                created_at
            FROM requests
            WHERE ready_to_invoice = 1
            AND service_status = 'completed'";

    $params = [];

    if ($search) {
        $sql .= " AND (
            Order_Nomenclature LIKE :search
            OR client_name LIKE :search2
            OR Company_Name LIKE :search3
        )";
        $params['search'] = "%$search%";
        $params['search2'] = "%$search%";
        $params['search3'] = "%$search%";
    }

    $sql .= " ORDER BY service_completed_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $services = $stmt->fetchAll();

    // Get count
    $countStmt = $pdo->query("SELECT COUNT(*) FROM requests WHERE ready_to_invoice = 1 AND service_status = 'completed'");
    $count = $countStmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'data' => $services,
        'count' => (int)$count
    ]);

} catch (Exception $e) {
    error_log("Error getting ready to invoice: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
