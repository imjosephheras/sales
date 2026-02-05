<?php
/**
 * ============================================================
 * EVENT CLASS - With Intelligent Scheduling
 * COMPATIBLE VERSION - Works with or without migration
 * ============================================================
 */

class Event {
    private $conn;
    private $table = 'events';
    
    // Event properties
    public $event_id;
    public $user_id;
    public $category_id;
    public $title;
    public $description;
    public $location;
    public $start_date;
    public $end_date;
    public $start_time;
    public $end_time;
    public $is_all_day;
    public $is_recurring;
    public $recurring_pattern_id;
    public $status;
    public $priority;
    
    // Scheduling properties (NEW)
    public $document_date;
    public $execution_date;
    public $frequency_months;
    public $frequency_years;
    public $next_service_date;
    public $is_reschedulable;
    public $original_date;
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }
    
    /**
     * Create new event with scheduling support
     */
    public function create($data) {
        // Check if scheduling columns exist
        $hasSchedulingColumns = $this->checkSchedulingColumns();
        
        if ($hasSchedulingColumns) {
            $query = "INSERT INTO {$this->table} SET
                user_id = :user_id,
                category_id = :category_id,
                title = :title,
                description = :description,
                location = :location,
                client = :client,
                start_date = :start_date,
                end_date = :end_date,
                start_time = :start_time,
                end_time = :end_time,
                is_all_day = :is_all_day,
                is_recurring = :is_recurring,
                status = :status,
                priority = :priority,
                document_date = :document_date,
                execution_date = :execution_date,
                frequency_months = :frequency_months,
                frequency_years = :frequency_years,
                is_reschedulable = :is_reschedulable,
                original_date = :original_date,
                created_at = NOW()";
        } else {
            // Fallback query without scheduling fields
            $query = "INSERT INTO {$this->table} SET
                user_id = :user_id,
                category_id = :category_id,
                title = :title,
                description = :description,
                location = :location,
                client = :client,
                start_date = :start_date,
                end_date = :end_date,
                start_time = :start_time,
                end_time = :end_time,
                is_all_day = :is_all_day,
                is_recurring = :is_recurring,
                status = :status,
                priority = :priority,
                created_at = NOW()";
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind basic values
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':location', $data['location']);
        
        // Bind client (may be null)
        $client = $data['client'] ?? null;
        $stmt->bindParam(':client', $client);
        
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        $stmt->bindParam(':start_time', $data['start_time']);
        $stmt->bindParam(':end_time', $data['end_time']);
        $stmt->bindParam(':is_all_day', $data['is_all_day'], PDO::PARAM_BOOL);
        $stmt->bindParam(':is_recurring', $data['is_recurring'], PDO::PARAM_BOOL);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':priority', $data['priority']);
        
        // Bind scheduling fields only if columns exist
        if ($hasSchedulingColumns) {
            $document_date = $data['document_date'] ?? null;
            $execution_date = $data['execution_date'] ?? null;
            $frequency_months = $data['frequency_months'] ?? null;
            $frequency_years = $data['frequency_years'] ?? 1;
            $is_reschedulable = $data['is_reschedulable'] ?? true;
            $original_date = $data['original_date'] ?? $data['start_date'];
            
            $stmt->bindParam(':document_date', $document_date);
            $stmt->bindParam(':execution_date', $execution_date);
            $stmt->bindParam(':frequency_months', $frequency_months);
            $stmt->bindParam(':frequency_years', $frequency_years);
            $stmt->bindParam(':is_reschedulable', $is_reschedulable, PDO::PARAM_BOOL);
            $stmt->bindParam(':original_date', $original_date);
        }
        
        if ($stmt->execute()) {
            $eventId = $this->conn->lastInsertId();
            
            // Calculate next service date if frequency is set and columns exist
            if ($hasSchedulingColumns && isset($frequency_months) && $frequency_months && isset($execution_date) && $execution_date) {
                $this->calculateNextServiceDate($eventId);
            }
            
            return $eventId;
        }
        
        return false;
    }
    
    /**
     * Check if scheduling columns exist in database
     */
    private function checkSchedulingColumns() {
        try {
            $query = "SHOW COLUMNS FROM {$this->table} LIKE 'document_date'";
            $stmt = $this->conn->query($query);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if view exists
     */
    private function checkViewExists($viewName) {
        try {
            $query = "SHOW TABLES LIKE '$viewName'";
            $stmt = $this->conn->query($query);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if stored procedure exists
     */
    private function checkProcedureExists($procName) {
        try {
            $query = "SHOW PROCEDURE STATUS WHERE Name = '$procName'";
            $stmt = $this->conn->query($query);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Reschedule event (drag & drop logic)
     */
    public function reschedule($eventId, $newDate, $userId) {
        // Check if stored procedure exists
        if ($this->checkProcedureExists('RescheduleEvent')) {
            // Use stored procedure
            $query = "CALL RescheduleEvent(:event_id, :new_date, :user_id)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            $stmt->bindParam(':new_date', $newDate);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result;
            }
        } else {
            // Fallback: manual reschedule
            $query = "UPDATE {$this->table} SET
                start_date = :new_date,
                execution_date = :new_date,
                updated_at = NOW()
            WHERE event_id = :event_id AND user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            $stmt->bindParam(':new_date', $newDate);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Event rescheduled'];
            }
        }
        
        return ['success' => false, 'message' => 'Failed to reschedule event'];
    }
    
    /**
     * Calculate next service date based on execution date + frequency
     */
    public function calculateNextServiceDate($eventId) {
        // Check if stored procedure exists
        if ($this->checkProcedureExists('CalculateNextServiceDate')) {
            $query = "CALL CalculateNextServiceDate(:event_id)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result;
            }
        }
        
        return null;
    }
    
    /**
     * Get event by ID with scheduling info
     */
    public function getById($eventId) {
        $hasSchedulingColumns = $this->checkSchedulingColumns();
        
        if ($hasSchedulingColumns) {
            $query = "SELECT
                e.*,
                ec.category_name,
                ec.color_hex,
                ec.icon,
                COALESCE(e.start_date, e.execution_date, e.document_date) as effective_date,
                DATEDIFF(e.next_service_date, CURDATE()) as days_until_next_service,
                CASE
                    WHEN e.execution_date IS NOT NULL AND e.execution_date != e.original_date
                    THEN TRUE
                    ELSE FALSE
                END as was_rescheduled
            FROM {$this->table} e
            LEFT JOIN event_categories ec ON e.category_id = ec.category_id
            WHERE e.event_id = :event_id AND COALESCE(e.is_active, TRUE) = TRUE
            LIMIT 1";
        } else {
            $query = "SELECT
                e.*,
                ec.category_name,
                ec.color_hex,
                ec.icon
            FROM {$this->table} e
            LEFT JOIN event_categories ec ON e.category_id = ec.category_id
            WHERE e.event_id = :event_id AND COALESCE(e.is_active, TRUE) = TRUE
            LIMIT 1";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get events by month with scheduling info
     * COMPATIBLE VERSION - Works with or without migration
     */
    public function getByMonth($userId, $year, $month) {
    $viewExists = $this->checkViewExists('v_scheduled_events');
    $hasSchedulingColumns = $this->checkSchedulingColumns();

    if ($viewExists) {
        // Use view if exists (after migration)
        $query = "SELECT
            e.*,
            ec.category_name,
            ec.color_hex,
            ec.icon,
            e.next_service_date,
            e.frequency_months,
            e.was_rescheduled,
            COALESCE(e.start_date, e.execution_date, e.document_date) as effective_date
        FROM v_scheduled_events e
        WHERE e.user_id = ?
        AND (
            (YEAR(e.start_date) = ? AND MONTH(e.start_date) = ?)
            OR (YEAR(e.end_date) = ? AND MONTH(e.end_date) = ?)
            OR (YEAR(e.next_service_date) = ? AND MONTH(e.next_service_date) = ?)
            OR (YEAR(e.execution_date) = ? AND MONTH(e.execution_date) = ?)
            OR (YEAR(e.document_date) = ? AND MONTH(e.document_date) = ?)
        )
        ORDER BY COALESCE(e.start_date, e.execution_date, e.document_date) ASC, e.start_time ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId, $year, $month, $year, $month, $year, $month, $year, $month, $year, $month]);

    } else if ($hasSchedulingColumns) {
        // Has columns but no view
        $query = "SELECT
            e.*,
            ec.category_name,
            ec.color_hex,
            ec.icon,
            e.next_service_date,
            e.frequency_months,
            COALESCE(e.start_date, e.execution_date, e.document_date) as effective_date
        FROM {$this->table} e
        LEFT JOIN event_categories ec ON e.category_id = ec.category_id
        WHERE e.user_id = ?
        AND COALESCE(e.is_active, TRUE) = TRUE
        AND (
            (YEAR(e.start_date) = ? AND MONTH(e.start_date) = ?)
            OR (YEAR(e.end_date) = ? AND MONTH(e.end_date) = ?)
            OR (YEAR(e.next_service_date) = ? AND MONTH(e.next_service_date) = ?)
            OR (YEAR(e.execution_date) = ? AND MONTH(e.execution_date) = ?)
            OR (YEAR(e.document_date) = ? AND MONTH(e.document_date) = ?)
        )
        ORDER BY COALESCE(e.start_date, e.execution_date, e.document_date) ASC, e.start_time ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId, $year, $month, $year, $month, $year, $month, $year, $month, $year, $month]);

    } else {
        // No scheduling columns (before migration)
        $query = "SELECT
            e.*,
            ec.category_name,
            ec.color_hex,
            ec.icon
        FROM {$this->table} e
        LEFT JOIN event_categories ec ON e.category_id = ec.category_id
        WHERE e.user_id = ?
        AND COALESCE(e.is_active, TRUE) = TRUE
        AND (
            (YEAR(e.start_date) = ? AND MONTH(e.start_date) = ?)
            OR (YEAR(e.end_date) = ? AND MONTH(e.end_date) = ?)
        )
        ORDER BY e.start_date ASC, e.start_time ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId, $year, $month, $year, $month]);
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    
    /**
     * Get upcoming services (next_service_date in range)
     */
    public function getUpcomingServices($userId, $days = 30) {
        $viewExists = $this->checkViewExists('v_scheduled_events');
        
        if ($viewExists) {
            $query = "SELECT 
                e.*,
                ec.category_name,
                ec.color_hex,
                ec.icon,
                e.days_until_next_service
            FROM v_scheduled_events e
            WHERE e.user_id = :user_id
            AND e.next_service_date IS NOT NULL
            AND e.next_service_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY e.next_service_date ASC";
        } else {
            // Fallback without view
            return [];
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update event with scheduling fields
     */
    public function update($eventId, $data) {
        $hasSchedulingColumns = $this->checkSchedulingColumns();
        
        if ($hasSchedulingColumns) {
            $query = "UPDATE {$this->table} SET
                category_id = :category_id,
                title = :title,
                description = :description,
                location = :location,
                client = :client,
                start_date = :start_date,
                end_date = :end_date,
                start_time = :start_time,
                end_time = :end_time,
                is_all_day = :is_all_day,
                status = :status,
                priority = :priority,
                document_date = :document_date,
                execution_date = :execution_date,
                frequency_months = :frequency_months,
                frequency_years = :frequency_years,
                updated_at = NOW()
            WHERE event_id = :event_id";
        } else {
            $query = "UPDATE {$this->table} SET
                category_id = :category_id,
                title = :title,
                description = :description,
                location = :location,
                client = :client,
                start_date = :start_date,
                end_date = :end_date,
                start_time = :start_time,
                end_time = :end_time,
                is_all_day = :is_all_day,
                status = :status,
                priority = :priority,
                updated_at = NOW()
            WHERE event_id = :event_id";
        }
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':location', $data['location']);
        
        // Bind client (may be null)
        $client = $data['client'] ?? null;
        $stmt->bindParam(':client', $client);
        
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        $stmt->bindParam(':start_time', $data['start_time']);
        $stmt->bindParam(':end_time', $data['end_time']);
        $stmt->bindParam(':is_all_day', $data['is_all_day'], PDO::PARAM_BOOL);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':priority', $data['priority']);
        
        if ($hasSchedulingColumns) {
            $document_date = $data['document_date'] ?? null;
            $execution_date = $data['execution_date'] ?? null;
            $frequency_months = $data['frequency_months'] ?? null;
            $frequency_years = $data['frequency_years'] ?? 1;
            
            $stmt->bindParam(':document_date', $document_date);
            $stmt->bindParam(':execution_date', $execution_date);
            $stmt->bindParam(':frequency_months', $frequency_months);
            $stmt->bindParam(':frequency_years', $frequency_years);
        }
        
        if ($stmt->execute()) {
            // Recalculate next service date if frequency changed
            if ($hasSchedulingColumns && isset($frequency_months) && $frequency_months && isset($execution_date) && $execution_date) {
                $this->calculateNextServiceDate($eventId);
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Change event status
     */
    public function changeStatus($eventId, $newStatus) {
        $query = "UPDATE {$this->table} SET
            status = :status,
            updated_at = NOW()
        WHERE event_id = :event_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->bindParam(':status', $newStatus);
        
        return $stmt->execute();
    }
    
    /**
     * Get events for today
     */
    public function getToday($userId) {
        $today = date('Y-m-d');
        $hasSchedulingColumns = $this->checkSchedulingColumns();

        if ($hasSchedulingColumns) {
            $query = "SELECT
                e.*,
                ec.category_name,
                ec.color_hex,
                ec.icon,
                COALESCE(e.start_date, e.execution_date, e.document_date) as effective_date
            FROM {$this->table} e
            LEFT JOIN event_categories ec ON e.category_id = ec.category_id
            WHERE e.user_id = :user_id
            AND COALESCE(e.start_date, e.execution_date, e.document_date) = :today
            AND COALESCE(e.is_active, TRUE) = TRUE
            ORDER BY e.start_time ASC";
        } else {
            $query = "SELECT
                e.*,
                ec.category_name,
                ec.color_hex,
                ec.icon
            FROM {$this->table} e
            LEFT JOIN event_categories ec ON e.category_id = ec.category_id
            WHERE e.user_id = :user_id
            AND e.start_date = :today
            AND COALESCE(e.is_active, TRUE) = TRUE
            ORDER BY e.start_time ASC";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':today', $today);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get events for the next 7 days (including today)
     */
    public function getNext7Days($userId) {
        $today = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+7 days'));
        $hasSchedulingColumns = $this->checkSchedulingColumns();

        if ($hasSchedulingColumns) {
            $query = "SELECT
                e.*,
                ec.category_name,
                ec.color_hex,
                ec.icon,
                COALESCE(e.start_date, e.execution_date, e.document_date) as effective_date
            FROM {$this->table} e
            LEFT JOIN event_categories ec ON e.category_id = ec.category_id
            WHERE e.user_id = :user_id
            AND COALESCE(e.start_date, e.execution_date, e.document_date) >= :today
            AND COALESCE(e.start_date, e.execution_date, e.document_date) <= :end_date
            AND COALESCE(e.is_active, TRUE) = TRUE
            ORDER BY COALESCE(e.start_date, e.execution_date, e.document_date) ASC, e.start_time ASC";
        } else {
            $query = "SELECT
                e.*,
                ec.category_name,
                ec.color_hex,
                ec.icon
            FROM {$this->table} e
            LEFT JOIN event_categories ec ON e.category_id = ec.category_id
            WHERE e.user_id = :user_id
            AND e.start_date >= :today
            AND e.start_date <= :end_date
            AND COALESCE(e.is_active, TRUE) = TRUE
            ORDER BY e.start_date ASC, e.start_time ASC";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':today', $today);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Delete event (soft delete)
     */
    public function delete($eventId) {
        $query = "UPDATE {$this->table} SET
            is_active = FALSE,
            updated_at = NOW()
        WHERE event_id = :event_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Parse frequency code (e.g., "03-01" -> 3 months, 1 year)
     */
    public static function parseFrequencyCode($code) {
        if (!$code || !str_contains($code, '-')) {
            return ['months' => null, 'years' => 1];
        }
        
        list($months, $years) = explode('-', $code);
        
        return [
            'months' => intval($months),
            'years' => intval($years)
        ];
    }
    
    /**
     * Generate frequency code (e.g., 3 months, 1 year -> "03-01")
     */
    public static function generateFrequencyCode($months, $years = 1) {
        return sprintf('%02d-%02d', $months, $years);
    }
}