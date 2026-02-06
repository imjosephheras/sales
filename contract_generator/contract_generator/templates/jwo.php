<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Work Order</title>
    <style>
        /* =============================================
           PAGE SETUP - Per-page control for dompdf
           ============================================= */
        @page {
            margin: 4.5cm 2cm 3.5cm 2cm;
        }

        @media print {
            @page {
                margin: 4.5cm 2cm 3.5cm 2cm;
            }
            body {
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.3;
            padding: 0 0.5cm;
        }

        /* =============================================
           PAGE STRUCTURE - Explicit page wrappers
           ============================================= */
        .page {
            /* Base page wrapper */
        }

        .page-2 {
            page-break-before: always;
        }

        .page-3 {
            page-break-before: always;
        }

        /* Page 3: Anchor signatures to the bottom via table layout.
           Height = A4 (29.7cm) - top margin (4.5cm) - bottom margin (3.5cm) = 21.7cm */
        .page-3-anchor {
            width: 100%;
            height: 21.7cm;
        }

        .page-3-anchor td.page-3-content {
            vertical-align: bottom;
            padding: 0;
        }

        /* =============================================
           HEADER
           ============================================= */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 3px solid #CC0000;
        }

        .header-left {
            display: table-cell;
            width: 45%;
            vertical-align: middle;
            padding: 10px 0;
        }

        .header-right {
            display: table-cell;
            width: 55%;
            vertical-align: middle;
            text-align: left;
            padding: 10px 0 10px 15px;
        }

        .header-logo {
            max-height: 70px;
            width: auto;
        }

        .doc-title {
            color: #CC0000;
            font-size: 22pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .doc-subtitle {
            font-size: 10pt;
            color: #000;
            font-style: italic;
        }

        /* =============================================
           CLIENT INFO TABLE - 7 columns, invisible borders
           ============================================= */
        .info-columns {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 7pt;
        }

        .info-columns td {
            padding: 3px 5px;
            vertical-align: top;
            border: none;
        }

        .info-columns .col-header {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7pt;
            padding-bottom: 3px;
            text-align: center;
        }

        .info-columns .col-content {
            font-size: 7pt;
            line-height: 1.3;
            text-align: center;
        }

        /* =============================================
           SERVICES TABLE
           ============================================= */
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .services-table th {
            background-color: #CC0000;
            color: white;
            font-weight: bold;
            padding: 8px 6px;
            text-align: center;
            border: 1px solid #000;
            font-size: 8pt;
            text-transform: uppercase;
        }

        .services-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
            font-size: 8pt;
        }

        .services-table .service-desc {
            text-align: left;
        }

        .services-table .amount {
            text-align: right;
            font-weight: bold;
        }

        /* =============================================
           TOTALS TABLE - Right aligned, 3 rows
           ============================================= */
        .totals-table {
            width: 280px;
            border-collapse: collapse;
            margin-left: auto;
            margin-bottom: 15px;
        }

        .totals-table td {
            padding: 6px 8px;
            font-size: 9pt;
            border: none;
            background: none;
        }

        .totals-table .value-cell {
            text-align: right;
            font-weight: bold;
            border: 1px solid #000;
        }

        .totals-table .label-cell {
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
        }

        .totals-table tr:last-child .label-cell {
            color: #CC0000;
        }

        .totals-table tr:last-child .value-cell {
            background-color: #CC0000;
            color: white;
            border: 1px solid #CC0000;
        }

        /* =============================================
           SCOPE OF WORK
           ============================================= */
        .scope-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
            border: none;
        }

        .scope-header {
            color: #CC0000;
            font-weight: bold;
            padding: 6px 10px;
            margin-bottom: 8px;
            font-size: 9pt;
            text-transform: uppercase;
        }

        .scope-content {
            border: none;
            padding: 10px 0;
            background-color: transparent;
        }

        .scope-content h4 {
            font-size: 9pt;
            font-weight: bold;
            margin: 8px 0 4px 0;
            text-transform: uppercase;
        }

        .scope-content ul {
            margin-left: 20px;
            margin-bottom: 8px;
        }

        .scope-content li {
            margin-bottom: 3px;
            font-size: 9pt;
        }

        .scope-content p {
            margin-bottom: 6px;
            font-size: 9pt;
        }

        /* =============================================
           TERMS AND CONDITIONS
           ============================================= */
        .terms-section {
            padding-left: 15px;
        }

        .terms-main-title {
            color: #CC0000;
            font-weight: bold;
            padding: 8px 10px;
            font-size: 10pt;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .term-box {
            margin-bottom: 12px;
            padding-left: 15px;
            page-break-inside: avoid;
        }

        .term-title {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 5px;
            text-transform: uppercase;
            color: #CC0000;
        }

        .term-box ul {
            margin-left: 20px;
        }

        .term-box li {
            margin-bottom: 3px;
            font-size: 9pt;
            color: #000;
        }

        .term-box p {
            margin-left: 15px;
            font-size: 9pt;
            margin-bottom: 5px;
            color: #000;
        }

        /* =============================================
           ACCEPTANCE / SIGNATURES
           ============================================= */
        .final-section {
            display: table;
            width: 100%;
            page-break-inside: avoid;
        }

        .contact-column {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding-right: 20px;
        }

        .signature-column {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }

        .acceptance-header {
            color: #CC0000;
            font-weight: bold;
            font-size: 10pt;
            text-transform: uppercase;
            margin-bottom: 5px;
            padding-bottom: 8px;
            border-bottom: 2px solid #CC0000;
        }

        .contact-title {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 8px;
            text-transform: uppercase;
            text-decoration: none;
            padding-bottom: 4px;
            border-bottom: 2px solid #CC0000;
            display: inline-block;
        }

        .contact-subtitle {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 8px;
            text-transform: uppercase;
            text-decoration: none;
            border-bottom: 1px solid #999;
            display: inline-block;
            padding-bottom: 4px;
        }

        .contact-info {
            font-size: 9pt;
            line-height: 1.5;
        }

        .contact-icon {
            color: #CC0000;
            font-size: 9pt;
            margin-right: 4px;
        }

        .signature-box {
            border: 2px solid #CC0000;
            padding: 10px;
            margin-bottom: 10px;
            height: 80px;
        }

        .sig-label {
            font-weight: bold;
            font-size: 9pt;
            text-transform: uppercase;
            color: #fff;
            background-color: #CC0000;
            display: inline-block;
            padding: 3px 10px;
            margin: -10px 0 5px -10px;
        }

        .sig-line {
            border-top: 1px solid #333;
            margin-top: 30px;
            padding-top: 3px;
            font-size: 8pt;
            color: #555;
        }

        /* =============================================
           FOOTER - Two-tone split design (fixed on all pages)
           ============================================= */
        .footer-wrapper {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
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
    </style>
</head>
<body>

    <?php
    /* =============================================
       DATA PREPARATION
       Compute all variables needed by page templates
       ============================================= */

    // --- Build service rows from DB detail tables ---
    $hasDetailServices = false;
    $allServiceRows = [];
    $runningTotal = 0.0;

    // Janitorial Services
    if (!empty($janitorialServices)) {
        $hasDetailServices = true;
        foreach ($janitorialServices as $svc) {
            $svcSubtotal = floatval($svc['subtotal'] ?? 0);
            $runningTotal += $svcSubtotal;
            $allServiceRows[] = [
                'type' => $svc['service_type'] ?? 'Janitorial',
                'time' => $svc['service_time'] ?? '',
                'freq' => $svc['frequency'] ?? '',
                'desc' => $svc['description'] ?? '',
                'subtotal' => $svcSubtotal,
            ];
        }
    } elseif (($data['includeJanitorial'] ?? '') === 'Yes' && !empty($data['type18']) && is_array($data['type18'])) {
        $hasDetailServices = true;
        foreach ($data['type18'] as $i => $type) {
            if (!$type) continue;
            $svcSubtotal = floatval($data['subtotal18'][$i] ?? 0);
            $runningTotal += $svcSubtotal;
            $allServiceRows[] = [
                'type' => $type,
                'time' => $data['time18'][$i] ?? '',
                'freq' => $data['freq18'][$i] ?? '',
                'desc' => $data['desc18'][$i] ?? '',
                'subtotal' => $svcSubtotal,
            ];
        }
    }

    // Kitchen Cleaning Services
    if (!empty($kitchenServices)) {
        $hasDetailServices = true;
        foreach ($kitchenServices as $svc) {
            $svcSubtotal = floatval($svc['subtotal'] ?? 0);
            $runningTotal += $svcSubtotal;
            $allServiceRows[] = [
                'type' => $svc['service_type'] ?? 'Kitchen Cleaning',
                'time' => $svc['service_time'] ?? '',
                'freq' => $svc['frequency'] ?? '',
                'desc' => $svc['description'] ?? '',
                'subtotal' => $svcSubtotal,
            ];
        }
    } elseif (($data['includeKitchen'] ?? '') === 'Yes' && !empty($data['type19']) && is_array($data['type19']) && empty($hoodVentServices)) {
        $hasDetailServices = true;
        foreach ($data['type19'] as $i => $type) {
            if (!$type) continue;
            $svcSubtotal = floatval($data['subtotal19'][$i] ?? 0);
            $runningTotal += $svcSubtotal;
            $allServiceRows[] = [
                'type' => $type,
                'time' => $data['time19'][$i] ?? '',
                'freq' => $data['freq19'][$i] ?? '',
                'desc' => $data['desc19'][$i] ?? '',
                'subtotal' => $svcSubtotal,
            ];
        }
    }

    // Hood Vent Services
    if (!empty($hoodVentServices)) {
        $hasDetailServices = true;
        foreach ($hoodVentServices as $svc) {
            $svcSubtotal = floatval($svc['subtotal'] ?? 0);
            $runningTotal += $svcSubtotal;
            $allServiceRows[] = [
                'type' => $svc['service_type'] ?? 'Hood Vent',
                'time' => $svc['service_time'] ?? '',
                'freq' => $svc['frequency'] ?? '',
                'desc' => $svc['description'] ?? '',
                'subtotal' => $svcSubtotal,
            ];
        }
    }

    // Calculate totals
    if ($hasDetailServices && $runningTotal > 0) {
        $subtotal = $runningTotal;
    } else {
        $subtotal = (float)($data['Total_Price'] ?? $data['Prime_Quoted_Price'] ?? $data['PriceInput'] ?? 0);
    }

    $tax_rate = 0.0825;
    $taxes = $subtotal * $tax_rate;
    $grand_total = $subtotal + $taxes;

    // If no detail service rows, create a single generic row
    if (empty($allServiceRows)) {
        $service_description = '';
        if (!empty($data['Site_Observation'])) {
            $service_description = htmlspecialchars($data['Site_Observation']);
        } elseif (!empty($data['scope_of_work'])) {
            $service_description = strip_tags($data['scope_of_work']);
        } else {
            $service_description = 'Professional service as per client requirements. All work performed to industry standards with quality assurance.';
        }

        $allServiceRows[] = [
            'type' => $data['Requested_Service'] ?? 'Service',
            'time' => $data['Service_Time'] ?? 'One Day',
            'freq' => $data['Service_Frequency'] ?? 'One Time',
            'desc' => $service_description,
            'subtotal' => $subtotal,
        ];
    }
    ?>

    <!-- =============================================
         PAGE 1: Header, Client Info, Services, Scope
         ============================================= -->
    <?php include __DIR__ . '/jwo/page1.php'; ?>

    <!-- =============================================
         PAGE 2: Terms and Conditions
         ============================================= -->
    <?php include __DIR__ . '/jwo/page2.php'; ?>

    <!-- =============================================
         PAGE 3: Acceptance / Signatures
         ============================================= -->
    <?php include __DIR__ . '/jwo/page3.php'; ?>

    <!-- FOOTER (fixed on all pages) -->
    <div class="footer-wrapper">
        <div class="footer-top">
            PRIME FACILITY SERVICES GROUP, INC.
        </div>
        <div class="footer-bottom">
            <strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>
            <a href="http://www.primefacilityservicesgroup.com">www.primefacilityservicesgroup.com</a>
        </div>
    </div>

</body>
</html>
