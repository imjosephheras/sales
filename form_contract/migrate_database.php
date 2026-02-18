<?php
// ============================================================
// MIGRATE DATABASE SCHEMA - FIX SERVICES TABLES
// Fecha: 2026-01-23
// Descripción: Ajusta las tablas de servicios para que coincidan con el formulario
// ============================================================

require_once __DIR__ . '/init.php';

echo "🚀 Starting database migration...\n\n";

try {
    $pdo = Database::getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ============================================================
    // 1. FIX hood_vent_costs
    // ============================================================
    echo "📝 Migrating hood_vent_costs table...\n";

    // Check if columns exist before dropping
    $stmt = $pdo->query("SHOW COLUMNS FROM hood_vent_costs LIKE 'hours_per_service'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("ALTER TABLE `hood_vent_costs` DROP COLUMN `hours_per_service`");
        echo "   ✓ Dropped hours_per_service\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM hood_vent_costs LIKE 'rate_per_hour'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("ALTER TABLE `hood_vent_costs` DROP COLUMN `rate_per_hour`");
        echo "   ✓ Dropped rate_per_hour\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM hood_vent_costs LIKE 'monthly_cost'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("ALTER TABLE `hood_vent_costs` DROP COLUMN `monthly_cost`");
        echo "   ✓ Dropped monthly_cost\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM hood_vent_costs LIKE 'annual_cost'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("ALTER TABLE `hood_vent_costs` DROP COLUMN `annual_cost`");
        echo "   ✓ Dropped annual_cost\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM hood_vent_costs LIKE 'supplies_cost'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("ALTER TABLE `hood_vent_costs` DROP COLUMN `supplies_cost`");
        echo "   ✓ Dropped supplies_cost\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM hood_vent_costs LIKE 'total_cost'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("ALTER TABLE `hood_vent_costs` DROP COLUMN `total_cost`");
        echo "   ✓ Dropped total_cost\n";
    }

    // Add new columns if they don't exist
    $stmt = $pdo->query("SHOW COLUMNS FROM hood_vent_costs LIKE 'service_type'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `hood_vent_costs` ADD COLUMN `service_type` VARCHAR(100) DEFAULT NULL AFTER `service_number`");
        echo "   ✓ Added service_type\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM hood_vent_costs LIKE 'service_time'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `hood_vent_costs` ADD COLUMN `service_time` VARCHAR(50) DEFAULT NULL AFTER `service_type`");
        echo "   ✓ Added service_time\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM hood_vent_costs LIKE 'subtotal'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `hood_vent_costs` ADD COLUMN `subtotal` DECIMAL(10,2) DEFAULT NULL AFTER `frequency`");
        echo "   ✓ Added subtotal\n";
    }

    // Rename service_description to description if needed
    $stmt = $pdo->query("SHOW COLUMNS FROM hood_vent_costs LIKE 'service_description'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("ALTER TABLE `hood_vent_costs` CHANGE COLUMN `service_description` `description` VARCHAR(200) DEFAULT NULL");
        echo "   ✓ Renamed service_description to description\n";
    }

    echo "✅ hood_vent_costs table migrated successfully!\n\n";

    // ============================================================
    // 2. FIX kitchen_cleaning_costs
    // ============================================================
    echo "📝 Migrating kitchen_cleaning_costs table...\n";

    // Check if columns exist before dropping
    $stmt = $pdo->query("SHOW COLUMNS FROM kitchen_cleaning_costs LIKE 'hours_per_service'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("ALTER TABLE `kitchen_cleaning_costs` DROP COLUMN `hours_per_service`");
        echo "   ✓ Dropped hours_per_service\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM kitchen_cleaning_costs LIKE 'rate_per_hour'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("ALTER TABLE `kitchen_cleaning_costs` DROP COLUMN `rate_per_hour`");
        echo "   ✓ Dropped rate_per_hour\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM kitchen_cleaning_costs LIKE 'monthly_cost'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("ALTER TABLE `kitchen_cleaning_costs` DROP COLUMN `monthly_cost`");
        echo "   ✓ Dropped monthly_cost\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM kitchen_cleaning_costs LIKE 'annual_cost'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("ALTER TABLE `kitchen_cleaning_costs` DROP COLUMN `annual_cost`");
        echo "   ✓ Dropped annual_cost\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM kitchen_cleaning_costs LIKE 'supplies_cost'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("ALTER TABLE `kitchen_cleaning_costs` DROP COLUMN `supplies_cost`");
        echo "   ✓ Dropped supplies_cost\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM kitchen_cleaning_costs LIKE 'total_cost'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("ALTER TABLE `kitchen_cleaning_costs` DROP COLUMN `total_cost`");
        echo "   ✓ Dropped total_cost\n";
    }

    // Add new columns if they don't exist
    $stmt = $pdo->query("SHOW COLUMNS FROM kitchen_cleaning_costs LIKE 'service_type'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `kitchen_cleaning_costs` ADD COLUMN `service_type` VARCHAR(100) DEFAULT NULL AFTER `service_number`");
        echo "   ✓ Added service_type\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM kitchen_cleaning_costs LIKE 'service_time'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `kitchen_cleaning_costs` ADD COLUMN `service_time` VARCHAR(50) DEFAULT NULL AFTER `service_type`");
        echo "   ✓ Added service_time\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM kitchen_cleaning_costs LIKE 'subtotal'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `kitchen_cleaning_costs` ADD COLUMN `subtotal` DECIMAL(10,2) DEFAULT NULL AFTER `frequency`");
        echo "   ✓ Added subtotal\n";
    }

    // Rename service_description to description if needed
    $stmt = $pdo->query("SHOW COLUMNS FROM kitchen_cleaning_costs LIKE 'service_description'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("ALTER TABLE `kitchen_cleaning_costs` CHANGE COLUMN `service_description` `description` VARCHAR(200) DEFAULT NULL");
        echo "   ✓ Renamed service_description to description\n";
    }

    echo "✅ kitchen_cleaning_costs table migrated successfully!\n\n";

    // ============================================================
    // 3. Display final structure
    // ============================================================
    echo "📋 Final table structures:\n\n";

    echo "hood_vent_costs columns:\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM hood_vent_costs");
    foreach ($stmt->fetchAll() as $col) {
        echo "   - {$col['Field']} ({$col['Type']})\n";
    }

    echo "\nkitchen_cleaning_costs columns:\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM kitchen_cleaning_costs");
    foreach ($stmt->fetchAll() as $col) {
        echo "   - {$col['Field']} ({$col['Type']})\n";
    }

    echo "\njanitorial_services_costs columns:\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM janitorial_services_costs");
    foreach ($stmt->fetchAll() as $col) {
        echo "   - {$col['Field']} ({$col['Type']})\n";
    }

    echo "\n\n✅✅✅ MIGRATION COMPLETED SUCCESSFULLY! ✅✅✅\n";
    echo "The tables are now ready to save data from the form.\n";

} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
