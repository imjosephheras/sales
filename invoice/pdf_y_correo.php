<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

// ===============================
// CAPTURA DE DATOS DEL FORM
// ===============================

// ➤ Nuevos campos
$provider_name = $_POST['provider_name'] ?? '';
$invoice_month = $_POST['invoice_month'] ?? '';
$invoice_year  = $_POST['invoice_year'] ?? '';

// ➤ Items
$item_codes   = $_POST['item_code']   ?? [];
$descriptions = $_POST['description'] ?? [];
$pack_cases   = $_POST['packcase']    ?? [];
$qtys         = $_POST['qty']         ?? [];
$unit_prices  = $_POST['unit_price']  ?? [];
$totals       = $_POST['total']       ?? [];

// ➤ Totales
$invoice_total       = $_POST['invoice_total']       ?? 0;
$invoice_tax         = $_POST['invoice_tax']         ?? 0;
$invoice_grand_total = $_POST['invoice_grand_total'] ?? 0;


// ===============================
// LOGO (BASE64) — AÑADIDO AQUÍ
// ===============================
$image_path = __DIR__ . '/Images/Facility.png';

if (file_exists($image_path)) {
    $image_data = base64_encode(file_get_contents($image_path));
    $image_src  = 'data:image/png;base64,' . $image_data;
} else {
    $image_src  = '';
}


// ===============================
// HTML PARA EL PDF
// ===============================
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial; padding:20px; }
h1 { text-align:center; color:#a30000; margin-bottom:5px; }
table { width:100%; border-collapse: collapse; margin-top:20px; }
th { background:#a30000; color:white; padding:8px; }
td { border:1px solid #ccc; padding:6px; text-align:center; }
.total-box { width:300px; float:right; margin-top:25px; border:1px solid #ccc; }
.total-box table { width:100%; }
.total-box td { padding:10px; }
.total-title { background:#a30000; color:white; font-weight:bold; text-align:center; }
</style>
</head>

<body>

<!-- LOGO -->
<div style="text-align:center; margin-bottom:15px;">
    <?php if ($image_src): ?>
        <img src="<?= $image_src ?>" style="width:150px; height:auto;">
    <?php endif; ?>
</div>

<h1>Invoice Summary</h1>

<p><strong>Provider:</strong> <?= htmlspecialchars($provider_name) ?></p>


<!-- PURCHASE ORDER TEXT -->
<p style="margin-top:25px; line-height:1.6;">
  These are the materials we will need for the month of 
  <strong><?= htmlspecialchars($invoice_month) ?> <?= htmlspecialchars($invoice_year) ?></strong>.
</p>

<p style="line-height:1.6;">
  The quantities and items listed below reflect our material needs based on the most recent pricing you provided.
</p>

<p style="line-height:1.6;">
  Please generate the corresponding invoice for this period using the agreed rates.
  If any item is unavailable, out of stock, has a price change, or requires substitution, 
  please inform us before processing the order.
</p>

<p style="line-height:1.6; margin-bottom:25px;">
  We appreciate your prompt attention to this matter.
</p>


<!-- TABLE OF ITEMS -->
<table>
    <thead>
        <tr>
            <th>Item Code</th>
            <th>Description</th>
            <th>Pack/Case</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>

    <?php for ($i = 0; $i < count($item_codes); $i++): ?>
        <?php if (floatval($qtys[$i]) <= 0) continue; ?>
        <tr>
            <td><?= htmlspecialchars($item_codes[$i]) ?></td>
            <td><?= htmlspecialchars($descriptions[$i]) ?></td>
            <td><?= htmlspecialchars($pack_cases[$i]) ?></td>
            <td><?= htmlspecialchars($qtys[$i]) ?></td>
            <td>$<?= number_format($unit_prices[$i], 2) ?></td>
            <td>$<?= number_format($totals[$i], 2) ?></td>
        </tr>
    <?php endfor; ?>

    </tbody>
</table>

<!-- TOTALS BOX -->
<div class="total-box">
<table>
    <tr><td colspan="2" class="total-title">TOTAL</td></tr>
    <tr>
        <td><strong>Subtotal</strong></td>
        <td>$<?= number_format($invoice_total, 2) ?></td>
    </tr>
    <tr>
        <td><strong>Taxes (8.25%)</strong></td>
        <td>$<?= number_format($invoice_tax, 2) ?></td>
    </tr>
    <tr>
        <td><strong>GRAND TOTAL</strong></td>
        <td><strong>$<?= number_format($invoice_grand_total, 2) ?></strong></td>
    </tr>
</table>
</div>

</body>
</html>

<?php
$html = ob_get_clean();


// ===============================
// GENERAR PDF
// ===============================
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4');
$dompdf->render();

$pdf_filename = "Invoice_" . date('Ymd_His') . ".pdf";
$pdf_path = sys_get_temp_dir() . "/" . $pdf_filename;

file_put_contents($pdf_path, $dompdf->output());


// ===============================
// ENVIAR CORREO
// ===============================
$mail_config = require '../mail_config.php';
$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host       = $mail_config['smtp_host'];
$mail->SMTPAuth   = true;
$mail->Username   = $mail_config['smtp_username'];
$mail->Password   = $mail_config['smtp_password'];
$mail->SMTPSecure = $mail_config['smtp_encryption'];
$mail->Port       = $mail_config['smtp_port'];

$mail->setFrom($mail_config['from_email'], "Invoice System");
$mail->addAddress($mail_config['to_email']);

$mail->Subject = "New Invoice Submitted";
$mail->isHTML(true);

$mail->Body = "
<h2>New Invoice Submitted</h2>
<p><strong>Provider:</strong> {$provider_name}</p>
<p><strong>Period:</strong> {$invoice_month} {$invoice_year}</p>
<p><strong>Grand Total:</strong> $" . number_format($invoice_grand_total, 2) . "</p>
<p>The invoice PDF is attached.</p>
";

$mail->addAttachment($pdf_path, $pdf_filename);

$mail->send();

unlink($pdf_path);


// ===============================
// CONFIRMATION PAGE
// ===============================
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Invoice Sent</title>
<style>
body { font-family:Arial; text-align:center; padding:50px; background:#f2f2f2; }
.box { display:inline-block; background:white; padding:40px; border-radius:15px;
       box-shadow:0 0 20px rgba(0,0,0,0.2); }
h1 { color:#0a0; }
.btn { background:#a30000; padding:12px 25px; color:white; text-decoration:none; border-radius:8px; }
</style>
</head>
<body>

<div class="box">
    <h1>Invoice Sent Successfully</h1>
    <p>Your invoice has been emailed.</p>
    <a href="index.php" class="btn">Back to Invoice</a>
</div>

</body>
</html>
