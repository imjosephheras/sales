-- ============================================================
-- restructure_forms_table.sql
-- Migration: Restructure forms + contract_items schema
-- ============================================================
-- Changes:
--   1. forms: Rename grand_total -> total_cost
--   2. contract_items: Rename service_category -> category
--   3. contract_items: Rename service_number -> position
--   4. contract_items: Add service_name column
--   5. Create contract_staff table
-- ============================================================

-- 1. Rename grand_total to total_cost in forms table
ALTER TABLE `forms`
  CHANGE COLUMN `grand_total` `total_cost` DECIMAL(10,2) DEFAULT NULL;

-- 2. Rename service_category to category in contract_items
ALTER TABLE `contract_items`
  CHANGE COLUMN `service_category` `category` VARCHAR(50) NOT NULL COMMENT 'janitorial, kitchen, hood_vent';

-- 3. Rename service_number to position in contract_items
ALTER TABLE `contract_items`
  CHANGE COLUMN `service_number` `position` INT DEFAULT NULL;

-- 4. Add service_name column to contract_items
ALTER TABLE `contract_items`
  ADD COLUMN `service_name` VARCHAR(255) DEFAULT NULL AFTER `category`;

-- 5. Update index names for renamed columns
ALTER TABLE `contract_items`
  DROP INDEX IF EXISTS `idx_category`,
  ADD INDEX `idx_category` (`category`);

-- 6. Create contract_staff table
CREATE TABLE IF NOT EXISTS `contract_staff` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `form_id` INT NOT NULL,
  `position` VARCHAR(150) DEFAULT NULL,
  `base_rate` DECIMAL(12,2) DEFAULT NULL,
  `percent_increase` DECIMAL(5,2) DEFAULT NULL,
  `bill_rate` DECIMAL(12,2) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_form_id` (`form_id`),
  CONSTRAINT `fk_contract_staff_form` FOREIGN KEY (`form_id`) REFERENCES `forms`(`form_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Add FK on scope_sections if not already declared
-- ALTER TABLE `scope_sections` ADD CONSTRAINT `fk_scope_sections_form` FOREIGN KEY (`form_id`) REFERENCES `forms`(`form_id`) ON DELETE CASCADE;
