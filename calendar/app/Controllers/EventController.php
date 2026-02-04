<?php
/**
 * ============================================================
 * EVENT CONTROLLER - SIMPLIFIED SYSTEM
 * Work Date based, no automatic recurrence
 * Data comes from Request Form
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
     * NO automatic series creation - recurrences are adjusted manually
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
                // UPDATE existing event
                unset($eventData['user_id']);
                $success = $this->eventModel->update($eventId, $eventData);
                $message = $success ? 'Event updated successfully' : 'Error updating event';
                setFlashMessage($message, $success ? 'success' : 'error');
            } else {
                // CREATE new event (single event, no automatic series)
                $newEventId = $this->eventModel->create($eventData);

                if (!$newEventId) {
                    setFlashMessage('Error creating event', 'error');
                    redirect('../../index.php');
                    return;
                }

                // Mark as standalone event (no automatic series)
                $this->eventModel->update($newEventId, [
                    'series_master_id' => null,
                    'series_index' => 0,
                    'series_total' => 1
                ]);

                setFlashMessage('Event created successfully', 'success');
            }

        } catch (Exception $e) {
            error_log("Error saving event: " . $e->getMessage());
            setFlashMessage('Error: ' . $e->getMessage(), 'error');
        }

        redirect('../../index.php');
    }

    /**
     * Delete event
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

            // Check if master (has children)
            if ($event['series_master_id'] === null && $event['series_total'] > 1) {
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
                // Delete single event
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
     * Reschedule event (changes Work Date)
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

            // Update Work Date (start_date and end_date are the same)
            $updateData = [
                'start_date' => $newDate,
                'end_date' => $newDate,
                'execution_date' => $newDate
            ];

            $success = $this->eventModel->update($eventId, $updateData);

            echo json_encode([
                'success' => $success,
                'message' => 'Event rescheduled to ' . $newDate
            ]);

        } catch (Exception $e) {
            error_log("Reschedule error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
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
     *
     * Title = Nomenclature (HJ-10000112222026) - comes from Request Form
     * Work Date = main scheduling date (stored as start_date and end_date)
     * Document Date = informational only
     * No automatic recurrence creation
     */
    private function prepareEventData($post) {
        // Title/Nomenclature comes directly from Request Form
        // Format: HJ-10000112222026
        $title = !empty($post['title']) ? sanitize($post['title']) : 'Service';

        // Work Date is the main scheduling date
        $workDate = !empty($post['work_date']) ? $post['work_date'] : date('Y-m-d');

        // Document Date is informational (when the work order was created)
        $documentDate = !empty($post['document_date']) ? $post['document_date'] : null;

        return [
            'user_id' => $this->userId,
            'category_id' => !empty($post['category_id']) ? intval($post['category_id']) : null,
            'title' => $title,
            'description' => sanitize($post['description'] ?? ''),
            'location' => sanitize($post['location'] ?? ''),
            'client' => sanitize($post['client'] ?? ''),

            // Work Date is stored as both start_date and end_date (same day)
            'start_date' => $workDate,
            'end_date' => $workDate,

            // No time fields - simplified system
            'start_time' => null,
            'end_time' => null,
            'is_all_day' => 1,

            // Status and Priority
            'status' => $post['status'] ?? STATUS_PENDING,
            'priority' => $post['priority'] ?? PRIORITY_NORMAL,

            // Scheduling fields
            'document_date' => $documentDate,
            'execution_date' => $workDate,

            // Frequency and Duration stored but NOT used for automatic recurrence
            'frequency_months' => !empty($post['frequency_months']) ? intval($post['frequency_months']) : null,
            'frequency_years' => !empty($post['frequency_years']) ? intval($post['frequency_years']) : 1,

            // Metadata
            'is_reschedulable' => 1,
            'original_date' => $workDate,

            // Request Form link
            'form_id' => !empty($post['request_id']) ? intval($post['request_id']) : null
        ];
    }

    /**
     * Manual series creation (called explicitly by user, not automatic)
     * This can be called later if user wants to create recurrence manually
     */
    public function createManualSeries($masterId, $frequencyMonths, $durationYears) {
        if (!$frequencyMonths || $frequencyMonths <= 0 || !$durationYears || $durationYears <= 0) {
            return 1; // No series to create
        }

        $master = $this->eventModel->getById($masterId);
        if (!$master) {
            return 0;
        }

        $total = floor(($durationYears * 12) / $frequencyMonths);
        $baseDate = new DateTime($master['start_date']);
        $originalDay = (int)$baseDate->format('d');

        // Update master event
        $this->eventModel->update($masterId, [
            'series_master_id' => null,
            'series_index' => 0,
            'series_total' => $total + 1
        ]);

        $created = 1;

        // Create child events
        for ($i = 1; $i <= $total; $i++) {
            try {
                $newDate = clone $baseDate;
                $monthsToAdd = $frequencyMonths * $i;
                $newDate->modify("+{$monthsToAdd} months");

                // Handle day overflow
                $newMonth = (int)$newDate->format('m');
                $newYear = (int)$newDate->format('Y');
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $newMonth, $newYear);
                $dayToUse = min($originalDay, $daysInMonth);
                $newDate->setDate($newYear, $newMonth, $dayToUse);

                $newDateStr = $newDate->format('Y-m-d');

                $childData = [
                    'user_id' => $master['user_id'],
                    'category_id' => $master['category_id'],
                    'title' => $master['title'],
                    'description' => $master['description'],
                    'location' => $master['location'],
                    'client' => $master['client'],
                    'start_date' => $newDateStr,
                    'end_date' => $newDateStr,
                    'start_time' => null,
                    'end_time' => null,
                    'is_all_day' => 1,
                    'status' => $master['status'],
                    'priority' => $master['priority'],
                    'document_date' => $master['document_date'],
                    'execution_date' => $newDateStr,
                    'frequency_months' => $frequencyMonths,
                    'frequency_years' => $durationYears,
                    'series_master_id' => $masterId,
                    'series_index' => $i,
                    'series_total' => $total + 1,
                    'form_id' => $master['form_id']
                ];

                if ($this->eventModel->create($childData)) {
                    $created++;
                }

            } catch (Exception $e) {
                error_log("Error creating child #{$i}: " . $e->getMessage());
            }
        }

        return $created;
    }
}
