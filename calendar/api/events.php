<?php
/**
 * Events API - Returns pure JSON
 */

// Disable HTML error output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set JSON header FIRST (before any output)
header('Content-Type: application/json');

// Catch ALL errors and convert to JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $errstr,
        'file' => basename($errfile),
        'line' => $errline
    ]);
    exit;
});

try {
    require_once '../config.php';
    requireAuth();
    
    $eventId = $_GET['id'] ?? null;
    
    if (!$eventId) {
        http_response_code(400);
        echo json_encode(['error' => 'Event ID required']);
        exit;
    }
    
    $db = Database::getInstance()->getConnection();
    $userId = getCurrentUserId();
    
    $query = "SELECT
                e.*,
                c.category_name,
                c.color_hex as category_color,
                c.icon as category_icon
              FROM events e
              LEFT JOIN event_categories c ON e.category_id = c.category_id
              WHERE e.event_id = :event_id
              AND e.user_id = :user_id
              AND e.is_active = TRUE";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        http_response_code(404);
        echo json_encode(['error' => 'Event not found']);
        exit;
    }
    
    // Convert types
    $event['is_all_day'] = (bool)$event['is_all_day'];
    $event['is_recurring'] = (bool)$event['is_recurring'];
    $event['is_reschedulable'] = (bool)$event['is_reschedulable'];
    $event['series_index'] = (int)($event['series_index'] ?? 0);
    $event['series_total'] = (int)($event['series_total'] ?? 0);
    
    // Add series info
    $event['is_master'] = ($event['series_master_id'] === null && $event['series_total'] > 1);
    $event['is_child'] = ($event['series_master_id'] !== null);
    
    echo json_encode($event, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}