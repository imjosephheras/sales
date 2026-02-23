<?php
/**
 * QUOTE TEMPLATE
 * Short quotation document (typically 1 page) with dynamic content from database.
 *
 * Variables available from generate_pdf.php:
 *   $data           - associative array with form fields
 *   $scopeSections  - array of scope sections (title, scope_content)
 *   $allItems       - all contract_items rows (via $janitorialServices, $kitchenServices, $hoodVentServices)
 *
 * Dynamic fields:
 *   logo             -> based on Service_Type (hospitality vs facility)
 *   work_date        -> $data['Work_Date'] or $data['Document_Date']
 *   client_name      -> $data['client_name']
 *   company_name     -> $data['Company_Name']
 *   address          -> $data['Company_Address']
 *   scope_sections   -> from scope_sections table
 *   service_type     -> from contract_items
 *   service_time     -> from contract_items
 *   frequency        -> from contract_items
 *   subtotal         -> from contract_items
 *   seller           -> $data['Seller']
 */

// ============================================================
// DATA PREPARATION
// ============================================================

// Logo
$dept = strtolower(trim($data['Service_Type'] ?? ''));
if (strpos($dept, 'hospitality') !== false) {
    $logo_file = __DIR__ . '/../../Images/phospitality.png';
} else {
    $logo_file = __DIR__ . '/../../Images/pfacility.png';
}
$logo_base64 = '';
if (file_exists($logo_file)) {
    $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_file));
}

// Client data
$company_name    = htmlspecialchars($data['Company_Name'] ?? '');
$company_address = htmlspecialchars($data['Company_Address'] ?? '');
$client_name     = htmlspecialchars($data['client_name'] ?? '');
$seller          = htmlspecialchars($data['Seller'] ?? '');
$requested_service = htmlspecialchars($data['Requested_Service'] ?? 'General Service');

// Date
$work_date_raw = $data['Work_Date'] ?? $data['Document_Date'] ?? null;
if ($work_date_raw) {
    $work_date = date('F d, Y', strtotime($work_date_raw));
} else {
    $work_date = date('F d, Y');
}

// Build service rows from contract_items
$serviceRows = [];
$runningTotal = 0.0;

foreach ([$janitorialServices ?? [], $kitchenServices ?? [], $hoodVentServices ?? []] as $serviceGroup) {
    foreach ($serviceGroup as $svc) {
        $svcSubtotal = floatval($svc['subtotal'] ?? 0);
        $runningTotal += $svcSubtotal;
        $serviceRows[] = [
            'type'     => $svc['service_type'] ?? '',
            'time'     => $svc['service_time'] ?? '',
            'freq'     => $svc['frequency'] ?? '',
            'desc'     => $svc['description'] ?? '',
            'subtotal' => $svcSubtotal,
        ];
    }
}

// Fallback: if no contract_items, use total_cost
if (empty($serviceRows)) {
    $subtotal = (float)($data['total_cost'] ?? $data['PriceInput'] ?? 0);
    $serviceRows[] = [
        'type'     => $requested_service,
        'time'     => '',
        'freq'     => '',
        'desc'     => '',
        'subtotal' => $subtotal,
    ];
} else {
    $subtotal = $runningTotal;
}

// Tax
$tax_rate = 0.0825;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation - <?php echo $company_name; ?></title>
    <style>
        @page {
            margin: 3.5cm 2cm 3.2cm 2cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        /* Header - fixed, repeats on every page */
        .header-wrapper {
            position: fixed;
            top: -3cm;
            left: 0;
            right: 0;
            height: 2.5cm;
            overflow: visible;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px solid #CC0000;
        }

        .header-table td {
            vertical-align: middle;
        }

        .header-logo {
            max-height: 65px;
            width: auto;
        }

        .doc-title {
            color: #CC0000;
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .doc-subtitle {
            font-size: 16pt;
            font-weight: bold;
            color: #000;
        }

        /* Footer - fixed, repeats on every page */
        .footer-wrapper {
            position: fixed;
            bottom: -2.7cm;
            left: 0;
            right: 0;
            height: 2.2cm;
            overflow: visible;
        }

        .footer-top {
            background-color: #A30000;
            color: white;
            text-align: center;
            padding: 3px 10px;
            font-size: 7pt;
        }

        .footer-bottom {
            background-color: #CC0000;
            color: white;
            text-align: center;
            padding: 8px 10px;
            font-size: 8pt;
        }

        .footer-bottom a {
            color: white;
            text-decoration: none;
        }

        /* Date */
        .quote-date {
            font-size: 10pt;
            color: #555;
            margin-bottom: 20px;
        }

        /* Greeting / client block */
        .greeting-block {
            margin-bottom: 18px;
            line-height: 1.6;
        }

        .greeting-block .dear {
            font-size: 10pt;
            color: #000;
        }

        .greeting-block .client-info {
            font-size: 10pt;
            color: #333;
        }

        /* Intro text */
        .intro-text {
            margin-bottom: 16px;
            font-size: 10pt;
            text-align: justify;
            line-height: 1.6;
        }

        /* Section titles */
        .section-title {
            color: #CC0000;
            font-weight: bold;
            font-size: 11pt;
            margin-top: 22px;
            margin-bottom: 8px;
            text-transform: uppercase;
            border-bottom: 2px solid #CC0000;
            padding-bottom: 4px;
        }

        .subsection-title {
            color: #CC0000;
            font-weight: bold;
            font-size: 10pt;
            margin-top: 12px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        /* Scope content */
        .scope-block {
            margin-bottom: 10px;
        }

        .scope-block .scope-title {
            font-weight: bold;
            font-size: 10pt;
            color: #000;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .scope-block .scope-content {
            font-size: 9.5pt;
            line-height: 1.5;
            padding-left: 10px;
        }

        /* Tax note */
        .tax-note {
            margin-top: 12px;
            margin-bottom: 18px;
            font-size: 9pt;
            color: #555;
            font-style: italic;
        }

        /* Pricing summary table */
        .pricing-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .pricing-table th {
            background-color: #CC0000;
            color: white;
            font-weight: bold;
            padding: 7px 10px;
            text-align: left;
            border: 1px solid #CC0000;
            font-size: 9pt;
            text-transform: uppercase;
        }

        .pricing-table td {
            padding: 6px 10px;
            border: 1px solid #ddd;
            font-size: 9.5pt;
        }

        .pricing-table td.amount {
            text-align: right;
            font-weight: bold;
        }

        .pricing-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Terms section */
        .terms-block {
            margin-top: 22px;
            margin-bottom: 16px;
        }

        .terms-block p {
            font-size: 9.5pt;
            margin-bottom: 6px;
            line-height: 1.5;
        }

        .terms-block ul {
            margin: 6px 0 6px 20px;
            padding: 0;
        }

        .terms-block li {
            font-size: 9.5pt;
            margin-bottom: 4px;
            line-height: 1.4;
        }

        /* Closing */
        .closing-block {
            margin-top: 24px;
            font-size: 10pt;
            line-height: 1.6;
        }

        .closing-block .signature-name {
            font-weight: bold;
            font-size: 10pt;
            margin-top: 30px;
        }

        .closing-block .signature-title {
            font-size: 9.5pt;
            color: #555;
        }

        /* Page break */
        .page-break {
            page-break-before: always;
        }

        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

    <!-- HEADER - repeats on every page -->
    <div class="header-wrapper">
        <table class="header-table">
            <tr>
                <td style="width: 40%; padding: 10px 0;">
                    <?php if (!empty($logo_base64)): ?>
                    <img class="header-logo" src="<?php echo $logo_base64; ?>" alt="Prime Services">
                    <?php endif; ?>
                </td>
                <td style="width: 60%; padding: 10px 0 10px 15px; text-align: left;">
                    <div class="doc-title">Service</div>
                    <div class="doc-subtitle">QUOTATION</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER - repeats on every page -->
    <div class="footer-wrapper">
        <div class="footer-top">
            <?php if (strpos($dept, 'hospitality') !== false): ?>
            PRIME HOSPITALITY SERVICES OF TEXAS
            <?php else: ?>
            PRIME FACILITY SERVICES GROUP, INC.
            <?php endif; ?>
        </div>
        <div class="footer-bottom">
            <strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>
            <a href="http://www.primefacilityservicesgroup.com">www.primefacilityservicesgroup.com</a>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- QUOTATION CONTENT                            -->
    <!-- ============================================ -->

    <!-- Date -->
    <div class="quote-date"><?php echo $work_date; ?></div>

    <!-- Greeting -->
    <div class="greeting-block">
        <div class="dear">Dear <?php echo $client_name ?: 'Valued Client'; ?>,</div>
        <div class="client-info">
            <?php echo $company_name; ?><br>
            <?php echo $company_address; ?>
        </div>
    </div>

    <!-- Intro paragraphs -->
    <div class="intro-text">
        We appreciate the opportunity to visit your business. We gathered the necessary information to prepare this quotation and provide you with the best possible service.
    </div>
    <div class="intro-text">
        In response to your request, we are pleased to present the following proposal for <strong><?php echo $requested_service; ?></strong>.
    </div>

    <!-- ============================================ -->
    <!-- QUOTATION - Scope of Service                 -->
    <!-- ============================================ -->

    <div class="section-title">Quotation</div>
    <div class="subsection-title">Scope of Service</div>

    <?php if (!empty($scopeSections)): ?>
        <?php foreach ($scopeSections as $section): ?>
        <div class="scope-block no-break">
            <div class="scope-title"><?php echo htmlspecialchars($section['title'] ?? ''); ?></div>
            <div class="scope-content">
                <?php echo nl2br(htmlspecialchars($section['scope_content'] ?? '')); ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <?php if (!empty($data['Site_Observation'])): ?>
        <div class="scope-block">
            <div class="scope-content">
                <?php echo nl2br(htmlspecialchars($data['Site_Observation'])); ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Tax note -->
    <div class="tax-note">
        An 8.25% sales tax will be added to the final invoice.
    </div>

    <!-- ============================================ -->
    <!-- Pricing Summary                              -->
    <!-- ============================================ -->

    <div class="section-title">Pricing Summary</div>

    <?php
    $isProductMode = isset($salesMode) && $salesMode === 'product';
    $qHdrType = $isProductMode ? 'Product' : 'Service';
    $qHdrTime = $isProductMode ? 'Quantity' : 'Service Time';
    $qHdrFreq = $isProductMode ? 'Unit Price' : 'Frequency';
    ?>
    <table class="pricing-table">
        <thead>
            <tr>
                <th style="width: 30%;"><?php echo $qHdrType; ?></th>
                <th style="width: 20%;"><?php echo $qHdrTime; ?></th>
                <th style="width: 20%;"><?php echo $qHdrFreq; ?></th>
                <th style="width: 30%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($serviceRows as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['type']); ?></td>
                <td><?php echo htmlspecialchars($row['time']); ?></td>
                <td><?php echo htmlspecialchars($row['freq']); ?></td>
                <td class="amount">$<?php echo number_format($row['subtotal'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- ============================================ -->
    <!-- Terms and Approval                           -->
    <!-- ============================================ -->

    <div class="section-title">Terms and Approval</div>

    <div class="terms-block">
        <p>Approval of this quotation by phone or email authorizes PRIME to proceed with the service described above.</p>
        <ul>
            <li>We protect your facilities with comprehensive insurance coverage.</li>
            <li>We are insured and authorized to operate in the State of Texas.</li>
            <li>References available upon request.</li>
        </ul>
    </div>

    <!-- ============================================ -->
    <!-- Closing                                      -->
    <!-- ============================================ -->

    <div class="closing-block">
        <p>Thank you in advance for your consideration.<br>
        We look forward to the opportunity to work with you.</p>

        <p>Sincerely,</p>

        <div class="signature-name"><?php echo $seller ?: 'Sales Department'; ?></div>
        <div class="signature-title">Sales Manager</div>
    </div>

</body>
</html>
