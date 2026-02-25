<?php
// ============================================================
// save_draft.php - Contract Form Save
// ============================================================
// ARCHITECTURE: forms + contract_items as single source of truth
// All services saved to contract_items. total_cost calculated
// from SUM(contract_items.subtotal).
// ============================================================

header('Content-Type: application/json');

// Incluir configuracion de base de datos y RBAC
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

    // RBAC: Completed contracts can only be edited by Admin or Leader
    if ($form_id) {
        $stmtStatus = $pdo->prepare("SELECT status FROM forms WHERE form_id = ?");
        $stmtStatus->execute([$form_id]);
        $currentStatus = $stmtStatus->fetchColumn();

        if ($currentStatus === 'completed' && !in_array((int) $currentUser['role_id'], RBAC_FULL_ACCESS_ROLES, true)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Completed contracts can only be edited by Admin or Leader roles.'
            ]);
            exit;
        }
    }

    $pdo->beginTransaction();

    // ============================================================
    // HELPER FUNCTION: Convertir string vacio a NULL
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
            docnum = :docnum,
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
            payment_terms,
            inflation_adjustment, total_area, buildings_included, start_date_services,
            site_observation, additional_comments, email_information_sent,
            seller, include_staff,
            Document_Date, Work_Date, order_number, Order_Nomenclature, docnum,
            status, service_status, submitted_by, created_at
        ) VALUES (
            :service_type, :request_type, :priority, :requested_service,
            :client_name, :company_name, :contact_name, :phone, :email, :address, :city, :state, :is_new_client,
            :site_visit_conducted, :invoice_frequency, :contract_duration,
            :payment_terms,
            :inflation_adjustment, :total_area, :buildings_included, :start_date_services,
            :site_observation, :additional_comments, :email_information_sent,
            :seller, :include_staff,
            :document_date, :work_date, :order_number, :order_nomenclature, :docnum,
            :status, :service_status, :submitted_by, NOW()
        )";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':submitted_by', 'system');
    }

    // ============================================================
    // BIND PARAMETERS
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
    $stmt->bindValue(':docnum', $nomenclature_val);

    // Status
    $stmt->bindValue(':status', $status);

    // Section 10: Service Status
    $stmt->bindValue(':service_status', emptyToNull($_POST['service_status'] ?? null));

    $stmt->execute();

    // Get the form_id
    $saved_form_id = $form_id ?: $pdo->lastInsertId();

    // ============================================================
    // PASO 2: GUARDAR SCOPE SECTIONS (dynamic blocks)
    // ============================================================
    $pdo->prepare("DELETE FROM scope_sections WHERE form_id = ?")->execute([$saved_form_id]);

    $stmtScopeSec = $pdo->prepare("INSERT INTO scope_sections (form_id, section_order, title, scope_content) VALUES (?, ?, ?, ?)");
    $scopeSectionOrder = 0;

    // Save manually created scope sections
    if (isset($_POST['Scope_Sections_Title']) && is_array($_POST['Scope_Sections_Title'])) {
        $titles = $_POST['Scope_Sections_Title'];
        $contents = $_POST['Scope_Sections_Content'] ?? [];

        for ($i = 0; $i < count($titles); $i++) {
            $secTitle = trim($titles[$i] ?? '');
            $secContent = trim($contents[$i] ?? '');
            if (!empty($secTitle) || !empty($secContent)) {
                $stmtScopeSec->execute([$saved_form_id, $scopeSectionOrder, $secTitle, $secContent]);
                $scopeSectionOrder++;
            }
        }
    }

    // ============================================================
    // PASO 3: GUARDAR CONTRACT ITEMS (unified: Q18 + Q19)
    // ============================================================
    // Delete all existing contract items for this form
    $pdo->prepare("DELETE FROM contract_items WHERE form_id = ?")->execute([$saved_form_id]);

    $stmtItem = $pdo->prepare("
        INSERT INTO contract_items (
            form_id, category, service_name, service_type,
            service_time, frequency, description, subtotal, bundle_group, position
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // 3a: Save Janitorial Services (Q18) as contract_items
    if (isset($_POST['includeJanitorial']) && $_POST['includeJanitorial'] === 'Yes') {
        if (isset($_POST['type18']) && is_array($_POST['type18'])) {
            $count = count($_POST['type18']);
            for ($i = 0; $i < $count; $i++) {
                $serviceType = '';
                if (isset($_POST['type18'][$i]) && $_POST['type18'][$i] !== '__write__' && !empty($_POST['type18'][$i])) {
                    $serviceType = $_POST['type18'][$i];
                } elseif (isset($_POST['write18'][$i]) && !empty($_POST['write18'][$i])) {
                    $serviceType = $_POST['write18'][$i];
                }

                if (!empty($serviceType)) {
                    $subtotal = !empty($_POST['subtotal18'][$i]) ? $_POST['subtotal18'][$i] : 0;
                    $bundleGroup = !empty($_POST['bundleGroup18'][$i]) ? $_POST['bundleGroup18'][$i] : null;

                    $stmtItem->execute([
                        $saved_form_id,
                        'janitorial',
                        $serviceType,
                        $serviceType,
                        $_POST['time18'][$i] ?? null,
                        $_POST['freq18'][$i] ?? null,
                        $_POST['desc18'][$i] ?? null,
                        $subtotal,
                        $bundleGroup,
                        $i + 1
                    ]);
                }
            }
        }
    }

    // 3b: Save Kitchen/Hood Vent Services (Q19) as contract_items
    if (isset($_POST['includeKitchen']) && $_POST['includeKitchen'] === 'Yes') {
        if (isset($_POST['type19']) && is_array($_POST['type19'])) {
            $count = count($_POST['type19']);
            for ($i = 0; $i < $count; $i++) {
                $serviceType = '';
                if (isset($_POST['type19'][$i]) && $_POST['type19'][$i] !== '__write__' && !empty($_POST['type19'][$i])) {
                    $serviceType = $_POST['type19'][$i];
                } elseif (isset($_POST['write19'][$i]) && !empty($_POST['write19'][$i])) {
                    $serviceType = $_POST['write19'][$i];
                }

                if (!empty($serviceType)) {
                    $subtotal = !empty($_POST['subtotal19'][$i]) ? $_POST['subtotal19'][$i] : 0;
                    $bundleGroup = !empty($_POST['bundleGroup19'][$i]) ? $_POST['bundleGroup19'][$i] : null;

                    // Determine category: hood_vent or kitchen
                    $isHoodVent = (stripos($serviceType, 'hood') !== false ||
                                   stripos($serviceType, 'vent') !== false ||
                                   stripos($serviceType, 'campana') !== false ||
                                   stripos($serviceType, 'extractora') !== false);
                    $category = $isHoodVent ? 'hood_vent' : 'kitchen';

                    $stmtItem->execute([
                        $saved_form_id,
                        $category,
                        $serviceType,
                        $serviceType,
                        $_POST['time19'][$i] ?? null,
                        $_POST['freq19'][$i] ?? null,
                        $_POST['desc19'][$i] ?? null,
                        $subtotal,
                        $bundleGroup,
                        $i + 1
                    ]);
                }
            }
        }
    }

    // ============================================================
    // PASO 3b: GUARDAR CONTRACT STAFF
    // ============================================================
    $pdo->prepare("DELETE FROM contract_staff WHERE form_id = ?")->execute([$saved_form_id]);

    if (isset($_POST['includeStaff']) && $_POST['includeStaff'] === 'Yes') {
        $stmtStaff = $pdo->prepare("
            INSERT INTO contract_staff (form_id, department, position, base_rate, percent_increase, bill_rate)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        // Collect staff positions from dynamic form fields (base_{slug}, increase_{slug}, bill_{slug})
        $staffPositions = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'base_') === 0) {
                $slug = substr($key, 5);
                $baseRate = floatval($value);
                $percentIncrease = floatval($_POST['increase_' . $slug] ?? 0);
                $billRateRaw = $_POST['bill_' . $slug] ?? '0';
                $billRate = floatval(str_replace(['$', ','], '', $billRateRaw));
                $department = $_POST['department_' . $slug] ?? null;

                if ($baseRate > 0 || $percentIncrease > 0 || $billRate > 0) {
                    // Convert slug back to readable position name
                    $positionName = ucwords(str_replace('_', ' ', $slug));
                    $stmtStaff->execute([
                        $saved_form_id,
                        !empty($department) ? $department : null,
                        $positionName,
                        $baseRate > 0 ? $baseRate : null,
                        $percentIncrease > 0 ? $percentIncrease : null,
                        $billRate > 0 ? $billRate : null
                    ]);
                }
            }
        }
    }

    // ============================================================
    // PASO 4: CALCULAR Y GUARDAR TOTAL_COST
    // ============================================================
    recalculateTotalCost($pdo, $saved_form_id);

    // ============================================================
    // PASO 5: GUARDAR FOTOS (Q29)
    // ============================================================
    if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
        $storage = new FileStorageService();
        $uploadResults = $storage->uploadMultiple(
            $_FILES['photos'],
            'form_photos',
            'images',
            'form_' . $saved_form_id
        );

        if (!empty($uploadResults['uploaded'])) {
            $stmt = $pdo->prepare("
                INSERT INTO form_photos (
                    form_id, photo_filename, photo_path, photo_size, photo_type
                ) VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($uploadResults['uploaded'] as $photo) {
                $stmt->execute([
                    $saved_form_id,
                    $photo['filename'],
                    $photo['path'],
                    $photo['size'],
                    $photo['type'],
                ]);
            }
        }
    }

    // ============================================================
    // CONFIRMAR TRANSACCION
    // ============================================================
    $pdo->commit();

    // ============================================================
    // PASO 6: SYNC CALENDAR EVENT (if Work_Date is set)
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
