<?php
// ============================================================
// Database.php - Clase centralizada de conexion a Base de Datos
// ============================================================
// Patron Singleton: garantiza una unica instancia de PDO
// en todo el sistema. Todas las conexiones deben obtenerse
// a traves de Database::getConnection().
// ============================================================

class Database
{
    /** @var PDO|null Instancia unica de PDO */
    private static ?PDO $instance = null;

    /** Prevenir instanciacion directa */
    private function __construct() {}

    /** Prevenir clonacion */
    private function __clone() {}

    /**
     * Obtener la conexion PDO (singleton).
     * Lee las credenciales desde las constantes definidas en config/database.php.
     *
     * @return PDO
     */
    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        try {
            self::$instance = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );

            return self::$instance;
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Database connection error: ' . $e->getMessage()
                ]);
                exit;
            }

            die("Database connection failed. Please check your configuration.");
        }
    }
}
