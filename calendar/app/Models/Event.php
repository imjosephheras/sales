<?php
/**
 * Event Model - BASIC
 * Reads events from the database. Does not infer, transform, or compute anything.
 * The only date used for calendar placement is: start_date
 */

class Event {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Get events for a given month/year.
     * Only returns events that have a start_date in that month.
     */
    public function getByMonth($year, $month) {
        $query = "SELECT
                e.event_id,
                e.title,
                e.description,
                e.client,
                e.location,
                e.start_date,
                e.status,
                e.category_id,
                ec.category_name,
                ec.color_hex
            FROM events e
            LEFT JOIN event_categories ec ON e.category_id = ec.category_id
            WHERE e.is_active = 1
              AND e.start_date IS NOT NULL
              AND YEAR(e.start_date) = :year
              AND MONTH(e.start_date) = :month
            ORDER BY e.start_date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->bindParam(':month', $month, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single event by ID.
     */
    public function getById($eventId) {
        $query = "SELECT
                e.event_id,
                e.title,
                e.description,
                e.client,
                e.location,
                e.start_date,
                e.status,
                e.priority,
                e.category_id,
                ec.category_name,
                ec.color_hex
            FROM events e
            LEFT JOIN event_categories ec ON e.category_id = ec.category_id
            WHERE e.event_id = :event_id
              AND e.is_active = 1
            LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new event. Returns the new event ID or false.
     */
    public function create($data) {
        $query = "INSERT INTO events (
                user_id, category_id, title, description, client, location,
                start_date, end_date, status, priority,
                is_all_day, is_active, created_at
            ) VALUES (
                :user_id, :category_id, :title, :description, :client, :location,
                :start_date, :start_date, :status, :priority,
                1, 1, NOW()
            )";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':client', $data['client']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':priority', $data['priority']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Update an existing event.
     */
    public function update($eventId, $data) {
        $query = "UPDATE events SET
                category_id = :category_id,
                title = :title,
                description = :description,
                client = :client,
                location = :location,
                start_date = :start_date,
                end_date = :start_date,
                status = :status,
                priority = :priority,
                updated_at = NOW()
            WHERE event_id = :event_id AND is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':client', $data['client']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':priority', $data['priority']);

        return $stmt->execute();
    }

    /**
     * Change start_date only (for drag & drop reschedule).
     */
    public function reschedule($eventId, $newDate) {
        $query = "UPDATE events SET
                start_date = :new_date,
                end_date = :new_date,
                updated_at = NOW()
            WHERE event_id = :event_id AND is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->bindParam(':new_date', $newDate);

        return $stmt->execute();
    }

    /**
     * Change event status.
     */
    public function changeStatus($eventId, $newStatus) {
        $query = "UPDATE events SET
                status = :status,
                updated_at = NOW()
            WHERE event_id = :event_id AND is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->bindParam(':status', $newStatus);

        return $stmt->execute();
    }

    /**
     * Soft delete.
     */
    public function delete($eventId) {
        $query = "UPDATE events SET
                is_active = 0,
                updated_at = NOW()
            WHERE event_id = :event_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
