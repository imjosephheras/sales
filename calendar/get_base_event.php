<?php
/**
 * GET BASE EVENT API
 * Returns the base event for a given form_id.
 * Used when a recurring event's base event is in a different month.
 *
 * GET params:
 *   form_id (int)
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../form_contract/db_config.php';

try {
    $pdo = getDBConnection();

    $formId = isset($_GET['form_id']) ? (int)$_GET['form_id'] : 0;
    if ($formId <= 0) {
        echo json_encode(['success' => false, 'message' => 'form_id is required']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT ce.event_id, ce.form_id, ce.event_date, ce.description,
               ce.frequency_months, ce.frequency_years,
               f.Work_Date, f.client_name, f.company_name
        FROM calendar_events ce
        JOIN forms f ON ce.form_id = f.form_id
        WHERE ce.form_id = :fid AND ce.is_base_event = 1
        LIMIT 1
    ");
    $stmt->execute([':fid' => $formId]);
    $baseEvent = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($baseEvent) {
        echo json_encode(['success' => true, 'base_event' => $baseEvent]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Base event not found']);
    }

} catch (Exception $e) {
    error_log("get_base_event error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
