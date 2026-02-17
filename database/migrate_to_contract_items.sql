-- ============================================================
-- MIGRATION: Adapt Database Architecture
-- Date: 2026-02-17
-- ============================================================
-- 1. Create unified contract_items table
-- 2. Add grand_total column to forms
-- 3. Add service tracking columns to forms (moved from requests)
-- 4. Migrate existing data from old tables to contract_items
-- 5. Drop obsolete tables: janitorial_services_costs, kitchen_cleaning_costs, hood_vent_costs, requests
-- ============================================================

-- ============================================================
-- STEP 1: Create contract_items table
-- ============================================================
CREATE TABLE IF NOT EXISTS `contract_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `form_id` INT NOT NULL,
  `service_category` VARCHAR(50) NOT NULL COMMENT 'janitorial, kitchen, hood_vent',
  `service_number` INT DEFAULT NULL,
  `service_type` VARCHAR(200) DEFAULT NULL,
  `service_time` VARCHAR(100) DEFAULT NULL,
  `frequency` VARCHAR(100) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `subtotal` DECIMAL(12,2) DEFAULT NULL,
  `bundle_group` VARCHAR(50) DEFAULT NULL,
  INDEX `idx_form_id` (`form_id`),
  INDEX `idx_category` (`service_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- STEP 2: Add grand_total to forms
-- ============================================================
ALTER TABLE `forms` ADD COLUMN IF NOT EXISTS `grand_total` DECIMAL(12,2) DEFAULT NULL AFTER `total_cost`;

-- ============================================================
-- STEP 3: Add service tracking columns to forms (from requests)
-- ============================================================
ALTER TABLE `forms` ADD COLUMN IF NOT EXISTS `task_tracking` JSON DEFAULT NULL;
ALTER TABLE `forms` ADD COLUMN IF NOT EXISTS `task_tracking_updated_at` TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `forms` ADD COLUMN IF NOT EXISTS `admin_notes` TEXT DEFAULT NULL;
ALTER TABLE `forms` ADD COLUMN IF NOT EXISTS `ready_to_invoice` TINYINT(1) DEFAULT 0;
ALTER TABLE `forms` ADD COLUMN IF NOT EXISTS `final_pdf_path` VARCHAR(500) DEFAULT NULL;
ALTER TABLE `forms` ADD COLUMN IF NOT EXISTS `completed_at` TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `forms` ADD COLUMN IF NOT EXISTS `docnum` VARCHAR(100) DEFAULT NULL;

-- ============================================================
-- STEP 4: Migrate existing data from old tables to contract_items
-- ============================================================

-- Migrate janitorial_services_costs
INSERT INTO contract_items (form_id, service_category, service_number, service_type, service_time, frequency, description, subtotal, bundle_group)
SELECT form_id, 'janitorial', service_number, service_type, service_time, frequency, description, subtotal, bundle_group
FROM janitorial_services_costs;

-- Migrate kitchen_cleaning_costs
INSERT INTO contract_items (form_id, service_category, service_number, service_type, service_time, frequency, description, subtotal, bundle_group)
SELECT form_id, 'kitchen', service_number, service_type, service_time, frequency, description, subtotal, bundle_group
FROM kitchen_cleaning_costs;

-- Migrate hood_vent_costs
INSERT INTO contract_items (form_id, service_category, service_number, service_type, service_time, frequency, description, subtotal, bundle_group)
SELECT form_id, 'hood_vent', service_number, service_type, service_time, frequency, description, subtotal, bundle_group
FROM hood_vent_costs;

-- Populate grand_total from existing total_cost
UPDATE forms SET grand_total = total_cost WHERE total_cost IS NOT NULL;

-- Populate docnum from Order_Nomenclature
UPDATE forms SET docnum = Order_Nomenclature WHERE Order_Nomenclature IS NOT NULL AND docnum IS NULL;

-- ============================================================
-- STEP 5: Update billing_documents to reference forms instead of requests
-- ============================================================
ALTER TABLE `billing_documents` ADD COLUMN IF NOT EXISTS `form_id` INT DEFAULT NULL AFTER `request_id`;
ALTER TABLE `billing_documents` ADD INDEX IF NOT EXISTS `idx_form_id` (`form_id`);

-- Migrate request_id to form_id in billing_documents
UPDATE billing_documents bd
INNER JOIN requests r ON bd.request_id = r.id
SET bd.form_id = r.form_id
WHERE r.form_id IS NOT NULL;

-- ============================================================
-- STEP 6: Drop obsolete tables (RUN ONLY AFTER VERIFYING MIGRATION)
-- ============================================================
-- DROP TABLE IF EXISTS `janitorial_services_costs`;
-- DROP TABLE IF EXISTS `kitchen_cleaning_costs`;
-- DROP TABLE IF EXISTS `hood_vent_costs`;
-- DROP TABLE IF EXISTS `requests`;
