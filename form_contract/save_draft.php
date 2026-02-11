<?php
// ============================================================
// save_draft.php - VERSIÓN FINAL CORREGIDA (v2.1)
// Fecha: 2026-01-05
// Fix: Manejo correcto de valores DECIMAL vacíos
// ============================================================

header('Content-Type: application/json');

// Incluir configuración de base de datos y RBAC
require_once 'db_config.php';
require_once 'order_access.php';

try {
    // Enforce authentication + module access
    $currentUser = requireOrderAccess();

    $pdo = getDBConnection();

    // Obtener datos del formulario
    $form_id = isset($_POST['form_id']) ? (int)$_POST['form_id'] : null;
    $status = isset($_POST['status']) ? $_POST['status'] : 'draft';

    // RBAC: If updating an existing form, verify the user has access
    if ($form_id && !canAccessOrder($pdo, $form_id, $currentUser)) {
        denyOrderAccess();
    }

    $pdo->beginTransaction();
    
    // ============================================================
    // HELPER FUNCTION: Convertir string vacío a NULL
    // ============================================================
    function emptyToNull($value) {
        return (isset($value) && $value !== '') ? $value : null;
    }
    
    // ============================================================
    // PASO 1: GUARDAR/ACTUALIZAR FORMULARIO PRINCIPAL
    // ============================================================
    if ($form_id) {
        // UPDATE
        $sql = "UPDATE forms SET
            service_type = :service_type,
            request_type = :request_type,
            priority = :priority,
            requested_service = :requested_service,
            client_name = :client_name,
            company_name = :company_name,
            contact_name = :contact_name,
            phone = :phone,
            email = :email,
            address = :address,
            city = :city,
            state = :state,
            is_new_client = :is_new_client,
            site_visit_conducted = :site_visit_conducted,
            invoice_frequency = :invoice_frequency,
            contract_duration = :contract_duration,
            total_cost = :total_cost,
            payment_terms = :payment_terms,
            inflation_adjustment = :inflation_adjustment,
            total_area = :total_area,
            buildings_included = :buildings_included,
            start_date_services = :start_date_services,
            site_observation = :site_observation,
            additional_comments = :additional_comments,
            email_information_sent = :email_information_sent,
            seller = :seller,
            include_staff = :include_staff,
            Document_Date = :document_date,
            Work_Date = :work_date,
            order_number = :order_number,
            Order_Nomenclature = :order_nomenclature,
            status = :status,
            service_status = :service_status,
            updated_at = NOW()
        WHERE form_id = :form_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':form_id', $form_id);
        
    } else {
        // INSERT
        $sql = "INSERT INTO forms (
            service_type, request_type, priority, requested_service,
            client_name, company_name, contact_name, phone, email, address, city, state, is_new_client,
            site_visit_conducted, invoice_frequency, contract_duration,
            total_cost, payment_terms,
            inflation_adjustment, total_area, buildings_included, start_date_services,
            site_observation, additional_comments, email_information_sent,
            seller, include_staff,
            Document_Date, Work_Date, order_number, Order_Nomenclature,
            status, service_status, submitted_by, created_at
        ) VALUES (
            :service_type, :request_type, :priority, :requested_service,
            :client_name, :company_name, :contact_name, :phone, :email, :address, :city, :state, :is_new_client,
            :site_visit_conducted, :invoice_frequency, :contract_duration,
            :total_cost, :payment_terms,
            :inflation_adjustment, :total_area, :buildings_included, :start_date_services,
            :site_observation, :additional_comments, :email_information_sent,
            :seller, :include_staff,
            :document_date, :work_date, :order_number, :order_nomenclature,
            :status, :service_status, :submitted_by, NOW()
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':submitted_by', 'system');
    }
    
    // ============================================================
    // BIND PARAMETERS (CON MANEJO CORRECTO DE DECIMALES)
    // ============================================================
    
    // Section 1: Request Information
    $stmt->bindValue(':service_type', emptyToNull($_POST['Service_Type'] ?? null));
    $stmt->bindValue(':request_type', emptyToNull($_POST['Request_Type'] ?? null));
    $stmt->bindValue(':priority', emptyToNull($_POST['Priority'] ?? null));
    $stmt->bindValue(':requested_service', emptyToNull($_POST['Requested_Service'] ?? null));
    
    // Section 2: Client Information
    $stmt->bindValue(':client_name', emptyToNull($_POST['Client_Name'] ?? null));
    $stmt->bindValue(':company_name', emptyToNull($_POST['Company_Name'] ?? null));
    $stmt->bindValue(':contact_name', emptyToNull($_POST['Client_Title'] ?? null));
    $stmt->bindValue(':phone', emptyToNull($_POST['Number_Phone'] ?? null));
    $stmt->bindValue(':email', emptyToNull($_POST['Email'] ?? null));
    $stmt->bindValue(':address', emptyToNull($_POST['Company_Address'] ?? null));
    $stmt->bindValue(':city', emptyToNull($_POST['City'] ?? null));
    $stmt->bindValue(':state', emptyToNull($_POST['State'] ?? null));
    $stmt->bindValue(':is_new_client', emptyToNull($_POST['Is_New_Client'] ?? null));
    
    // Section 3: Operational Details
    $stmt->bindValue(':site_visit_conducted', emptyToNull($_POST['Site_Visit_Conducted'] ?? null));
    $stmt->bindValue(':invoice_frequency', emptyToNull($_POST['Invoice_Frequency'] ?? null));
    $stmt->bindValue(':contract_duration', emptyToNull($_POST['Contract_Duration'] ?? null));
    
    // Section 4: Economic Information
    // ⚠️ CRÍTICO: Campos DECIMAL deben ser NULL si están vacíos
    // Calculate total_cost as sum of PriceInput + grand18 (Janitorial) + grand19 (Kitchen/Hood Vent)
    $priceInput = floatval(str_replace(['$', ','], '', $_POST['PriceInput'] ?? '0'));
    $grand18 = floatval(str_replace(['$', ','], '', $_POST['grand18'] ?? '0'));
    $grand19 = floatval(str_replace(['$', ','], '', $_POST['grand19'] ?? '0'));
    $calculatedTotalCost = $priceInput + $grand18 + $grand19;
    $stmt->bindValue(':total_cost', $calculatedTotalCost > 0 ? $calculatedTotalCost : null);
    $stmt->bindValue(':payment_terms', emptyToNull($_POST['payment_terms'] ?? null));
    $stmt->bindValue(':seller', emptyToNull($_POST['Seller'] ?? null));
    $stmt->bindValue(':include_staff', emptyToNull($_POST['includeStaff'] ?? null));
    
    // Section 5: Contract Information
    $stmt->bindValue(':inflation_adjustment', emptyToNull($_POST['inflationAdjustment'] ?? null));
    $stmt->bindValue(':total_area', emptyToNull($_POST['totalArea'] ?? null));
    $stmt->bindValue(':buildings_included', emptyToNull($_POST['buildingsIncluded'] ?? null));
    $stmt->bindValue(':start_date_services', emptyToNull($_POST['startDateServices'] ?? null));
    
    // Section 6: Observations
    $stmt->bindValue(':site_observation', emptyToNull($_POST['Site_Observation'] ?? null));
    $stmt->bindValue(':additional_comments', emptyToNull($_POST['Additional_Comments'] ?? null));
    $stmt->bindValue(':email_information_sent', emptyToNull($_POST['Email_Information_Sent'] ?? null));
    
    // Section 9: Document & Work Dates
    $document_date_val = emptyToNull($_POST['Document_Date'] ?? null);
    $work_date_val = emptyToNull($_POST['Work_Date'] ?? null);

    // Generate order number for new drafts (range: 100000-999999)
    $order_number_val = null;
    $nomenclature_val = null;
    if (!$form_id) {
        // New form - generate order number
        $stmtNums = $pdo->query("SELECT order_number FROM forms WHERE order_number IS NOT NULL ORDER BY order_number ASC");
        $usedNumbers = $stmtNums->fetchAll(PDO::FETCH_COLUMN);
        $usedSet = array_flip($usedNumbers);
        for ($i = 100000; $i <= 999999; $i++) {
            if (!isset($usedSet[(string)$i])) {
                $order_number_val = $i;
                break;
            }
        }
        if ($order_number_val === null) $order_number_val = 100000;
    } else {
        // Existing form - keep existing order number
        $stmtExisting = $pdo->prepare("SELECT order_number FROM forms WHERE form_id = ?");
        $stmtExisting->execute([$form_id]);
        $order_number_val = $stmtExisting->fetchColumn() ?: null;
        if ($order_number_val === null) {
            $stmtNums = $pdo->query("SELECT order_number FROM forms WHERE order_number IS NOT NULL ORDER BY order_number ASC");
            $usedNumbers = $stmtNums->fetchAll(PDO::FETCH_COLUMN);
            $usedSet = array_flip($usedNumbers);
            for ($i = 100000; $i <= 999999; $i++) {
                if (!isset($usedSet[(string)$i])) {
                    $order_number_val = $i;
                    break;
                }
            }
            if ($order_number_val === null) $order_number_val = 100000;
        }
    }

    // Build nomenclature: HJ-{order_number}{MMDDYYYY}
    if ($order_number_val) {
        $dateFormatted = date('mdY'); // Current date in MMDDYYYY format
        if (!empty($document_date_val)) {
            $dateParts = explode('-', $document_date_val);
            $dateFormatted = $dateParts[1] . $dateParts[2] . $dateParts[0]; // MMDDYYYY
        }
        $nomenclature_val = 'HJ-' . $order_number_val . $dateFormatted;
    }

    $stmt->bindValue(':document_date', $document_date_val);
    $stmt->bindValue(':work_date', $work_date_val);
    $stmt->bindValue(':order_number', $order_number_val);
    $stmt->bindValue(':order_nomenclature', $nomenclature_val);

    // Status
    $stmt->bindValue(':status', $status);

    // Section 10: Service Status
    $stmt->bindValue(':service_status', emptyToNull($_POST['service_status'] ?? null));

    $stmt->execute();
    
    // Get the form_id
    $saved_form_id = $form_id ?: $pdo->lastInsertId();
    
    // ============================================================
    // PASO 2: GUARDAR SCOPE OF WORK (Q28 + Q19 service scopes)
    // ============================================================
    $pdo->prepare("DELETE FROM scope_of_work WHERE form_id = ?")->execute([$saved_form_id]);
    $stmtScope = $pdo->prepare("INSERT INTO scope_of_work (form_id, task_name) VALUES (?, ?)");

    // 2a: Save Q28 manual scope of work checkboxes
    if (isset($_POST['Scope_Of_Work']) && is_array($_POST['Scope_Of_Work'])) {
        foreach ($_POST['Scope_Of_Work'] as $task) {
            if (!empty($task)) {
                $stmtScope->execute([$saved_form_id, $task]);
            }
        }
    }

    // 2b: Save scope items from Q19 service catalog selections
    if (isset($_POST['scope19']) && is_array($_POST['scope19'])) {
        foreach ($_POST['scope19'] as $scopeJson) {
            if (!empty($scopeJson)) {
                $scopeItems = json_decode($scopeJson, true);
                if (is_array($scopeItems)) {
                    foreach ($scopeItems as $item) {
                        if (!empty($item)) {
                            $stmtScope->execute([$saved_form_id, $item]);
                        }
                    }
                }
            }
        }
    }
    
    // ============================================================
    // PASO 3: GUARDAR JANITORIAL SERVICES (Q18)
    // ============================================================
    if (isset($_POST['includeJanitorial']) && $_POST['includeJanitorial'] === 'Yes') {
        $pdo->prepare("DELETE FROM janitorial_services_costs WHERE form_id = ?")->execute([$saved_form_id]);

        if (isset($_POST['type18']) && is_array($_POST['type18'])) {
            $stmt = $pdo->prepare("
                INSERT INTO janitorial_services_costs (
                    form_id, service_number, service_type, service_time,
                    frequency, description, subtotal, bundle_group
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $count = count($_POST['type18']);
            for ($i = 0; $i < $count; $i++) {
                // Obtener tipo de servicio
                $serviceType = '';
                if (isset($_POST['type18'][$i]) && $_POST['type18'][$i] !== '__write__' && !empty($_POST['type18'][$i])) {
                    $serviceType = $_POST['type18'][$i];
                } elseif (isset($_POST['write18'][$i]) && !empty($_POST['write18'][$i])) {
                    $serviceType = $_POST['write18'][$i];
                }

                // Solo guardar si hay datos
                if (!empty($serviceType)) {
                    // ⚠️ Convertir subtotal vacío a NULL
                    $subtotal = !empty($_POST['subtotal18'][$i]) ? $_POST['subtotal18'][$i] : null;
                    $bundleGroup = !empty($_POST['bundleGroup18'][$i]) ? $_POST['bundleGroup18'][$i] : null;

                    $stmt->execute([
                        $saved_form_id,
                        $i + 1,
                        $serviceType,
                        $_POST['time18'][$i] ?? null,
                        $_POST['freq18'][$i] ?? null,
                        $_POST['desc18'][$i] ?? null,
                        $subtotal,
                        $bundleGroup
                    ]);
                }
            }
        }
    }
    
    // ============================================================
    // PASO 4: GUARDAR KITCHEN/HOODVENT COSTS (Q19)
    // ============================================================
    if (isset($_POST['includeKitchen']) && $_POST['includeKitchen'] === 'Yes') {
        $pdo->prepare("DELETE FROM kitchen_cleaning_costs WHERE form_id = ?")->execute([$saved_form_id]);
        $pdo->prepare("DELETE FROM hood_vent_costs WHERE form_id = ?")->execute([$saved_form_id]);

        if (isset($_POST['type19']) && is_array($_POST['type19'])) {
            $stmt_kitchen = $pdo->prepare("
                INSERT INTO kitchen_cleaning_costs (
                    form_id, service_number, service_type, service_time,
                    frequency, description, subtotal, bundle_group
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt_hood = $pdo->prepare("
                INSERT INTO hood_vent_costs (
                    form_id, service_number, service_type, service_time,
                    frequency, description, subtotal, bundle_group
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $count = count($_POST['type19']);
            for ($i = 0; $i < $count; $i++) {
                // Obtener tipo de servicio
                $serviceType = '';
                if (isset($_POST['type19'][$i]) && $_POST['type19'][$i] !== '__write__' && !empty($_POST['type19'][$i])) {
                    $serviceType = $_POST['type19'][$i];
                } elseif (isset($_POST['write19'][$i]) && !empty($_POST['write19'][$i])) {
                    $serviceType = $_POST['write19'][$i];
                }

                if (!empty($serviceType)) {
                    $serviceTime = $_POST['time19'][$i] ?? null;
                    $description = $_POST['desc19'][$i] ?? null;
                    $frequency = $_POST['freq19'][$i] ?? null;

                    // ⚠️ Convertir subtotal vacío a NULL
                    $subtotal = !empty($_POST['subtotal19'][$i]) ? $_POST['subtotal19'][$i] : null;
                    $bundleGroup = !empty($_POST['bundleGroup19'][$i]) ? $_POST['bundleGroup19'][$i] : null;

                    // Determinar si es Kitchen o Hood Vent
                    $isHoodVent = (stripos($serviceType, 'hood') !== false ||
                                   stripos($serviceType, 'vent') !== false ||
                                   stripos($serviceType, 'campana') !== false ||
                                   stripos($serviceType, 'extractora') !== false);

                    if ($isHoodVent) {
                        $stmt_hood->execute([
                            $saved_form_id,
                            $i + 1,
                            $serviceType,
                            $serviceTime,
                            $frequency,
                            $description,
                            $subtotal,
                            $bundleGroup
                        ]);
                    } else {
                        $stmt_kitchen->execute([
                            $saved_form_id,
                            $i + 1,
                            $serviceType,
                            $serviceTime,
                            $frequency,
                            $description,
                            $subtotal,
                            $bundleGroup
                        ]);
                    }
                }
            }
        }
    }
    
    // ============================================================
    // PASO 5: GUARDAR FOTOS (Q29)
    // ============================================================
    if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
        $upload_dir = __DIR__ . '/Uploads/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO form_photos (
                form_id, photo_filename, photo_path, photo_size, photo_type
            ) VALUES (?, ?, ?, ?, ?)
        ");
        
        $total_files = count($_FILES['photos']['name']);
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
                $original_name = $_FILES['photos']['name'][$i];
                $file_size = $_FILES['photos']['size'][$i];
                $file_type = $_FILES['photos']['type'][$i];
                $tmp_name = $_FILES['photos']['tmp_name'][$i];
                
                $timestamp = time();
                $safe_filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $original_name);
                $new_filename = $timestamp . '_' . $safe_filename;
                $destination = $upload_dir . $new_filename;
                
                if (move_uploaded_file($tmp_name, $destination)) {
                    $stmt->execute([
                        $saved_form_id,
                        $new_filename,
                        'Uploads/' . $new_filename,
                        $file_size,
                        $file_type
                    ]);
                }
            }
        }
    }
    
    // ============================================================
    // CONFIRMAR TRANSACCIÓN
    // ============================================================
    $pdo->commit();

    // ============================================================
    // PASO 6: SINCRONIZAR CON TABLA REQUESTS (Contract Generator)
    // ============================================================
    $requestId = null;
    // Prepare complete form data for requests sync
    $requestFormData = array_merge($_POST, [
        'Order_Nomenclature' => $nomenclature_val,
        'order_number' => $order_number_val,
        'Document_Date' => $document_date_val,
        'Work_Date' => $work_date_val,
        'status' => $status
    ]);

    $requestId = syncFormToRequests($pdo, $saved_form_id, $requestFormData);
    if ($requestId) {
        error_log("Form #$saved_form_id synced to requests table as request #$requestId");
    }

    // ============================================================
    // PASO 7: SYNC CALENDAR EVENT (if Work_Date is set)
    // ============================================================
    $calendarEventId = null;
    if (!empty($work_date_val)) {
        // Get existing calendar event frequency settings (preserve them on form save)
        $existingFreqMonths = 0;
        $existingFreqYears = 0;
        $existingDesc = null;
        $stmtCal = $pdo->prepare("SELECT frequency_months, frequency_years, description FROM calendar_events WHERE form_id = :fid AND is_base_event = 1 LIMIT 1");
        $stmtCal->execute([':fid' => $saved_form_id]);
        $existingCalEvent = $stmtCal->fetch();
        if ($existingCalEvent) {
            $existingFreqMonths = (int)$existingCalEvent['frequency_months'];
            $existingFreqYears = (int)$existingCalEvent['frequency_years'];
            $existingDesc = $existingCalEvent['description'];
        }

        $calendarEventId = syncCalendarEvent($pdo, $saved_form_id, $work_date_val, $existingFreqMonths, $existingFreqYears, $existingDesc);
    }

    echo json_encode([
        'success' => true,
        'form_id' => $saved_form_id,
        'request_id' => $requestId,
        'calendar_event_id' => $calendarEventId,
        'message' => $form_id ? 'Form updated successfully' : 'Form saved successfully'
    ]);
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_code' => $e->getCode(),
        'error_line' => $e->getLine()
    ]);
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'error_line' => $e->getLine()
    ]);
}
?>