<?php
/**
 * UPDATE EVENT API
 * Updates fields from the mini form:
 *   - work_date      → updates forms.Work_Date + regenerates agendas
 *   - description    → updates calendar_events.description for the specific event
 *   - frequency_months / frequency_years → updates base event + regenerates agendas
 *
 * POST params:
 *   event_id          (required)
 *   work_date         (optional) - YYYY-MM-DD
 *   description       (optional)
 *   frequency_months  (optional) - 0-6
 *   frequency_years   (optional) - 0-5
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../form_contract/db_config.php';

try {
    $pdo = getDBConnection();

    $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
    if ($eventId <= 0) {
        echo json_encode(['success' => false, 'message' => 'event_id is required']);
        exit;
    }

    // Get the event
    $stmt = $pdo->prepare("SELECT * FROM calendar_events WHERE event_id = :eid");
    $stmt->execute([':eid' => $eventId]);
    $event = $stmt->fetch();

    if (!$event) {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit;
    }

    // Find the base event (if this is a recurring event, find its parent)
    $baseEventId = $event['is_base_event'] ? $event['event_id'] : $event['parent_event_id'];
    $formId = $event['form_id'];

    // Get base event data
    $stmt = $pdo->prepare("SELECT * FROM calendar_events WHERE event_id = :eid");
    $stmt->execute([':eid' => $baseEventId]);
    $baseEvent = $stmt->fetch();

    if (!$baseEvent) {
        echo json_encode(['success' => false, 'message' => 'Base event not found']);
        exit;
    }

    // Determine new values
    $newWorkDate = isset($_POST['work_date']) && !empty($_POST['work_date'])
        ? $_POST['work_date']
        : $baseEvent['event_date'];

    $newDescription = isset($_POST['description'])
        ? trim($_POST['description'])
        : $baseEvent['description'];

    $newFreqMonths = isset($_POST['frequency_months'])
        ? max(0, min(6, (int)$_POST['frequency_months']))
        : (int)$baseEvent['frequency_months'];

    $newFreqYears = isset($_POST['frequency_years'])
        ? max(0, min(5, (int)$_POST['frequency_years']))
        : (int)$baseEvent['frequency_years'];

    $newServiceStatus = isset($_POST['service_status']) && !empty($_POST['service_status'])
        ? trim($_POST['service_status'])
        : null;

    // Update forms table directly (same data source as Request Form)
    // This ensures changes from calendar reflect in the Request Form
    $formUpdates = [];
    $formParams = [':fid' => $formId];

    if ($newWorkDate !== $baseEvent['event_date']) {
        $formUpdates[] = 'Work_Date = :wd';
        $formParams[':wd'] = $newWorkDate;
    }

    if ($newServiceStatus !== null) {
        $formUpdates[] = 'service_status = :ss';
        $formParams[':ss'] = $newServiceStatus;
    }

    if (count($formUpdates) > 0) {
        $pdo->prepare("UPDATE forms SET " . implode(', ', $formUpdates) . " WHERE form_id = :fid")
            ->execute($formParams);
    }

    // If description was updated for a specific recurring event, save it there
    if (!$event['is_base_event'] && isset($_POST['description'])) {
        $pdo->prepare("UPDATE calendar_events SET description = :desc WHERE event_id = :eid")
            ->execute([':desc' => $newDescription, ':eid' => $eventId]);
    }

    // Sync the base event (this will regenerate recurring agendas)
    $result = syncCalendarEvent($pdo, $formId, $newWorkDate, $newFreqMonths, $newFreqYears, $newDescription);

    // Count total agendas
    $count = $pdo->prepare("SELECT COUNT(*) FROM calendar_events WHERE form_id = :fid");
    $count->execute([':fid' => $formId]);
    $totalAgendas = $count->fetchColumn();

    echo json_encode([
        'success' => true,
        'base_event_id' => $baseEventId,
        'total_agendas' => (int)$totalAgendas,
        'message' => 'Event updated successfully'
    ]);

} catch (Exception $e) {
    error_log("update_event error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
