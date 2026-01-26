<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Work Order</title>
    <style>
        @page {
            margin: 0.5in 0.5in 0.75in 0.5in;
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
        }

        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 3px solid #8B1A1A;
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
            padding: 10px 0;
            padding-left: 20px;
        }

        .company-logo img {
            max-height: 70px;
            width: auto;
        }

        .doc-title {
            color: #8B1A1A;
            font-size: 26pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .doc-subtitle {
            font-size: 10pt;
            color: #000;
            font-style: italic;
        }

        /* Info Tables */
        .info-section {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .info-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }

        .info-table .label {
            font-weight: bold;
            background-color: #f0f0f0;
            width: 25%;
        }

        .info-table .value {
            width: 75%;
        }

        .info-table .label-small {
            font-weight: bold;
            background-color: #f0f0f0;
            width: 20%;
        }

        .info-table .value-small {
            width: 30%;
        }

        /* 7 Column Info Table - Invisible borders */
        .info-columns {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8pt;
        }

        .info-columns td {
            padding: 4px 6px;
            vertical-align: top;
            border: none;
        }

        .info-columns .col-header {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7pt;
            padding-bottom: 2px;
        }

        .info-columns .col-content {
            font-size: 8pt;
            line-height: 1.4;
        }

        /* Services Table */
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .services-table th {
            background-color: #8B1A1A;
            color: white;
            font-weight: bold;
            padding: 8px 6px;
            text-align: center;
            border: 1px solid #000;
            font-size: 9pt;
        }

        .services-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
        }

        .services-table .amount {
            text-align: right;
            font-weight: bold;
        }

        .services-table .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        /* Scope Section */
        .scope-section {
            margin-bottom: 15px;
        }

        .scope-header {
            background-color: #8B1A1A;
            color: white;
            font-weight: bold;
            padding: 6px 10px;
            margin-bottom: 8px;
            font-size: 9pt;
        }

        .scope-content {
            border: 1px solid #000;
            padding: 10px;
            background-color: #fff;
        }

        .scope-content h4 {
            font-size: 9pt;
            font-weight: bold;
            margin: 8px 0 4px 0;
            text-decoration: underline;
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

        /* Requirements Section */
        .requirements-section {
            margin-top: 20px;
            page-break-before: always;
        }

        .req-box {
            border: 2px solid #8B1A1A;
            padding: 8px;
            margin-bottom: 10px;
        }

        .req-title {
            color: #8B1A1A;
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 5px;
        }

        .req-box ul {
            margin-left: 15px;
        }

        .req-box li {
            margin-bottom: 3px;
            font-size: 9pt;
        }

        /* Signature Section */
        .signature-section {
            display: table;
            width: 100%;
            margin-top: 20px;
        }

        .signature-box {
            display: table-cell;
            width: 48%;
            border: 1px solid #000;
            padding: 10px;
            height: 80px;
        }

        .signature-box.right {
            margin-left: 4%;
        }

        .sig-label {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 5px;
        }

        .sig-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 3px;
            font-size: 8pt;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #8B1A1A;
            color: white;
            text-align: center;
            padding: 8px 10px;
            font-size: 8pt;
        }

        .footer a {
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            <div class="company-logo">
                <?php
                // Determine logo based on Service_Type
                $service_type = strtolower($data['Service_Type'] ?? 'facility');
                $logo_file = (strpos($service_type, 'hospitality') !== false) ? 'Hospitality.png' : 'Facility.png';
                $logo_path = __DIR__ . '/../../../Images/' . $logo_file;

                // Convert to base64 for PDF embedding
                if (file_exists($logo_path)) {
                    $logo_data = base64_encode(file_get_contents($logo_path));
                    $logo_mime = 'image/png';
                    echo '<img src="data:' . $logo_mime . ';base64,' . $logo_data . '" alt="Prime Logo">';
                } else {
                    // Fallback text logo if image not found
                    echo '<div style="background-color: #8B1A1A; color: white; padding: 15px 20px; display: inline-block;">
                        <div style="font-size: 24pt; font-weight: bold; letter-spacing: 2px;">PRIME</div>
                        <div style="font-size: 7pt; margin-top: 2px;">' . ($service_type === 'hospitality' ? 'Hospitality Services of Texas' : 'Facility Services Group') . '</div>
                    </div>';
                }
                ?>
            </div>
        </div>
        <div class="header-right">
            <div class="doc-title">JOB WORK ORDER</div>
            <div class="doc-subtitle">"The best services in the industry or nothing at all"</div>
        </div>
    </div>

    <!-- CLIENT & WORK INFO - 7 COLUMNS -->
    <table class="info-columns">
        <tr>
            <td class="col-header" style="width: 18%;">BILL TO</td>
            <td class="col-header" style="width: 16%;">WORK SITE</td>
            <td class="col-header" style="width: 12%;">SALES PERSON</td>
            <td class="col-header" style="width: 12%;">WORK DATE</td>
            <td class="col-header" style="width: 14%;">DEPARTMENT</td>
            <td class="col-header" style="width: 14%;">PAYMENT TERMS</td>
            <td class="col-header" style="width: 14%;">W.O. NO.</td>
        </tr>
        <tr>
            <td class="col-content">
                <?php echo htmlspecialchars($data['client_name'] ?? 'N/A'); ?><br>
                <?php if (!empty($data['Client_Title'])): ?>
                    <?php echo htmlspecialchars($data['Client_Title']); ?><br>
                <?php endif; ?>
                <?php echo htmlspecialchars($data['Email'] ?? ''); ?><br>
                <?php echo htmlspecialchars($data['Number_Phone'] ?? ''); ?>
            </td>
            <td class="col-content">
                <?php echo htmlspecialchars($data['Company_Name'] ?? 'N/A'); ?><br>
                <?php echo htmlspecialchars($data['Company_Address'] ?? ''); ?>
            </td>
            <td class="col-content">
                <?php echo htmlspecialchars($data['Seller'] ?? 'N/A'); ?>
            </td>
            <td class="col-content">
                <?php echo date('m/d/Y'); ?>
            </td>
            <td class="col-content">
                <?php echo htmlspecialchars($data['Service_Type'] ?? 'N/A'); ?>
            </td>
            <td class="col-content">
                <?php
                $freq_map = [
                    '15' => 'Net 15',
                    '30' => 'Net 30',
                    '50_deposit' => '50% Deposit',
                    'completion' => 'Upon Completion'
                ];
                echo htmlspecialchars($freq_map[$data['Invoice_Frequency'] ?? ''] ?? 'Upon Completion');
                ?>
            </td>
            <td class="col-content">
                <strong><?php echo htmlspecialchars($data['docnum'] ?? ''); ?></strong>
            </td>
        </tr>
    </table>

    <!-- SERVICES TABLE -->
    <table class="services-table">
        <thead>
            <tr>
                <th style="width: 30%;">Type of Services</th>
                <th style="width: 10%;">Day</th>
                <th style="width: 15%;">Frequency</th>
                <th style="width: 15%;">Duration</th>
                <th style="width: 15%;">Amount per Service</th>
                <th style="width: 15%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: left;"><?php echo htmlspecialchars($data['Requested_Service'] ?? 'Window Cleaning'); ?></td>
                <td><?php echo htmlspecialchars($data['Service_Day'] ?? '1'); ?></td>
                <td><?php echo htmlspecialchars($data['Service_Frequency'] ?? 'One Time'); ?></td>
                <td><?php echo htmlspecialchars($data['Service_Duration'] ?? '4-5 Hours'); ?></td>
                <td class="amount">$<?php echo number_format((float)($data['Total_Price'] ?? 0), 2); ?></td>
                <td class="amount">$<?php echo number_format((float)($data['Total_Price'] ?? 0), 2); ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="5" style="text-align: right; padding-right: 10px;">TOTAL</td>
                <td class="amount">$<?php echo number_format((float)($data['Total_Price'] ?? 0), 2); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- SCOPE OF WORK -->
    <div class="scope-section">
        <div class="scope-header">SCOPE OF WORK - <?php echo strtoupper(htmlspecialchars($data['Requested_Service'] ?? 'SERVICE DESCRIPTION')); ?></div>
        <div class="scope-content">
            <?php if (!empty($data['Site_Observation'])): ?>
                <h4>Area to be Serviced (Measured Glass Panels):</h4>
                <p><?php echo nl2br(htmlspecialchars($data['Site_Observation'])); ?></p>
            <?php endif; ?>

            <h4>Work to be Performed:</h4>
            <?php if (!empty($data['scope_of_work'])): ?>
                <?php echo $data['scope_of_work']; ?>
            <?php else: ?>
                <ul>
                    <li>Pre-cleaning and preparation of all exterior glass panels listed above</li>
                    <li>Removal of fingerprints, dust, and any residues to ensure proper film adhesion</li>
                    <li>Installation of window tint on doors, side panels, and upper transom window</li>
                    <li>Removal of bubbles and inspection of adhesion during installation</li>
                    <li>Cleaning of the work area to maintain a professional finish</li>
                    <li>Final inspection to ensure an even and uniform appearance across the entire storefront</li>
                </ul>
            <?php endif; ?>

            <?php if (!empty($data['Additional_Comments'])): ?>
                <h4>Additional Notes:</h4>
                <p><?php echo nl2br(htmlspecialchars($data['Additional_Comments'])); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- PAGE 2: REQUIREMENTS -->
    <div class="requirements-section">

        <div class="req-box">
            <div class="req-title">TAXES:</div>
            <ul>
                <li>Prices exclude Texas state tax (8.25%), which will be added to the final bill</li>
            </ul>
        </div>

        <div class="req-box">
            <div class="req-title">POST-SERVICE REQUIREMENTS:</div>
            <ul>
                <li>Client management must verify completion</li>
                <li>Any concerns must be reported within 24 hours</li>
                <li>Follow recommended maintenance schedule</li>
            </ul>
        </div>

        <div class="req-box">
            <div class="req-title">SITE ACCESS REQUIREMENTS:</div>
            <ul>
                <li>Client must ensure the front parking area is clear or accessible for ladder placement</li>
                <li>Access to water and power outlets if needed</li>
                <li>All approved interior access as needed</li>
                <li>Any obstructions near the glass (signs, displays, etc.) must be moved</li>
            </ul>
        </div>

        <div class="req-box">
            <div class="req-title">PREPARATION REQUIREMENTS:</div>
            <ul>
                <li>Remove posters, decals, or temporary signs from the glass (if requested)</li>
                <li>Move items blocking the lower portion of the windows</li>
                <li>Ensure exterior areas are safe for services and equipment</li>
            </ul>
        </div>

        <div class="req-box">
            <div class="req-title">PLEASE SEND TWO COPIES OF YOUR WORK ORDER:</div>
            <p style="margin-left: 15px; font-size: 9pt;">
                Both copies should be signed with the prices, terms, and specifications.
            </p>
        </div>

        <div class="req-box">
            <div class="req-title">SEND ALL CORRESPONDENCE TO:</div>
            <p style="margin-left: 15px; font-size: 9pt;">
                <strong>Prime Facility Services Group, Inc</strong><br>
                8303 Westglen Drive<br>
                Houston, TX 77063
            </p>
            <p style="margin-left: 15px; font-size: 9pt; margin-top: 10px;">
                <strong>Email:</strong> customerservice@primefacilityservicesgroup.com<br>
                <strong>Phone:</strong> (713) 338-2553<br>
                <strong>Fax:</strong> 713-574-3065
            </p>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="sig-label">Authorized by:</div>
                <div class="sig-line">Signature & Date</div>
            </div>
            <div class="signature-box" style="margin-left: 20px;">
                <div class="sig-label">Print Name:</div>
                <div class="sig-line">Name & Title</div>
            </div>
        </div>

    </div>

    <!-- FOOTER -->
    <div class="footer">
        <strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>
        <a href="http://www.primefacilityservicesgroup.com">www.primefacilityservicesgroup.com</a>
    </div>

</body>
</html>
