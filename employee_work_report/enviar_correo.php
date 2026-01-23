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
//  GENERAR HTML PARA PDF
// =====================================
ob_start();
?>
<html>
<head>
<meta charset="UTF-8">

<style>
body { font-family: Arial, sans-serif; }

h1 { text-align:center; color:#a30000; margin-bottom:5px; }
h3 { text-align:center; margin-top:5px; }
h2 { 
  color:#a30000; 
  margin-top:25px; 
  border-bottom:1px solid #ddd; 
  padding-bottom:5px; 
}

.template-box {
    background: #f7f7f7;
    padding: 12px;
    margin-top:20px;
    border-left: 4px solid #a30000;
    font-size: 13px;
    line-height: 1.4;
}

/* SECCION CON PAGINACION */
.page-section {
    page-break-inside: avoid;
    page-break-after: auto;
    overflow: hidden;
    width: 100%;
}

/* 2 COLUMNAS COMPATIBLES */
.photo-box {
    width: 45%;
    float: left;
    border: 1px solid #aaa;
    padding: 4px;
    margin-right: 12px;
    margin-bottom: 15px;
    page-break-inside: avoid;
}

/* Imagen comprimida */
.photo-box img {
    width: 100%;
    height: auto;
    max-height: 150px;
    object-fit: contain;
}

.clearfix {
    clear: both;
    width: 100%;
    height: 1px;
}
</style>

</head>

<body>

<h1>Employee Work Report</h1>
<h3>JWO #: <?= htmlspecialchars($jwo_number) ?></h3>

<div class="template-box">
<b>This document includes photographic evidence taken by our field team as part of the assigned Job Work Order (JWO).</b><br><br>
All images represent the condition of the service area <b>before and after</b> work was completed.
This report is generated to ensure transparency, quality assurance, and proper documentation of services rendered.
</div>

<!-- BEFORE -->
<h2>Before Photos</h2>
<div class="page-section">
<?php foreach ($before_photos as $p):
    $mime = mime_content_type($p);
    $base64 = base64_encode(file_get_contents($p)); ?>
    <div class="photo-box">
        <img src="data:<?= $mime ?>;base64,<?= $base64 ?>">
    </div>
<?php endforeach; ?>
<div class="clearfix"></div>
</div>

<!-- AFTER -->
<h2>After Photos</h2>
<div class="page-section">
<?php foreach ($after_photos as $p):
    $mime = mime_content_type($p);
    $base64 = base64_encode(file_get_contents($p)); ?>
    <div class="photo-box">
        <img src="data:<?= $mime ?>;base64,<?= $base64 ?>">
    </div>
<?php endforeach; ?>
<div class="clearfix"></div>
</div>

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
.box { background:white; padding:30px; border-radius:12px; display:inline-block; }
</style>
</head>
<body>
<div class="box">
<h2>✓ Report Submitted Successfully</h2>
<p>JWO #: <?= htmlspecialchars($jwo_number) ?></p>
<a href="index.php">Back</a>
</div>
</body>
</html>
 