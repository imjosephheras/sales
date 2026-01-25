<?php
/**
 * FIND REQUEST BY EVENT CONTROLLER
 * Checks if a request exists for a given event_id
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $event_id = $_GET['event_id'] ?? null;

    if (!$event_id) {
        throw new Exception('Event ID is required');
    }

    // Try to find a request linked to this event
    // For now, we'll try to match by event title or business name
    // In the future, you may want to add an event_id column to the requests table

    // Connect to calendar_system to get event details
    $pdo_calendar = new PDO(
        "mysql:host=localhost;dbname=calendar_system;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo_calendar->prepare("SELECT title, client FROM events WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo json_encode([
            'success' => false,
            'message' => 'Event not found'
        ]);
        exit;
    }

    // Try to find request by company name (client) or docnum (event title)
    $sql = "SELECT id, Request_Type, Company_Name, docnum, status
            FROM requests
            WHERE Company_Name = :client OR docnum = :title
            ORDER BY created_at DESC
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':client' => $event['client'],
        ':title' => $event['title']
    ]);

    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($request) {
        echo json_encode([
            'success' => true,
            'found' => true,
            'request' => $request
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'found' => false,
            'message' => 'No existing request found for this event'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
