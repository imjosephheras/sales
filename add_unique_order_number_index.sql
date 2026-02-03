-- ============================================================
-- Migration: Add UNIQUE index on order_number column
-- Table: forms
-- Date: 2026-02-03
-- Description: Ensures order_number uniqueness for Form Contract nomenclature system
-- ============================================================

-- Step 1: Check for and remove any existing non-unique index on order_number
DROP INDEX IF EXISTS idx_order_number ON forms;

-- Step 2: Add UNIQUE constraint on order_number
-- This ensures no two forms can have the same order number
ALTER TABLE forms ADD UNIQUE INDEX unique_order_number (order_number);

-- Note: If there are duplicate order_number values, this migration will fail.
-- In that case, run the following query first to identify duplicates:
-- SELECT order_number, COUNT(*) as count FROM forms GROUP BY order_number HAVING count > 1;
-- Then manually resolve duplicates before running this migration.
