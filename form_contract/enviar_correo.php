<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);

try {
    // ============================================
    // CONFIGURACIÓN PARA DESARROLLO LOCAL
    // ============================================
    if ($_SERVER['HTTP_HOST'] == 'localhost' || 
        strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
        
        // Usar MailHog para desarrollo
        $mail->Host = 'localhost';
        $mail->Port = 1025;
        $mail->SMTPAuth = false;
        $mail->SMTPSecure = false;
        
    } else {
        // ============================================
        // CONFIGURACIÓN PARA PRODUCCIÓN
        // ============================================
        $mail->Host = 'smtp-relay.brevo.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USERNAME'); // Usar variables de entorno
        $mail->Password = getenv('SMTP_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }
    
    $mail->isSMTP();
    $mail->setFrom('noreply@tuempresa.com', 'Prime Facility Services');
    $mail->addAddress($email_destino);
    $mail->isHTML(true);
    $mail->Subject = 'Nuevo Formulario';
    $mail->Body = $contenido_html;
    
    $mail->send();
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $mail->ErrorInfo
    ]);
}
?>

<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
// ===============================
// LOGO BASE64 PARA PDF
// ===============================
$logo_path = __DIR__ . '/Images/Facility.png';
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
use PHPMailer\PHPMailer\Exception;

// Load email configuration
$mail_config = require_once '../mail_config.php';

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

    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO requests (
            Service_Type, Request_Type, Priority, Requested_Service,
            client_name, Client_Title, Email, Number_Phone,
            Company_Name, Company_Address, Is_New_Client,
            Site_Visit_Conducted, frequency_period, week_days, one_time,
            Invoice_Frequency, Contract_Duration,
            Seller, PriceInput, Prime_Quoted_Price,
            includeJanitorial, type18, write18, time18, freq18, desc18, subtotal18,
            total18, taxes18, grand18,
            includeKitchen, type19, time19, freq19, desc19, subtotal19,
            total19, taxes19, grand19,
            includeStaff, base_staff, increase_staff, bill_staff,
            inflationAdjustment, totalArea, buildingsIncluded, startDateServices,
            Site_Observation, Additional_Comments, Email_Information_Sent,
            Scope_Of_Work, photos,
            status
        ) VALUES (
            :service_type, :request_type, :priority, :requested_service,
            :client_name, :client_title, :email, :number_phone,
            :company_name, :company_address, :is_new_client,
            :site_visit_conducted, :frequency_period, :week_days, :one_time,
            :invoice_frequency, :contract_duration,
            :seller, :price_input, :prime_quoted_price,
            :includeJanitorial, :type18, :write18, :time18, :freq18, :desc18, :subtotal18,
            :total18, :taxes18, :grand18,
            :includeKitchen, :type19, :time19, :freq19, :desc19, :subtotal19,
            :total19, :taxes19, :grand19,
            :includeStaff, :base_staff, :increase_staff, :bill_staff,
            :inflation_adjustment, :total_area, :buildings_included, :start_date_services,
            :site_observation, :additional_comments, :email_info_sent,
            :scope_of_work, :photos,
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
        ':client_title' => $client_title,
        ':email' => $email,
        ':number_phone' => $number_phone,
        ':company_name' => $company_name,
        ':company_address' => $company_address,
        ':is_new_client' => $is_new_client,
        ':site_visit_conducted' => $site_visit_conducted,
        ':frequency_period' => $frequency_period,
        ':week_days' => $week_days_json,
        ':one_time' => $one_time,
        ':invoice_frequency' => $invoice_frequency,
        ':contract_duration' => $contract_duration,
        ':seller' => $Seller,
        ':price_input' => $PriceInput,
        ':prime_quoted_price' => $prime_quoted_price,
        ':includeJanitorial' => $includeJanitorial,
        ':type18' => $type18_json,
        ':write18' => $write18_json,
        ':time18' => $time18_json,
        ':freq18' => $freq18_json,
        ':desc18' => $desc18_json,
        ':subtotal18' => $subtotal18_json,
        ':total18' => $total18,
        ':taxes18' => $taxes18,
        ':grand18' => $grand18,
        ':includeKitchen' => $includeKitchen,
        ':type19' => $type19_json,
        ':time19' => $time19_json,
        ':freq19' => $freq19_json,
        ':desc19' => $desc19_json,
        ':subtotal19' => $subtotal19_json,
        ':total19' => $total19,
        ':taxes19' => $taxes19,
        ':grand19' => $grand19,
        ':includeStaff' => $includeStaff,
        ':base_staff' => $base_staff_json,
        ':increase_staff' => $increase_staff_json,
        ':bill_staff' => $bill_staff_json,
        ':inflation_adjustment' => $inflation_adjustment,
        ':total_area' => $total_area,
        ':buildings_included' => $buildings_included,
        ':start_date_services' => $start_date_services,
        ':site_observation' => $site_observation,
        ':additional_comments' => $additional_comments,
        ':email_info_sent' => $email_info_sent,
        ':scope_of_work' => $scope_json,
        ':photos' => $photos_json
    ]);

    $request_id = $pdo->lastInsertId();

} catch (Exception $e) {
    // Log error but continue with email sending
    error_log("Database save error: " . $e->getMessage());
    $request_id = null;
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

// Save PDF
$pdf_filename = "RequestForm_" . date('Ymd_His') . ".pdf";
$pdf_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $pdf_filename;
file_put_contents($pdf_path, $dompdf->output());


// ==============================
// SEND EMAIL
// ==============================

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host       = $mail_config['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mail_config['smtp_username'];
    $mail->Password   = $mail_config['smtp_password'];
    $mail->SMTPSecure = $mail_config['smtp_encryption'];
    $mail->Port       = $mail_config['smtp_port'];
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($mail_config['from_email'], $mail_config['from_name']);
    $mail->addAddress($mail_config['to_email'], $mail_config['to_name']);

    $mail->isHTML(true);
    $mail->Subject = 'New Contract Request Form - ' . htmlspecialchars($company_name);

    $mail->Body = "
    <h3>A new request form has been submitted.</h3>
    <p>Company: <strong>{$company_name}</strong></p>
    <p>Client: {$client_name}</p>
    <p>Email: {$email}</p>
    <p>PDF is attached.</p>
    ";

    $mail->addAttachment($pdf_path, $pdf_filename);

    // ADJUNTAR FOTOS AL CORREO
    if (!empty($uploaded_photos)) {
        foreach ($uploaded_photos as $photo) {
            $mail->addAttachment($photo);
        }
    }

    $mail->send();
    $email_status = 'success';
    $email_message = 'Form submitted and email sent successfully!';

} catch (Exception $e) {
    $email_status = 'error';
    $email_message = "Error sending form: {$mail->ErrorInfo}";
}

if (file_exists($pdf_path)) unlink($pdf_path);

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