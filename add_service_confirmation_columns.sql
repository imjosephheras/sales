-- ============================================================
-- ADD SERVICE CONFIRMATION COLUMNS (Module 10)
-- Fecha: 2026-02-01
-- Descripcion: Agrega campos para el modulo de confirmacion de servicio
-- ============================================================

USE `form`;

-- ============================================================
-- ADD service_status COLUMN TO requests TABLE
-- Estados: 'pending' (pendiente), 'completed' (realizado), 'not_completed' (no realizado)
-- ============================================================

-- Add service_status column if not exists
SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'form'
    AND TABLE_NAME = 'requests'
    AND COLUMN_NAME = 'service_status'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `requests` ADD COLUMN `service_status` ENUM("pending", "completed", "not_completed") DEFAULT "pending" COMMENT "Estado del servicio: pending=Pendiente, completed=Si realizado, not_completed=No realizado"',
    'SELECT "Column service_status already exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add service_completed_at column if not exists
SET @col_exists2 = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'form'
    AND TABLE_NAME = 'requests'
    AND COLUMN_NAME = 'service_completed_at'
);

SET @sql2 = IF(@col_exists2 = 0,
    'ALTER TABLE `requests` ADD COLUMN `service_completed_at` TIMESTAMP NULL DEFAULT NULL COMMENT "Fecha cuando el servicio fue marcado como completado"',
    'SELECT "Column service_completed_at already exists"'
);

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Add ready_to_invoice column if not exists
SET @col_exists3 = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'form'
    AND TABLE_NAME = 'requests'
    AND COLUMN_NAME = 'ready_to_invoice'
);

SET @sql3 = IF(@col_exists3 = 0,
    'ALTER TABLE `requests` ADD COLUMN `ready_to_invoice` TINYINT(1) DEFAULT 0 COMMENT "1=Listo para facturar, 0=No listo"',
    'SELECT "Column ready_to_invoice already exists"'
);

PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

-- Add final_pdf_path column if not exists
SET @col_exists4 = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'form'
    AND TABLE_NAME = 'requests'
    AND COLUMN_NAME = 'final_pdf_path'
);

SET @sql4 = IF(@col_exists4 = 0,
    'ALTER TABLE `requests` ADD COLUMN `final_pdf_path` VARCHAR(500) DEFAULT NULL COMMENT "Ruta del PDF final generado"',
    'SELECT "Column final_pdf_path already exists"'
);

PREPARE stmt4 FROM @sql4;
EXECUTE stmt4;
DEALLOCATE PREPARE stmt4;

-- Add index on service_status for faster queries
SET @idx_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = 'form'
    AND TABLE_NAME = 'requests'
    AND INDEX_NAME = 'idx_service_status'
);

SET @sql5 = IF(@idx_exists = 0,
    'ALTER TABLE `requests` ADD INDEX `idx_service_status` (`service_status`)',
    'SELECT "Index idx_service_status already exists"'
);

PREPARE stmt5 FROM @sql5;
EXECUTE stmt5;
DEALLOCATE PREPARE stmt5;

-- Add index on ready_to_invoice for billing queries
SET @idx_exists2 = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = 'form'
    AND TABLE_NAME = 'requests'
    AND INDEX_NAME = 'idx_ready_to_invoice'
);

SET @sql6 = IF(@idx_exists2 = 0,
    'ALTER TABLE `requests` ADD INDEX `idx_ready_to_invoice` (`ready_to_invoice`)',
    'SELECT "Index idx_ready_to_invoice already exists"'
);

PREPARE stmt6 FROM @sql6;
EXECUTE stmt6;
DEALLOCATE PREPARE stmt6;

-- ============================================================
-- ALSO ADD TO forms TABLE FOR CONSISTENCY
-- ============================================================

-- Add service_status to forms table
SET @col_exists_forms = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'form'
    AND TABLE_NAME = 'forms'
    AND COLUMN_NAME = 'service_status'
);

SET @sql_forms = IF(@col_exists_forms = 0,
    'ALTER TABLE `forms` ADD COLUMN `service_status` ENUM("pending", "completed", "not_completed") DEFAULT "pending"',
    'SELECT "Column service_status already exists in forms"'
);

PREPARE stmt_forms FROM @sql_forms;
EXECUTE stmt_forms;
DEALLOCATE PREPARE stmt_forms;

-- ============================================================
-- NOTES
-- ============================================================
--
-- service_status values:
--   'pending'       = Servicio pendiente (aun no se realiza)
--   'completed'     = Servicio realizado (Si)
--   'not_completed' = Servicio no realizado (No)
--
-- Workflow:
--   1. Cuando se crea un Request Form -> service_status = 'pending'
--   2. Cuando se marca como Si -> service_status = 'completed'
--      - Se registra service_completed_at
--      - Se genera PDF final
--      - Se marca ready_to_invoice = 1
--      - Se mueve a "Listos para facturar" en billing
--   3. Cuando se marca como No -> service_status = 'not_completed'
--      - Se mantiene en historial pero no va a facturacion
--
-- ============================================================
