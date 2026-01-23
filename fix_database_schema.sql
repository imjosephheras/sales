-- ============================================================
-- FIX DATABASE SCHEMA FOR SERVICES TABLES
-- Fecha: 2026-01-23
-- Descripción: Ajusta las tablas para que coincidan con el formulario
-- ============================================================

USE `form`;

-- ============================================================
-- 1. FIX hood_vent_costs
-- ============================================================

-- Eliminar columnas que NO se usan
ALTER TABLE `hood_vent_costs` DROP COLUMN IF EXISTS `hours_per_service`;
ALTER TABLE `hood_vent_costs` DROP COLUMN IF EXISTS `rate_per_hour`;
ALTER TABLE `hood_vent_costs` DROP COLUMN IF EXISTS `monthly_cost`;
ALTER TABLE `hood_vent_costs` DROP COLUMN IF EXISTS `annual_cost`;
ALTER TABLE `hood_vent_costs` DROP COLUMN IF EXISTS `supplies_cost`;
ALTER TABLE `hood_vent_costs` DROP COLUMN IF EXISTS `total_cost`;

-- Agregar columnas nuevas que SÍ se necesitan
ALTER TABLE `hood_vent_costs`
  ADD COLUMN IF NOT EXISTS `service_type` VARCHAR(100) DEFAULT NULL AFTER `service_number`;

ALTER TABLE `hood_vent_costs`
  ADD COLUMN IF NOT EXISTS `service_time` VARCHAR(50) DEFAULT NULL AFTER `service_type`;

ALTER TABLE `hood_vent_costs`
  ADD COLUMN IF NOT EXISTS `subtotal` DECIMAL(10,2) DEFAULT NULL AFTER `frequency`;

-- Renombrar service_description a description
ALTER TABLE `hood_vent_costs`
  CHANGE COLUMN `service_description` `description` VARCHAR(200) DEFAULT NULL;

-- ============================================================
-- 2. FIX kitchen_cleaning_costs
-- ============================================================

-- Eliminar columnas que NO se usan
ALTER TABLE `kitchen_cleaning_costs` DROP COLUMN IF EXISTS `hours_per_service`;
ALTER TABLE `kitchen_cleaning_costs` DROP COLUMN IF EXISTS `rate_per_hour`;
ALTER TABLE `kitchen_cleaning_costs` DROP COLUMN IF EXISTS `monthly_cost`;
ALTER TABLE `kitchen_cleaning_costs` DROP COLUMN IF EXISTS `annual_cost`;
ALTER TABLE `kitchen_cleaning_costs` DROP COLUMN IF EXISTS `supplies_cost`;
ALTER TABLE `kitchen_cleaning_costs` DROP COLUMN IF EXISTS `total_cost`;

-- Agregar columnas nuevas que SÍ se necesitan
ALTER TABLE `kitchen_cleaning_costs`
  ADD COLUMN IF NOT EXISTS `service_type` VARCHAR(100) DEFAULT NULL AFTER `service_number`;

ALTER TABLE `kitchen_cleaning_costs`
  ADD COLUMN IF NOT EXISTS `service_time` VARCHAR(50) DEFAULT NULL AFTER `service_type`;

ALTER TABLE `kitchen_cleaning_costs`
  ADD COLUMN IF NOT EXISTS `subtotal` DECIMAL(10,2) DEFAULT NULL AFTER `frequency`;

-- Renombrar service_description a description
ALTER TABLE `kitchen_cleaning_costs`
  CHANGE COLUMN `service_description` `description` VARCHAR(200) DEFAULT NULL;

-- ============================================================
-- 3. VERIFICAR estructura final de las 3 tablas
-- ============================================================

-- Mostrar estructura de hood_vent_costs
DESCRIBE `hood_vent_costs`;

-- Mostrar estructura de kitchen_cleaning_costs
DESCRIBE `kitchen_cleaning_costs`;

-- Mostrar estructura de janitorial_services_costs (ya debe estar correcta)
DESCRIBE `janitorial_services_costs`;

-- ============================================================
-- RESULTADO ESPERADO
-- ============================================================
-- Las 3 tablas quedarán con esta estructura consistente:
-- - id (PK AUTO_INCREMENT)
-- - form_id (FK INT NOT NULL)
-- - service_number (INT DEFAULT 1)
-- - service_type (VARCHAR(100)) - Nuevo campo para "Bar Cleaning", "Vent Hood", etc.
-- - service_time (VARCHAR(50)) - Nuevo campo para "1 Day", "1-2 Days", etc.
-- - frequency (VARCHAR(50)) - "Weekly", "Quarterly", etc.
-- - description (VARCHAR(200)) - Descripción libre (antes service_description)
-- - subtotal (DECIMAL(10,2)) - Nuevo campo para el costo
-- - created_at (TIMESTAMP)
-- ============================================================
