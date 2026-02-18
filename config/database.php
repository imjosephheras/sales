<?php
// ============================================================
// database.php - Configuracion centralizada de Base de Datos
// ============================================================
// Este es el UNICO archivo donde se configuran las credenciales
// de la base de datos. Todos los modulos deben usar la clase
// Database::getConnection() para obtener la conexion PDO.
// ============================================================

// Zona horaria
date_default_timezone_set('America/Chicago');

// Configuracion de la base de datos
if (!defined('DB_HOST'))    define('DB_HOST', 'localhost');
if (!defined('DB_NAME'))    define('DB_NAME', 'form');
if (!defined('DB_USER'))    define('DB_USER', 'root');
if (!defined('DB_PASS'))    define('DB_PASS', '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// Cargar la clase Database (singleton)
require_once __DIR__ . '/../app/Core/Database.php';

/**
 * Obtener conexion PDO (singleton).
 * Wrapper de compatibilidad que delega a Database::getConnection().
 *
 * @return PDO
 */
function getDBConnection() {
    return Database::getConnection();
}
