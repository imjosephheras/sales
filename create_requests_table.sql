-- ============================================================
-- CREATE REQUESTS TABLE FOR CONTRACT GENERATOR
-- Fecha: 2026-01-23
-- Descripción: Tabla para guardar formularios enviados desde form_contract
-- ============================================================

USE `form`;

-- ============================================================
-- CREAR TABLA REQUESTS
-- ============================================================

CREATE TABLE IF NOT EXISTS `requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,

  -- ========================
  -- SECTION 1: Request Information
  -- ========================
  `Service_Type` VARCHAR(100) DEFAULT NULL,
  `Request_Type` VARCHAR(100) DEFAULT NULL,
  `Priority` VARCHAR(50) DEFAULT NULL,
  `Requested_Service` VARCHAR(200) DEFAULT NULL,

  -- ========================
  -- SECTION 2: Client Information
  -- ========================
  `client_name` VARCHAR(200) DEFAULT NULL,
  `Client_Title` VARCHAR(100) DEFAULT NULL,
  `Email` VARCHAR(200) DEFAULT NULL,
  `Number_Phone` VARCHAR(50) DEFAULT NULL,
  `Company_Name` VARCHAR(200) DEFAULT NULL,
  `Company_Address` TEXT DEFAULT NULL,
  `Is_New_Client` VARCHAR(10) DEFAULT NULL,

  -- ========================
  -- SECTION 3: Operational Details
  -- ========================
  `Site_Visit_Conducted` VARCHAR(10) DEFAULT NULL,
  `frequency_period` VARCHAR(50) DEFAULT NULL,
  `week_days` TEXT DEFAULT NULL COMMENT 'JSON array of selected days',
  `one_time` VARCHAR(100) DEFAULT NULL,
  `Invoice_Frequency` VARCHAR(50) DEFAULT NULL,
  `Contract_Duration` VARCHAR(100) DEFAULT NULL,

  -- ========================
  -- SECTION 4: Economic Information
  -- ========================
  `Seller` VARCHAR(100) DEFAULT NULL,
  `PriceInput` VARCHAR(100) DEFAULT NULL,
  `Prime_Quoted_Price` VARCHAR(100) DEFAULT NULL,

  -- Janitorial Services (Section 18)
  `includeJanitorial` VARCHAR(10) DEFAULT NULL,
  `type18` TEXT DEFAULT NULL COMMENT 'JSON array',
  `write18` TEXT DEFAULT NULL COMMENT 'JSON array',
  `time18` TEXT DEFAULT NULL COMMENT 'JSON array',
  `freq18` TEXT DEFAULT NULL COMMENT 'JSON array',
  `desc18` TEXT DEFAULT NULL COMMENT 'JSON array',
  `subtotal18` TEXT DEFAULT NULL COMMENT 'JSON array',
  `total18` VARCHAR(50) DEFAULT NULL,
  `taxes18` VARCHAR(50) DEFAULT NULL,
  `grand18` VARCHAR(50) DEFAULT NULL,

  -- Hoodvent & Kitchen Cleaning (Section 19)
  `includeKitchen` VARCHAR(10) DEFAULT NULL,
  `type19` TEXT DEFAULT NULL COMMENT 'JSON array',
  `time19` TEXT DEFAULT NULL COMMENT 'JSON array',
  `freq19` TEXT DEFAULT NULL COMMENT 'JSON array',
  `desc19` TEXT DEFAULT NULL COMMENT 'JSON array',
  `subtotal19` TEXT DEFAULT NULL COMMENT 'JSON array',
  `total19` VARCHAR(50) DEFAULT NULL,
  `taxes19` VARCHAR(50) DEFAULT NULL,
  `grand19` VARCHAR(50) DEFAULT NULL,

  -- Staff (Section 20)
  `includeStaff` VARCHAR(10) DEFAULT NULL,
  `base_staff` TEXT DEFAULT NULL COMMENT 'JSON object',
  `increase_staff` TEXT DEFAULT NULL COMMENT 'JSON object',
  `bill_staff` TEXT DEFAULT NULL COMMENT 'JSON object',

  -- ========================
  -- SECTION 5: Contract Information
  -- ========================
  `inflationAdjustment` VARCHAR(50) DEFAULT NULL,
  `totalArea` VARCHAR(100) DEFAULT NULL,
  `buildingsIncluded` TEXT DEFAULT NULL,
  `startDateServices` DATE DEFAULT NULL,

  -- ========================
  -- SECTION 6: Observations
  -- ========================
  `Site_Observation` TEXT DEFAULT NULL,
  `Additional_Comments` TEXT DEFAULT NULL,
  `Email_Information_Sent` TEXT DEFAULT NULL,

  -- ========================
  -- SECTION 7: Scope of Work
  -- ========================
  `Scope_Of_Work` TEXT DEFAULT NULL COMMENT 'JSON array of selected tasks',

  -- ========================
  -- SECTION 8: Photos
  -- ========================
  `photos` TEXT DEFAULT NULL COMMENT 'JSON array of photo paths',

  -- ========================
  -- SECTION 9: Document & Work Dates
  -- ========================
  `Document_Date` DATE DEFAULT NULL COMMENT 'Fecha del documento (Q30)',
  `Work_Date` DATE DEFAULT NULL COMMENT 'Fecha del trabajo (Q31)',
  `order_number` INT DEFAULT NULL COMMENT 'Order number 1000-9999, reusable',
  `Order_Nomenclature` VARCHAR(50) DEFAULT NULL COMMENT 'Auto-generated: [ST][RT]-[OrderNum][MMDDYYYY]',

  -- ========================
  -- Status & Metadata
  -- ========================
  `status` VARCHAR(50) DEFAULT 'pending' COMMENT 'pending, in_progress, completed',
  `document_type` VARCHAR(50) DEFAULT NULL COMMENT 'contract, jwo, proposal, quote',
  `document_number` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_status` (`status`),
  INDEX `idx_company` (`Company_Name`),
  INDEX `idx_service_type` (`Service_Type`),
  INDEX `idx_created` (`created_at`),
  INDEX `idx_order_number` (`order_number`),
  INDEX `idx_nomenclature` (`Order_Nomenclature`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NOTAS DE USO
-- ============================================================
--
-- 1. Los campos JSON (type18, freq18, etc.) se guardan como TEXT
--    y se decodifican con json_decode() en PHP
--
-- 2. El campo `status` tiene 3 estados:
--    - 'pending': Recién enviado, esperando edición
--    - 'in_progress': En edición en Contract Generator
--    - 'completed': Documento finalizado
--
-- 3. El campo `document_type` define qué plantilla usar:
--    - 'contract': Contrato
--    - 'jwo': Job Work Order
--    - 'proposal': Propuesta
--    - 'quote': Cotización
--
-- ============================================================
