<?php
/**
 * ============================================================
 * SYNC EXISTING FORMS TO CALENDAR
 * ============================================================
 * Este script sincroniza todos los formularios existentes que tienen
 * Work_Date pero NO tienen un evento correspondiente en el calendario.
 *
 * Ejecutar: http://localhost/sales/calendar/utils/debug/sync-existing-forms.php
 *
 * Modo de uso:
 *   - Sin parámetros: Muestra formularios pendientes de sincronizar (preview)
 *   - ?action=sync: Ejecuta la sincronización
 *   - ?action=sync&form_id=123: Sincroniza un formulario específico
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================================
// HTML Header
// ============================================================
echo "<!DOCTYPE html><html><head><title>Sincronización Masiva: Forms → Calendar</title>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; max-width: 1400px; margin: 0 auto; background: #f5f5f5; }
    .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
    .success { background: #d4edda; border-left: 4px solid #28a745; padding: 10px; margin: 5px 0; }
    .error { background: #f8d7da; border-left: 4px solid #dc3545; padding: 10px; margin: 5px 0; }
    .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 5px 0; }
    .info { background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 10px; margin: 5px 0; }
    h1 { color: #001f54; border-bottom: 2px solid #001f54; padding-bottom: 10px; }
    h2 { color: #333; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
    th { background: #001f54; color: white; }
    tr:nth-child(even) { background: #f8f9fa; }
    tr:hover { background: #e9ecef; }
    .btn { display: inline-block; padding: 10px 20px; background: #001f54; color: white; text-decoration: none; border-radius: 4px; margin: 5px; cursor: pointer; border: none; font-size: 14px; }
    .btn:hover { background: #003087; }
    .btn-success { background: #28a745; }
    .btn-success:hover { background: #1e7e34; }
    .btn-warning { background: #ffc107; color: #333; }
    .btn-danger { background: #dc3545; }
    code { background: #f4f4f4; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
    .stats { display: flex; gap: 20px; flex-wrap: wrap; }
    .stat-box { background: #001f54; color: white; padding: 20px; border-radius: 8px; text-align: center; min-width: 150px; }
    .stat-number { font-size: 2em; font-weight: bold; }
    .stat-label { font-size: 0.9em; opacity: 0.9; }
    .log { background: #1a1a2e; color: #0f0; padding: 15px; border-radius: 4px; font-family: monospace; max-height: 400px; overflow-y: auto; white-space: pre-wrap; }
</style></head><body>";

echo "<h1>🔄 Sincronización Masiva: Forms → Calendar</h1>";
echo "<p>Fecha: " . date('Y-m-d H:i:s') . "</p>";

// ============================================================
// Database Connections
// ============================================================
try {
    $pdoForm = new PDO(
        "mysql:host=localhost;dbname=form;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    $pdoCal = new PDO(
        "mysql:host=localhost;dbname=calendar_system;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    echo "<div class='error'>Error de conexión: " . $e->getMessage() . "</div>";
    exit;
}

// ============================================================
// Ensure form_id column exists in events table
// ============================================================
try {
    $stmt = $pdoCal->query("SHOW COLUMNS FROM events LIKE 'form_id'");
    if ($stmt->rowCount() == 0) {
        $pdoCal->exec("ALTER TABLE events ADD COLUMN form_id INT DEFAULT NULL");
        $pdoCal->exec("ALTER TABLE events ADD INDEX idx_form_id (form_id)");
        echo "<div class='success'>✓ Columna 'form_id' creada en tabla events</div>";
    }
} catch (Exception $e) {
    echo "<div class='warning'>Advertencia al verificar columna form_id: " . $e->getMessage() . "</div>";
}

// ============================================================
// Get Statistics
// ============================================================
// Use >= '1000-01-01' instead of != '0000-00-00' for MySQL 8.x strict mode compatibility
$stmt = $pdoForm->query("SELECT COUNT(*) FROM forms WHERE Work_Date IS NOT NULL AND Work_Date >= '1000-01-01'");
$totalFormsWithWorkDate = $stmt->fetchColumn();

$stmt = $pdoCal->query("SELECT COUNT(*) FROM events WHERE form_id IS NOT NULL AND is_active = 1");
$totalSyncedEvents = $stmt->fetchColumn();

$stmt = $pdoCal->query("SELECT COUNT(*) FROM events WHERE is_active = 1");
$totalActiveEvents = $stmt->fetchColumn();

// Forms pending sync (have Work_Date but no event in calendar)
$stmt = $pdoForm->query("
    SELECT f.form_id
    FROM forms f
    WHERE f.Work_Date IS NOT NULL AND f.Work_Date >= '1000-01-01'
    AND f.form_id NOT IN (
        SELECT COALESCE(form_id, 0) FROM calendar_system.events WHERE form_id IS NOT NULL AND is_active = 1
    )
");
$pendingFormIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
$totalPending = count($pendingFormIds);

// ============================================================
// Display Statistics
// ============================================================
echo "<div class='container'>";
echo "<h2>📊 Estadísticas</h2>";
echo "<div class='stats'>";
echo "<div class='stat-box'><div class='stat-number'>$totalFormsWithWorkDate</div><div class='stat-label'>Forms con Work_Date</div></div>";
echo "<div class='stat-box'><div class='stat-number'>$totalSyncedEvents</div><div class='stat-label'>Eventos Sincronizados</div></div>";
echo "<div class='stat-box'><div class='stat-number'>$totalActiveEvents</div><div class='stat-label'>Eventos Activos Total</div></div>";
echo "<div class='stat-box' style='background:" . ($totalPending > 0 ? '#dc3545' : '#28a745') . "'><div class='stat-number'>$totalPending</div><div class='stat-label'>Pendientes de Sync</div></div>";
echo "</div>";
echo "</div>";

// ============================================================
// Action: Sync
// ============================================================
$action = $_GET['action'] ?? 'preview';
$specificFormId = isset($_GET['form_id']) ? intval($_GET['form_id']) : null;

if ($action === 'sync') {
    echo "<div class='container'>";
    echo "<h2>🔄 Ejecutando Sincronización...</h2>";
    echo "<div class='log'>";

    $synced = 0;
    $errors = 0;
    $skipped = 0;

    // If specific form_id, only sync that one
    if ($specificFormId) {
        $formsToSync = [$specificFormId];
    } else {
        $formsToSync = $pendingFormIds;
    }

    foreach ($formsToSync as $formId) {
        // Get form data
        $stmt = $pdoForm->prepare("SELECT * FROM forms WHERE form_id = ?");
        $stmt->execute([$formId]);
        $form = $stmt->fetch();

        if (!$form) {
            echo "⚠️ Form ID $formId no encontrado\n";
            $skipped++;
            continue;
        }

        if (empty($form['Work_Date']) || $form['Work_Date'] < '1000-01-01') {
            echo "⚠️ Form ID $formId sin Work_Date válido\n";
            $skipped++;
            continue;
        }

        // Check if event already exists
        $stmt = $pdoCal->prepare("SELECT event_id FROM events WHERE form_id = ? AND is_active = 1");
        $stmt->execute([$formId]);
        if ($stmt->fetch()) {
            echo "⏭️ Form ID $formId ya tiene evento sincronizado\n";
            $skipped++;
            continue;
        }

        // Prepare event data
        $title = $form['Order_Nomenclature'] ?: ('Service Order #' . $formId);
        $client = $form['company_name'] ?? '';
        $workDate = $form['Work_Date'];
        $documentDate = $form['Document_Date'] ?? null;
        $requestType = strtoupper($form['request_type'] ?? 'JWO');

        // Build description
        $descParts = [];
        if (!empty($form['Order_Nomenclature'])) $descParts[] = "Order: " . $form['Order_Nomenclature'];
        if (!empty($form['order_number'])) $descParts[] = "Order #: " . $form['order_number'];
        if (!empty($form['company_name'])) $descParts[] = "Client: " . $form['company_name'];
        if (!empty($form['requested_service'])) $descParts[] = "Service: " . $form['requested_service'];
        if (!empty($form['service_type'])) $descParts[] = "Type: " . $form['service_type'];
        if (!empty($form['Document_Date'])) $descParts[] = "Document Date: " . $form['Document_Date'];
        $description = implode("\n", $descParts);

        // Build location
        $location = trim(($form['address'] ?? '') . ', ' . ($form['city'] ?? '') . ', ' . ($form['state'] ?? ''));
        $location = trim($location, ', ');

        // Map status
        $status = ($form['status'] === 'submitted') ? 'confirmed' : 'pending';
        $priority = strtolower($form['priority'] ?? 'normal');

        // Map category
        $categoryMap = [
            'JWO' => ['name' => 'JWO', 'color' => '#3b82f6', 'icon' => '📋'],
            'CONTRACT' => ['name' => 'Contract', 'color' => '#10b981', 'icon' => '📄'],
            'PROPOSAL' => ['name' => 'Proposal', 'color' => '#f59e0b', 'icon' => '📊'],
            'HOODVENT' => ['name' => 'Hoodvent', 'color' => '#ef4444', 'icon' => '🔥'],
            'JANITORIAL' => ['name' => 'Janitorial', 'color' => '#8b5cf6', 'icon' => '🧹'],
        ];
        $categoryInfo = $categoryMap[$requestType] ?? $categoryMap['JWO'];

        // Get or create category
        $stmt = $pdoCal->prepare("SELECT category_id FROM event_categories WHERE UPPER(category_name) = ? LIMIT 1");
        $stmt->execute([strtoupper($categoryInfo['name'])]);
        $category = $stmt->fetch();

        if ($category) {
            $categoryId = $category['category_id'];
        } else {
            $stmt = $pdoCal->prepare("INSERT INTO event_categories (user_id, category_name, color_hex, icon, is_default) VALUES (1, ?, ?, ?, 0)");
            $stmt->execute([$categoryInfo['name'], $categoryInfo['color'], $categoryInfo['icon']]);
            $categoryId = $pdoCal->lastInsertId();
            echo "📁 Categoría '{$categoryInfo['name']}' creada (ID: $categoryId)\n";
        }

        // Insert event
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

            $stmt = $pdoCal->prepare($sql);
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

            $eventId = $pdoCal->lastInsertId();
            echo "✅ Form #$formId → Event #$eventId (Work: $workDate, Client: $client)\n";
            $synced++;

        } catch (Exception $e) {
            echo "❌ Form #$formId ERROR: " . $e->getMessage() . "\n";
            $errors++;
        }
    }

    echo "</div>";

    // Summary
    echo "<div class='info' style='margin-top: 15px;'>";
    echo "<strong>Resumen:</strong><br>";
    echo "✅ Sincronizados: $synced<br>";
    echo "⏭️ Omitidos: $skipped<br>";
    echo "❌ Errores: $errors";
    echo "</div>";

    echo "</div>";
}

// ============================================================
// Preview: Forms pending sync
// ============================================================
echo "<div class='container'>";
echo "<h2>📋 Formularios Pendientes de Sincronizar</h2>";

if ($totalPending > 0) {
    // Get detailed info of pending forms
    $stmt = $pdoForm->prepare("
        SELECT f.form_id, f.company_name, f.Order_Nomenclature, f.Document_Date, f.Work_Date,
               f.request_type, f.status, f.created_at
        FROM forms f
        WHERE f.form_id IN (" . implode(',', array_fill(0, count($pendingFormIds), '?')) . ")
        ORDER BY f.Work_Date ASC
        LIMIT 50
    ");
    $stmt->execute($pendingFormIds);
    $pendingForms = $stmt->fetchAll();

    echo "<p>Mostrando " . count($pendingForms) . " de $totalPending formularios pendientes.</p>";

    echo "<table>";
    echo "<tr><th>Form ID</th><th>Company</th><th>Nomenclatura</th><th>Work Date</th><th>Request Type</th><th>Status</th><th>Acción</th></tr>";

    foreach ($pendingForms as $form) {
        echo "<tr>";
        echo "<td>{$form['form_id']}</td>";
        echo "<td>" . htmlspecialchars($form['company_name'] ?? 'N/A') . "</td>";
        echo "<td><code>{$form['Order_Nomenclature']}</code></td>";
        echo "<td>{$form['Work_Date']}</td>";
        echo "<td>{$form['request_type']}</td>";
        echo "<td>{$form['status']}</td>";
        echo "<td><a href='?action=sync&form_id={$form['form_id']}' class='btn btn-success' style='padding:5px 10px;font-size:12px;'>Sync</a></td>";
        echo "</tr>";
    }
    echo "</table>";

    // Action buttons
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='?action=sync' class='btn btn-success' onclick=\"return confirm('¿Sincronizar $totalPending formularios?');\">🔄 Sincronizar Todos ($totalPending)</a>";
    echo "<a href='?' class='btn'>🔍 Actualizar Vista</a>";
    echo "</div>";

} else {
    echo "<div class='success'>✓ Todos los formularios con Work_Date están sincronizados con el calendario.</div>";
}

echo "</div>";

// ============================================================
// Already Synced Events
// ============================================================
echo "<div class='container'>";
echo "<h2>✅ Eventos Ya Sincronizados (Últimos 20)</h2>";

$stmt = $pdoCal->query("
    SELECT e.event_id, e.title, e.client, e.start_date, e.status, e.form_id, ec.category_name
    FROM events e
    LEFT JOIN event_categories ec ON e.category_id = ec.category_id
    WHERE e.form_id IS NOT NULL AND e.is_active = 1
    ORDER BY e.created_at DESC
    LIMIT 20
");
$syncedEvents = $stmt->fetchAll();

if (count($syncedEvents) > 0) {
    echo "<table>";
    echo "<tr><th>Event ID</th><th>Form ID</th><th>Título</th><th>Cliente</th><th>Fecha</th><th>Categoría</th><th>Status</th></tr>";

    foreach ($syncedEvents as $evt) {
        echo "<tr>";
        echo "<td>{$evt['event_id']}</td>";
        echo "<td>{$evt['form_id']}</td>";
        echo "<td>" . htmlspecialchars($evt['title']) . "</td>";
        echo "<td>" . htmlspecialchars($evt['client'] ?? 'N/A') . "</td>";
        echo "<td>{$evt['start_date']}</td>";
        echo "<td>{$evt['category_name']}</td>";
        echo "<td>{$evt['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='info'>No hay eventos sincronizados todavía.</div>";
}

echo "</div>";

// ============================================================
// Navigation
// ============================================================
echo "<div class='container'>";
echo "<h2>🔗 Enlaces Útiles</h2>";
echo "<a href='diagnose-sync.php' class='btn'>🔍 Diagnóstico de Sync</a>";
echo "<a href='../../index.php' class='btn'>📅 Ver Calendario</a>";
echo "<a href='../../../form_contract/' class='btn'>📝 Request Form</a>";
echo "</div>";

echo "</body></html>";
?>
