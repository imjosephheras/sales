<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ==============================
// CONFIG
// ==============================
$mail_config = require_once '../mail_config.php';

// ==============================
// CAPTURAR DATOS (FORMULARIO NUEVO)
// ==============================
$job_number        = $_POST['Job_Number'] ?? '';
$lead_source       = $_POST['Lead_Source'] ?? '';
$lead_closer       = $_POST['Lead_Closer'] ?? '';
$order_paid        = $_POST['Order_Paid'] ?? '';
$net_utility       = $_POST['Net_Utility'] ?? '';
$commission_pct    = $_POST['Closer_Commission_Pct'] ?? '';
$commission_amount = $_POST['Closer_Commission_Amt'] ?? '';

// ==============================
// LOGO
// ==============================
$image_path = __DIR__ . '/Images/Facility.png';
$image_src = file_exists($image_path)
    ? 'data:image/png;base64,' . base64_encode(file_get_contents($image_path))
    : '';

// ==============================
// PDF HTML
// ==============================
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; color:#333; }
h1 { text-align:center; color:#a30000; }
table { width:100%; font-size:14px; line-height:1.6; }
td { padding:6px 0; }
td:first-child { font-weight:bold; width:240px; }
</style>
</head>

<body>

<div style="max-width:700px; margin:0 auto; padding:40px;">

<?php if ($image_src): ?>
<div style="text-align:center; margin-bottom:15px;">
    <img src="<?= $image_src ?>" style="width:220px;">
</div>
<?php endif; ?>

<h1>Commission Authorization Form</h1>

<div style="text-align:center; font-size:13px; color:#555; margin-bottom:25px;">
    Date: <?= date('m/d/Y') ?>
</div>

<p>
This document records the commission related to the referenced Work Order.
All values were entered manually and must match the official records.
</p>

<h2 style="color:#a30000;">Commission Summary</h2>

<table>
<tr><td>Job / Work Order Number:</td><td><?= htmlspecialchars($job_number) ?></td></tr>
<tr><td>Lead Source:</td><td><?= htmlspecialchars($lead_source) ?></td></tr>
<tr><td>Sales Closer:</td><td><?= htmlspecialchars($lead_closer) ?></td></tr>
<tr><td>Order Paid:</td><td><?= htmlspecialchars($order_paid) ?></td></tr>
<tr><td>Net Utility:</td><td>$<?= number_format((float)$net_utility, 2) ?></td></tr>
<tr><td>Commission Percentage:</td><td><?= htmlspecialchars($commission_pct) ?>%</td></tr>
<tr><td>Total Commission:</td><td>$<?= number_format((float)$commission_amount, 2) ?></td></tr>
</table>

<h2 style="color:#a30000; margin-top:30px;">Notes</h2>
<p>
This commission will be processed only after validation and approval.
Any discrepancy may delay or void payment.
</p>

<h2 style="color:#a30000; margin-top:30px;">Authorization</h2>

<div style="margin-top:40px;">
______________________________________<br>
Authorized by: Rafael Perez<br>
Position: Senior Vice President<br>
Date: _______________________________
</div>

</div>

</body>
</html>
<?php
$html = ob_get_clean();

// ==============================
// GENERAR PDF
// ==============================
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$pdf_filename = "CommissionForm_" . date('Ymd_His') . ".pdf";
$pdf_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $pdf_filename;
file_put_contents($pdf_path, $dompdf->output());

// ==============================
// EMAIL
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
    $mail->Subject = "New Commission Form - Job #{$job_number}";

    $mail->Body = "
        <p>A new commission form has been submitted.</p>
        <ul>
            <li><strong>Job Number:</strong> {$job_number}</li>
            <li><strong>Sales Closer:</strong> {$lead_closer}</li>
            <li><strong>Net Utility:</strong> $" . number_format((float)$net_utility,2) . "</li>
            <li><strong>Commission:</strong> $" . number_format((float)$commission_amount,2) . "</li>
        </ul>
        <p>The PDF is attached.</p>
    ";

    $mail->AltBody =
        "Job: $job_number\n" .
        "Sales Closer: $lead_closer\n" .
        "Commission: $$commission_amount\n";

    $mail->addAttachment($pdf_path, $pdf_filename);
    $mail->send();

    $email_status = 'success';
    $email_message = 'Email sent successfully.';

} catch (Exception $e) {
    $email_status = 'error';
    $email_message = $mail->ErrorInfo;
}

if (file_exists($pdf_path)) unlink($pdf_path);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Submitted</title>
</head>
<body style="text-align:center; font-family:Arial;">
<h1><?= $email_status === 'success' ? 'Form Submitted' : 'Error' ?></h1>
<p><?= htmlspecialchars($email_message) ?></p>
<a href="index.php">← Back</a>
</body>
</html>
