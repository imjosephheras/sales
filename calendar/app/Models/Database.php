<?php
/**
 * ============================================================
 * DATABASE CONNECTION CLASS
 * ============================================================
 * Maneja la conexión a la base de datos usando PDO
 * Implementa patrón Singleton para una única instancia
 * ============================================================
 */

class Database {
    
    // Instancia única de la clase (Singleton)
    private static $instance = null;
    
    // Objeto PDO
    private $connection;
    
    // Configuración de la base de datos
    private $host = 'localhost';
    private $dbname = 'calendar_system';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    
    /**
     * Constructor privado (Singleton pattern)
     */
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => true, // Conexiones persistentes para mejor rendimiento
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $e) {
            // En producción, registra esto en un log en lugar de mostrarlo
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener la instancia única de Database (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtener la conexión PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Prevenir clonación del objeto
     */
    private function __clone() {}
    
    /**
     * Prevenir deserialización
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}