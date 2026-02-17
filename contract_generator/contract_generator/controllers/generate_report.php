<?php
/**
 * GENERATE REPORT CONTROLLER
 * Generates printable reports for different service types.
 * Reads from forms + contract_items (single source of truth).
 */

require_once '../config/db_config.php';

// Get parameters
$reportType = $_GET['type'] ?? '';
$formId = $_GET['id'] ?? null;

if (!$formId) {
    die('Error: Form ID is required');
}

if (!in_array($reportType, ['hood_vent', 'kitchen', 'janitorial', 'staff', 'summary'])) {
    die('Error: Invalid report type');
}

try {
    // Get form data
    $stmt = $pdo->prepare("SELECT * FROM forms WHERE form_id = :id");
    $stmt->execute([':id' => $formId]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        die('Error: Form not found');
    }

    // Map form columns to expected field names
    $request['Company_Name'] = $request['company_name'];
    $request['client_name'] = $request['client_name'];
    $request['Company_Address'] = $request['address'];
    $request['Number_Phone'] = $request['phone'];
    $request['Email'] = $request['email'];
    $request['Seller'] = $request['seller'];
    $request['Invoice_Frequency'] = $request['invoice_frequency'];
    $request['Contract_Duration'] = $request['contract_duration'];
    $request['Site_Observation'] = $request['site_observation'];
    $request['Additional_Comments'] = $request['additional_comments'];
    $request['docnum'] = $request['docnum'] ?? $request['Order_Nomenclature'];
    $request['PriceInput'] = $request['grand_total'];

    // Get contract items split by category
    $stmtItems = $pdo->prepare("SELECT * FROM contract_items WHERE form_id = ? ORDER BY service_category, service_number");
    $stmtItems->execute([$formId]);
    $allItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    $janitorialServices = [];
    $kitchenServices = [];
    $hoodVentServices = [];
    foreach ($allItems as $item) {
        switch ($item['service_category']) {
            case 'janitorial': $janitorialServices[] = $item; break;
            case 'kitchen': $kitchenServices[] = $item; break;
            case 'hood_vent': $hoodVentServices[] = $item; break;
        }
    }

    // Get scope of work
    $stmtScope = $pdo->prepare("SELECT task_name FROM scope_of_work WHERE form_id = ?");
    $stmtScope->execute([$formId]);
    $scopeOfWork = $stmtScope->fetchAll(PDO::FETCH_COLUMN);

    // Generate report based on type
    $reportTitle = '';
    $reportData = [];

    switch ($reportType) {
        case 'hood_vent':
            $reportTitle = 'Hood Vent Service Report';
            $reportData = $hoodVentServices;
            break;
        case 'kitchen':
            $reportTitle = 'Kitchen Cleaning Service Report';
            $reportData = $kitchenServices;
            break;
        case 'janitorial':
            $reportTitle = 'Janitorial Service Report';
            $reportData = $janitorialServices;
            break;
        case 'staff':
            $reportTitle = 'Staff Requirements Report';
            $reportData = [];
            break;
        case 'summary':
            $reportTitle = 'Service Summary Report';
            break;
    }

} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}

// Format currency
function formatCurrency($value) {
    if (empty($value)) return '$0.00';
    $num = floatval(preg_replace('/[^0-9.-]/', '', $value));
    return '$' . number_format($num, 2);
}

// Format date
function formatDate($date) {
    if (empty($date)) return 'N/A';
    return date('F d, Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($reportTitle) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 12px; line-height: 1.5; color: #333; background: #fff; padding: 20px; }
        .report-container { max-width: 800px; margin: 0 auto; }
        .report-header { border-bottom: 3px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .report-header h1 { font-size: 24px; color: #1e40af; margin-bottom: 5px; }
        .report-meta { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; font-size: 11px; color: #666; }
        .report-meta span { background: #f3f4f6; padding: 3px 8px; border-radius: 4px; }
        .company-info { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
        .company-info h2 { font-size: 16px; color: #1e40af; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; }
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .info-item { display: flex; flex-direction: column; }
        .info-label { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-value { font-size: 13px; font-weight: 500; color: #1e293b; }
        .service-section { margin-bottom: 25px; }
        .service-section h3 { font-size: 14px; color: #1e40af; background: #eff6ff; padding: 8px 12px; border-radius: 6px 6px 0 0; border: 1px solid #bfdbfe; border-bottom: none; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th { background: #f1f5f9; color: #475569; font-weight: 600; text-align: left; padding: 10px 8px; border: 1px solid #e2e8f0; }
        td { padding: 8px; border: 1px solid #e2e8f0; vertical-align: top; }
        tr:nth-child(even) { background: #f8fafc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-row { background: #1e40af !important; color: white; font-weight: 600; }
        .totals-row td { border-color: #1e40af; }
        .summary-box { background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 15px; margin-top: 20px; }
        .summary-box h4 { color: #92400e; margin-bottom: 10px; }
        .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .summary-item { text-align: center; }
        .summary-value { font-size: 20px; font-weight: 700; color: #92400e; }
        .summary-label { font-size: 10px; color: #78716c; text-transform: uppercase; }
        .notes-section { margin-top: 20px; padding: 15px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; }
        .notes-section h4 { color: #475569; font-size: 12px; margin-bottom: 8px; }
        .notes-content { font-size: 11px; color: #64748b; white-space: pre-wrap; }
        .scope-list { list-style: none; padding: 0; margin: 0; }
        .scope-list li { padding: 6px 0 6px 20px; position: relative; border-bottom: 1px solid #f1f5f9; }
        .scope-list li:before { content: "✓"; position: absolute; left: 0; color: #22c55e; font-weight: bold; }
        @media print { body { padding: 0; } .report-container { max-width: 100%; } .no-print { display: none !important; } }
        .print-btn { position: fixed; top: 20px; right: 20px; background: #2563eb; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .print-btn:hover { background: #1d4ed8; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .status-draft { background: #fef3c7; color: #92400e; }
        .status-pending { background: #dbeafe; color: #1e40af; }
        .status-ready { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">Print Report</button>

    <div class="report-container">
        <div class="report-header">
            <h1><?= htmlspecialchars($reportTitle) ?></h1>
            <div class="report-meta">
                <span><strong>Order:</strong> <?= htmlspecialchars($request['docnum'] ?? 'N/A') ?></span>
                <span><strong>Document Date:</strong> <?= formatDate($request['Document_Date']) ?></span>
                <span><strong>Work Date:</strong> <?= formatDate($request['Work_Date']) ?></span>
                <span class="status-badge status-<?= strtolower($request['status'] ?? 'pending') ?>">
                    <?= ucfirst($request['status'] ?? 'Pending') ?>
                </span>
            </div>
        </div>

        <div class="company-info">
            <h2>Client Information</h2>
            <div class="info-grid">
                <div class="info-item"><span class="info-label">Company Name</span><span class="info-value"><?= htmlspecialchars($request['Company_Name'] ?? 'N/A') ?></span></div>
                <div class="info-item"><span class="info-label">Contact Person</span><span class="info-value"><?= htmlspecialchars($request['client_name'] ?? 'N/A') ?></span></div>
                <div class="info-item"><span class="info-label">Address</span><span class="info-value"><?= htmlspecialchars($request['Company_Address'] ?? 'N/A') ?></span></div>
                <div class="info-item"><span class="info-label">Phone</span><span class="info-value"><?= htmlspecialchars($request['Number_Phone'] ?? 'N/A') ?></span></div>
                <div class="info-item"><span class="info-label">Email</span><span class="info-value"><?= htmlspecialchars($request['Email'] ?? 'N/A') ?></span></div>
                <div class="info-item"><span class="info-label">Seller</span><span class="info-value"><?= htmlspecialchars($request['Seller'] ?? 'N/A') ?></span></div>
            </div>
        </div>

        <?php if (in_array($reportType, ['hood_vent', 'kitchen', 'janitorial'])): ?>
        <div class="service-section">
            <h3><?= htmlspecialchars($reportTitle) ?></h3>
            <?php if (!empty($reportData)): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Service Type</th>
                        <th>Time</th>
                        <th>Frequency</th>
                        <th>Description</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    foreach ($reportData as $i => $service):
                        $subtotal = floatval($service['subtotal'] ?? 0);
                        $total += $subtotal;
                    ?>
                    <tr>
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($service['service_type'] ?? '') ?></td>
                        <td><?= htmlspecialchars($service['service_time'] ?? '') ?></td>
                        <td><?= htmlspecialchars($service['frequency'] ?? '') ?></td>
                        <td><?= htmlspecialchars($service['description'] ?? '') ?></td>
                        <td class="text-right"><?= formatCurrency($subtotal) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="totals-row">
                        <td colspan="5" class="text-right"><strong>TOTAL</strong></td>
                        <td class="text-right"><?= formatCurrency($total) ?></td>
                    </tr>
                </tbody>
            </table>
            <?php else: ?>
            <p style="padding: 20px; text-align: center; color: #666;">No services registered</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="summary-box">
            <h4>Contract Summary</h4>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-value"><?= htmlspecialchars($request['Invoice_Frequency'] ?? 'N/A') ?></div>
                    <div class="summary-label">Invoice Frequency</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value"><?= htmlspecialchars($request['Contract_Duration'] ?? 'N/A') ?></div>
                    <div class="summary-label">Contract Duration</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value"><?= formatCurrency($request['grand_total'] ?? 0) ?></div>
                    <div class="summary-label">Total Cost</div>
                </div>
            </div>
        </div>

        <?php if (!empty($scopeOfWork)): ?>
        <div class="notes-section">
            <h4>Scope of Work</h4>
            <ul class="scope-list">
                <?php foreach ($scopeOfWork as $task): ?>
                <li><?= htmlspecialchars($task) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($request['Site_Observation'])): ?>
        <div class="notes-section">
            <h4>Site Observations</h4>
            <div class="notes-content"><?= htmlspecialchars($request['Site_Observation']) ?></div>
        </div>
        <?php endif; ?>

        <?php if (!empty($request['Additional_Comments'])): ?>
        <div class="notes-section">
            <h4>Additional Comments</h4>
            <div class="notes-content"><?= htmlspecialchars($request['Additional_Comments']) ?></div>
        </div>
        <?php endif; ?>

        <div style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #94a3b8; text-align: center;">
            Generated on <?= date('F d, Y \a\t g:i A') ?> | HJ Sales Management System
        </div>
    </div>
</body>
</html>
