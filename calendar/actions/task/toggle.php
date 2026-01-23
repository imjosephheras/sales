<?php
/**
 * ============================================================
 * TOGGLE TASK ACTION
 * Mark task as complete or incomplete
 * ============================================================
 */

header('Content-Type: application/json');

require_once '../../config.php';

// Require authentication
requireAuth();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON data
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['task_id']) || !isset($input['is_completed'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$taskId = intval($input['task_id']);
$isCompleted = (bool)$input['is_completed'];

try {
    $task = new Task();
    
    if ($isCompleted) {
        $success = $task->complete($taskId);
    } else {
        $success = $task->uncomplete($taskId);
    }
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'task_id' => $taskId,
            'is_completed' => $isCompleted
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error updating task']);
    }
    
} catch (Exception $e) {
    error_log("Error toggling task: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}