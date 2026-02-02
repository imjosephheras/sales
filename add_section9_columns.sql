-- ============================================================
-- MIGRATION: Add Section 9 columns (Document Date, Work Date,
-- Order Number, Order Nomenclature)
-- Fecha: 2026-01-27
-- ============================================================

USE `form`;

-- Add Document Date column (Q30)
ALTER TABLE `requests`
  ADD COLUMN IF NOT EXISTS `Document_Date` DATE DEFAULT NULL COMMENT 'Fecha del documento (Q30)' AFTER `photos`;

-- Add Work Date column (Q31)
ALTER TABLE `requests`
  ADD COLUMN IF NOT EXISTS `Work_Date` DATE DEFAULT NULL COMMENT 'Fecha del trabajo (Q31)' AFTER `Document_Date`;

-- Add Order Number (1000-9999, reusable when deleted)
ALTER TABLE `requests`
  ADD COLUMN IF NOT EXISTS `order_number` INT DEFAULT NULL COMMENT 'Order number 1000-9999, reusable' AFTER `Work_Date`;

-- Add Order Nomenclature (auto-generated)
ALTER TABLE `requests`
  ADD COLUMN IF NOT EXISTS `Order_Nomenclature` VARCHAR(50) DEFAULT NULL COMMENT 'Auto-generated: [ST][RT]-[OrderNum][MMDDYYYY]' AFTER `order_number`;

-- Add indexes
ALTER TABLE `requests`
  ADD INDEX `idx_order_number` (`order_number`),
  ADD INDEX `idx_nomenclature` (`Order_Nomenclature`);
