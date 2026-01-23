<?php
/**
 * ============================================================
 * USER CLASS
 * ============================================================
 * Maneja todas las operaciones relacionadas con usuarios
 * ============================================================
 */

require_once 'Database.php';

class User {
    
    private $db;
    private $conn;
    
    // Propiedades del usuario
    public $user_id;
    public $username;
    public $email;
    public $password_hash;
    public $full_name;
    public $timezone;
    public $created_at;
    public $is_active;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Crear nuevo usuario
     */
    public function create($username, $email, $password, $full_name, $timezone = 'America/Chicago') {
        try {
            $query = "INSERT INTO users (username, email, password_hash, full_name, timezone) 
                      VALUES (:username, :email, :password_hash, :full_name, :timezone)";
            
            $stmt = $this->conn->prepare($query);
            
            // Hash de la contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':timezone', $timezone);
            
            if ($stmt->execute()) {
                $this->user_id = $this->conn->lastInsertId();
                
                // Crear configuración de calendario por defecto
                $this->createDefaultSettings($this->user_id);
                
                return $this->user_id;
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Error creando usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener usuario por ID
     */
    public function getById($user_id) {
        try {
            $query = "SELECT * FROM users WHERE user_id = :user_id AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $row = $stmt->fetch();
            
            if ($row) {
                $this->user_id = $row['user_id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->full_name = $row['full_name'];
                $this->timezone = $row['timezone'];
                $this->created_at = $row['created_at'];
                $this->is_active = $row['is_active'];
                return true;
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Error obteniendo usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener usuario por email
     */
    public function getByEmail($email) {
        try {
            $query = "SELECT * FROM users WHERE email = :email AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Error obteniendo usuario por email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar login
     */
    public function login($email, $password) {
        try {
            $user = $this->getByEmail($email);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $this->user_id = $user['user_id'];
                $this->username = $user['username'];
                $this->email = $user['email'];
                $this->full_name = $user['full_name'];
                $this->timezone = $user['timezone'];
                
                return true;
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar usuario
     */
    public function update($user_id, $data) {
        try {
            $fields = [];
            $params = [':user_id' => $user_id];
            
            if (isset($data['username'])) {
                $fields[] = "username = :username";
                $params[':username'] = $data['username'];
            }
            if (isset($data['email'])) {
                $fields[] = "email = :email";
                $params[':email'] = $data['email'];
            }
            if (isset($data['full_name'])) {
                $fields[] = "full_name = :full_name";
                $params[':full_name'] = $data['full_name'];
            }
            if (isset($data['timezone'])) {
                $fields[] = "timezone = :timezone";
                $params[':timezone'] = $data['timezone'];
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Error actualizando usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear configuración por defecto del calendario
     */
    private function createDefaultSettings($user_id) {
        try {
            $query = "INSERT INTO calendar_settings (user_id) VALUES (:user_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error creando configuración: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar usuario (soft delete)
     */
    public function delete($user_id) {
        try {
            $query = "UPDATE users SET is_active = 0 WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error eliminando usuario: " . $e->getMessage());
            return false;
        }
    }
}