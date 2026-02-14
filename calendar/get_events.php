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
require_once __DIR__ . '/../form_contract/db_config.php';

// Enforce authentication and calendar module access
Middleware::module('calendar');
$user = Auth::user();

// Build RBAC filter (Vendedor sees only own orders)
$rbac = getOrderRbacFilter($user);

try {
    $pdo = getDBConnection();

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
            (SELECT GROUP_CONCAT(DISTINCT jsc.service_type SEPARATOR '||')
             FROM janitorial_services_costs jsc
             WHERE jsc.form_id = ce.form_id
               AND jsc.service_type IS NOT NULL
               AND jsc.service_type != '') AS janitorial_services,
            (SELECT GROUP_CONCAT(DISTINCT kcc.service_type SEPARATOR '||')
             FROM kitchen_cleaning_costs kcc
             WHERE kcc.form_id = ce.form_id
               AND kcc.service_type IS NOT NULL
               AND kcc.service_type != '') AS kitchen_services,
            (SELECT GROUP_CONCAT(DISTINCT hvc.service_type SEPARATOR '||')
             FROM hood_vent_costs hvc
             WHERE hvc.form_id = ce.form_id
               AND hvc.service_type IS NOT NULL
               AND hvc.service_type != '') AS hood_vent_services,
            (
                COALESCE((SELECT SUM(jsc2.subtotal) FROM janitorial_services_costs jsc2 WHERE jsc2.form_id = ce.form_id), 0) +
                COALESCE((SELECT SUM(kcc2.subtotal) FROM kitchen_cleaning_costs kcc2 WHERE kcc2.form_id = ce.form_id), 0) +
                COALESCE((SELECT SUM(hvc2.subtotal) FROM hood_vent_costs hvc2 WHERE hvc2.form_id = ce.form_id), 0)
            ) AS grand_total
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
