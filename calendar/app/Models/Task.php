<?php
/**
 * ============================================================
 * TASK CLASS
 * ============================================================
 * Maneja tareas pendientes (pueden estar ligadas a eventos)
 * ============================================================
 */

require_once 'Database.php';

class Task {
    
    private $db;
    private $conn;
    
    // Propiedades
    public $task_id;
    public $user_id;
    public $event_id;
    public $title;
    public $description;
    public $due_date;
    public $due_time;
    public $is_completed;
    public $priority;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Crear nueva tarea
     */
    public function create($data) {
        try {
            $query = "INSERT INTO tasks (
                        user_id, event_id, title, description,
                        due_date, due_time, priority
                      ) VALUES (
                        :user_id, :event_id, :title, :description,
                        :due_date, :due_time, :priority
                      )";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':event_id', $data['event_id']);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':due_date', $data['due_date']);
            $stmt->bindParam(':due_time', $data['due_time']);
            
            $priority = $data['priority'] ?? 'normal';
            $stmt->bindParam(':priority', $priority);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Error creando tarea: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todas las tareas pendientes de un usuario
     */
    public function getPending($user_id, $days = 60) {
        try {
            $today = date('Y-m-d');
            $end_date = date('Y-m-d', strtotime("+{$days} days"));
            
            $query = "SELECT t.*, e.title as event_title
                      FROM tasks t
                      LEFT JOIN events e ON t.event_id = e.event_id
                      WHERE t.user_id = :user_id
                        AND t.is_completed = 0
                        AND (t.due_date IS NULL OR t.due_date BETWEEN :today AND :end_date)
                      ORDER BY t.due_date ASC, t.priority DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':today', $today);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error obteniendo tareas pendientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener tareas completadas
     */
    public function getCompleted($user_id, $limit = 50) {
        try {
            $query = "SELECT t.*, e.title as event_title
                      FROM tasks t
                      LEFT JOIN events e ON t.event_id = e.event_id
                      WHERE t.user_id = :user_id
                        AND t.is_completed = 1
                      ORDER BY t.completed_at DESC
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error obteniendo tareas completadas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Marcar tarea como completada
     */
    public function complete($task_id) {
        try {
            $query = "UPDATE tasks 
                      SET is_completed = 1, 
                          completed_at = CURRENT_TIMESTAMP 
                      WHERE task_id = :task_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':task_id', $task_id);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error completando tarea: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Desmarcar tarea (volver a pendiente)
     */
    public function uncomplete($task_id) {
        try {
            $query = "UPDATE tasks 
                      SET is_completed = 0, 
                          completed_at = NULL 
                      WHERE task_id = :task_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':task_id', $task_id);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error desmarcando tarea: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar tarea
     */
    public function update($task_id, $data) {
        try {
            $fields = [];
            $params = [':task_id' => $task_id];
            
            $allowed_fields = ['title', 'description', 'due_date', 'due_time', 'priority'];
            
            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $query = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE task_id = :task_id";
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Error actualizando tarea: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar tarea
     */
    public function delete($task_id) {
        try {
            $query = "DELETE FROM tasks WHERE task_id = :task_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':task_id', $task_id);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error eliminando tarea: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener tareas de un evento específico
     */
    public function getByEvent($event_id) {
        try {
            $query = "SELECT * FROM tasks 
                      WHERE event_id = :event_id 
                      ORDER BY is_completed ASC, priority DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':event_id', $event_id);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error obteniendo tareas del evento: " . $e->getMessage());
            return [];
        }
    }
}