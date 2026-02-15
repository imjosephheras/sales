<?php
// ============================================================
// database.php - Configuracion centralizada de Base de Datos
// ============================================================
// Este es el UNICO archivo donde se configuran las credenciales
// de la base de datos. Todos los modulos deben incluir este archivo
// en lugar de definir sus propias conexiones.
// ============================================================

// Zona horaria
date_default_timezone_set('America/Chicago');

// Configuracion de la base de datos
if (!defined('DB_HOST'))    define('DB_HOST', 'localhost');
if (!defined('DB_NAME'))    define('DB_NAME', 'form');
if (!defined('DB_USER'))    define('DB_USER', 'root');
if (!defined('DB_PASS'))    define('DB_PASS', '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

/**
 * Obtener conexion PDO (singleton).
 * Retorna siempre la misma instancia de PDO.
 *
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );

        return $pdo;
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
