<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Work Order</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
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

        /* 7 Column Info Table - Invisible borders with centered content */
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
            text-align: center;
        }

        .info-columns .col-header {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7pt;
            padding-bottom: 2px;
            text-align: center;
        }

        .info-columns .col-content {
            font-size: 8pt;
            line-height: 1.4;
            text-align: center;
        }

        /* Services Table */
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
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

        .services-table .service-desc {
            text-align: left;
        }

        .services-table .amount {
            text-align: right;
            font-weight: bold;
        }

        /* Totals Table */
        .totals-table {
            width: 250px;
            border-collapse: collapse;
            margin-left: auto;
            margin-bottom: 15px;
        }

        .totals-table td {
            padding: 6px 10px;
            font-size: 9pt;
        }

        .totals-table .label-cell {
            text-align: right;
            font-weight: bold;
            border: none;
        }

        .totals-table .value-cell {
            text-align: right;
            border: 1px solid #000;
            width: 100px;
            background-color: #fff;
        }

        .totals-table .header-cell {
            background-color: #8B1A1A;
            color: white;
            font-weight: bold;
            text-align: center;
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

        /* Terms Section - Clean without red borders */
        .terms-section {
            margin-top: 20px;
            page-break-before: always;
        }

        .terms-main-title {
            background-color: #8B1A1A;
            color: white;
            font-weight: bold;
            padding: 8px 10px;
            font-size: 10pt;
            margin-bottom: 15px;
        }

        .term-box {
            margin-bottom: 12px;
            padding-left: 5px;
        }

        .term-title {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 5px;
        }

        .term-box ul {
            margin-left: 20px;
        }

        .term-box li {
            margin-bottom: 3px;
            font-size: 9pt;
        }

        .term-box p {
            margin-left: 15px;
            font-size: 9pt;
            margin-bottom: 5px;
        }

        /* Signature and Contact Section */
        .final-section {
            display: table;
            width: 100%;
            margin-top: 30px;
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

        .contact-title {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 8px;
            text-decoration: underline;
        }

        .contact-info {
            font-size: 9pt;
            line-height: 1.5;
        }

        .signature-title {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 10px;
            text-decoration: underline;
        }

        .signature-box {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 10px;
            height: 70px;
        }

        .sig-label {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 5px;
        }

        .sig-line {
            border-top: 1px solid #000;
            margin-top: 35px;
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

    <!-- CLIENT & WORK INFO - 7 COLUMNS CENTERED -->
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
    <?php
    // Calculate totals
    $subtotal = (float)($data['Total_Price'] ?? 0);
    $tax_rate = 0.0825;
    $taxes = $subtotal * $tax_rate;
    $grand_total = $subtotal + $taxes;
    ?>
    <table class="services-table">
        <thead>
            <tr>
                <th style="width: 25%;">Type of Services</th>
                <th style="width: 12%;">Service Time</th>
                <th style="width: 12%;">Frequency</th>
                <th style="width: 36%;">Service Description</th>
                <th style="width: 15%;">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="service-desc"><?php echo htmlspecialchars($data['Requested_Service'] ?? 'Window Cleaning'); ?></td>
                <td><?php echo htmlspecialchars($data['Service_Duration'] ?? 'One Day'); ?></td>
                <td><?php echo htmlspecialchars($data['Service_Frequency'] ?? 'One Time'); ?></td>
                <td class="service-desc">
                    <?php if (!empty($data['scope_of_work'])): ?>
                        <?php echo strip_tags($data['scope_of_work']); ?>
                    <?php else: ?>
                        Interior cleaning of accessible glass surfaces, including windows, doors, and partitions, removing dust, fingerprints, and residue for a clean, streak-free finish.
                    <?php endif; ?>
                </td>
                <td class="amount">$<?php echo number_format($subtotal, 2); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- TOTALS TABLE -->
    <table class="totals-table">
        <tr>
            <td class="label-cell">TOTAL</td>
            <td class="value-cell header-cell">TOTAL</td>
        </tr>
        <tr>
            <td class="label-cell">TOTAL</td>
            <td class="value-cell">$<?php echo number_format($subtotal, 2); ?></td>
        </tr>
        <tr>
            <td class="label-cell">TAXES</td>
            <td class="value-cell">$<?php echo number_format($taxes, 2); ?></td>
        </tr>
        <tr>
            <td class="label-cell">GRAND TOTAL</td>
            <td class="value-cell">$<?php echo number_format($grand_total, 2); ?></td>
        </tr>
    </table>

    <!-- SCOPE OF WORK -->
    <div class="scope-section">
        <div class="scope-header">SCOPE OF WORK - <?php echo strtoupper(htmlspecialchars($data['Requested_Service'] ?? 'SERVICE DESCRIPTION')); ?></div>
        <div class="scope-content">
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

    <!-- PAGE 2: TERMS AND CONDITIONS -->
    <div class="terms-section">

        <div class="terms-main-title">TERMS AND CONDITIONS</div>

        <div class="term-box">
            <div class="term-title">1. SERVICE LIMITATIONS</div>
            <ul>
                <li>Work will be performed during approved service windows.</li>
                <li>Additional charges may apply for emergency service requests.</li>
                <li>Separate scheduling is required for areas containing wood-burning equipment.</li>
            </ul>
        </div>

        <?php
        // Check if this is a kitchen cleaning service
        $requested_service = strtolower($data['Requested_Service'] ?? '');
        $is_kitchen_service = (strpos($requested_service, 'kitchen') !== false || strpos($requested_service, 'hood') !== false);

        if ($is_kitchen_service):
        ?>
        <div class="term-box">
            <div class="term-title">2. AREA PREPARATION</div>
            <ul>
                <li>All cooking equipment must be turned off at least two (2) hours before service.</li>
            </ul>
        </div>

        <div class="term-box">
            <div class="term-title">3. KITCHEN PREPARATION</div>
            <p>The Client must ensure that the kitchen is ready for service, including:</p>
            <ul>
                <li>Turning off all kitchen equipment and allowing it to cool completely</li>
                <li>Removing food, utensils, and personal items from work surfaces</li>
                <li>Keeping access areas clear for the cleaning crew</li>
            </ul>
            <p>Additional time caused by lack of preparation may be billed at <strong>$30.00 USD per hour</strong>.</p>
        </div>
        <?php endif; ?>

        <div class="term-box">
            <div class="term-title"><?php echo $is_kitchen_service ? '4' : '2'; ?>. PROPOSAL VALIDITY PERIOD</div>
            <p>The proposal issued for this Work Order will be valid for fourteen (14) days from the date of issuance. Prime Facility Services Group may revise pricing, scope, or terms if approval is not received within this period.</p>
            <p>If actual site conditions differ from those observed during the initial inspection, a revised proposal may be issued.</p>
        </div>

        <div class="term-box">
            <div class="term-title"><?php echo $is_kitchen_service ? '5' : '3'; ?>. CANCELLATIONS</div>
            <p>Cancellations made with less than twenty-four (24) hours' notice will incur a charge equal to one hundred percent (100%) of the minimum scheduled labor.</p>
            <p>Cancellations made with more than twenty-four (24) hours' notice will not incur charges unless otherwise specified in the applicable price list.</p>
        </div>

        <div class="term-box">
            <div class="term-title"><?php echo $is_kitchen_service ? '6' : '4'; ?>. RESCHEDULING</div>
            <p>Rescheduling requests must be submitted at least twenty-four (24) hours in advance. Requests made within 24 hours may incur a fee of up to the total scheduled labor and are subject to personnel and equipment availability.</p>
            <p>Availability for rescheduled dates or times is not guaranteed.</p>
        </div>

        <div class="term-box">
            <div class="term-title"><?php echo $is_kitchen_service ? '7' : '5'; ?>. LACK OF ACCESS</div>
            <p>If personnel arrive on site and are unable to begin work due to lack of access, incomplete area preparation, or delays caused by the Client, the situation will be treated as a same-day cancellation and the corresponding charges will apply.</p>
        </div>

        <div class="term-box">
            <div class="term-title"><?php echo $is_kitchen_service ? '8' : '6'; ?>. WEATHER OR SAFETY DELAYS</div>
            <p>If work cannot be safely performed due to weather conditions, hazardous environments, or other safety-related circumstances beyond the company's control, the service will be rescheduled to the next available date.</p>
            <p>No penalties will apply; however, labor or material costs may be adjusted if conditions change significantly.</p>
        </div>

        <div class="term-box">
            <div class="term-title"><?php echo $is_kitchen_service ? '9' : '7'; ?>. POST-SERVICE REQUIREMENTS</div>
            <ul>
                <li>Kitchen management must verify completion.</li>
                <li>Any concerns must be reported within twenty-four (24) hours.</li>
                <li>Recommended maintenance schedules must be followed.</li>
            </ul>
        </div>

        <div class="term-box">
            <div class="term-title"><?php echo $is_kitchen_service ? '10' : '8'; ?>. SITE ACCESS AND SECURITY COORDINATION</div>
            <ul>
                <li>The Client must notify on-site security personnel or building management in advance that services will be performed.</li>
                <li>If the service requires access to rooftops, ceilings, ventilation systems, or other restricted areas, the Client must ensure safe and full access.</li>
                <li>The Client must provide clear instructions and prior authorization to security or access-control personnel to allow entry for the service team.</li>
            </ul>
        </div>

        <!-- ACCEPTANCE / SIGNATURES SECTION -->
        <div class="terms-main-title" style="margin-top: 20px;">ACCEPTANCE / SIGNATURES</div>

        <div class="final-section">
            <div class="contact-column">
                <div class="contact-title">PLEASE SEND TWO COPIES OF YOUR WORK ORDER:</div>
                <div class="contact-info">
                    Enter this order in accordance with the prices, terms, and<br>
                    specifications listed above.
                </div>
                <br>
                <div class="contact-title">SEND ALL CORRESPONDENCES TO:</div>
                <div class="contact-info">
                    <strong>Prime Facility Services Group, Inc.</strong><br>
                    8303 Westglen Drive<br>
                    Houston, TX 77063<br><br>
                    customerservice@primefacilityservicesgroup.com<br>
                    (713) 338-2553 Phone<br>
                    (713) 574-3065 Fax
                </div>
            </div>
            <div class="signature-column">
                <div class="signature-box">
                    <div class="sig-label">Authorized by:</div>
                    <div class="sig-line">Signature & Date</div>
                </div>
                <div class="signature-box">
                    <div class="sig-label">Print Name:</div>
                    <div class="sig-line">Name & Title</div>
                </div>
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
