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

            // Ensure required tables exist
            $this->initializeTables();

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
     * Create required tables if they don't exist
     */
    private function initializeTables() {
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS event_categories (
                category_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                category_name VARCHAR(255) NOT NULL,
                color_hex VARCHAR(7) DEFAULT '#1a73e8',
                icon VARCHAR(50) DEFAULT NULL,
                is_default TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS events (
                event_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                category_id INT DEFAULT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT DEFAULT NULL,
                client VARCHAR(255) DEFAULT NULL,
                location VARCHAR(255) DEFAULT NULL,
                start_date DATE DEFAULT NULL,
                end_date DATE DEFAULT NULL,
                start_time TIME DEFAULT NULL,
                end_time TIME DEFAULT NULL,
                is_all_day TINYINT(1) DEFAULT 1,
                is_recurring TINYINT(1) DEFAULT 0,
                status VARCHAR(50) DEFAULT 'pending',
                priority VARCHAR(50) DEFAULT 'normal',
                document_date DATE DEFAULT NULL,
                original_date DATE DEFAULT NULL,
                form_id INT DEFAULT NULL,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_start_date (start_date),
                INDEX idx_category_id (category_id),
                INDEX idx_form_id (form_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Ensure all required columns exist (table may have been created with an older schema)
        $columns = [
            ['events', 'user_id',       'INT NOT NULL DEFAULT 1 AFTER `event_id`'],
            ['events', 'category_id',   'INT DEFAULT NULL AFTER `user_id`'],
            ['events', 'title',         'VARCHAR(255) NOT NULL DEFAULT "" AFTER `category_id`'],
            ['events', 'description',   'TEXT DEFAULT NULL AFTER `title`'],
            ['events', 'client',        'VARCHAR(255) DEFAULT NULL AFTER `description`'],
            ['events', 'location',      'VARCHAR(255) DEFAULT NULL AFTER `client`'],
            ['events', 'start_date',    'DATE DEFAULT NULL AFTER `location`'],
            ['events', 'end_date',      'DATE DEFAULT NULL AFTER `start_date`'],
            ['events', 'start_time',    'TIME DEFAULT NULL AFTER `end_date`'],
            ['events', 'end_time',      'TIME DEFAULT NULL AFTER `start_time`'],
            ['events', 'is_all_day',    'TINYINT(1) DEFAULT 1 AFTER `end_time`'],
            ['events', 'is_recurring',  'TINYINT(1) DEFAULT 0 AFTER `is_all_day`'],
            ['events', 'status',        "VARCHAR(50) DEFAULT 'pending' AFTER `is_recurring`"],
            ['events', 'priority',      "VARCHAR(50) DEFAULT 'normal' AFTER `status`"],
            ['events', 'document_date', 'DATE DEFAULT NULL AFTER `priority`'],
            ['events', 'original_date', 'DATE DEFAULT NULL AFTER `document_date`'],
            ['events', 'form_id',       'INT DEFAULT NULL AFTER `original_date`'],
            ['events', 'is_active',     'TINYINT(1) DEFAULT 1 AFTER `form_id`'],
        ];
        foreach ($columns as [$table, $column, $definition]) {
            $this->addColumnIfNotExists($table, $column, $definition);
        }
    }

    /**
     * Add a column to a table if it doesn't already exist
     */
    private function addColumnIfNotExists($table, $column, $definition) {
        $stmt = $this->connection->prepare(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table AND COLUMN_NAME = :column"
        );
        $stmt->execute([':db' => $this->dbname, ':table' => $table, ':column' => $column]);
        if ($stmt->fetchColumn() == 0) {
            $this->connection->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        }
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