<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

/**
 * enviar_correo.php (Versión con envío por correo)
 */

require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load email configuration
$mail_config = require_once '../mail_config.php';

// ==============================
// Capturar datos del nuevo formulario
// ==============================
$job_number            = $_POST['Job_Number'] ?? '';
$seller           = $_POST['Seller'] ?? '';
$order_paid            = $_POST['Order_Paid'] ?? '';
$bill_rate             = $_POST['Bill_Rate'] ?? '';
$operating_cost        = $_POST['Operating_Cost'] ?? '';
$supplies              = $_POST['Supplies'] ?? '';
$fixed_expenses        = $_POST['Fixed_Expenses'] ?? '';
$net_utility           = $_POST['Net_Utility'] ?? '';
$commission_percentage = $_POST['Commission_Percentage'] ?? '';
$commission_amount     = $_POST['Commission_Amount'] ?? '';

$image_path = __DIR__ . '/Images/Facility.png';
if (file_exists($image_path)) {
    $image_data = base64_encode(file_get_contents($image_path));
    $image_src = 'data:image/png;base64,' . $image_data;
} else {
    $image_src = '';
}

// ==============================
// Generar contenido HTML del PDF
// ==============================
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; background-color:#fff; color:#333; }
  h1 { text-align:center; color:#a30000; }
  .section { margin-top:20px; border-left:4px solid #a30000; padding-left:10px; }
  .section h2 { color:#a30000; font-size:18px; margin-bottom:10px; }
  .field { margin-bottom:6px; }
  .label { font-weight:bold; display:inline-block; min-width:220px; }
</style>
</head>

<body style="font-family: Arial, sans-serif;">

<div style="
    width: 90%;
    max-width: 700px;
    margin: 0 auto;
    padding: 40px 40px;
    font-size: 14px;
    line-height: 1.55;
">

    <!-- LOGO -->
    <div style="text-align:center; margin-bottom:15px;">
    <?php if ($image_src): ?>
        <img src="<?= $image_src ?>" style="width:240px;">
    <?php endif; ?>
    </div>

    <!-- TITULO -->
    <h1 style="text-align:center; color:#a30000; margin:5px 0 0 0; font-size:26px;">
        Commission Authorization Form
    </h1>

    <!-- FECHA -->
    <div style="text-align:center; font-size:13px; color:#555; margin-bottom:30px;">
        Date: <?= date('m/d/Y') ?>
    </div>

    <!-- INTRO -->
    <p style="text-align:justify;">
        This document outlines the commission corresponding to the work performed.
        The information entered in this form will be written manually by the responsible user and must match exactly the details of the assigned Work Order.
        <br><br>
        Any discrepancy between this document and the official Work Order may delay the processing and authorization of the commission.
    </p>

    <!-- SUMMARY -->
    <h2 style="color:#a30000; margin-top:25px;">Commission Summary</h2>

    <table style="width:100%; font-size:14px; line-height:1.6;">
        <tr><td><strong>Job / Work Order Number:</strong></td><td><?= htmlspecialchars($job_number) ?></td></tr>
        <tr><td><strong>seller:</strong></td><td><?= htmlspecialchars($seller) ?></td></tr>
        <tr><td><strong>Order Paid:</strong></td><td><?= htmlspecialchars($order_paid) ?></td></tr>
        <tr><td><strong>Bill Rate:</strong></td><td>$<?= htmlspecialchars($bill_rate) ?></td></tr>
        <tr><td><strong>Operating Cost:</strong></td><td>$<?= htmlspecialchars($operating_cost) ?></td></tr>
        <tr><td><strong>Supplies:</strong></td><td>$<?= htmlspecialchars($supplies) ?></td></tr>
        <tr><td><strong>Operational & Fixed Expenses:</strong></td><td>$<?= htmlspecialchars($fixed_expenses) ?></td></tr>
        <tr><td><strong>Net Utility:</strong></td><td>$<?= htmlspecialchars($net_utility) ?></td></tr>
        <tr><td><strong>Commission Percentage:</strong></td><td><?= htmlspecialchars($commission_percentage) ?>%</td></tr>
        <tr><td><strong>Total Commission:</strong></td><td>$<?= htmlspecialchars($commission_amount) ?></td></tr>
    </table>

    <!-- NOTES -->
    <h2 style="color:#a30000; margin-top:30px;">Notes</h2>
    <p style="text-align:justify;">
        The commission will only be processed once all information has been verified and approved by the appropriate department.
        Any missing, incorrect, or inconsistent information may delay processing.
    </p>

    <!-- POLICY -->
    <h2 style="color:#a30000; margin-top:30px;">Commission Policy</h2>
    <ul style="margin-top:5px;">
        <li>The commission applies only to the Work Order listed on this form.</li>
        <li>The Work Order must be paid and validated before any commission can be issued.</li>
        <li>Commission percentages are capped according to internal company policy.</li>
        <li>Any fraudulent or incorrect reporting may result in disciplinary action.</li>
    </ul>

    <!-- EXTRA CLAUSES -->
    <h2 style="color:#a30000; margin-top:30px;">Additional Clauses</h2>
    <ul>
        <li>All submitted information must be accurate and verifiable.</li>
        <li>The company reserves the right to adjust or deny commission if discrepancies are found.</li>
        <li>This document must match the Work Order records in the system.</li>
    </ul>

    <!-- SIGNATURE -->
    <div style="margin-top:50px; font-size:14px;">
        <br><br>
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
// Generar PDF con DOMPDF
// ==============================
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Save PDF to temporary file
$pdf_filename = "CommissionForm_" . date('Ymd_His') . ".pdf";
$pdf_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $pdf_filename;
file_put_contents($pdf_path, $dompdf->output());

// ==============================
// Send Email via Brevo (PHPMailer)
// ==============================
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = $mail_config['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mail_config['smtp_username'];
    $mail->Password   = $mail_config['smtp_password'];
    $mail->SMTPSecure = $mail_config['smtp_encryption'];
    $mail->Port       = $mail_config['smtp_port'];
    $mail->CharSet    = 'UTF-8';

    // Recipients
    $mail->setFrom($mail_config['from_email'], $mail_config['from_name']);
    $mail->addAddress($mail_config['to_email'], $mail_config['to_name']);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Commission Form - Job #' . htmlspecialchars($job_number);

    $email_body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background: #a30000; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .info-table td { padding: 8px; border-bottom: 1px solid #ddd; }
            .info-table td:first-child { font-weight: bold; width: 200px; }
            .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>New Commission Form Submitted</h2>
        </div>
        <div class='content'>
            <p>A new commission form has been submitted. Please find the details below:</p>

            <table class='info-table'>
                <tr><td>Job Number:</td><td>" . htmlspecialchars($job_number) . "</td></tr>
                <tr><td>seller:</td><td>" . htmlspecialchars($seller) . "</td></tr>
                <tr><td>Order Paid:</td><td>" . htmlspecialchars($order_paid) . "</td></tr>
                <tr><td>Bill Rate:</td><td>$" . htmlspecialchars($bill_rate) . "</td></tr>
                <tr><td>Operating Cost:</td><td>$" . htmlspecialchars($operating_cost) . "</td></tr>
                <tr><td>Supplies:</td><td>$" . htmlspecialchars($supplies) . "</td></tr>
                <tr><td>Fixed Expenses:</td><td>$" . htmlspecialchars($fixed_expenses) . "</td></tr>
                <tr><td>Net Utility:</td><td>$" . htmlspecialchars($net_utility) . "</td></tr>
                <tr><td>Commission %:</td><td>" . htmlspecialchars($commission_percentage) . "%</td></tr>
                <tr><td>Commission Amount:</td><td>$" . htmlspecialchars($commission_amount) . "</td></tr>
            </table>

            <p>The complete PDF document is attached to this email.</p>
        </div>
        <div class='footer'>
            <p>Submitted on " . date('F j, Y, g:i a') . "</p>
            <p>Sales Management System</p>
        </div>
    </body>
    </html>
    ";

    $mail->Body = $email_body;
    $mail->AltBody = "New Commission Form - Job #$job_number\n\n" .
                     "seller: $seller\n" .
                     "Commission Amount: $$commission_amount\n\n" .
                     "Please see attached PDF for complete details.";

    // Attach PDF
    $mail->addAttachment($pdf_path, $pdf_filename);

    // Send email
    $mail->send();
    $email_status = 'success';
    $email_message = 'Email sent successfully!';

} catch (Exception $e) {
    $email_status = 'error';
    $email_message = "Email could not be sent. Error: {$mail->ErrorInfo}";
}

// Clean up temporary PDF file
if (file_exists($pdf_path)) {
    unlink($pdf_path);
}

// ==============================
// Show confirmation page
// ==============================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Submitted</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #001f54 0%, #003d82 100%);
            font-family: 'Inter', sans-serif;
            padding: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .confirmation-box {
            background: white;
            padding: 50px 60px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        h1 {
            color: #333;
            margin-bottom: 15px;
            font-size: 2rem;
        }
        p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #001f54 0%, #003d82 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 31, 84, 0.4);
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(0, 31, 84, 0.5);
        }
        .details {
            background: #f9fafb;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }
        .details h3 {
            color: #a30000;
            margin-top: 0;
        }
        .detail-item {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-item:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #374151;
            display: inline-block;
            min-width: 180px;
        }
    </style>
</head>
<body>
    <div class="confirmation-box">
        <div class="icon <?php echo $email_status; ?>">
            <?php echo $email_status === 'success' ? '✓' : '✗'; ?>
        </div>

        <h1><?php echo $email_status === 'success' ? 'Form Submitted!' : 'Submission Error'; ?></h1>

        <p><?php echo $email_message; ?></p>

        <?php if ($email_status === 'success'): ?>
        <div class="details">
            <h3>Submission Details</h3>
            <div class="detail-item">
                <span class="detail-label">Job Number:</span>
                <span><?php echo htmlspecialchars($job_number); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">seller:</span>
                <span><?php echo htmlspecialchars($seller); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Commission Amount:</span>
                <span>$<?php echo htmlspecialchars($commission_amount); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Sent to:</span>
                <span><?php echo htmlspecialchars($mail_config['to_email']); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <a href="index.php" class="btn">← Back to Form</a>
    </div>
</body>
</html>