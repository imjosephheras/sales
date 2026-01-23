<?php
/**
 * ============================================================
 * CATEGORY CLASS
 * ============================================================
 * Maneja categorías de eventos (Reuniones, Cumpleaños, etc.)
 * ============================================================
 */

require_once 'Database.php';

class Category {
    
    private $db;
    private $conn;
    
    // Propiedades
    public $category_id;
    public $user_id;
    public $category_name;
    public $color_hex;
    public $icon;
    public $is_default;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Crear nueva categoría
     */
    public function create($user_id, $category_name, $color_hex = '#1a73e8', $icon = null, $is_default = false) {
        try {
            $query = "INSERT INTO event_categories (user_id, category_name, color_hex, icon, is_default) 
                      VALUES (:user_id, :category_name, :color_hex, :icon, :is_default)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':category_name', $category_name);
            $stmt->bindParam(':color_hex', $color_hex);
            $stmt->bindParam(':icon', $icon);
            $stmt->bindParam(':is_default', $is_default, PDO::PARAM_BOOL);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Error creando categoría: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todas las categorías de un usuario
     */
    public function getAllByUser($user_id) {
        try {
            $query = "SELECT * FROM event_categories 
                      WHERE user_id = :user_id 
                      ORDER BY is_default DESC, category_name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error obteniendo categorías: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener categoría por ID
     */
    public function getById($category_id) {
        try {
            $query = "SELECT * FROM event_categories WHERE category_id = :category_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Error obteniendo categoría: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar categoría
     */
    public function update($category_id, $data) {
        try {
            $fields = [];
            $params = [':category_id' => $category_id];
            
            if (isset($data['category_name'])) {
                $fields[] = "category_name = :category_name";
                $params[':category_name'] = $data['category_name'];
            }
            if (isset($data['color_hex'])) {
                $fields[] = "color_hex = :color_hex";
                $params[':color_hex'] = $data['color_hex'];
            }
            if (isset($data['icon'])) {
                $fields[] = "icon = :icon";
                $params[':icon'] = $data['icon'];
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $query = "UPDATE event_categories SET " . implode(', ', $fields) . " WHERE category_id = :category_id";
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Error actualizando categoría: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar categoría
     */
    public function delete($category_id) {
        try {
            // Primero, desvincula los eventos de esta categoría
            $query1 = "UPDATE events SET category_id = NULL WHERE category_id = :category_id";
            $stmt1 = $this->conn->prepare($query1);
            $stmt1->bindParam(':category_id', $category_id);
            $stmt1->execute();
            
            // Luego elimina la categoría
            $query2 = "DELETE FROM event_categories WHERE category_id = :category_id";
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bindParam(':category_id', $category_id);
            
            return $stmt2->execute();
            
        } catch (PDOException $e) {
            error_log("Error eliminando categoría: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear categorías por defecto para un nuevo usuario
     */
    public function createDefaultCategories($user_id) {
        $defaults = [
            ['name' => 'Reuniones', 'color' => '#1a73e8', 'icon' => '👥'],
            ['name' => 'Cumpleaños', 'color' => '#e67c73', 'icon' => '🎂'],
            ['name' => 'Llamadas', 'color' => '#33b679', 'icon' => '📞'],
            ['name' => 'Viajes', 'color' => '#f4511e', 'icon' => '✈️'],
            ['name' => 'Personal', 'color' => '#8e24aa', 'icon' => '🏠'],
        ];
        
        foreach ($defaults as $index => $cat) {
            $this->create(
                $user_id,
                $cat['name'],
                $cat['color'],
                $cat['icon'],
                $index === 0 // Primera categoría como default
            );
        }
    }
}