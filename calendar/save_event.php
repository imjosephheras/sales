<?php
/**
 * SAVE EVENT API
 * Creates or updates a base calendar event for a form and generates recurring agendas.
 * Called from the mini form or externally.
 *
 * POST params:
 *   form_id           (required) - FK to forms table
 *   frequency_months  (optional) - 0-6, default 0
 *   frequency_years   (optional) - 0-5, default 0
 *   description       (optional) - Notes
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../form_contract/init.php';

try {
    $pdo = Database::getConnection();

    $formId = isset($_POST['form_id']) ? (int)$_POST['form_id'] : 0;
    if ($formId <= 0) {
        echo json_encode(['success' => false, 'message' => 'form_id is required']);
        exit;
    }

    // Get Work_Date from the form
    $stmt = $pdo->prepare("SELECT Work_Date FROM forms WHERE form_id = :fid");
    $stmt->execute([':fid' => $formId]);
    $form = $stmt->fetch();

    if (!$form || empty($form['Work_Date'])) {
        echo json_encode(['success' => false, 'message' => 'Form not found or Work_Date is empty']);
        exit;
    }

    $frequencyMonths = isset($_POST['frequency_months']) ? max(0, min(6, (int)$_POST['frequency_months'])) : 0;
    $frequencyYears  = isset($_POST['frequency_years'])  ? max(0, min(5, (int)$_POST['frequency_years']))  : 0;
    $description     = isset($_POST['description']) ? trim($_POST['description']) : null;

    $baseEventId = syncCalendarEvent($pdo, $formId, $form['Work_Date'], $frequencyMonths, $frequencyYears, $description);

    if ($baseEventId) {
        // Count total agendas created
        $count = $pdo->prepare("SELECT COUNT(*) FROM calendar_events WHERE form_id = :fid");
        $count->execute([':fid' => $formId]);
        $totalAgendas = $count->fetchColumn();

        echo json_encode([
            'success' => true,
            'base_event_id' => $baseEventId,
            'total_agendas' => (int)$totalAgendas,
            'message' => 'Event saved successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save event']);
    }

} catch (Exception $e) {
    error_log("save_event error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
