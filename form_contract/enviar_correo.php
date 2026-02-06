<?php
// ===============================
// ENVÍO CONDICIONAL DE CORREO
// Si hay fotos → PDF completo + fotos adjuntas
// Si NO hay fotos → solo aviso de confirmación de precio
// ===============================
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
// ===============================
// LOGO BASE64 PARA PDF (dynamic based on Service_Type)
// ===============================
$service_type_raw = strtolower(trim($_POST['Service_Type'] ?? ''));
if (strpos($service_type_raw, 'hospitality') !== false) {
    $logo_path = __DIR__ . '/../Images/phospitality.png';
} else {
    $logo_path = __DIR__ . '/../Images/pfacility.png';
}
$image_src = '';

if (file_exists($logo_path)) {
    $image_data = base64_encode(file_get_contents($logo_path));
    $image_src = 'data:image/png;base64,' . $image_data;
}


/**
 * enviar_correo.php (FIXED + FULL CLEAN)
 * GENERA PDF CORRECTAMENTE Y ENVÍA EMAIL
 */

require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//
// ===========================================
//  SECTION 1 — CAPTURA DE DATOS DEL FORMULARIO
// ===========================================
//

// ---- Request Information ----
$service_type       = $_POST['Service_Type']       ?? '';
$request_type       = $_POST['Request_Type']       ?? '';
$priority           = $_POST['Priority']           ?? '';
$requested_service  = $_POST['Requested_Service']  ?? '';


//  SECTION 2
// ---- Client Information ----
$client_name        = $_POST['client_name']        ?? '';
$client_title       = $_POST['Client_Title']       ?? '';
$email              = $_POST['Email']              ?? '';
$number_phone       = $_POST['Number_Phone']       ?? '';
$company_name       = $_POST['Company_Name']       ?? '';
$company_address    = $_POST['Company_Address']    ?? '';
$is_new_client      = $_POST['Is_New_Client']      ?? '';

//  SECTION 3
$site_visit_conducted = $_POST['Site_Visit_Conducted'] ?? '';

$frequency_period = $_POST['period'] ?? '';
$week_days        = $_POST['week_days'] ?? [];
$one_time         = $_POST['one_time'] ?? '';

$invoice_frequency     = $_POST['Invoice_Frequency']   ?? '';
$contract_duration     = $_POST['Contract_Duration']   ?? '';

//  SECTION 4
$Seller       = $_POST['Seller'] ?? '';
$PriceInput   = $_POST['PriceInput'] ?? '';
$prime_quoted_price = $_POST['Prime_Quoted_Price'] ?? '';

$includeJanitorial = $_POST['includeJanitorial'] ?? '';

$type18      = $_POST['type18']      ?? [];
$write18     = $_POST['write18']     ?? [];
$time18      = $_POST['time18']      ?? [];
$freq18      = $_POST['freq18']      ?? [];
$desc18      = $_POST['desc18']      ?? [];
$subtotal18  = $_POST['subtotal18']  ?? [];

$total18     = $_POST['total18']     ?? '';
$taxes18     = $_POST['taxes18']     ?? '';
$grand18     = $_POST['grand18']     ?? '';

// SECTION 19 — Hoodvent & Kitchen
$includeKitchen = $_POST['includeKitchen'] ?? '';

$type19      = $_POST['type19']      ?? [];
$time19      = $_POST['time19']      ?? [];
$freq19      = $_POST['freq19']      ?? [];
$desc19      = $_POST['desc19']      ?? [];
$subtotal19  = $_POST['subtotal19']  ?? [];

$total19     = $_POST['total19']     ?? '';
$taxes19     = $_POST['taxes19']     ?? '';
$grand19     = $_POST['grand19']     ?? '';
// SECTION 20 — Staff
$includeStaff = $_POST['includeStaff'] ?? '';

$base_staff     = [];
$increase_staff = [];
$bill_staff     = [];

// Capturar TODOS los inputs dinámicos
foreach ($_POST as $key => $value) {
    if (strpos($key, 'base_') === 0) {
        $base_staff[$key] = $value;
    }
    if (strpos($key, 'increase_') === 0) {
        $increase_staff[$key] = $value;
    }
    if (strpos($key, 'bill_') === 0) {
        $bill_staff[$key] = $value;
    }
}


// TODOS los inputs dinámicos generados por slugify
$base_rates    = [];
$increase_rates = [];
$bill_rates     = [];

foreach ($_POST as $key => $value) {
    if (strpos($key, 'base_') === 0) {
        $base_rates[$key] = $value;
    }
    if (strpos($key, 'increase_') === 0) {
        $increase_rates[$key] = $value;
    }
    if (strpos($key, 'bill_') === 0) {
        $bill_rates[$key] = $value;
    }
}


//  SECTION 5

$inflation_adjustment = $_POST['inflationAdjustment'] ?? '';
$total_area           = $_POST['totalArea']          ?? '';
$buildings_included   = $_POST['buildingsIncluded']  ?? '';
$start_date_services  = $_POST['startDateServices']  ?? '';

//  SECTION 6

$site_observation     = $_POST['Site_Observation']    ?? '';
$additional_comments  = $_POST['Additional_Comments'] ?? '';
$email_info_sent      = $_POST['Email_Information_Sent'] ?? '';

// SECTION 7
$scope_of_work = isset($_POST['Scope_Of_Work']) 
                 ? implode(', ', $_POST['Scope_Of_Work']) 
                 : '';

// SECTION 9 — Document & Work Dates
$document_date = $_POST['Document_Date'] ?? '';
$work_date     = $_POST['Work_Date']     ?? '';

// SECTION 8 — PHOTOS
$photos = $_FILES['photos'] ?? null;

//
// ===============================
//   PROCESAR FOTOS (ANTES DEL PDF)
// ===============================
//

$uploaded_photos = [];

if (!empty($photos) && isset($photos['tmp_name'])) {

    for ($i = 0; $i < count($photos['tmp_name']); $i++) {

        if ($photos['error'][$i] === UPLOAD_ERR_OK) {

            $tmp_name = $photos['tmp_name'][$i];
            $name     = time() . "_" . basename($photos['name'][$i]);

            $destination = __DIR__ . "/Uploads/" . $name;

            if (!is_dir(__DIR__ . "/Uploads/")) {
                mkdir(__DIR__ . "/Uploads/", 0777, true);
            }

            if (move_uploaded_file($tmp_name, $destination)) {
                $uploaded_photos[] = $destination;
            }
        }
    }
}

//
// ===============================
//   GUARDAR EN BASE DE DATOS
// ===============================
//

try {
    require_once 'db_config.php';
    $pdo = getDBConnection();

    // Helper function to convert empty strings to null
    function emptyToNull($value) {
        return ($value === '' || $value === null) ? null : $value;
    }

    // Preparar datos para guardar
    $week_days_json = !empty($week_days) ? json_encode($week_days) : null;
    $scope_json = !empty($_POST['Scope_Of_Work']) ? json_encode($_POST['Scope_Of_Work']) : null;
    $photos_json = !empty($uploaded_photos) ? json_encode($uploaded_photos) : null;

    // Arrays de la sección 18 (Janitorial)
    $type18_json = !empty($type18) ? json_encode($type18) : null;
    $write18_json = !empty($write18) ? json_encode($write18) : null;
    $time18_json = !empty($time18) ? json_encode($time18) : null;
    $freq18_json = !empty($freq18) ? json_encode($freq18) : null;
    $desc18_json = !empty($desc18) ? json_encode($desc18) : null;
    $subtotal18_json = !empty($subtotal18) ? json_encode($subtotal18) : null;

    // Arrays de la sección 19 (Kitchen)
    $type19_json = !empty($type19) ? json_encode($type19) : null;
    $time19_json = !empty($time19) ? json_encode($time19) : null;
    $freq19_json = !empty($freq19) ? json_encode($freq19) : null;
    $desc19_json = !empty($desc19) ? json_encode($desc19) : null;
    $subtotal19_json = !empty($subtotal19) ? json_encode($subtotal19) : null;

    // Staff data
    $base_staff_json = !empty($base_staff) ? json_encode($base_staff) : null;
    $increase_staff_json = !empty($increase_staff) ? json_encode($increase_staff) : null;
    $bill_staff_json = !empty($bill_staff) ? json_encode($bill_staff) : null;

    // Convert empty date to null for startDateServices (DATE column)
    $start_date_services_db = emptyToNull($start_date_services);

    // Convert empty dates to null for Document_Date and Work_Date
    $document_date_db = emptyToNull($document_date);
    $work_date_db     = emptyToNull($work_date);

    // =============================================
    // GENERATE ORDER NUMBER & NOMENCLATURE
    // =============================================
    // Order number: 1000-9999, reuses gaps from deleted records
    $stmtNums = $pdo->query("SELECT order_number FROM forms WHERE order_number IS NOT NULL ORDER BY order_number ASC");
    $usedNumbers = $stmtNums->fetchAll(PDO::FETCH_COLUMN);
    $usedSet = array_flip($usedNumbers);

    $order_number = null;
    for ($i = 1000; $i <= 9999; $i++) {
        if (!isset($usedSet[(string)$i])) {
            $order_number = $i;
            break;
        }
    }
    if ($order_number === null) {
        $order_number = 1000; // wrap around
    }

    // Build nomenclature: [ServiceTypeInitial][RequestTypeInitial]-[OrderNumber][MMDDYYYY]
    $st_initial = !empty($service_type) ? strtoupper($service_type[0]) : 'X';
    $rt_initial = !empty($request_type) ? strtoupper($request_type[0]) : 'X';

    $nomenclature = '';
    if (!empty($document_date)) {
        $dateParts = explode('-', $document_date); // yyyy-mm-dd
        $dateFormatted = $dateParts[1] . $dateParts[2] . $dateParts[0]; // MMDDYYYY
        $nomenclature = $st_initial . $rt_initial . '-' . $order_number . $dateFormatted;
    }

    // Insert into database - column names must match actual DB schema
    $stmt = $pdo->prepare("
        INSERT INTO forms (
            service_type, request_type, priority, requested_service,
            client_name, contact_name, email, phone,
            company_name, address, is_new_client,
            city, state,
            site_visit_conducted,
            invoice_frequency, contract_duration,
            seller, total_cost,
            include_staff,
            inflation_adjustment, total_area, buildings_included, start_date_services,
            site_observation, additional_comments, email_information_sent,
            payment_terms,
            Document_Date, Work_Date, order_number, Order_Nomenclature,
            status
        ) VALUES (
            :service_type, :request_type, :priority, :requested_service,
            :client_name, :contact_name, :email, :phone,
            :company_name, :address, :is_new_client,
            :city, :state,
            :site_visit_conducted,
            :invoice_frequency, :contract_duration,
            :seller, :total_cost,
            :include_staff,
            :inflation_adjustment, :total_area, :buildings_included, :start_date_services,
            :site_observation, :additional_comments, :email_info_sent,
            :payment_terms,
            :document_date, :work_date, :order_number, :order_nomenclature,
            'pending'
        )
    ");

    // Bind parameters
    $stmt->execute([
        ':service_type' => $service_type,
        ':request_type' => $request_type,
        ':priority' => $priority,
        ':requested_service' => $requested_service,
        ':client_name' => $client_name,
        ':contact_name' => $client_title,
        ':email' => $email,
        ':phone' => $number_phone,
        ':company_name' => $company_name,
        ':address' => $company_address,
        ':is_new_client' => $is_new_client,
        ':city' => emptyToNull($_POST['City'] ?? ''),
        ':state' => emptyToNull($_POST['State'] ?? ''),
        ':site_visit_conducted' => $site_visit_conducted,
        ':invoice_frequency' => $invoice_frequency,
        ':contract_duration' => $contract_duration,
        ':seller' => $Seller,
        ':total_cost' => (function() use ($PriceInput, $grand18, $grand19) {
            $p = floatval(str_replace(['$', ','], '', $PriceInput ?: '0'));
            $g18 = floatval(str_replace(['$', ','], '', $grand18 ?: '0'));
            $g19 = floatval(str_replace(['$', ','], '', $grand19 ?: '0'));
            $sum = $p + $g18 + $g19;
            return $sum > 0 ? $sum : null;
        })(),
        ':include_staff' => $includeStaff,
        ':inflation_adjustment' => emptyToNull($inflation_adjustment),
        ':total_area' => emptyToNull($total_area),
        ':buildings_included' => emptyToNull($buildings_included),
        ':start_date_services' => $start_date_services_db,
        ':site_observation' => $site_observation,
        ':additional_comments' => $additional_comments,
        ':email_info_sent' => $email_info_sent,
        ':payment_terms' => emptyToNull($_POST['payment_terms'] ?? ''),
        ':document_date' => $document_date_db,
        ':work_date' => $work_date_db,
        ':order_number' => $order_number,
        ':order_nomenclature' => emptyToNull($nomenclature)
    ]);

    $request_id = $pdo->lastInsertId();

    // ============================================================
    // SINCRONIZAR CON CALENDARIO (Calendar System)
    // ============================================================
    $calendarEventId = null;
    if (!empty($work_date) && $request_id) {
        $calendarFormData = [
            'Work_Date' => $work_date,
            'Document_Date' => $document_date,
            'Order_Nomenclature' => $nomenclature,
            'order_number' => $order_number,
            'Company_Name' => $company_name,
            'Company_Address' => $company_address,
            'City' => $_POST['City'] ?? '',
            'State' => $_POST['State'] ?? '',
            'Requested_Service' => $requested_service,
            'Service_Type' => $service_type,
            'Request_Type' => $request_type,
            'Priority' => $priority ?: 'Medium',
            'status' => 'pending'
        ];

        $calendarEventId = syncFormToCalendar($request_id, $calendarFormData);
        if ($calendarEventId) {
            error_log("Form #$request_id synced to calendar event #$calendarEventId");
        }
    }

    // ============================================================
    // SINCRONIZAR CON TABLA REQUESTS (Contract Generator)
    // ============================================================
    $syncedRequestId = null;
    if ($request_id) {
        $requestFormData = [
            'Service_Type' => $service_type,
            'Request_Type' => $request_type,
            'Priority' => $priority,
            'Requested_Service' => $requested_service,
            'client_name' => $client_name,
            'Client_Title' => $client_title,
            'Email' => $email,
            'Number_Phone' => $number_phone,
            'Company_Name' => $company_name,
            'Company_Address' => $company_address,
            'City' => $_POST['City'] ?? '',
            'State' => $_POST['State'] ?? '',
            'Is_New_Client' => $is_new_client,
            'Site_Visit_Conducted' => $site_visit_conducted,
            'Invoice_Frequency' => $invoice_frequency,
            'Contract_Duration' => $contract_duration,
            'Seller' => $Seller,
            'PriceInput' => $PriceInput,
            'Prime_Quoted_Price' => $prime_quoted_price,
            'inflationAdjustment' => $inflation_adjustment,
            'totalArea' => $total_area,
            'buildingsIncluded' => $buildings_included,
            'startDateServices' => $start_date_services,
            'Site_Observation' => $site_observation,
            'Additional_Comments' => $additional_comments,
            'Order_Nomenclature' => $nomenclature,
            'order_number' => $order_number,
            'Document_Date' => $document_date,
            'Work_Date' => $work_date,
            'status' => 'pending'
        ];

        $syncedRequestId = syncFormToRequests($pdo, $request_id, $requestFormData);
        if ($syncedRequestId) {
            error_log("Form #$request_id synced to requests table as request #$syncedRequestId");
        }
    }

} catch (Exception $e) {
    // Log error with full details
    error_log("Database save error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $request_id = null;
    $db_error = $e->getMessage();
}

//
// ===============================
//   CREAR CONTENIDO DEL PDF
// ===============================
//

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  body { 
    font-family: Arial, sans-serif; 
    background-color:#fff; 
    color:#333; 
  }
  h1 { text-align:center; color:#a30000; margin-bottom:5px; }
  .logo { text-align:center; margin-bottom:15px; }
  img { width:250px; }
  .section { margin-top:25px; border-left:4px solid #a30000; padding-left:10px; }
  .section h2 { color:#a30000; font-size:18px; margin-bottom:10px; }
  .field { margin-bottom:6px; }
  .label { font-weight:bold; display:inline-block; min-width:220px; }
</style>
</head>

<body>

<!-- LOGO -->
<div class="logo-container">
  <?php if (!empty($image_src)): ?>
      <img src="<?= $image_src ?>" alt="Prime Facility Services Logo" style="max-width:250px; height:auto;">
  <?php endif; ?>
</div>


<!-- TITLE -->
<h1>Request Form - Prime Facility Services</h1>

<!-- ========================= -->
<!-- SECTION 1: REQUEST INFO   -->
<!-- ========================= -->
<div class="section">
<h2>Section 1: Request Information</h2>

<div class="field"><span class="label">Service Type:</span><?= htmlspecialchars($service_type) ?></div>
<div class="field"><span class="label">Request Type:</span><?= htmlspecialchars($request_type) ?></div>
<div class="field"><span class="label">Priority:</span><?= htmlspecialchars($priority) ?></div>
<div class="field"><span class="label">Requested Service:</span><?= htmlspecialchars($requested_service) ?></div>
</div>

<!-- ========================= -->
<!-- SECTION 2: CLIENT INFO    -->
<!-- ========================= -->
<div class="section">
<h2>Section 2: Client Information</h2>

<div class="field"><span class="label">Client Name:</span><?= htmlspecialchars($client_name) ?></div>
<div class="field"><span class="label">Client Title:</span><?= htmlspecialchars($client_title) ?></div>
<div class="field"><span class="label">Email:</span><?= htmlspecialchars($email) ?></div>
<div class="field"><span class="label">Phone:</span><?= htmlspecialchars($number_phone) ?></div>
<div class="field"><span class="label">Company:</span><?= htmlspecialchars($company_name) ?></div>
<div class="field"><span class="label">Address:</span><?= htmlspecialchars($company_address) ?></div>
<div class="field"><span class="label">New Client:</span><?= htmlspecialchars($is_new_client) ?></div>
</div>

<!-- ========================= -->
<!-- SECTION 3: OPERATIONAL    -->
<!-- ========================= -->
<div class="section">
<h2>Section 3: Operational Details</h2>

<div class="field"><span class="label">Site Visit Conducted:</span><?= htmlspecialchars($site_visit_conducted) ?></div>

<div class="field"><span class="label">Service Frequency:</span>
<div style="margin-left:20px; line-height:1.6;">
<?php
if ($frequency_period) echo "<strong>Period:</strong> " . htmlspecialchars($frequency_period) . "<br>";
if (!empty($week_days)) echo "<strong>Selected Days:</strong> " . htmlspecialchars(implode(', ', $week_days)) . "<br>";
if ($one_time) echo "<strong>One Time:</strong> " . htmlspecialchars($one_time) . "<br>";
?>
</div>
</div>

<div class="field"><span class="label">Invoice Frequency:</span><?= htmlspecialchars($invoice_frequency) ?></div>
<div class="field"><span class="label">Contract Duration:</span><?= htmlspecialchars($contract_duration) ?></div>
</div>

<!-- ============================== -->
<!-- SECTION 4 — ECONOMIC INFO -->
<!-- ============================== -->
<div class="section">
<h2>Section 4: Economic Information</h2>

<!-- SELLER -->
<div class="field"><span class="label">Seller:</span><?= htmlspecialchars($Seller) ?></div>

<!-- PRICE INPUT -->
<div class="field">
    <span class="label">
        <?= ($Seller === "Miguel Palma" || $Seller === "Sandra Hernandez") 
                ? "Subcontractor Price:" 
                : "Prime Quoted Price:" ?>
    </span>
    <?= htmlspecialchars($PriceInput) ?>
</div>

<hr style="margin:20px 0;">

<!-- ========================== -->
<!-- 18 — JANITORIAL SERVICES -->
<!-- ========================== -->
<h3 style="color:#c00;">18. Janitorial Services</h3>

<div class="field"><span class="label">Included:</span><?= htmlspecialchars($includeJanitorial) ?></div>

<?php if ($includeJanitorial === "Yes"): ?>

<table style="width:100%; border-collapse: collapse; font-size:13px; margin-top:10px;">
    <thead>
        <tr style="background:#c00; color:white;">
            <th style="padding:6px; border:1px solid #ddd;">Type of Services</th>
            <th style="padding:6px; border:1px solid #ddd;">Service Time</th>
            <th style="padding:6px; border:1px solid #ddd;">Frequency</th>
            <th style="padding:6px; border:1px solid #ddd;">Description</th>
            <th style="padding:6px; border:1px solid #ddd;">Subtotal</th>
        </tr>
    </thead>

    <tbody>
        <?php for ($i = 0; $i < count($type18); $i++): ?>
        <tr>
            <td style="border:1px solid #ddd; padding:6px;">
                <?= htmlspecialchars($type18[$i] === "__write__" ? $write18[$i] : $type18[$i]) ?>
            </td>
            <td style="border:1px solid #ddd; padding:6px;"><?= htmlspecialchars($time18[$i]) ?></td>
            <td style="border:1px solid #ddd; padding:6px;"><?= htmlspecialchars($freq18[$i]) ?></td>
            <td style="border:1px solid #ddd; padding:6px;"><?= htmlspecialchars($desc18[$i]) ?></td>
            <td style="border:1px solid #ddd; padding:6px;">$<?= number_format(floatval($subtotal18[$i] ?? 0), 2) ?></td>
        </tr>
        <?php endfor; ?>
    </tbody>
</table>

<!-- Totales Section 18 -->
<div style="margin-top:15px;">
    <p><strong>Total:</strong> <?= htmlspecialchars($total18) ?></p>
    <p><strong>Taxes (8.25%):</strong> <?= htmlspecialchars($taxes18) ?></p>
    <p><strong>Grand Total:</strong> <?= htmlspecialchars($grand18) ?></p>
</div>

<?php endif; ?>

<hr style="margin:25px 0;">


<!-- ========================== -->
<!-- 19 — HOODVENT & KITCHEN -->
<!-- ========================== -->
 <h3 style="color:#c00;">19. Hoodvent & Kitchen Cleaning</h3>
<?php if ($includeKitchen === "Yes"): ?>

<table style="width:100%; border-collapse: collapse; font-size:13px; margin-top:10px;">
    <thead>
        <tr style="background:#c00; color:white;">
            <th style="padding:6px; border:1px solid #ddd;">Type of Services</th>
            <th style="padding:6px; border:1px solid #ddd;">Service Time</th>
            <th style="padding:6px; border:1px solid #ddd;">Frequency</th>
            <th style="padding:6px; border:1px solid #ddd;">Description</th>
            <th style="padding:6px; border:1px solid #ddd;">Subtotal</th>
        </tr>
    </thead>

    <tbody>
        <?php
        // OBTENER EL TOTAL REAL DE FILAS
        $rows19 = max(
            count($type19),
            count($time19),
            count($freq19),
            count($desc19),
            count($subtotal19)
        );

        for ($i = 0; $i < $rows19; $i++):
            $t19  = $type19[$i]     ?? "";
            $ti19 = $time19[$i]     ?? "";
            $fr19 = $freq19[$i]     ?? "";
            $de19 = $desc19[$i]     ?? "";
            $st19 = $subtotal19[$i] ?? "";
        ?>

        <!-- FILA -->
        <tr>
            <td style="border:1px solid #ddd; padding:6px;"><?= htmlspecialchars($t19) ?></td>
            <td style="border:1px solid #ddd; padding:6px;"><?= htmlspecialchars($ti19) ?></td>
            <td style="border:1px solid #ddd; padding:6px;"><?= htmlspecialchars($fr19) ?></td>
            <td style="border:1px solid #ddd; padding:6px;"><?= htmlspecialchars($de19) ?></td>
            <td style="border:1px solid #ddd; padding:6px;">
                <?= $st19 !== "" ? "$" . number_format(floatval($st19 ?? 0), 2) : "" ?>
            </td>
        </tr>

        <?php endfor; ?>
    </tbody>
</table>

<!-- TOTALES -->
<div style="margin-top:15px;">
    <p><strong>Total:</strong> <?= htmlspecialchars($total19) ?></p>
    <p><strong>Taxes (8.25%):</strong> <?= htmlspecialchars($taxes19) ?></p>
    <p><strong>Grand Total:</strong> <?= htmlspecialchars($grand19) ?></p>
</div>

<?php endif; ?>

<!-- =============================== -->
<!-- SECTION 20 — STAFF -->
<!-- =============================== -->
<div class="section">
<h2>20. Staff</h2>

<div class="field">
    <span class="label">Include Staff:</span><?= htmlspecialchars($includeStaff) ?>
</div>

<?php if ($includeStaff === "Yes"): ?>

<?php 
// CATEGORÍAS ORDENADAS COMO EN EL FORMULARIO
$categories = [
    "housekeeping" => "HOUSEKEEPING",
    "food_beverage" => "FOOD & BEVERAGE",
    "maintenance" => "MAINTENANCE",
    "recreation_pool" => "RECREATION & POOL",
    "security" => "SECURITY",
    "valet_parking" => "VALET PARKING",
    "front_desk" => "FRONT DESK"
];

// AGRUPAR POR CATEGORÍA
$grouped = [];

foreach ($base_staff as $key => $value) {

    $slug = substr($key, 5); // quitar "base_"

    foreach ($categories as $catSlug => $catName) {

        if (strpos($slug, $catSlug) === 0) {

            $base     = trim($value);
            $increase = trim($increase_staff["increase_" . $slug] ?? "");
            $bill     = trim($bill_staff["bill_" . $slug] ?? "");

            // ❗ SOLO agregar si al menos uno tiene valor
            if ($base !== "" || $increase !== "" || $bill !== "") {
                $grouped[$catSlug][$slug] = [
                    "base"     => $base,
                    "increase" => $increase,
                    "bill"     => $bill
                ];
            }

            break;
        }
    }
}
?>
<?php foreach ($categories as $catSlug => $catName): ?>
    <?php if (!empty($grouped[$catSlug])): ?>

    <h3 style="margin-top:25px; color:#c00;"><?= $catName ?></h3>

    <table style="width:100%; border-collapse: collapse; font-size:13px; margin-top:10px;">
        <thead>
            <tr style="background:#c00; color:white;">
                <th style="padding:6px; border:1px solid #ddd;">Position</th>
                <th style="padding:6px; border:1px solid #ddd;">Base Rate</th>
                <th style="padding:6px; border:1px solid #ddd;">% Increase</th>
                <th style="padding:6px; border:1px solid #ddd;">Bill Rate</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($grouped[$catSlug] as $slug => $info): ?>
            <?php $positionName = ucwords(str_replace("_", " ", $slug)); ?>
            <tr>
                <td style="border:1px solid #ddd; padding:6px;"><?= htmlspecialchars($positionName) ?></td>
                <td style="border:1px solid #ddd; padding:6px;"><?= $info["base"] ?></td>
                <td style="border:1px solid #ddd; padding:6px;"><?= $info["increase"] ?></td>
                <td style="border:1px solid #ddd; padding:6px;"><?= $info["bill"] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php endif; ?>
<?php endforeach; ?>

<?php endif; ?>
</div>


<!-- ============================== -->
<!-- SECTION 5 — CONTRACT INFORMATION -->
<!-- ============================== -->
<div class="section">
<h2>Section 5: Contract Information</h2>

<div class="field"><span class="label">Inflation Adjustment:</span><?= htmlspecialchars($inflation_adjustment) ?></div>
<div class="field"><span class="label">Total Area:</span><?= htmlspecialchars($total_area) ?></div>
<div class="field"><span class="label">Buildings Included:</span><?= htmlspecialchars($buildings_included) ?></div>
<div class="field"><span class="label">Start Date of Services:</span><?= htmlspecialchars($start_date_services) ?></div>

</div>

<!-- ============================== -->
<!-- SECTION 6 — OBSERVATIONS -->
<!-- ============================== -->
<div class="section">
<h2>Section 6: Observations</h2>

<div class="field"><span class="label">Site Observation:</span><?= htmlspecialchars($site_observation) ?></div>
<div class="field"><span class="label">Additional Comments:</span><?= htmlspecialchars($additional_comments) ?></div>
<div class="field"><span class="label">Email Info Sent:</span><?= htmlspecialchars($email_info_sent) ?></div>
</div>

<!-- ============================== -->
<!-- SECTION 7 — SCOPE OF WORK -->
<!-- ============================== -->
<div class="section">
<h2>Section 7: Scope of Work</h2>

<?php if (!empty($_POST['Scope_Of_Work'])): ?>
<ul style="margin-left:25px; line-height:1.6;">
<?php foreach ($_POST['Scope_Of_Work'] as $item): ?>
<li><?= htmlspecialchars($item) ?></li>
<?php endforeach; ?>
</ul>
<?php else: ?>
<p style="color:#777;">No scope of work items were selected.</p>
<?php endif; ?>

</div>

<!-- ============================== -->
<!-- SECTION 8 — PHOTOS -->
<!-- ============================== -->
<?php if (!empty($uploaded_photos)): ?>
<div class="section">
<h2>Section 8: Photos</h2>
<?php foreach ($uploaded_photos as $photo): 
    $base64 = base64_encode(file_get_contents($photo));
    $mime_type = mime_content_type($photo);
?>
    <img src="data:<?= $mime_type ?>;base64,<?= $base64 ?>" style="width:200px; margin:10px;">
<?php endforeach; ?>
</div>
<?php endif; ?>

<div style="text-align:center; font-size:12px; margin-top:40px; color:#777;">
Generated on <?= date('d/m/Y H:i:s') ?> — IP: <?= $_SERVER['REMOTE_ADDR'] ?>
</div>

</body>
</html>

<?php
// CAPTURAR HTML DEL PDF
$html = ob_get_clean();

//
// ==============================
// GENERAR PDF (DOMPDF OK)
// ==============================
//

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ==============================
// CONDITIONAL EMAIL SENDING
// If photos → send PDF + photos attached
// If no photos → send brief notification
// ==============================

$mail_config = include('../mail_config.php');
$email_sent = false;
$email_error = '';

try {
    $mailer = new PHPMailer(true);

    // SMTP Configuration
    $mailer->isSMTP();
    $mailer->Host = $mail_config['smtp_host'];
    $mailer->SMTPAuth = true;
    $mailer->Username = $mail_config['smtp_username'];
    $mailer->Password = $mail_config['smtp_password'];
    $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mailer->Port = $mail_config['smtp_port'];
    $mailer->CharSet = 'UTF-8';

    // Sender and Recipient
    $mailer->setFrom($mail_config['from_email'], $mail_config['from_name']);
    $mailer->addAddress($mail_config['to_email'], $mail_config['to_name']);

    // Check if there are photos
    $has_photos = !empty($uploaded_photos);

    if ($has_photos) {
        // ===============================
        // CASE A: With Photos
        // Send complete PDF + photos attached
        // ===============================
        $mailer->Subject = "New Request Form - $company_name (With Photos)";

        // Attach PDF
        $pdf_content = $dompdf->output();
        $pdf_filename = 'RequestForm_' . str_replace(' ', '_', $company_name) . '_' . date('Ymd') . '.pdf';
        $mailer->addStringAttachment($pdf_content, $pdf_filename, 'base64', 'application/pdf');

        // Attach photos
        foreach ($uploaded_photos as $photo_path) {
            if (file_exists($photo_path)) {
                $mailer->addAttachment($photo_path);
            }
        }

        // Email body
        $mailer->isHTML(true);
        $mailer->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: linear-gradient(135deg, #001f54, #a30000); color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .info-box { background: #f8f9fa; border-left: 4px solid #001f54; padding: 15px; margin: 15px 0; }
                .label { font-weight: bold; color: #001f54; }
                .footer { background: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>New Request Form Submitted</h1>
                <p>With Photographs Attached</p>
            </div>
            <div class='content'>
                <div class='info-box'>
                    <p><span class='label'>Company:</span> $company_name</p>
                    <p><span class='label'>Client:</span> $client_name</p>
                    <p><span class='label'>Email:</span> $email</p>
                    <p><span class='label'>Phone:</span> $number_phone</p>
                    <p><span class='label'>Service Type:</span> $service_type</p>
                    <p><span class='label'>Request Type:</span> $request_type</p>
                    <p><span class='label'>Seller:</span> $Seller</p>
                    <p><span class='label'>Price:</span> " . ($PriceInput ?: 'Pending confirmation') . "</p>
                </div>
                <p><strong>Attachments:</strong></p>
                <ul>
                    <li>Complete Request Form PDF</li>
                    <li>" . count($uploaded_photos) . " photograph(s)</li>
                </ul>
                <p>Please review the attached documents and confirm pricing if needed.</p>
            </div>
            <div class='footer'>
                <p>Generated by Sales Management System - " . date('m/d/Y H:i:s') . "</p>
            </div>
        </body>
        </html>";

    } else {
        // ===============================
        // CASE B: No Photos
        // Send brief notification only
        // ===============================
        $mailer->Subject = "Request Form Submitted - Price Confirmation Needed - $company_name";

        $mailer->isHTML(true);
        $mailer->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: linear-gradient(135deg, #001f54, #a30000); color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .alert { background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; border-radius: 8px; margin: 15px 0; }
                .info-box { background: #f8f9fa; border-left: 4px solid #001f54; padding: 15px; margin: 15px 0; }
                .label { font-weight: bold; color: #001f54; }
                .button { display: inline-block; background: #001f54; color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; margin-top: 15px; }
                .footer { background: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Request Form Submitted</h1>
                <p>Price Confirmation Needed</p>
            </div>
            <div class='content'>
                <div class='alert'>
                    <strong>Note:</strong> A new Request Form has been submitted and requires price confirmation.
                </div>
                <div class='info-box'>
                    <p><span class='label'>Company:</span> $company_name</p>
                    <p><span class='label'>Client:</span> $client_name</p>
                    <p><span class='label'>Service Type:</span> $service_type</p>
                    <p><span class='label'>Request Type:</span> $request_type</p>
                    <p><span class='label'>Seller:</span> $Seller</p>
                    <p><span class='label'>Current Price:</span> " . ($PriceInput ?: '<em>Not set - needs confirmation</em>') . "</p>
                </div>
                <p>All form details are available in the database. Please access the Contract Generator to review and confirm the pricing.</p>
                <p style='text-align:center;'>
                    <a href='#' class='button'>Access Contract Generator</a>
                </p>
            </div>
            <div class='footer'>
                <p>Generated by Sales Management System - " . date('m/d/Y H:i:s') . "</p>
                <p>No photographs were included with this request.</p>
            </div>
        </body>
        </html>";
    }

    // Send email
    $mailer->send();
    $email_sent = true;

} catch (Exception $e) {
    $email_error = $mailer->ErrorInfo;
    error_log("Email sending failed: " . $email_error);
}

// Set success status for redirect
if (isset($db_error)) {
    $email_status = 'error';
    $email_message = 'Database error: ' . $db_error;
} elseif ($request_id) {
    $email_status = 'success';
    if ($email_sent) {
        if (!empty($uploaded_photos)) {
            $email_message = 'Form submitted successfully! Email sent with PDF and ' . count($uploaded_photos) . ' photo(s) attached.';
        } else {
            $email_message = 'Form submitted successfully! Notification email sent for price confirmation.';
        }
    } else {
        $email_message = 'Form submitted successfully! (Email notification could not be sent: ' . $email_error . ')';
    }
} else {
    $email_status = 'error';
    $email_message = 'Unknown error: Form could not be saved to database.';
}

?>

<!-- ============================== -->
<!-- CONFIRMATION PAGE -->
<!-- ============================== -->
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Form Submitted</title>

<style>
body {
  background: linear-gradient(135deg, #001f54, #a30000);
  padding: 40px;
  font-family: Arial;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  margin: 0;
}
.box {
  background:white;
  padding:40px;
  border-radius:20px;
  text-align:center;
  width:500px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}
.box h2 {
  color: #001f54;
  margin-bottom: 20px;
}
.box p {
  color: #333;
  line-height: 1.6;
}
.btn-group {
  display: flex;
  gap: 15px;
  justify-content: center;
  margin-top: 25px;
}
.btn {
  background:#001f54;
  padding:14px 28px;
  color:#fff;
  display:inline-block;
  border-radius:50px;
  text-decoration:none;
  font-weight: 600;
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
  font-size: 14px;
}
.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0,31,84,0.4);
}
.btn-primary {
  background: linear-gradient(135deg, #28a745 0%, #218838 100%);
}
.btn-primary:hover {
  box-shadow: 0 6px 16px rgba(40,167,69,0.4);
}
.btn-secondary {
  background: #6c757d;
}
.btn-secondary:hover {
  background: #5a6268;
}
.info-box {
  background: #f8f9fa;
  border-left: 4px solid #001f54;
  padding: 15px;
  margin: 20px 0;
  text-align: left;
}
.info-box strong {
  color: #001f54;
}
</style>
</head>

<body>
<div class="box">
<h2><?= $email_status === 'success' ? '✅ Form Submitted Successfully!' : '❌ Error' ?></h2>
<p><?= $email_message ?></p>

<?php if ($email_status === 'success'): ?>
<div class="info-box">
  <p><strong>Company:</strong> <?= htmlspecialchars($company_name) ?></p>
  <p><strong>Client:</strong> <?= htmlspecialchars($client_name) ?></p>
  <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
  <?php if (isset($request_id) && $request_id): ?>
  <p><strong>Request ID:</strong> #<?= $request_id ?></p>
  <?php endif; ?>
</div>

<p style="color:#666; font-size:14px;">
  Your request has been saved and is ready for contract generation.
</p>

<div class="btn-group">
  <?php if (isset($request_id) && $request_id): ?>
  <a href="../contract_generator/contract_generator/index.php?request_id=<?= $request_id ?>" class="btn btn-primary">
    📋 Go to Contract Generator
  </a>
  <?php endif; ?>
  <a href="index.php" class="btn btn-secondary">
    ← Back to Form
  </a>
</div>

<?php else: ?>

<div class="btn-group">
  <a href="index.php" class="btn btn-secondary">← Try Again</a>
</div>

<?php endif; ?>

</div>
</body>
</html>