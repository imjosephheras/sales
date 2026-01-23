<?php
/**
 * ============================================================
 * REMINDER CLASS
 * ============================================================
 * Maneja recordatorios de eventos
 * ============================================================
 */

require_once 'Database.php';

class Reminder {
    
    private $db;
    private $conn;
    
    // Propiedades
    public $reminder_id;
    public $event_id;
    public $remind_before_minutes;
    public $reminder_type;
    public $is_sent;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Crear recordatorio
     */
    public function create($event_id, $remind_before_minutes, $reminder_type = 'popup') {
        try {
            $query = "INSERT INTO reminders (event_id, remind_before_minutes, reminder_type) 
                      VALUES (:event_id, :remind_before_minutes, :reminder_type)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':event_id', $event_id);
            $stmt->bindParam(':remind_before_minutes', $remind_before_minutes);
            $stmt->bindParam(':reminder_type', $reminder_type);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Error creando recordatorio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener recordatorios de un evento
     */
    public function getByEvent($event_id) {
        try {
            $query = "SELECT * FROM reminders 
                      WHERE event_id = :event_id 
                      ORDER BY remind_before_minutes ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':event_id', $event_id);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error obteniendo recordatorios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener recordatorios pendientes (no enviados)
     */
    public function getPendingReminders() {
        try {
            $query = "SELECT r.*, e.title, e.start_date, e.start_time, e.user_id
                      FROM reminders r
                      JOIN events e ON r.event_id = e.event_id
                      WHERE r.is_sent = 0
                        AND e.status != 'cancelled'
                        AND CONCAT(e.start_date, ' ', IFNULL(e.start_time, '00:00:00')) 
                            > DATE_ADD(NOW(), INTERVAL r.remind_before_minutes MINUTE)
                      ORDER BY e.start_date, e.start_time";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error obteniendo recordatorios pendientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Marcar recordatorio como enviado
     */
    public function markAsSent($reminder_id) {
        try {
            $query = "UPDATE reminders 
                      SET is_sent = 1, sent_at = CURRENT_TIMESTAMP 
                      WHERE reminder_id = :reminder_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':reminder_id', $reminder_id);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error marcando recordatorio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar recordatorio
     */
    public function delete($reminder_id) {
        try {
            $query = "DELETE FROM reminders WHERE reminder_id = :reminder_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':reminder_id', $reminder_id);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error eliminando recordatorio: " . $e->getMessage());
            return false;
        }
    }
}