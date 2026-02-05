<?php
/**
 * GET EVENTS API
 * Returns forms with Work_Date for calendar display
 * Only returns client_name, company_name, and Work_Date
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../form_contract/db_config.php';

try {
    $pdo = getDBConnection();

    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
    $year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');

    // Fetch forms that have a Work_Date in the requested month/year
    // Include request form fields for schedule display
    $stmt = $pdo->prepare("
        SELECT form_id, client_name, company_name, Work_Date, status,
               service_type, request_type, priority, requested_service,
               Order_Nomenclature, seller, Document_Date
        FROM forms
        WHERE Work_Date IS NOT NULL
          AND MONTH(Work_Date) = :month
          AND YEAR(Work_Date) = :year
        ORDER BY Work_Date ASC
    ");
    $stmt->execute([
        ':month' => $month,
        ':year'  => $year
    ]);

    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'events'  => $events
    ]);

} catch (Exception $e) {
    error_log("Calendar get_events error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading events',
        'events'  => []
    ]);
}
?>
