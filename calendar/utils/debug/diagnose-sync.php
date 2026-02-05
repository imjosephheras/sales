<?php
/**
 * ============================================================
 * DIAGNOSTIC SCRIPT: Request Form → Calendar Sync
 * ============================================================
 * Este script diagnostica problemas de sincronización entre
 * el formulario de request y el calendario.
 *
 * Ejecutar: http://localhost/sales/calendar/utils/debug/diagnose-sync.php
 */

// Configuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Diagnóstico de Sincronización</title>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
    .success { background: #d4edda; border-color: #c3e6cb; }
    .error { background: #f8d7da; border-color: #f5c6cb; }
    .warning { background: #fff3cd; border-color: #ffeaa7; }
    .info { background: #d1ecf1; border-color: #bee5eb; }
    h1 { color: #001f54; }
    h2 { color: #333; margin-top: 0; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background: #f8f9fa; }
    code { background: #f4f4f4; padding: 2px 6px; border-radius: 4px; }
    .check { color: #28a745; font-weight: bold; }
    .cross { color: #dc3545; font-weight: bold; }
</style></head><body>";

echo "<h1>🔍 Diagnóstico de Sincronización: Request Form → Calendario</h1>";
echo "<p>Fecha: " . date('Y-m-d H:i:s') . "</p>";

// ============================================================
// TEST 1: Conexión a base de datos FORM
// ============================================================
echo "<div class='section'>";
echo "<h2>1. Conexión a Base de Datos: FORM</h2>";

try {
    $pdoForm = new PDO(
        "mysql:host=localhost;dbname=form;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p class='check'>✓ Conexión exitosa a base de datos 'form'</p>";

    // Verificar tabla forms
    $stmt = $pdoForm->query("SELECT COUNT(*) FROM forms");
    $formCount = $stmt->fetchColumn();
    echo "<p>Total de formularios en 'forms': <strong>$formCount</strong></p>";

    // Verificar tabla requests
    $stmt = $pdoForm->query("SELECT COUNT(*) FROM requests");
    $requestCount = $stmt->fetchColumn();
    echo "<p>Total de requests: <strong>$requestCount</strong></p>";

    // Formularios con Work_Date
    $stmt = $pdoForm->query("SELECT COUNT(*) FROM forms WHERE Work_Date IS NOT NULL AND Work_Date != '0000-00-00'");
    $formsWithWorkDate = $stmt->fetchColumn();
    echo "<p>Formularios con Work_Date: <strong>$formsWithWorkDate</strong></p>";

    if ($formsWithWorkDate == 0) {
        echo "<p class='warning'>⚠️ No hay formularios con Work_Date. Sin fecha de trabajo, no se crean eventos en el calendario.</p>";
    }

} catch (PDOException $e) {
    echo "<p class='cross'>✗ Error de conexión: " . $e->getMessage() . "</p>";
}
echo "</div>";

// ============================================================
// TEST 2: Conexión a base de datos CALENDAR_SYSTEM
// ============================================================
echo "<div class='section'>";
echo "<h2>2. Conexión a Base de Datos: CALENDAR_SYSTEM</h2>";

try {
    $pdoCal = new PDO(
        "mysql:host=localhost;dbname=calendar_system;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p class='check'>✓ Conexión exitosa a base de datos 'calendar_system'</p>";

    // Verificar tabla events
    $stmt = $pdoCal->query("SELECT COUNT(*) FROM events");
    $eventCount = $stmt->fetchColumn();
    echo "<p>Total de eventos: <strong>$eventCount</strong></p>";

    // Eventos activos
    $stmt = $pdoCal->query("SELECT COUNT(*) FROM events WHERE is_active = 1");
    $activeEvents = $stmt->fetchColumn();
    echo "<p>Eventos activos: <strong>$activeEvents</strong></p>";

    // Eventos con form_id
    $stmt = $pdoCal->query("SELECT COUNT(*) FROM events WHERE form_id IS NOT NULL");
    $eventsWithFormId = $stmt->fetchColumn();
    echo "<p>Eventos sincronizados (con form_id): <strong>$eventsWithFormId</strong></p>";

    // Verificar columna form_id
    $stmt = $pdoCal->query("SHOW COLUMNS FROM events LIKE 'form_id'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='check'>✓ Columna 'form_id' existe en tabla events</p>";
    } else {
        echo "<p class='cross'>✗ Columna 'form_id' NO existe en tabla events</p>";
    }

    // Verificar categorías
    $stmt = $pdoCal->query("SELECT * FROM event_categories ORDER BY category_name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Categorías disponibles: <strong>" . count($categories) . "</strong></p>";

    if (count($categories) > 0) {
        echo "<table><tr><th>ID</th><th>Nombre</th><th>Color</th><th>Icono</th></tr>";
        foreach ($categories as $cat) {
            echo "<tr>";
            echo "<td>{$cat['category_id']}</td>";
            echo "<td>{$cat['category_name']}</td>";
            echo "<td><span style='background:{$cat['color_hex']};color:white;padding:2px 8px;border-radius:4px;'>{$cat['color_hex']}</span></td>";
            echo "<td>{$cat['icon']}</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Verificar si existe JWO
        $hasJWO = false;
        foreach ($categories as $cat) {
            if (stripos($cat['category_name'], 'JWO') !== false) {
                $hasJWO = true;
                break;
            }
        }
        if (!$hasJWO) {
            echo "<p class='warning'>⚠️ Categoría 'JWO' no encontrada. Se creará automáticamente en la próxima sincronización.</p>";
        }
    } else {
        echo "<p class='warning'>⚠️ No hay categorías. Se crearán automáticamente.</p>";
    }

} catch (PDOException $e) {
    echo "<p class='cross'>✗ Error de conexión: " . $e->getMessage() . "</p>";
    echo "<p class='error'>La base de datos 'calendar_system' no existe o no es accesible. Esto impide la sincronización.</p>";
}
echo "</div>";

// ============================================================
// TEST 3: Eventos para hoy y próximos 7 días
// ============================================================
if (isset($pdoCal)) {
    echo "<div class='section'>";
    echo "<h2>3. Eventos para Hoy y Próximos 7 Días</h2>";

    $today = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime('+7 days'));

    echo "<p>Hoy: <code>$today</code> | Fin periodo: <code>$endDate</code></p>";

    // Eventos de hoy
    $stmt = $pdoCal->prepare("
        SELECT e.*, ec.category_name, ec.color_hex
        FROM events e
        LEFT JOIN event_categories ec ON e.category_id = ec.category_id
        WHERE e.start_date = ? AND e.is_active = 1
        ORDER BY e.start_time
    ");
    $stmt->execute([$today]);
    $todayEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Eventos de Hoy (" . count($todayEvents) . ")</h3>";
    if (count($todayEvents) > 0) {
        echo "<table><tr><th>ID</th><th>Título</th><th>Cliente</th><th>Hora</th><th>Categoría</th><th>Estado</th></tr>";
        foreach ($todayEvents as $evt) {
            echo "<tr>";
            echo "<td>{$evt['event_id']}</td>";
            echo "<td>{$evt['title']}</td>";
            echo "<td>{$evt['client']}</td>";
            echo "<td>{$evt['start_time']}</td>";
            echo "<td>{$evt['category_name']}</td>";
            echo "<td>{$evt['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>No hay eventos programados para hoy.</p>";
    }

    // Próximos 7 días
    $stmt = $pdoCal->prepare("
        SELECT e.*, ec.category_name, ec.color_hex
        FROM events e
        LEFT JOIN event_categories ec ON e.category_id = ec.category_id
        WHERE e.start_date >= ? AND e.start_date <= ? AND e.is_active = 1
        ORDER BY e.start_date, e.start_time
    ");
    $stmt->execute([$today, $endDate]);
    $next7Events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Próximos 7 Días (" . count($next7Events) . ")</h3>";
    if (count($next7Events) > 0) {
        echo "<table><tr><th>ID</th><th>Fecha</th><th>Título</th><th>Cliente</th><th>Categoría</th><th>form_id</th></tr>";
        foreach ($next7Events as $evt) {
            echo "<tr>";
            echo "<td>{$evt['event_id']}</td>";
            echo "<td>{$evt['start_date']}</td>";
            echo "<td>{$evt['title']}</td>";
            echo "<td>{$evt['client']}</td>";
            echo "<td>{$evt['category_name']}</td>";
            echo "<td>" . ($evt['form_id'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>No hay eventos en los próximos 7 días.</p>";
    }

    echo "</div>";
}

// ============================================================
// TEST 4: Formularios sin sincronizar
// ============================================================
if (isset($pdoForm) && isset($pdoCal)) {
    echo "<div class='section'>";
    echo "<h2>4. Formularios Sin Sincronizar al Calendario</h2>";

    $stmt = $pdoForm->query("
        SELECT form_id, company_name, Order_Nomenclature, Document_Date, Work_Date, status, created_at
        FROM forms
        WHERE Work_Date IS NOT NULL AND Work_Date != '0000-00-00'
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $formsWithDate = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($formsWithDate) > 0) {
        echo "<table><tr><th>Form ID</th><th>Company</th><th>Nomenclatura</th><th>Work Date</th><th>Status</th><th>¿En Calendario?</th></tr>";

        foreach ($formsWithDate as $form) {
            // Verificar si está en calendario
            $stmtCheck = $pdoCal->prepare("SELECT event_id FROM events WHERE form_id = ? AND is_active = 1");
            $stmtCheck->execute([$form['form_id']]);
            $inCalendar = $stmtCheck->fetch();

            echo "<tr>";
            echo "<td>{$form['form_id']}</td>";
            echo "<td>{$form['company_name']}</td>";
            echo "<td>{$form['Order_Nomenclature']}</td>";
            echo "<td>{$form['Work_Date']}</td>";
            echo "<td>{$form['status']}</td>";
            echo "<td>" . ($inCalendar ? "<span class='check'>✓ Sí (ID: {$inCalendar['event_id']})</span>" : "<span class='cross'>✗ No</span>") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>No hay formularios con Work_Date definido.</p>";
    }

    echo "</div>";
}

// ============================================================
// TEST 5: Verificar user_id consistency
// ============================================================
echo "<div class='section'>";
echo "<h2>5. Verificación de User ID</h2>";

echo "<p>El sistema usa <code>user_id = 1</code> para todos los eventos y consultas.</p>";

if (isset($pdoCal)) {
    $stmt = $pdoCal->query("SELECT DISTINCT user_id FROM events");
    $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<p>User IDs en eventos: <strong>" . implode(', ', $userIds) . "</strong></p>";

    if (count($userIds) == 1 && $userIds[0] == 1) {
        echo "<p class='check'>✓ Todos los eventos tienen user_id = 1 (consistente)</p>";
    } elseif (count($userIds) == 0) {
        echo "<p class='warning'>⚠️ No hay eventos en la base de datos</p>";
    } else {
        echo "<p class='warning'>⚠️ Hay eventos con diferentes user_ids. Esto puede causar problemas de visualización.</p>";
    }
}
echo "</div>";

// ============================================================
// DIAGNÓSTICO FINAL
// ============================================================
echo "<div class='section info'>";
echo "<h2>📋 Resumen de Diagnóstico</h2>";

$issues = [];

if (!isset($pdoForm)) {
    $issues[] = "❌ No hay conexión a la base de datos 'form'";
}

if (!isset($pdoCal)) {
    $issues[] = "❌ No hay conexión a la base de datos 'calendar_system'";
}

if (isset($formsWithWorkDate) && $formsWithWorkDate == 0) {
    $issues[] = "⚠️ No hay formularios con Work_Date. La sincronización requiere una fecha de trabajo.";
}

if (isset($activeEvents) && $activeEvents == 0) {
    $issues[] = "⚠️ No hay eventos activos en el calendario.";
}

if (isset($eventsWithFormId) && $eventsWithFormId == 0 && isset($formsWithWorkDate) && $formsWithWorkDate > 0) {
    $issues[] = "⚠️ Hay formularios con Work_Date pero ningún evento sincronizado. La sincronización puede estar fallando.";
}

if (count($issues) > 0) {
    echo "<h3>Problemas Detectados:</h3>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
} else {
    echo "<p class='check'>✓ No se detectaron problemas críticos de configuración.</p>";
}

echo "<h3>Posibles Causas de Problemas de Sincronización:</h3>";
echo "<ol>";
echo "<li><strong>Work_Date vacío:</strong> Si el formulario no tiene fecha de trabajo, no se crea evento.</li>";
echo "<li><strong>Error de conexión silencioso:</strong> La función syncFormToCalendar() puede fallar silenciosamente.</li>";
echo "<li><strong>Categoría inexistente:</strong> Si la categoría no existe, se crea automáticamente.</li>";
echo "<li><strong>Transacción rollback:</strong> Si hay error en save_draft.php, la sincronización se ejecuta después del commit.</li>";
echo "<li><strong>Eventos inactivos:</strong> Los eventos pueden estar con is_active = 0.</li>";
echo "</ol>";

echo "</div>";

echo "</body></html>";
?>
