<?php
/**
 * Calendar Data API - BASIC
 * GET ?month=2&year=2026 -> returns events for that month as JSON
 */

header('Content-Type: application/json');
require_once '../config.php';

$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year  = isset($_GET['year'])  ? intval($_GET['year'])  : date('Y');

if ($month < 1 || $month > 12) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid month']);
    exit;
}

try {
    $event = new Event();
    $events = $event->getByMonth($year, $month);

    echo json_encode([
        'success' => true,
        'month' => $month,
        'year' => $year,
        'events' => $events,
        'total' => count($events)
    ]);
} catch (Exception $e) {
    error_log("API calendar-data error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
