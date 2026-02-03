<?php
/**
 * ============================================================
 * EVENT CONTROLLER - MASTER-CHILD SERIES SYSTEM
 * When master moves, all children are recreated
 * ============================================================
 */

class EventController {
    
    private $eventModel;
    private $userId;
    
    public function __construct() {
        $this->eventModel = new Event();
        $this->userId = getCurrentUserId();
    }
    
    /**
     * Save event (create or update)
     */
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('../../index.php');
            return;
        }
        
        try {
            $eventData = $this->prepareEventData($_POST);
            $eventId = !empty($_POST['event_id']) ? intval($_POST['event_id']) : null;
            
            if ($eventId) {
                // Get event to check if it's a master
                $event = $this->eventModel->getById($eventId);
                
                if ($event['series_master_id'] === null && $event['series_total'] > 0) {
                    // This is a MASTER event - regenerate entire series
                    $this->regenerateSeries($eventId, $eventData);
                    setFlashMessage("Series regenerated: {$event['series_total']} events", 'success');
                } else {
                    // Regular update or child event
                    unset($eventData['user_id']);
                    $success = $this->eventModel->update($eventId, $eventData);
                    $message = $success ? 'Event updated successfully' : 'Error updating event';
                    setFlashMessage($message, $success ? 'success' : 'error');
                }
            } else {
                // Create new event (master)
                $newEventId = $this->eventModel->create($eventData);
                
                if (!$newEventId) {
                    setFlashMessage('Error creating event', 'error');
                    redirect('../../index.php');
                    return;
                }
                
                // Create child events if recurring
                $eventsCreated = $this->createSeriesFromMaster($newEventId, $eventData);
                
                $message = $eventsCreated > 1 
                    ? "Created series: $eventsCreated events" 
                    : 'Event created successfully';
                setFlashMessage($message, 'success');
            }
            
        } catch (Exception $e) {
            error_log("Error saving event: " . $e->getMessage());
            setFlashMessage('Error: ' . $e->getMessage(), 'error');
        }
        
        redirect('../../index.php');
    }
    
    /**
     * Delete master event (cascades to children via FK)
     */
    public function delete() {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $eventId = $input['event_id'] ?? null;
    
    if (!$eventId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Event ID required']);
        return;
    }
    
    try {
        $event = $this->eventModel->getById($eventId);
        
        if (!$event || $event['user_id'] != $this->userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        
        // Check if master
        if ($event['series_master_id'] === null && $event['series_total'] > 0) {
            // Delete children first
            $query = "DELETE FROM events WHERE series_master_id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $eventId, PDO::PARAM_INT);
            $stmt->execute();
            $deleted = $stmt->rowCount();
            
            // Delete master
            $this->eventModel->delete($eventId);
            
            echo json_encode([
                'success' => true,
                'message' => "Deleted series: " . ($deleted + 1) . " events"
            ]);
        } else {
            // Delete single
            $success = $this->eventModel->delete($eventId);
            echo json_encode([
                'success' => $success,
                'message' => 'Event deleted'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Delete error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Server error']);
    }
}
    
    /**
     * Reschedule master event - regenerates entire series
     */
    public function reschedule() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $eventId = $input['event_id'] ?? null;
        $newDate = $input['new_date'] ?? null;
        
        if (!$eventId || !$newDate) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            return;
        }
        
        if (!validateDate($newDate)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid date format']);
            return;
        }
        
        try {
            $event = $this->eventModel->getById($eventId);
            
            if (!$event || $event['user_id'] != $this->userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Access denied']);
                return;
            }
            
            // Check if this is a master event
            if ($event['series_master_id'] === null && $event['series_total'] > 0) {
                // MASTER EVENT - Regenerate entire series from new date
                $event['start_date'] = $newDate;
                $event['end_date'] = $newDate;
                $event['execution_date'] = $newDate;
                
                $regenerated = $this->regenerateSeries($eventId, $event);
                
                echo json_encode([
                    'success' => true,
                    'message' => "Series regenerated: $regenerated events",
                    'is_master' => true
                ]);
            } else {
                // CHILD EVENT or standalone - just move this one
                $duration = (strtotime($event['end_date']) - strtotime($event['start_date'])) / 86400;
                $newEndDate = date('Y-m-d', strtotime($newDate . " +{$duration} days"));
                
                $updateData = [
                    'start_date' => $newDate,
                    'end_date' => $newEndDate,
                    'execution_date' => $newDate
                ];
                
                $success = $this->eventModel->update($eventId, $updateData);
                
                echo json_encode([
                    'success' => $success,
                    'message' => 'Event rescheduled',
                    'is_master' => false
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Reschedule error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Create series from master event
     */
    private function createSeriesFromMaster($masterId, $masterData) {
        $freq = $masterData['frequency_months'];
        $dur = $masterData['frequency_years'];
        
        if (!$freq || $freq <= 0 || !$dur || $dur <= 0) {
            // No series - update master to reflect it's standalone
            $this->eventModel->update($masterId, [
                'series_master_id' => null,
                'series_index' => 0,
                'series_total' => 1
            ]);
            return 1;
        }
        
        $total = floor(($dur * 12) / $freq);
        $baseDate = new DateTime($masterData['start_date']);
        $originalDay = (int)$baseDate->format('d');
        
        // Update master event
        $this->eventModel->update($masterId, [
            'series_master_id' => null,  // NULL = is master
            'series_index' => 0,          // 0 = master
            'series_total' => $total + 1  // Including master
        ]);
        
        error_log("Creating series from master #{$masterId}: " . ($total + 1) . " events total");
        
        $created = 1; // Master already exists
        
        // Create child events
        for ($i = 1; $i <= $total; $i++) {
            try {
                $childData = $masterData;
                
                // Calculate new date SAFELY
                $newDate = clone $baseDate;
                $monthsToAdd = $freq * $i;
                $newDate->modify("+{$monthsToAdd} months");
                
                // Handle day overflow
                $newMonth = (int)$newDate->format('m');
                $newYear = (int)$newDate->format('Y');
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $newMonth, $newYear);
                $dayToUse = min($originalDay, $daysInMonth);
                $newDate->setDate($newYear, $newMonth, $dayToUse);
                
                $newDateStr = $newDate->format('Y-m-d');
                
                $childData['start_date'] = $newDateStr;
                $childData['end_date'] = $newDateStr;
                $childData['execution_date'] = null;
                $childData['series_master_id'] = $masterId;  // Link to master
                $childData['series_index'] = $i;             // Position in series
                $childData['series_total'] = $total + 1;     // Total events
                
                if ($this->eventModel->create($childData)) {
                    $created++;
                    error_log("  Child #{$i}: {$newDateStr} (day {$dayToUse})");
                } else {
                    error_log("  Failed to create child #{$i}");
                }
                
            } catch (Exception $e) {
                error_log("Error creating child #{$i}: " . $e->getMessage());
            }
        }
        
        error_log("✅ Series created: {$created} events");
        return $created;
    }
    
    /**
     * Regenerate series when master changes
     */
    private function regenerateSeries($masterId, $newMasterData) {
        $db = Database::getInstance()->getConnection();
        
        error_log("🔄 Regenerating series for master #{$masterId}");
        
        // 1. Delete all existing children
        $deleteQuery = "DELETE FROM events WHERE series_master_id = :master_id";
        $deleteStmt = $db->prepare($deleteQuery);
        $deleteStmt->bindParam(':master_id', $masterId, PDO::PARAM_INT);
        $deleteStmt->execute();
        $deleted = $deleteStmt->rowCount();
        
        error_log("  Deleted {$deleted} old children");
        
        // 2. Update master with new data
        unset($newMasterData['user_id']);
        $this->eventModel->update($masterId, $newMasterData);
        
        error_log("  Master updated with new date: {$newMasterData['start_date']}");
        
        // 3. Recreate children from new master data
        $freq = $newMasterData['frequency_months'];
        $dur = $newMasterData['frequency_years'];
        
        if (!$freq || $freq <= 0 || !$dur || $dur <= 0) {
            // No longer a series
            $this->eventModel->update($masterId, [
                'series_master_id' => null,
                'series_index' => 0,
                'series_total' => 1
            ]);
            error_log("  Series removed, now standalone");
            return 1;
        }
        
        $total = floor(($dur * 12) / $freq);
        $baseDate = new DateTime($newMasterData['start_date']);
        $originalDay = (int)$baseDate->format('d');
        
        // Update master's total
        $this->eventModel->update($masterId, [
            'series_total' => $total + 1
        ]);
        
        $created = 1; // Master
        
        // Create new children
        for ($i = 1; $i <= $total; $i++) {
            try {
                $childData = $newMasterData;
                
                // Calculate date
                $newDate = clone $baseDate;
                $monthsToAdd = $freq * $i;
                $newDate->modify("+{$monthsToAdd} months");
                
                // Handle day overflow
                $newMonth = (int)$newDate->format('m');
                $newYear = (int)$newDate->format('Y');
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $newMonth, $newYear);
                $dayToUse = min($originalDay, $daysInMonth);
                $newDate->setDate($newYear, $newMonth, $dayToUse);
                
                $newDateStr = $newDate->format('Y-m-d');
                
                $childData['start_date'] = $newDateStr;
                $childData['end_date'] = $newDateStr;
                $childData['execution_date'] = null;
                $childData['series_master_id'] = $masterId;
                $childData['series_index'] = $i;
                $childData['series_total'] = $total + 1;
                $childData['user_id'] = $this->userId;
                
                if ($this->eventModel->create($childData)) {
                    $created++;
                } else {
                    error_log("  Failed to create child #{$i}");
                }
                
            } catch (Exception $e) {
                error_log("Error creating child #{$i}: " . $e->getMessage());
            }
        }
        
        error_log("✅ Series regenerated: {$created} events");
        return $created;
    }
    
    /**
     * Toggle status
     */
    public function toggleStatus() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $eventId = $input['event_id'] ?? null;
        $newStatus = $input['status'] ?? null;
        
        if (!$eventId || !$newStatus) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            return;
        }
        
        try {
            $success = $this->eventModel->changeStatus($eventId, $newStatus);
            
            echo json_encode([
                'success' => $success,
                'event_id' => $eventId,
                'status' => $newStatus
            ]);
            
        } catch (Exception $e) {
            error_log("Error toggling status: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
    }
    
    /**
     * Get event by ID
     */
    public function getById($eventId) {
        header('Content-Type: application/json');
        
        if (!$eventId) {
            http_response_code(400);
            echo json_encode(['error' => 'Event ID required']);
            return;
        }
        
        try {
            $event = $this->eventModel->getById($eventId);
            
            if (!$event || $event['user_id'] != $this->userId) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                return;
            }
            
            echo json_encode($event);
            
        } catch (Exception $e) {
            error_log("Error loading event: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error']);
        }
    }
    
    /**
     * Prepare event data from POST
     * Title format: "Service - HJ-XXXXXX MMDDYYYY" or directly the Form Contract nomenclature
     */
private function prepareEventData($post) {
    // Get title from form - should be Form Contract nomenclature (HJ-XXXXXX MMDDYYYY)
    $title = !empty($post['title']) ? sanitize($post['title']) : null;

    // If no title provided, use "Service" as fallback
    // The calendar should NOT generate nomenclature - that's Form Contract's job
    if (empty($title)) {
        $title = 'Service';
    }

    // If title is a Form Contract nomenclature (HJ-...), optionally prefix with "Service - "
    // Check if it already starts with "Service - " to avoid duplicating
    if (preg_match('/^HJ-\d{6}\d{8}$/i', $title)) {
        $title = 'Service - ' . $title;
    }

    return [
        'user_id' => $this->userId,
        'category_id' => !empty($post['category_id']) ? intval($post['category_id']) : null,
        'title' => $title, // NEVER NULL
        'description' => sanitize($post['description'] ?? ''),
        'location' => sanitize($post['location'] ?? ''),
        'client' => sanitize($post['client'] ?? ''),
        'start_date' => $post['start_date'],
        'end_date' => $post['end_date'],
        'start_time' => !empty($post['start_time']) ? $post['start_time'] : null,
        'end_time' => !empty($post['end_time']) ? $post['end_time'] : null,
        'is_all_day' => isset($post['is_all_day']),
        'status' => $post['status'] ?? STATUS_PENDING,
        'priority' => $post['priority'] ?? PRIORITY_NORMAL,
        'document_date' => !empty($post['document_date']) ? $post['document_date'] : $post['start_date'],
        'execution_date' => !empty($post['execution_date']) ? $post['execution_date'] : null,
        'frequency_months' => !empty($post['frequency_months']) ? intval($post['frequency_months']) : null,
        'frequency_years' => !empty($post['frequency_years']) ? intval($post['frequency_years']) : 1,
        'is_reschedulable' => 1,
        'original_date' => $post['start_date']
    ];
}