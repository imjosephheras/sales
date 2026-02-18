<?php
/**
 * UPDATE WORK DATE API (Drag & Drop)
 * When a user drags an event to a new date:
 *   - Updates forms.Work_Date
 *   - Updates the base event's event_date
 *   - Regenerates all recurring agendas from the new base date
 *
 * POST params:
 *   event_id  (required)
 *   new_date  (required) - YYYY-MM-DD
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../form_contract/init.php';

try {
    $pdo = Database::getConnection();

    $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
    $newDate = isset($_POST['new_date']) ? trim($_POST['new_date']) : '';

    if ($eventId <= 0 || empty($newDate)) {
        echo json_encode(['success' => false, 'message' => 'event_id and new_date are required']);
        exit;
    }

    // Validate date format
    $dateObj = DateTime::createFromFormat('Y-m-d', $newDate);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $newDate) {
        echo json_encode(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD']);
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

    // Find base event
    $baseEventId = $event['is_base_event'] ? $event['event_id'] : $event['parent_event_id'];
    $formId = $event['form_id'];

    // Get base event
    $stmt = $pdo->prepare("SELECT * FROM calendar_events WHERE event_id = :eid");
    $stmt->execute([':eid' => $baseEventId]);
    $baseEvent = $stmt->fetch();

    if (!$baseEvent) {
        echo json_encode(['success' => false, 'message' => 'Base event not found']);
        exit;
    }

    // Update forms.Work_Date
    $pdo->prepare("UPDATE forms SET Work_Date = :wd WHERE form_id = :fid")
        ->execute([':wd' => $newDate, ':fid' => $formId]);

    // Regenerate all calendar events from new base date
    syncCalendarEvent(
        $pdo,
        $formId,
        $newDate,
        (int)$baseEvent['frequency_months'],
        (int)$baseEvent['frequency_years'],
        $baseEvent['description']
    );

    echo json_encode([
        'success' => true,
        'message' => 'Work date updated successfully',
        'new_date' => $newDate
    ]);

} catch (Exception $e) {
    error_log("update_work_date error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
