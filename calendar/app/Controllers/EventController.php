<?php
/**
 * Event Controller - BASIC
 * Simple CRUD. No series, no recurrence, no frequency logic.
 */

class EventController {

    private $eventModel;
    private $userId;

    public function __construct() {
        $this->eventModel = new Event();
        $this->userId = getCurrentUserId();
    }

    /**
     * Save event (create or update) from POST.
     */
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('../../index.php');
            return;
        }

        try {
            $eventId = !empty($_POST['event_id']) ? intval($_POST['event_id']) : null;

            $data = [
                'user_id'     => $this->userId,
                'category_id' => !empty($_POST['category_id']) ? intval($_POST['category_id']) : null,
                'title'       => sanitize($_POST['title'] ?? ''),
                'description' => sanitize($_POST['description'] ?? ''),
                'client'      => sanitize($_POST['client'] ?? ''),
                'location'    => sanitize($_POST['location'] ?? ''),
                'start_date'  => $_POST['start_date'] ?? date('Y-m-d'),
                'status'      => $_POST['status'] ?? 'pending',
                'priority'    => $_POST['priority'] ?? 'normal',
            ];

            if ($eventId) {
                $success = $this->eventModel->update($eventId, $data);
                setFlashMessage($success ? 'Event updated' : 'Error updating event', $success ? 'success' : 'error');
            } else {
                $newId = $this->eventModel->create($data);
                setFlashMessage($newId ? 'Event created' : 'Error creating event', $newId ? 'success' : 'error');
            }
        } catch (Exception $e) {
            error_log("Error saving event: " . $e->getMessage());
            setFlashMessage('Error: ' . $e->getMessage(), 'error');
        }

        redirect('../../index.php');
    }

    /**
     * Delete event (JSON endpoint).
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
            $success = $this->eventModel->delete($eventId);
            echo json_encode(['success' => $success, 'message' => 'Event deleted']);
        } catch (Exception $e) {
            error_log("Delete error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
    }

    /**
     * Reschedule event (JSON endpoint).
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
            echo json_encode(['success' => false, 'error' => 'Missing event_id or new_date']);
            return;
        }

        if (!validateDate($newDate)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid date format']);
            return;
        }

        try {
            $success = $this->eventModel->reschedule($eventId, $newDate);
            echo json_encode(['success' => $success, 'message' => 'Event rescheduled to ' . $newDate]);
        } catch (Exception $e) {
            error_log("Reschedule error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
    }

    /**
     * Toggle status (JSON endpoint).
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
            echo json_encode(['success' => false, 'error' => 'Missing fields']);
            return;
        }

        try {
            $success = $this->eventModel->changeStatus($eventId, $newStatus);
            echo json_encode(['success' => $success, 'event_id' => $eventId, 'status' => $newStatus]);
        } catch (Exception $e) {
            error_log("Status error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
    }

    /**
     * Get event by ID (JSON endpoint).
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
            if (!$event) {
                http_response_code(404);
                echo json_encode(['error' => 'Event not found']);
                return;
            }
            echo json_encode($event);
        } catch (Exception $e) {
            error_log("Error loading event: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error']);
        }
    }
}
