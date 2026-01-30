<?php
/**
 * ============================================================
 * CHECK SERIES API - MASTER-CHILD SYSTEM
 * ============================================================
 */

require_once '../config.php';

header('Content-Type: application/json');
requireAuth();

$eventId = $_GET['event_id'] ?? null;

if (!$eventId) {
    http_response_code(400);
    echo json_encode(['error' => 'Event ID required']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $userId = getCurrentUserId();
    
    // Get event
    $query = "SELECT event_id, title, series_master_id, series_index, series_total
              FROM events
              WHERE event_id = :event_id AND user_id = :user_id AND is_active = TRUE";
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
    
    $isMaster = ($event['series_master_id'] === null && $event['series_total'] > 1);
    $isChild = ($event['series_master_id'] !== null);
    $isSeries = $isMaster || $isChild;
    
    // Get master info if this is a child
    $masterInfo = null;
    if ($isChild) {
        $masterQuery = "SELECT event_id, title, start_date, document_date
                        FROM events
                        WHERE event_id = :master_id";
        $masterStmt = $db->prepare($masterQuery);
        $masterStmt->bindParam(':master_id', $event['series_master_id'], PDO::PARAM_INT);
        $masterStmt->execute();
        $masterInfo = $masterStmt->fetch(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'is_series' => $isSeries,
        'is_master' => $isMaster,
        'is_child' => $isChild,
        'total' => (int)$event['series_total'],
        'index' => (int)$event['series_index'],
        'master_id' => $event['series_master_id'],
        'master_info' => $masterInfo,
        'title' => $event['title'],
        'message' => $isMaster 
            ? "Este es el evento MAESTRO de la serie ({$event['series_total']} eventos)"
            : ($isChild 
                ? "Este es el evento #{$event['series_index']} de {$event['series_total']} (hijo del maestro #{$event['series_master_id']})"
                : "Evento individual")
    ]);
    
} catch (Exception $e) {
    error_log("Error checking series: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}