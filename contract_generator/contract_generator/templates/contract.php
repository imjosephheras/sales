<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temporary Staff Services Agreement</title>
    <style>
        @page {
            margin: 3.5cm 2cm 3.2cm 2cm;
        }

        @media print {
            @page {
                margin: 3.5cm 2cm 3.2cm 2cm;
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
            font-size: 9.5pt;
            color: #000;
            line-height: 1.4;
            padding: 0;
        }

        /* Header - fixed position, repeats on every page */
        .header-wrapper {
            position: fixed;
            top: -3cm;
            left: 0;
            right: 0;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 0;
            border-bottom: 3px solid #CC0000;
        }

        .header-left {
            display: table-cell;
            width: 40%;
            vertical-align: middle;
            padding: 10px 0;
        }

        .header-right {
            display: table-cell;
            width: 60%;
            vertical-align: middle;
            text-align: left;
            padding: 10px 0 10px 15px;
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

        /* Section titles */
        .section-number {
            color: #CC0000;
            font-weight: bold;
            font-size: 10pt;
            margin-top: 14px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .subsection {
            margin-left: 15px;
            margin-bottom: 6px;
        }

        .subsection p {
            margin-bottom: 5px;
            text-align: justify;
        }

        .subsection-label {
            font-weight: bold;
            margin-right: 4px;
        }

        .subsection ol {
            margin-left: 20px;
            margin-bottom: 6px;
            list-style-type: lower-alpha;
        }

        .subsection ol li {
            margin-bottom: 4px;
            text-align: justify;
        }

        .subsection ul {
            margin-left: 20px;
            margin-bottom: 6px;
        }

        .subsection ul li {
            margin-bottom: 4px;
            text-align: justify;
        }

        /* Preamble / parties */
        .preamble {
            margin-bottom: 12px;
            text-align: justify;
            line-height: 1.5;
        }

        .preamble strong {
            font-size: 10pt;
        }

        /* Page break utilities */
        .page-break {
            page-break-before: always;
        }

        .no-break {
            page-break-inside: avoid;
        }

        /* Signature blocks */
        .signature-block {
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .signature-block-title {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 12px;
        }

        .sig-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sig-table td {
            width: 48%;
            vertical-align: top;
            padding: 5px 10px;
        }

        .sig-line-item {
            margin-bottom: 15px;
        }

        .sig-label {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 3px;
        }

        .sig-underline {
            border-bottom: 1px solid #000;
            height: 25px;
            margin-top: 5px;
        }

        /* Appendix header */
        .appendix-title {
            color: #CC0000;
            font-weight: bold;
            font-size: 14pt;
            text-align: center;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .appendix-subtitle {
            font-weight: bold;
            font-size: 11pt;
            text-align: center;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .appendix-content {
            margin: 15px 10px;
            text-align: justify;
            line-height: 1.5;
        }

        /* Notice block */
        .notice-block {
            margin: 10px 15px;
            line-height: 1.5;
        }

        .notice-block strong {
            display: block;
            margin-bottom: 2px;
        }

        /* Footer - fixed position, repeats on every page */
        .footer-wrapper {
            position: fixed;
            bottom: -2.7cm;
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

    <!-- HEADER -->
    <?php
    // Encode logo as base64 for DOMPDF compatibility
    $dept = strtolower(trim($data['Service_Type'] ?? ''));
    if (strpos($dept, 'hospitality') !== false) {
        $logo_file = __DIR__ . '/../../../Images/phospitality.png';
    } else {
        $logo_file = __DIR__ . '/../../../Images/pfacility.png';
    }
    $logo_base64 = '';
    if (file_exists($logo_file)) {
        $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_file));
    }

    // Prepare client data
    $client_name = htmlspecialchars($data['client_name'] ?? $data['Client_Name'] ?? '');
    $client_title = htmlspecialchars($data['Client_Title'] ?? '');
    $company_name = htmlspecialchars($data['Company_Name'] ?? '');
    $company_address = htmlspecialchars($data['Company_Address'] ?? '');

    // Contract duration mapping
    $duration_map = [
        '6_months' => '6 Months',
        '1_year' => '1 Year',
        '1_5_years' => '1.5 Years (18 Months)',
        '2_years' => '2 Years',
        '3_years' => '3 Years',
        '4_years' => '4 Years',
        '5_years' => '5 Years',
        'not_applicable' => 'Not Applicable'
    ];
    $contract_duration = $duration_map[$data['Contract_Duration'] ?? ''] ?? htmlspecialchars($data['Contract_Duration'] ?? '___________');

    $inflation_adj = htmlspecialchars($data['inflationAdjustment'] ?? '3.1');
    $start_date = htmlspecialchars($data['startDateServices'] ?? '');
    ?>

    <!-- HEADER - position:fixed makes DOMPDF repeat this on every page -->
    <div class="header-wrapper">
        <div class="header">
            <div class="header-left">
                <?php if ($logo_base64): ?>
                <img class="header-logo" src="<?php echo $logo_base64; ?>" alt="Prime Hospitality Services">
                <?php endif; ?>
            </div>
            <div class="header-right">
                <div class="doc-title">Temporary Staff</div>
                <div class="doc-subtitle">SERVICES AGREEMENT</div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- CONTRACT PAGES                               -->
    <!-- Each page is in its own file for easy editing -->
    <!-- ============================================ -->

    <?php include __DIR__ . '/contract/page1_preamble_and_responsibilities.php'; ?>
    <?php include __DIR__ . '/contract/page2_payment_terms.php'; ?>
    <?php include __DIR__ . '/contract/page3_confidentiality_and_indemnification.php'; ?>
    <?php include __DIR__ . '/contract/page4_notices.php'; ?>
    <?php include __DIR__ . '/contract/page5_miscellaneous.php'; ?>
    <?php include __DIR__ . '/contract/page6_terms_and_signatures.php'; ?>
    <?php include __DIR__ . '/contract/page7_emergency_and_insurance.php'; ?>
    <?php include __DIR__ . '/contract/page8_penalties_and_arbitration.php'; ?>
    <?php include __DIR__ . '/contract/page9_appendix_a_services.php'; ?>
    <?php include __DIR__ . '/contract/page10_appendix_b_benefits_waiver.php'; ?>
    <?php include __DIR__ . '/contract/page11_appendix_c_confidentiality.php'; ?>

    <!-- FOOTER - position:fixed makes DOMPDF repeat this on every page -->
    <div class="footer-wrapper">
        <div class="footer-top">
            PRIME HOSPITALITY SERVICES OF TEXAS
        </div>
        <div class="footer-bottom">
            <strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>
            <a href="http://www.primefacilityservicesgroup.com">www.primefacilityservicesgroup.com</a>
        </div>
    </div>

</body>
</html>
