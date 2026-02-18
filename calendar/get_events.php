<?php
/**
 * GET EVENTS API
 * Returns calendar events (agendas) joined with form data for calendar display.
 * Fetches from calendar_events table + forms table.
 * Applies RBAC: Vendedor only sees their own orders; Admin/Leader see all.
 *
 * GET params:
 *   month (int) - 1-12
 *   year  (int)
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../form_contract/order_access.php';
require_once __DIR__ . '/../form_contract/init.php';

// Enforce authentication and calendar module access
Middleware::module('calendar');
$user = Auth::user();

// Build RBAC filter (Vendedor sees only own orders)
$rbac = getOrderRbacFilter($user);

try {
    $pdo = Database::getConnection();

    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
    $year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');

    // Fetch calendar events joined with form data + service types from Q18/Q19
    $sql = "
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
            f.service_status,
            f.service_type,
            f.request_type,
            f.priority,
            f.requested_service,
            f.Order_Nomenclature,
            f.seller,
            f.Document_Date,
            (SELECT GROUP_CONCAT(DISTINCT ci.service_type SEPARATOR '||')
             FROM contract_items ci
             WHERE ci.form_id = ce.form_id
               AND ci.category = 'janitorial'
               AND ci.service_type IS NOT NULL
               AND ci.service_type != '') AS janitorial_services,
            (SELECT GROUP_CONCAT(DISTINCT ci.service_type SEPARATOR '||')
             FROM contract_items ci
             WHERE ci.form_id = ce.form_id
               AND ci.category = 'kitchen'
               AND ci.service_type IS NOT NULL
               AND ci.service_type != '') AS kitchen_services,
            (SELECT GROUP_CONCAT(DISTINCT ci.service_type SEPARATOR '||')
             FROM contract_items ci
             WHERE ci.form_id = ce.form_id
               AND ci.category = 'hood_vent'
               AND ci.service_type IS NOT NULL
               AND ci.service_type != '') AS hood_vent_services
        FROM calendar_events ce
        JOIN forms f ON ce.form_id = f.form_id
        WHERE MONTH(ce.event_date) = :month
          AND YEAR(ce.event_date) = :year
          {$rbac['sql']}
        ORDER BY ce.event_date ASC, ce.is_base_event DESC
    ";

    $stmt = $pdo->prepare($sql);
    $params = array_merge(
        [':month' => $month, ':year' => $year],
        $rbac['params']
    );
    $stmt->execute($params);

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
