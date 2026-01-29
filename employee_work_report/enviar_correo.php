<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

// =====================================
//  LOAD DEPENDENCIES
// =====================================
require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;

// Email config
$mail_config = require '../mail_config.php';

// =====================================
//  CAPTURAR DATOS DEL FORM
// =====================================
$jwo_number = $_POST['JWO_Number'] ?? 'N/A';
$before = $_FILES['before'] ?? null;
$after  = $_FILES['after']  ?? null;

// =====================================
//  CREAR RUTA DE UPLOADS
// =====================================
$upload_dir = rtrim(__DIR__, "/\\") . DIRECTORY_SEPARATOR . "Uploads" . DIRECTORY_SEPARATOR;

if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

// =====================================
//  FUNCION PARA COMPRIMIR IMAGENES
// =====================================
function compressImage($source, $destination, $quality = 55) {
    $info = getimagesize($source);

    switch ($info['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            imagepalettetotruecolor($image);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }

    imagejpeg($image, $destination, $quality);
    return true;
}

// =====================================
//  GUARDAR Y COMPRIMIR FOTOS
// =====================================
function processPhotos($files, $upload_dir) {
    $saved = [];
    if (!$files || !isset($files['tmp_name'])) return $saved;

    for ($i = 0; $i < count($files['tmp_name']); $i++) {

        if ($files['error'][$i] === UPLOAD_ERR_OK) {

            $tmp  = $files['tmp_name'][$i];
            $original = $upload_dir . "orig_" . basename($files['name'][$i]);
            move_uploaded_file($tmp, $original);

            // Crear versión comprimida
            $compressed = $upload_dir . "cmp_" . time() . "_" . rand(1000,9999) . ".jpg";
            compressImage($original, $compressed, 55);

            // Agregar solo la comprimida
            $saved[] = $compressed;

            // Borrar original para ahorrar espacio
            unlink($original);
        }
    }
    return $saved;
}

$before_photos = processPhotos($before, $upload_dir);
$after_photos  = processPhotos($after,  $upload_dir);

// =====================================
//  LOGO BASE64 PARA PDF
// =====================================
$logo_path = __DIR__ . '/Images/Facility.png';
$logo_base64 = '';
if (file_exists($logo_path)) {
    $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
}

// =====================================
//  GENERAR HTML PARA PDF
// =====================================
ob_start();
?>
<html>
<head>
<meta charset="UTF-8">

<style>
body { font-family: Arial, sans-serif; }

.logo-container {
    text-align: center;
    margin-bottom: 15px;
}
.logo-container img {
    max-width: 200px;
    height: auto;
}

h1 { text-align:center; color:#a30000; margin-bottom:5px; margin-top: 10px; }
h3 { text-align:center; margin-top:5px; }
h2 {
  color:#a30000;
  margin-top:25px;
  border-bottom:1px solid #ddd;
  padding-bottom:5px;
}

.template-box {
    padding: 12px;
    margin-top:20px;
    font-size: 13px;
    line-height: 1.5;
}

/* TABLA DE FOTOS */
.photo-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.photo-table th {
    background: #a30000;
    color: white;
    padding: 10px;
    text-align: center;
    font-size: 14px;
    width: 50%;
}

.photo-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
    vertical-align: top;
    width: 50%;
}

.photo-table img {
    width: auto;
    max-width: 140px;
    height: 200px;
    object-fit: cover;
}

.photo-row {
    page-break-inside: avoid;
}
</style>

</head>

<body>

<!-- LOGO -->
<div class="logo-container">
    <?php if ($logo_base64): ?>
        <img src="<?= $logo_base64 ?>" alt="Prime Facility Services Logo">
    <?php endif; ?>
</div>

<h1>Employee Work Report</h1>
<h3>JWO #: <?= htmlspecialchars($jwo_number) ?></h3>

<div class="template-box">
<b>This document contains photographic evidence captured by our field team in connection with the assigned Job Work Order (JWO).</b><br><br>
The images illustrate the condition of the service area both <b>before and after</b> the completion of the work.<br><br>
This report has been prepared to support transparency, quality assurance, and accurate documentation of the services performed.
</div>

<!-- PHOTO COMPARISON TABLE -->
<h2>Photo Evidence</h2>
<table class="photo-table">
    <thead>
        <tr>
            <th>Before</th>
            <th>After</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $max_photos = max(count($before_photos), count($after_photos));
    for ($i = 0; $i < $max_photos; $i++):
    ?>
        <tr class="photo-row">
            <td>
                <?php if (isset($before_photos[$i])):
                    $mime = mime_content_type($before_photos[$i]);
                    $base64 = base64_encode(file_get_contents($before_photos[$i])); ?>
                    <img src="data:<?= $mime ?>;base64,<?= $base64 ?>">
                <?php endif; ?>
            </td>
            <td>
                <?php if (isset($after_photos[$i])):
                    $mime = mime_content_type($after_photos[$i]);
                    $base64 = base64_encode(file_get_contents($after_photos[$i])); ?>
                    <img src="data:<?= $mime ?>;base64,<?= $base64 ?>">
                <?php endif; ?>
            </td>
        </tr>
    <?php endfor; ?>
    </tbody>
</table>

</body>
</html>

<?php
$html = ob_get_clean();

// =====================================
//  GENERAR PDF
// =====================================
$options = new Options();
$options->set('isRemoteEnabled', true);
$pdf = new Dompdf($options);

$pdf->set_option('isHtml5ParserEnabled', true);
$pdf->set_option('isPhpEnabled', true);

$pdf->loadHtml($html);
$pdf->setPaper('A4', 'portrait');
$pdf->render();

$pdf_filename = "WorkReport_" . str_replace("/", "_", $jwo_number) . "_" . date("Ymd_His") . ".pdf";
$pdf_path = $upload_dir . $pdf_filename;

file_put_contents($pdf_path, $pdf->output());

// =====================================
//  ENVIAR EMAIL (solo PDF, NO fotos)
// =====================================
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $mail_config['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mail_config['smtp_username'];
    $mail->Password   = $mail_config['smtp_password'];
    $mail->SMTPSecure = $mail_config['smtp_encryption'];
    $mail->Port       = $mail_config['smtp_port'];
    $mail->CharSet    = "UTF-8";

    $mail->setFrom($mail_config['from_email'], $mail_config['from_name']);
    $mail->addAddress($mail_config['to_email']);

    $mail->Subject = "Employee Work Report - JWO $jwo_number";
    $mail->Body    = "Attached is the Employee Work Report.<br><br>JWO: $jwo_number";
    $mail->isHTML(true);

    // SOLO PDF
    $mail->addAttachment($pdf_path);

    $mail->send();

} catch (Exception $e) {
    echo "Email error: " . $mail->ErrorInfo . "<br>";
    echo "Could not access file: $pdf_path";
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Success</title>
<style>
body { background:#f0f0f0; text-align:center; padding:40px; font-family:Arial; }
.box { background:white; padding:30px; border-radius:12px; display:inline-block; max-width:500px; }
.btn {
    display: inline-block;
    padding: 12px 24px;
    margin: 10px 5px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    cursor: pointer;
    border: none;
    font-size: 14px;
}
.btn-print {
    background: #a30000;
    color: white;
}
.btn-print:hover {
    background: #8a0000;
}
.btn-back {
    background: #666;
    color: white;
}
.btn-back:hover {
    background: #555;
}
</style>
</head>
<body>
<div class="box">
<h2 style="color:#2e7d32;">✓ Report Submitted Successfully</h2>
<p>JWO #: <?= htmlspecialchars($jwo_number) ?></p>
<div style="margin-top:20px;">
    <a href="Uploads/<?= htmlspecialchars($pdf_filename) ?>" target="_blank" class="btn btn-print">Print Report</a>
    <a href="index.php" class="btn btn-back">Back</a>
</div>
</div>
</body>
</html>
 