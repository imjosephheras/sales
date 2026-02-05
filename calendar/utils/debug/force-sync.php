<?php
/**
 * Force Sync - Sincronización forzada de formularios al calendario
 *
 * Este script permite:
 * 1. Sincronizar manualmente un formulario específico
 * 2. Sincronizar todos los formularios pendientes
 * 3. Diagnóstico detallado paso a paso
 */

// Configuración de errores para debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluir configuración de base de datos
require_once __DIR__ . '/../../../form_contract/db_config.php';

// Headers
header('Content-Type: text/html; charset=utf-8');

// Función para mostrar mensajes con estilo
function printStatus($message, $type = 'info') {
    $colors = [
        'success' => '#10b981',
        'error' => '#ef4444',
        'warning' => '#f59e0b',
        'info' => '#3b82f6'
    ];
    $icons = [
        'success' => '✓',
        'error' => '✗',
        'warning' => '⚠️',
        'info' => 'ℹ️'
    ];
    $color = $colors[$type] ?? $colors['info'];
    $icon = $icons[$type] ?? $icons['info'];
    echo "<div style='padding: 8px 12px; margin: 5px 0; border-left: 4px solid {$color}; background: #f8f9fa;'>";
    echo "<span style='color: {$color}; font-weight: bold;'>{$icon}</span> {$message}";
    echo "</div>";
    flush();
}

// Función mejorada de sincronización con logging detallado
function forceSyncFormToCalendar($formId, $formData, $verbose = true) {
    $result = [
        'success' => false,
        'event_id' => null,
        'errors' => [],
        'steps' => []
    ];

    // PASO 1: Conexión al calendario
    if ($verbose) printStatus("Conectando a base de datos calendar_system...", 'info');

    try {
        $calendarPdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . CALENDAR_DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        $result['steps'][] = "Conexión a calendar_system: OK";
        if ($verbose) printStatus("Conexión exitosa a calendar_system", 'success');
    } catch (PDOException $e) {
        $error = "Error de conexión: " . $e->getMessage();
        $result['errors'][] = $error;
        if ($verbose) printStatus($error, 'error');
        return $result;
    }

    // PASO 2: Verificar columna form_id
    if ($verbose) printStatus("Verificando columna form_id en tabla events...", 'info');
    try {
        $stmt = $calendarPdo->query("SHOW COLUMNS FROM `events` LIKE 'form_id'");
        if ($stmt->rowCount() == 0) {
            if ($verbose) printStatus("Creando columna form_id...", 'warning');
            $calendarPdo->exec("ALTER TABLE `events` ADD COLUMN `form_id` INT DEFAULT NULL");
            $calendarPdo->exec("ALTER TABLE `events` ADD INDEX `idx_form_id` (`form_id`)");
            $result['steps'][] = "Columna form_id creada";
            if ($verbose) printStatus("Columna form_id creada exitosamente", 'success');
        } else {
            $result['steps'][] = "Columna form_id existe";
            if ($verbose) printStatus("Columna form_id existe", 'success');
        }
    } catch (Exception $e) {
        $error = "Error verificando columna: " . $e->getMessage();
        $result['errors'][] = $error;
        if ($verbose) printStatus($error, 'error');
    }

    // PASO 3: Verificar Work_Date
    $workDate = $formData['Work_Date'] ?? null;
    if (empty($workDate)) {
        $error = "Work_Date está vacío - No se puede sincronizar sin fecha de trabajo";
        $result['errors'][] = $error;
        if ($verbose) printStatus($error, 'error');
        return $result;
    }
    $result['steps'][] = "Work_Date: $workDate";
    if ($verbose) printStatus("Work_Date: <strong>$workDate</strong>", 'success');

    // PASO 4: Verificar si ya existe evento para este form
    if ($verbose) printStatus("Buscando evento existente para form_id = $formId...", 'info');
    try {
        $stmt = $calendarPdo->prepare("SELECT event_id, title, start_date FROM events WHERE form_id = ? AND is_active = 1");
        $stmt->execute([$formId]);
        $existingEvent = $stmt->fetch();

        if ($existingEvent) {
            $result['steps'][] = "Evento existente: ID " . $existingEvent['event_id'];
            if ($verbose) printStatus("Evento existente encontrado: ID {$existingEvent['event_id']} - '{$existingEvent['title']}' ({$existingEvent['start_date']})", 'warning');
        } else {
            $result['steps'][] = "No hay evento existente";
            if ($verbose) printStatus("No existe evento previo - Se creará uno nuevo", 'info');
        }
    } catch (Exception $e) {
        $error = "Error buscando evento: " . $e->getMessage();
        $result['errors'][] = $error;
        if ($verbose) printStatus($error, 'error');
        return $result;
    }

    // PASO 5: Preparar datos del evento
    $title = $formData['Order_Nomenclature'] ?? ('Service Order #' . $formId);
    $client = $formData['Company_Name'] ?? '';
    $documentDate = $formData['Document_Date'] ?? null;
    $location = trim(($formData['Company_Address'] ?? '') . ', ' . ($formData['City'] ?? '') . ', ' . ($formData['State'] ?? ''));
    $location = trim($location, ', ');
    $priority = strtolower($formData['Priority'] ?? 'medium');
    $status = ($formData['status'] ?? 'draft') === 'submitted' ? 'confirmed' : 'pending';
    $requestType = strtoupper($formData['Request_Type'] ?? 'JWO');

    if ($verbose) {
        printStatus("Datos del evento:", 'info');
        echo "<ul style='margin: 5px 0 5px 30px;'>";
        echo "<li>Título: <strong>$title</strong></li>";
        echo "<li>Cliente: <strong>$client</strong></li>";
        echo "<li>Ubicación: <strong>" . ($location ?: 'N/A') . "</strong></li>";
        echo "<li>Fecha trabajo: <strong>$workDate</strong></li>";
        echo "<li>Prioridad: <strong>$priority</strong></li>";
        echo "<li>Estado: <strong>$status</strong></li>";
        echo "<li>Tipo: <strong>$requestType</strong></li>";
        echo "</ul>";
    }

    // PASO 6: Obtener o crear categoría
    $categoryMap = [
        'JWO' => ['name' => 'JWO', 'color' => '#3b82f6', 'icon' => '📋'],
        'CONTRACT' => ['name' => 'Contract', 'color' => '#10b981', 'icon' => '📄'],
        'PROPOSAL' => ['name' => 'Proposal', 'color' => '#f59e0b', 'icon' => '📊'],
        'HOODVENT' => ['name' => 'Hoodvent', 'color' => '#ef4444', 'icon' => '🔥'],
        'JANITORIAL' => ['name' => 'Janitorial', 'color' => '#8b5cf6', 'icon' => '🧹'],
    ];
    $categoryInfo = $categoryMap[$requestType] ?? $categoryMap['JWO'];
    $categoryName = $categoryInfo['name'];

    if ($verbose) printStatus("Buscando/creando categoría '$categoryName'...", 'info');

    try {
        $stmt = $calendarPdo->prepare("SELECT category_id FROM event_categories WHERE UPPER(category_name) = :name LIMIT 1");
        $stmt->execute([':name' => strtoupper($categoryName)]);
        $category = $stmt->fetch();

        if ($category) {
            $categoryId = $category['category_id'];
            $result['steps'][] = "Categoría existente: ID $categoryId";
            if ($verbose) printStatus("Categoría encontrada: ID $categoryId", 'success');
        } else {
            // Crear categoría
            if ($verbose) printStatus("Creando nueva categoría '$categoryName'...", 'warning');
            $insertStmt = $calendarPdo->prepare(
                "INSERT INTO event_categories (user_id, category_name, color_hex, icon, is_default)
                 VALUES (:user_id, :name, :color, :icon, :is_default)"
            );
            $insertStmt->execute([
                ':user_id' => 1,
                ':name' => $categoryName,
                ':color' => $categoryInfo['color'],
                ':icon' => $categoryInfo['icon'],
                ':is_default' => ($categoryName === 'JWO') ? 1 : 0
            ]);
            $categoryId = $calendarPdo->lastInsertId();
            $result['steps'][] = "Categoría creada: ID $categoryId";
            if ($verbose) printStatus("Categoría creada: ID $categoryId", 'success');
        }
    } catch (Exception $e) {
        $error = "Error con categoría: " . $e->getMessage();
        $result['errors'][] = $error;
        if ($verbose) printStatus($error, 'error');
        return $result;
    }

    // PASO 7: Crear o actualizar evento
    try {
        if ($existingEvent) {
            // Actualizar evento existente
            if ($verbose) printStatus("Actualizando evento existente ID {$existingEvent['event_id']}...", 'info');

            $sql = "UPDATE events SET
                category_id = :category_id,
                title = :title,
                description = :description,
                location = :location,
                client = :client,
                start_date = :start_date,
                end_date = :end_date,
                document_date = :document_date,
                status = :status,
                priority = :priority,
                updated_at = NOW()
            WHERE event_id = :event_id AND is_active = 1";

            $description = "Formulario #$formId\nCliente: $client\nTipo: $requestType";

            $stmt = $calendarPdo->prepare($sql);
            $stmt->execute([
                ':category_id' => $categoryId,
                ':title' => $title,
                ':description' => $description,
                ':location' => $location,
                ':client' => $client,
                ':start_date' => $workDate,
                ':end_date' => $workDate,
                ':document_date' => $documentDate,
                ':status' => $status,
                ':priority' => $priority,
                ':event_id' => $existingEvent['event_id']
            ]);

            $result['success'] = true;
            $result['event_id'] = $existingEvent['event_id'];
            $result['steps'][] = "Evento actualizado: ID {$existingEvent['event_id']}";
            if ($verbose) printStatus("<strong>Evento actualizado exitosamente: ID {$existingEvent['event_id']}</strong>", 'success');

        } else {
            // Crear nuevo evento
            if ($verbose) printStatus("Insertando nuevo evento...", 'info');

            $sql = "INSERT INTO events (
                user_id, category_id, title, description, location, client,
                start_date, end_date, start_time, end_time,
                is_all_day, is_recurring, status, priority,
                document_date, original_date, form_id,
                is_active, created_at
            ) VALUES (
                :user_id, :category_id, :title, :description, :location, :client,
                :start_date, :end_date, :start_time, :end_time,
                :is_all_day, :is_recurring, :status, :priority,
                :document_date, :original_date, :form_id,
                1, NOW()
            )";

            $description = "Formulario #$formId\nCliente: $client\nTipo: $requestType";

            $stmt = $calendarPdo->prepare($sql);
            $stmt->execute([
                ':user_id' => 1,
                ':category_id' => $categoryId,
                ':title' => $title,
                ':description' => $description,
                ':location' => $location,
                ':client' => $client,
                ':start_date' => $workDate,
                ':end_date' => $workDate,
                ':start_time' => '09:00:00',
                ':end_time' => '17:00:00',
                ':is_all_day' => 0,
                ':is_recurring' => 0,
                ':status' => $status,
                ':priority' => $priority,
                ':document_date' => $documentDate,
                ':original_date' => $workDate,
                ':form_id' => $formId
            ]);

            $eventId = $calendarPdo->lastInsertId();
            $result['success'] = true;
            $result['event_id'] = $eventId;
            $result['steps'][] = "Evento creado: ID $eventId";
            if ($verbose) printStatus("<strong>Evento creado exitosamente: ID $eventId</strong>", 'success');
        }

    } catch (Exception $e) {
        $error = "Error creando/actualizando evento: " . $e->getMessage();
        $result['errors'][] = $error;
        if ($verbose) printStatus($error, 'error');

        // Mostrar información adicional del error SQL
        if ($verbose) {
            printStatus("Código SQL Error: " . $e->getCode(), 'error');
            if (method_exists($e, 'errorInfo')) {
                printStatus("SQL State: " . implode(", ", $e->errorInfo ?? []), 'error');
            }
        }
    }

    return $result;
}

// Función para obtener formularios sin sincronizar
function getUnsyncedForms($pdo, $calendarPdo) {
    // Obtener todos los forms con Work_Date
    $stmt = $pdo->query("
        SELECT f.form_id, f.Order_Nomenclature, f.Company_Name, f.Work_Date,
               f.Document_Date, f.Company_Address, f.City, f.State,
               f.Request_Type, f.Priority, f.status
        FROM forms f
        WHERE f.Work_Date IS NOT NULL
          AND f.Work_Date >= '1000-01-01'
        ORDER BY f.Work_Date DESC
    ");
    $forms = $stmt->fetchAll();

    // Verificar cuáles no están sincronizados
    $unsyncedForms = [];
    foreach ($forms as $form) {
        $checkStmt = $calendarPdo->prepare("SELECT event_id FROM events WHERE form_id = ? AND is_active = 1");
        $checkStmt->execute([$form['form_id']]);
        if (!$checkStmt->fetch()) {
            $unsyncedForms[] = $form;
        }
    }

    return $unsyncedForms;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Force Sync - Sincronización Forzada</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0; padding: 20px;
            background: #f5f5f5;
        }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { color: #1e3a8a; margin-bottom: 10px; }
        h2 { color: #374151; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; margin-top: 30px; }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-primary:hover { background: #2563eb; }
        .btn-success { background: #10b981; color: white; }
        .btn-success:hover { background: #059669; }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-warning:hover { background: #d97706; }
        .badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-error { background: #fee2e2; color: #991b1b; }
        .result-box {
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🔄 Force Sync - Sincronización Forzada</h1>
    <p>Herramienta para sincronizar manualmente formularios al calendario con diagnóstico detallado.</p>

<?php

// Procesar acciones
$action = $_GET['action'] ?? '';
$formId = $_GET['form_id'] ?? null;

try {
    // Conexiones
    $pdo = getDBConnection();
    $calendarPdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . CALENDAR_DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Verificar columna form_id al inicio
    $stmt = $calendarPdo->query("SHOW COLUMNS FROM `events` LIKE 'form_id'");
    if ($stmt->rowCount() == 0) {
        $calendarPdo->exec("ALTER TABLE `events` ADD COLUMN `form_id` INT DEFAULT NULL");
        $calendarPdo->exec("ALTER TABLE `events` ADD INDEX `idx_form_id` (`form_id`)");
        printStatus("Columna form_id creada en tabla events", 'success');
    }

    if ($action === 'sync' && $formId) {
        // Sincronizar un formulario específico
        echo "<div class='card'>";
        echo "<h2>🔄 Sincronizando Formulario #$formId</h2>";

        // Obtener datos del formulario
        $stmt = $pdo->prepare("
            SELECT form_id, Order_Nomenclature, Company_Name, Work_Date,
                   Document_Date, Company_Address, City, State,
                   Request_Type, Priority, status
            FROM forms WHERE form_id = ?
        ");
        $stmt->execute([$formId]);
        $form = $stmt->fetch();

        if (!$form) {
            printStatus("Formulario #$formId no encontrado", 'error');
        } else {
            // Preparar datos
            $formData = [
                'Work_Date' => $form['Work_Date'],
                'Document_Date' => $form['Document_Date'],
                'Order_Nomenclature' => $form['Order_Nomenclature'],
                'Company_Name' => $form['Company_Name'],
                'Company_Address' => $form['Company_Address'],
                'City' => $form['City'],
                'State' => $form['State'],
                'Request_Type' => $form['Request_Type'],
                'Priority' => $form['Priority'] ?? 'Medium',
                'status' => $form['status']
            ];

            echo "<div class='result-box'>";
            $result = forceSyncFormToCalendar($formId, $formData, true);
            echo "</div>";

            if ($result['success']) {
                echo "<p style='margin-top:15px;'>";
                echo "<a href='/sales/calendar/' class='btn btn-primary' target='_blank'>📅 Ver Calendario</a> ";
                echo "<a href='?action=list' class='btn btn-success'>Ver Lista Completa</a>";
                echo "</p>";
            }
        }
        echo "</div>";

    } elseif ($action === 'sync_all') {
        // Sincronizar todos los formularios pendientes
        echo "<div class='card'>";
        echo "<h2>🔄 Sincronizando Todos los Formularios Pendientes</h2>";

        $unsyncedForms = getUnsyncedForms($pdo, $calendarPdo);

        if (empty($unsyncedForms)) {
            printStatus("No hay formularios pendientes de sincronizar", 'success');
        } else {
            $successCount = 0;
            $errorCount = 0;

            foreach ($unsyncedForms as $form) {
                $formData = [
                    'Work_Date' => $form['Work_Date'],
                    'Document_Date' => $form['Document_Date'],
                    'Order_Nomenclature' => $form['Order_Nomenclature'],
                    'Company_Name' => $form['Company_Name'],
                    'Company_Address' => $form['Company_Address'],
                    'City' => $form['City'],
                    'State' => $form['State'],
                    'Request_Type' => $form['Request_Type'],
                    'Priority' => $form['Priority'] ?? 'Medium',
                    'status' => $form['status']
                ];

                echo "<div class='result-box'>";
                echo "<strong>Formulario #{$form['form_id']}: {$form['Order_Nomenclature']}</strong><br>";
                $result = forceSyncFormToCalendar($form['form_id'], $formData, true);
                echo "</div>";

                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }

            echo "<h3>📊 Resumen</h3>";
            printStatus("Sincronizados exitosamente: $successCount", 'success');
            if ($errorCount > 0) {
                printStatus("Con errores: $errorCount", 'error');
            }
        }

        echo "<p style='margin-top:15px;'>";
        echo "<a href='/sales/calendar/' class='btn btn-primary' target='_blank'>📅 Ver Calendario</a> ";
        echo "<a href='diagnose-sync.php' class='btn btn-warning'>Ver Diagnóstico</a>";
        echo "</p>";
        echo "</div>";

    } else {
        // Mostrar lista de formularios
        echo "<div class='card'>";
        echo "<h2>📋 Estado de Sincronización</h2>";

        // Obtener todos los formularios con Work_Date
        $stmt = $pdo->query("
            SELECT f.form_id, f.Order_Nomenclature, f.Company_Name, f.Work_Date,
                   f.Document_Date, f.status
            FROM forms f
            WHERE f.Work_Date IS NOT NULL
              AND f.Work_Date >= '1000-01-01'
            ORDER BY f.Work_Date DESC
        ");
        $forms = $stmt->fetchAll();

        // Obtener eventos sincronizados
        $syncedFormIds = [];
        $eventsByFormId = [];
        $eventsStmt = $calendarPdo->query("SELECT event_id, form_id, title, start_date FROM events WHERE form_id IS NOT NULL AND is_active = 1");
        foreach ($eventsStmt->fetchAll() as $event) {
            $syncedFormIds[] = $event['form_id'];
            $eventsByFormId[$event['form_id']] = $event;
        }

        $unsyncedCount = 0;
        foreach ($forms as $form) {
            if (!in_array($form['form_id'], $syncedFormIds)) {
                $unsyncedCount++;
            }
        }

        if ($unsyncedCount > 0) {
            echo "<p style='margin-bottom:15px;'>";
            echo "<a href='?action=sync_all' class='btn btn-success'>🔄 Sincronizar Todos ($unsyncedCount pendientes)</a>";
            echo "</p>";
        }

        echo "<table>";
        echo "<thead><tr>";
        echo "<th>Form ID</th>";
        echo "<th>Nomenclatura</th>";
        echo "<th>Empresa</th>";
        echo "<th>Work Date</th>";
        echo "<th>Estado</th>";
        echo "<th>Sincronizado</th>";
        echo "<th>Acción</th>";
        echo "</tr></thead>";
        echo "<tbody>";

        foreach ($forms as $form) {
            $isSynced = in_array($form['form_id'], $syncedFormIds);
            $event = $eventsByFormId[$form['form_id']] ?? null;

            echo "<tr>";
            echo "<td>{$form['form_id']}</td>";
            echo "<td>{$form['Order_Nomenclature']}</td>";
            echo "<td>{$form['Company_Name']}</td>";
            echo "<td>{$form['Work_Date']}</td>";
            echo "<td><span class='badge " . ($form['status'] === 'submitted' ? 'badge-success' : 'badge-warning') . "'>{$form['status']}</span></td>";
            echo "<td>";
            if ($isSynced) {
                echo "<span class='badge badge-success'>✓ Evento #{$event['event_id']}</span>";
            } else {
                echo "<span class='badge badge-error'>✗ No sincronizado</span>";
            }
            echo "</td>";
            echo "<td>";
            if ($isSynced) {
                echo "<a href='?action=sync&form_id={$form['form_id']}' class='btn btn-warning' style='font-size:12px;'>Re-sincronizar</a>";
            } else {
                echo "<a href='?action=sync&form_id={$form['form_id']}' class='btn btn-primary' style='font-size:12px;'>Sincronizar</a>";
            }
            echo "</td>";
            echo "</tr>";
        }

        echo "</tbody></table>";
        echo "</div>";

        // Mostrar eventos en calendario
        echo "<div class='card'>";
        echo "<h2>📅 Eventos Actuales en Calendario</h2>";

        $eventsStmt = $calendarPdo->query("
            SELECT e.event_id, e.title, e.client, e.start_date, e.status, e.form_id,
                   c.category_name, c.color_hex
            FROM events e
            LEFT JOIN event_categories c ON e.category_id = c.category_id
            WHERE e.is_active = 1
            ORDER BY e.start_date DESC
            LIMIT 20
        ");
        $events = $eventsStmt->fetchAll();

        if (empty($events)) {
            echo "<p style='color: #6b7280;'>No hay eventos en el calendario.</p>";
        } else {
            echo "<table>";
            echo "<thead><tr>";
            echo "<th>Event ID</th>";
            echo "<th>Título</th>";
            echo "<th>Cliente</th>";
            echo "<th>Fecha</th>";
            echo "<th>Categoría</th>";
            echo "<th>Form ID</th>";
            echo "</tr></thead>";
            echo "<tbody>";

            foreach ($events as $event) {
                echo "<tr>";
                echo "<td>{$event['event_id']}</td>";
                echo "<td>{$event['title']}</td>";
                echo "<td>{$event['client']}</td>";
                echo "<td>{$event['start_date']}</td>";
                echo "<td><span style='background: {$event['color_hex']}; color: white; padding: 2px 8px; border-radius: 4px;'>{$event['category_name']}</span></td>";
                echo "<td>" . ($event['form_id'] ? "#{$event['form_id']}" : '-') . "</td>";
                echo "</tr>";
            }

            echo "</tbody></table>";
        }
        echo "</div>";
    }

} catch (Exception $e) {
    echo "<div class='card'>";
    printStatus("Error crítico: " . $e->getMessage(), 'error');
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

?>

    <div class="card" style="margin-top: 30px;">
        <h2>🔗 Enlaces Útiles</h2>
        <p>
            <a href="/sales/calendar/" class="btn btn-primary" target="_blank">📅 Ver Calendario</a>
            <a href="diagnose-sync.php" class="btn btn-warning">🔍 Diagnóstico Completo</a>
            <a href="/sales/form_contract/" class="btn btn-success" target="_blank">📝 Formularios</a>
        </p>
    </div>
</div>
</body>
</html>
