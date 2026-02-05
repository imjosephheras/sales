<?php
/**
 * Events API - BASIC
 * GET ?id=123 -> returns single event as JSON
 */

header('Content-Type: application/json');
require_once '../config.php';

$eventId = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$eventId) {
    http_response_code(400);
    echo json_encode(['error' => 'Event ID required']);
    exit;
}

try {
    $event = new Event();
    $data = $event->getById($eventId);

    if (!$data) {
        http_response_code(404);
        echo json_encode(['error' => 'Event not found']);
        exit;
    }

    echo json_encode($data);
} catch (Exception $e) {
    error_log("API events error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
