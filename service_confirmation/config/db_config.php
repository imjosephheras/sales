<?php
// ============================================================
// db_config.php - Service Confirmation Module (Module 10)
// ============================================================
// Uses centralized database config + forms table (single source of truth).
// Service confirmation columns are now part of the forms table,
// managed by form_contract/db_config.php.
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../form_contract/db_config.php';

$pdo = getDBConnection();
?>
