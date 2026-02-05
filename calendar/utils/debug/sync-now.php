<?php
/**
 * sync-now.php - Script de sincronización directa
 *
 * Sincroniza todos los formularios pendientes al calendario.
 * Usa: php sync-now.php  (CLI)
 * O accede via web: /sales/calendar/utils/debug/sync-now.php
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Detectar si es CLI o web
$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>Sync Now</title>";
    echo "<style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#eee;}";
    echo ".ok{color:#10b981}.err{color:#ef4444}.warn{color:#f59e0b}.info{color:#3b82f6}</style></head><body>";
    echo "<h1>🔄 Sincronización Directa</h1><pre>";
}

function out($msg, $type = 'info') {
    global $isCli;
    $prefix = ['ok' => '✓', 'err' => '✗', 'warn' => '⚠', 'info' => '→'];
    if ($isCli) {
        echo $prefix[$type] . " $msg\n";
    } else {
        echo "<span class='$type'>{$prefix[$type]}</span> $msg\n";
    }
    flush();
}

// Incluir config
require_once __DIR__ . '/../../../form_contract/db_config.php';

try {
    out("Conectando a base de datos FORM...", 'info');

    // Conexión al form database
    $host = defined('DB_HOST') ? DB_HOST : 'localhost';
    // Usar 127.0.0.1 para evitar problemas de socket
    if ($host === 'localhost') {
        $host = '127.0.0.1';
    }

    $formPdo = new PDO(
        "mysql:host={$host};dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    out("Conectado a base de datos 'form'", 'ok');

    out("Conectando a base de datos CALENDAR_SYSTEM...", 'info');
    $calPdo = new PDO(
        "mysql:host={$host};dbname=" . CALENDAR_DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    out("Conectado a base de datos 'calendar_system'", 'ok');

    // Verificar/crear columna form_id
    $stmt = $calPdo->query("SHOW COLUMNS FROM `events` LIKE 'form_id'");
    if ($stmt->rowCount() == 0) {
        out("Creando columna form_id en events...", 'warn');
        $calPdo->exec("ALTER TABLE `events` ADD COLUMN `form_id` INT DEFAULT NULL");
        $calPdo->exec("ALTER TABLE `events` ADD INDEX `idx_form_id` (`form_id`)");
        out("Columna form_id creada", 'ok');
    }

    // Obtener formularios con Work_Date
    out("Buscando formularios con Work_Date...", 'info');
    $stmt = $formPdo->query("
        SELECT form_id, Order_Nomenclature, Company_Name, Work_Date,
               Document_Date, Company_Address, City, State,
               Request_Type, Priority, status
        FROM forms
        WHERE Work_Date IS NOT NULL
          AND Work_Date >= '1000-01-01'
        ORDER BY Work_Date DESC
    ");
    $forms = $stmt->fetchAll();
    out("Encontrados " . count($forms) . " formularios con Work_Date", 'ok');

    // Obtener eventos existentes
    $eventsStmt = $calPdo->query("SELECT form_id FROM events WHERE form_id IS NOT NULL AND is_active = 1");
    $syncedFormIds = array_column($eventsStmt->fetchAll(), 'form_id');
    out("Formularios ya sincronizados: " . count($syncedFormIds), 'info');

    // Filtrar formularios no sincronizados
    $toSync = [];
    foreach ($forms as $form) {
        if (!in_array($form['form_id'], $syncedFormIds)) {
            $toSync[] = $form;
        }
    }
    out("Formularios pendientes de sincronizar: " . count($toSync), count($toSync) > 0 ? 'warn' : 'ok');

    if (empty($toSync)) {
        out("No hay nada que sincronizar. Todos los formularios están en el calendario.", 'ok');
    } else {
        // Obtener o crear categorías
        $categoryMap = [
            'JWO' => ['name' => 'JWO', 'color' => '#3b82f6', 'icon' => '📋'],
            'CONTRACT' => ['name' => 'Contract', 'color' => '#10b981', 'icon' => '📄'],
            'PROPOSAL' => ['name' => 'Proposal', 'color' => '#f59e0b', 'icon' => '📊'],
            'HOODVENT' => ['name' => 'Hoodvent', 'color' => '#ef4444', 'icon' => '🔥'],
            'JANITORIAL' => ['name' => 'Janitorial', 'color' => '#8b5cf6', 'icon' => '🧹'],
        ];

        $categoryIds = [];
        foreach ($categoryMap as $key => $info) {
            $stmt = $calPdo->prepare("SELECT category_id FROM event_categories WHERE UPPER(category_name) = ?");
            $stmt->execute([strtoupper($info['name'])]);
            $cat = $stmt->fetch();

            if ($cat) {
                $categoryIds[$key] = $cat['category_id'];
            } else {
                $stmt = $calPdo->prepare(
                    "INSERT INTO event_categories (user_id, category_name, color_hex, icon, is_default)
                     VALUES (1, ?, ?, ?, ?)"
                );
                $stmt->execute([$info['name'], $info['color'], $info['icon'], $key === 'JWO' ? 1 : 0]);
                $categoryIds[$key] = $calPdo->lastInsertId();
                out("Categoría '{$info['name']}' creada con ID {$categoryIds[$key]}", 'ok');
            }
        }

        // Sincronizar cada formulario
        $successCount = 0;
        $errorCount = 0;

        foreach ($toSync as $form) {
            $formId = $form['form_id'];
            $title = $form['Order_Nomenclature'] ?: "Service Order #$formId";
            $client = $form['Company_Name'] ?: '';
            $workDate = $form['Work_Date'];
            $documentDate = $form['Document_Date'];
            $location = trim(($form['Company_Address'] ?? '') . ', ' . ($form['City'] ?? '') . ', ' . ($form['State'] ?? ''));
            $location = trim($location, ', ');
            $priority = strtolower($form['Priority'] ?? 'medium');
            $status = ($form['status'] ?? 'draft') === 'submitted' ? 'confirmed' : 'pending';
            $requestType = strtoupper($form['Request_Type'] ?? 'JWO');
            $categoryId = $categoryIds[$requestType] ?? $categoryIds['JWO'];

            $description = "Formulario #$formId\nCliente: $client\nTipo: $requestType";

            try {
                $sql = "INSERT INTO events (
                    user_id, category_id, title, description, location, client,
                    start_date, end_date, start_time, end_time,
                    is_all_day, is_recurring, status, priority,
                    document_date, original_date, form_id,
                    is_active, created_at
                ) VALUES (
                    1, :category_id, :title, :description, :location, :client,
                    :start_date, :end_date, '09:00:00', '17:00:00',
                    0, 0, :status, :priority,
                    :document_date, :original_date, :form_id,
                    1, NOW()
                )";

                $stmt = $calPdo->prepare($sql);
                $stmt->execute([
                    ':category_id' => $categoryId,
                    ':title' => $title,
                    ':description' => $description,
                    ':location' => $location,
                    ':client' => $client,
                    ':start_date' => $workDate,
                    ':end_date' => $workDate,
                    ':status' => $status,
                    ':priority' => $priority,
                    ':document_date' => $documentDate,
                    ':original_date' => $workDate,
                    ':form_id' => $formId
                ]);

                $eventId = $calPdo->lastInsertId();
                out("Form #$formId ($title) → Evento #$eventId creado [Fecha: $workDate]", 'ok');
                $successCount++;

            } catch (Exception $e) {
                out("Form #$formId ($title) → ERROR: " . $e->getMessage(), 'err');
                $errorCount++;
            }
        }

        out("", 'info');
        out("═══════════════════════════════════════════", 'info');
        out("RESUMEN: $successCount sincronizados, $errorCount errores", $errorCount > 0 ? 'warn' : 'ok');
        out("═══════════════════════════════════════════", 'info');
    }

    // Mostrar estado final
    out("", 'info');
    out("Estado final del calendario:", 'info');
    $stmt = $calPdo->query("SELECT COUNT(*) as total FROM events WHERE is_active = 1");
    $total = $stmt->fetch()['total'];
    out("Total eventos activos: $total", 'ok');

    $stmt = $calPdo->query("SELECT COUNT(*) as total FROM events WHERE form_id IS NOT NULL AND is_active = 1");
    $synced = $stmt->fetch()['total'];
    out("Eventos sincronizados desde formularios: $synced", 'ok');

} catch (Exception $e) {
    out("ERROR CRÍTICO: " . $e->getMessage(), 'err');
    out($e->getTraceAsString(), 'err');
}

if (!$isCli) {
    echo "</pre>";
    echo "<p><a href='/sales/calendar/' style='color:#3b82f6;'>→ Ver Calendario</a></p>";
    echo "<p><a href='diagnose-sync.php' style='color:#f59e0b;'>→ Ver Diagnóstico</a></p>";
    echo "<p><a href='force-sync.php' style='color:#10b981;'>→ Sincronización Detallada</a></p>";
    echo "</body></html>";
}
?>
