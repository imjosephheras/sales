-- ============================================================
-- ADD TASK TRACKING COLUMNS (Admin Panel Enhancement)
-- Fecha: 2026-02-02
-- Descripcion: Agrega campo JSON para tracking de tareas por servicio
-- ============================================================

USE `form`;

-- ============================================================
-- ADD task_tracking COLUMN TO requests TABLE
-- Almacena el estado de múltiples tareas como JSON
-- ============================================================

-- Add task_tracking column if not exists
SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'form'
    AND TABLE_NAME = 'requests'
    AND COLUMN_NAME = 'task_tracking'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `requests` ADD COLUMN `task_tracking` JSON DEFAULT NULL COMMENT "JSON con estado de tareas: site_visit, quote_sent, contract_signed, staff_assigned, work_started, work_completed, client_approved, invoice_ready"',
    'SELECT "Column task_tracking already exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add task_tracking_updated_at column if not exists
SET @col_exists2 = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'form'
    AND TABLE_NAME = 'requests'
    AND COLUMN_NAME = 'task_tracking_updated_at'
);

SET @sql2 = IF(@col_exists2 = 0,
    'ALTER TABLE `requests` ADD COLUMN `task_tracking_updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT "Ultima actualizacion del tracking"',
    'SELECT "Column task_tracking_updated_at already exists"'
);

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Add admin_notes column for internal notes
SET @col_exists3 = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'form'
    AND TABLE_NAME = 'requests'
    AND COLUMN_NAME = 'admin_notes'
);

SET @sql3 = IF(@col_exists3 = 0,
    'ALTER TABLE `requests` ADD COLUMN `admin_notes` TEXT DEFAULT NULL COMMENT "Notas internas del administrador"',
    'SELECT "Column admin_notes already exists"'
);

PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

-- ============================================================
-- NOTES - Task Tracking Structure
-- ============================================================
--
-- task_tracking JSON example:
-- {
--   "site_visit": true,
--   "quote_sent": true,
--   "contract_signed": false,
--   "staff_assigned": false,
--   "equipment_ready": false,
--   "work_started": false,
--   "work_completed": false,
--   "client_approved": false,
--   "invoice_ready": false
-- }
--
-- Workflow:
--   1. Admin entra al panel y ve todos los servicios
--   2. Puede marcar/desmarcar cada tarea individualmente
--   3. El sistema muestra visualmente qué falta (tareas sin marcar)
--   4. Historial completo de todo lo gestionado
--
-- ============================================================
