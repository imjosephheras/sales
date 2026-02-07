<?php
/**
 * GET EVENTS API
 * Returns calendar events (agendas) joined with form data for calendar display.
 * Fetches from calendar_events table + forms table.
 *
 * GET params:
 *   month (int) - 1-12
 *   year  (int)
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../form_contract/db_config.php';

try {
    $pdo = getDBConnection();

    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
    $year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');

    // Fetch calendar events joined with form data
    $stmt = $pdo->prepare("
        SELECT
            ce.event_id,
            ce.form_id,
            ce.parent_event_id,
            ce.is_base_event,
            ce.event_date,
            ce.description,
            ce.frequency_months,
            ce.frequency_years,
            f.client_name,
            f.company_name,
            f.Work_Date,
            f.status,
            f.service_type,
            f.request_type,
            f.priority,
            f.requested_service,
            f.Order_Nomenclature,
            f.seller,
            f.Document_Date
        FROM calendar_events ce
        JOIN forms f ON ce.form_id = f.form_id
        WHERE MONTH(ce.event_date) = :month
          AND YEAR(ce.event_date) = :year
        ORDER BY ce.event_date ASC, ce.is_base_event DESC
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
