<?php
/**
 * VENT HOOD SERVICE REPORT TEMPLATE
 * Template for PDF generation of Vent Hood service reports
 */

// Company info - Fixed values
$company_name = "PRIME FACILITY SERVICES GROUP";
$company_address = "8303 Westglen Dr ~ Houston, TX 77063";
$company_phone = "713-338-2553";
$company_fax = "713-574-3065";
$company_website = "www.primefacilityservicesgroup.com";

// Logo path (base64 encode for PDF) - dynamic based on Service_Type
$dept = strtolower(trim($data['Service_Type'] ?? ''));
if (strpos($dept, 'hospitality') !== false) {
    $logo_path = __DIR__ . '/../../../Images/Hospitality.png';
} else {
    $logo_path = __DIR__ . '/../../../Images/Facility.png';
}
$logo_base64 = '';
if (file_exists($logo_path)) {
    $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
}

// Get data from request
$work_order = $data['docnum'] ?? '';
$service_date = date('m/d/Y');
$next_service_date = '';
$frequency = '';

// Client info from database
$client_name = $data['Company_Name'] ?? '';
$client_address = $data['Company_Address'] ?? '';
$client_contact = $data['Client_Name'] ?? '';
$client_email = $data['Email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vent Hood Service Report - <?php echo htmlspecialchars($client_name); ?></title>
    <style>
        @page {
            margin: 35mm 18mm 32mm 18mm;
            size: letter;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.2;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Header - fixed position, repeats on every page */
        .header-wrapper {
            position: fixed;
            top: -30mm;
            left: 0;
            right: 0;
            overflow: visible;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #001f54;
            padding-bottom: 4px;
            margin-bottom: 0;
            text-align: center;
        }

        .company-logo {
            max-width: 280px;
            max-height: 90px;
            margin-bottom: 6px;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #001f54;
            margin-bottom: 1px;
        }

        .company-info {
            font-size: 9px;
            color: #666;
            line-height: 1.2;
        }

        .report-title {
            font-size: 13px;
            font-weight: bold;
            color: #001f54;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-subtitle {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
        }

        .section {
            margin-bottom: 3px;
            border: 1px solid #ddd;
            border-radius: 2px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .section-header {
            background: #001f54;
            color: #ffffff;
            padding: 3px 8px;
            font-weight: bold;
            font-size: 10px;
        }

        .section-content {
            padding: 4px 8px;
            background: #fafafa;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            padding: 2px 4px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }

        .info-label {
            font-weight: bold;
            color: #001f54;
            width: 35%;
        }

        .info-value {
            color: #333;
        }

        .two-columns {
            display: table;
            width: 100%;
        }

        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 8px;
        }

        .column:last-child {
            padding-right: 0;
            padding-left: 8px;
        }

        .checkbox-group {
            margin: 1px 0;
        }

        .checkbox-item {
            display: block;
            margin: 1px 0;
            padding-left: 14px;
            position: relative;
            font-size: 10px;
        }

        .checkbox-item:before {
            content: "\2610";
            position: absolute;
            left: 0;
            font-size: 12px;
        }

        .checkbox-item.checked:before {
            content: "\2611";
        }

        .checklist-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            page-break-inside: avoid;
        }

        .checklist-table th,
        .checklist-table td {
            border: 1px solid #ddd;
            padding: 2px 4px;
            text-align: left;
            font-size: 10px;
        }

        .checklist-table th {
            background: #e8e8e8;
            font-weight: bold;
            color: #001f54;
        }

        .checklist-table td.center {
            text-align: center;
            width: 35px;
        }

        .checklist-table td.comment-cell {
            font-size: 9px;
            color: #555;
        }

        .frequency-options {
            display: table;
            width: 100%;
        }

        .freq-option {
            display: table-cell;
            padding: 2px 6px;
            text-align: center;
        }

        .notes-area {
            min-height: 25px;
            border: 1px solid #ddd;
            border-radius: 2px;
            padding: 3px;
            background: white;
            margin-top: 2px;
        }

        .signature-section {
            display: table;
            width: 100%;
            margin-top: 4px;
            page-break-inside: avoid;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            padding: 4px;
            vertical-align: top;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            height: 18px;
            margin-bottom: 2px;
        }

        .signature-label {
            font-size: 9px;
            color: #666;
        }

        .page-break {
            page-break-before: always;
        }

        /* Footer - fixed position, repeats on every page */
        .footer-wrapper {
            position: fixed;
            bottom: -27mm;
            left: 0;
            right: 0;
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

        .sub-section {
            margin: 2px 0;
            padding: 3px;
            background: white;
            border: 1px solid #eee;
            border-radius: 2px;
            page-break-inside: avoid;
        }

        .sub-section-title {
            font-weight: bold;
            color: #001f54;
            font-size: 10px;
            margin-bottom: 2px;
            padding-bottom: 1px;
            border-bottom: 1px solid #eee;
        }

        .table-subheader {
            background: #d0e4f7;
            font-weight: bold;
            color: #001f54;
            text-align: left;
        }

        .inline-checkbox {
            display: inline-block;
            margin-right: 10px;
            font-size: 10px;
        }

        .photos-grid {
            display: table;
            width: 100%;
        }

        .photo-item {
            display: table-cell;
            width: 25%;
            padding: 4px;
            text-align: center;
        }

        /* Section 7 - Products */
        .products-table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table td {
            width: 20%;
            padding: 4px;
            text-align: center;
            vertical-align: top;
            border: 1px solid #eee;
        }

        .products-table img {
            width: 80px;
            height: 60px;
        }

        .product-name-cell {
            font-size: 7px;
            font-weight: bold;
            color: #001f54;
            margin-top: 2px;
            line-height: 1.2;
        }

        .product-qty-cell {
            font-size: 8px;
            color: #333;
            margin-top: 2px;
        }

        .authorization-block {
            margin-top: 8px;
            padding: 6px 8px;
            background: #f0f4fa;
            border: 1px solid #c0d0e0;
            font-size: 8px;
            color: #333;
            line-height: 1.5;
        }

        .authorization-sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            border-top: 1px solid #ccc;
        }

        .authorization-sig-table td {
            padding: 6px 8px;
            vertical-align: top;
            font-size: 8px;
            color: #555;
            width: 33.33%;
        }

        .authorization-sig-table .sig-label {
            font-weight: bold;
            color: #001f54;
            display: block;
            margin-bottom: 3px;
        }

        .sig-line {
            border-bottom: 1px solid #333;
            height: 25px;
            margin-top: 4px;
        }

    </style>
</head>
<body>

    <!-- HEADER - position:fixed makes DOMPDF repeat this on every page -->
    <div class="header-wrapper">
        <div class="header">
            <?php if ($logo_base64): ?>
                <img src="<?php echo $logo_base64; ?>" class="company-logo" alt="Logo">
            <?php endif; ?>
            <div class="company-name"><?php echo htmlspecialchars($company_name); ?></div>
            <div class="company-info">
                <?php echo htmlspecialchars($company_address); ?><br>
                Phone: <?php echo htmlspecialchars($company_phone); ?> ~ Fax: <?php echo htmlspecialchars($company_fax); ?><br>
                <?php echo htmlspecialchars($company_website); ?>
            </div>
        </div>
    </div>

    <!-- FOOTER - position:fixed makes DOMPDF repeat this on every page -->
    <div class="footer-wrapper">
        <div class="footer-top">
            PRIME FACILITY SERVICES GROUP, INC.
        </div>
        <div class="footer-bottom">
            <strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>
            <a href="http://www.primefacilityservicesgroup.com">www.primefacilityservicesgroup.com</a>
        </div>
    </div>

    <div style="text-align: center; margin-bottom: 4px;">
        <div class="report-title">KITCHEN EXHAUST CLEANING AND GREASE GUTTER SERVICE REPORT</div>
    </div>

    <!-- SERVICE REPORT / WORK ORDER INFO -->
    <div class="section">
        <div class="section-header">SERVICE REPORT / WORK ORDER</div>
        <div class="section-content">
            <div class="two-columns">
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Work Order #:</div>
                            <div class="info-cell info-value">__________________________</div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Service date:</div>
                            <div class="info-cell info-value">____ / ____ / ______</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Next recommended date:</div>
                            <div class="info-cell info-value">____ / ____ / ______</div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-top: 3px;">
                <span style="font-weight: bold; color: #001f54; font-size: 10px;">Frequency:</span>
                <span class="inline-checkbox">&square; 30 days</span>
                <span class="inline-checkbox">&square; 60 days</span>
                <span class="inline-checkbox">&square; 90 days</span>
                <span class="inline-checkbox">&square; 120 days</span>
                <span class="inline-checkbox">&square; Other: ____</span>
            </div>
        </div>
    </div>

    <!-- 1. INFORMACION DEL CLIENTE -->
    <div class="section">
        <div class="section-header">1. CLIENT INFORMATION</div>
        <div class="section-content">
            <div class="two-columns">
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Client / Restaurant:</div>
                            <div class="info-cell info-value"><?php echo htmlspecialchars($client_name); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Address:</div>
                            <div class="info-cell info-value"><?php echo htmlspecialchars($client_address); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. SISTEMA SERVICIADO -->
    <div class="section">
        <div class="section-header">2. SYSTEM SERVICED</div>
        <div class="section-content">
            <div class="two-columns">
                <div class="column">
                    <div class="checkbox-item">Main Hood </div>
                    <div class="checkbox-item">Extraction Ducts </div>
                    <div class="checkbox-item">Roof Fan </div>
                </div>
                <div class="column">
                    <div class="checkbox-item">Grease Gutter</div>
                    <div class="checkbox-item">Fire System (inspection only)</div>
                    <div class="checkbox-item">Other: _______________________</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. INSPECTION CHECKLIST, RESULTS & ROOF INSPECTION (CONSOLIDATED) -->
    <div class="section">
        <div class="section-header">3. INSPECTION CHECKLIST, RESULTS & ROOF INSPECTION</div>
        <div class="section-content">
            <table class="checklist-table">
                <thead>
                    <tr>
                        <th>Element</th>
                        <th class="center">Yes</th>
                        <th class="center">No</th>
                        <th class="center">N/A</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- BEFORE CLEANING -->
                    <tr><td colspan="5" class="table-subheader">BEFORE CLEANING</td></tr>
                    <tr>
                        <td>Fans working correctly?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="comment-cell"></td>
                    </tr>
                    <tr>
                        <td>Filters with grease accumulation?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="comment-cell"></td>
                    </tr>
                    <tr>
                        <td>Hood lights working?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="comment-cell"></td>
                    </tr>
                    <tr>
                        <td>Visible grease in ducts?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="comment-cell"></td>
                    </tr>
                    <tr>
                        <td>Grease container present?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="comment-cell"></td>
                    </tr>
                    <tr>
                        <td>Visible damage in system?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="comment-cell"></td>
                    </tr>
                    <!-- AFTER CLEANING -->
                    <tr><td colspan="5" class="table-subheader">AFTER CLEANING</td></tr>
                    <tr>
                        <td>System clean and operative</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="comment-cell"></td>
                    </tr>
                    <tr>
                        <td>Fan working at completion</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="comment-cell"></td>
                    </tr>
                    <tr>
                        <td>Work area delivered clean</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="comment-cell"></td>
                    </tr>
                    <tr>
                        <td>Client informed of final status</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="comment-cell"></td>
                    </tr>
                    <!-- ROOF INSPECTION -->
                    <tr><td colspan="5" class="table-subheader">ROOF INSPECTION</td></tr>
                    <tr>
                        <td>Grease accumulation on roof?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="comment-cell"></td>
                    </tr>
                    <tr>
                        <td>Is it a severe problem?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="comment-cell"></td>
                    </tr>
                    <tr>
                        <td>Absorption unit installation recommended?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="comment-cell"></td>
                    </tr>
                    <tr>
                        <td>Roof damage from grease?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="comment-cell"></td>
                    </tr>
                    <tr>
                        <td>Is there proper drainage?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="comment-cell"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. SERVICE PERFORMED (CLEANING) -->
    <div class="section">
        <div class="section-header">4. SERVICE PERFORMED (CLEANING)</div>
        <div class="section-content">
            <div class="two-columns">
                <div class="column">
                    <div class="checkbox-item">Complete hood cleaning</div>
                    <div class="checkbox-item">Filter cleaning</div>
                    <div class="checkbox-item">Duct cleaning</div>
                    <div class="checkbox-item">Extractor/fan cleaning</div>
                </div>
                <div class="column">
                    <div class="checkbox-item">Grease gutter cleaning</div>
                    <div class="checkbox-item">Kitchen area cleaning (affected)</div>
                    <div class="checkbox-item">Sticker placed on site</div>
                    <div class="checkbox-item">Before/after photos taken</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. TECHNICAL SYSTEM DATA -->
    <div class="section">
        <div class="section-header">5. TECHNICAL SYSTEM DATA</div>
        <div class="section-content">
            <div class="two-columns">
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Number of Fans:</div>
                            <div class="info-cell info-value">____________</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Number of Stacks:</div>
                            <div class="info-cell info-value">____________</div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Fan Type:</div>
                            <div class="info-cell info-value">
                                <span class="inline-checkbox">&square; Marshall</span>
                                <span class="inline-checkbox">&square; Upblast</span>
                                <span class="inline-checkbox">&square; Supreme</span>
                                <span class="inline-checkbox">&square; Other: ____</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. TECHNICIAN NOTES & SIGNATURES -->
    <div class="section">
        <div class="section-header">6. TECHNICIAN NOTES & SIGNATURES</div>
        <div class="section-content">
            <p style="font-weight: bold; color: #001f54; font-size: 10px; margin-bottom: 2px;">Notes / Observations:</p>
            <div class="notes-area"></div>

            <div class="signature-section">
                <div class="signature-box">
                    <p style="font-weight: bold; margin-bottom: 2px; color: #001f54; font-size: 10px;">Responsible Technician:</p>
                    <p style="font-size: 9px;">Name: _________________ Signature: _________________</p>
                    <p style="font-size: 9px; margin-top: 2px;">Date: ____/____/______</p>
                </div>
                <div class="signature-box">
                    <p style="font-weight: bold; margin-bottom: 2px; color: #001f54; font-size: 10px;">Client / Manager:</p>
                    <p style="font-size: 9px;">Name: _________________ Signature: _________________</p>
                    <p style="font-size: 9px; margin-top: 2px;">Date: ____/____/______</p>
                </div>
            </div>

            <div style="margin-top: 4px; padding: 4px; background: #e8f5e9; border-radius: 2px; border-left: 2px solid #28a745;">
                <p style="font-weight: bold; color: #1b5e20; font-size: 9px;">ACKNOWLEDGEMENT OF KITCHEN CONDITION & SERVICE COMPLETED</p>
                <p style="font-size: 8px; color: #333; margin-top: 2px;">By signing above, the customer acknowledges that the service was completed and the kitchen was left clean and in satisfactory condition.</p>
            </div>
        </div>
    </div>

    <?php
    // Section 7 - Products (only shown if product data is provided)
    $products_list = [
        ['image' => 'Downblast HVAC Exhaust Fans.png', 'name' => 'Downblast HVAC Exhaust Fans'],
        ['image' => 'Driploc Grease Containment.png', 'name' => 'DripLoc Grease Containment'],
        ['image' => 'Exhaust Fan Grease Box.png', 'name' => 'Exhaust Fan Grease Box'],
        ['image' => 'Food Truck Exhaust Fans.png', 'name' => 'Food Truck Exhaust Fans'],
        ['image' => 'Grease Catcher.png', 'name' => 'Grease Catcher'],
        ['image' => 'Grease containment ring.png', 'name' => 'Grease Containment Ring'],
        ['image' => 'Hood Filters with Bottom Hooks – All Brands.png', 'name' => 'Hood Filters with Bottom Hooks – All Brands'],
        ['image' => 'Kason Welded Grease Filters.png', 'name' => 'Kason Welded Grease Filters'],
        ['image' => 'Mavrik Stainless Steel Hood Filters.jpg', 'name' => 'Mavrik Stainless Steel Hood Filters'],
        ['image' => 'Replacement Grease Pillows.png', 'name' => 'Replacement Grease Pillows'],
        ['image' => 'Restaurant Upblast Exhaust.png', 'name' => 'Restaurant Upblast Exhaust'],
        ['image' => 'Roof Curbs.png', 'name' => 'Roof Curbs'],
        ['image' => 'Spark Arrestor Hood Filters.png', 'name' => 'Spark Arrestor Hood Filters'],
        ['image' => 'Standard Aluminum Grease Filters.png', 'name' => 'Standard Aluminum Grease Filters'],
        ['image' => 'Standard Galvanized Grease Filters.png', 'name' => 'Standard Galvanized Grease Filters'],
    ];

    $show_products = isset($data['show_products']) && $data['show_products'];
    if ($show_products):
    ?>
    <!-- PAGE BREAK for Section 7 -->
    <div class="page-break"></div>

    <!-- 7. ACCEPTANCE OF REPAIR PARTS AND AUTHORIZATION -->
    <div class="section">
        <div class="section-header">7. ACCEPTANCE OF REPAIR PARTS AND AUTHORIZATION</div>
        <div class="section-content">
            <p style="font-size: 9px; color: #555; margin-bottom: 4px;">If additional parts or products are needed, please indicate the quantity required for each item below.</p>
            <?php
            // Filter only products that have a quantity
            $selected_products = [];
            foreach ($products_list as $j => $prod) {
                $qty_key = 'product_qty_' . $j;
                $qty_val = $data[$qty_key] ?? '';
                if (!empty($qty_val) && $qty_val != '0') {
                    $selected_products[] = ['index' => $j, 'product' => $prod, 'qty' => $qty_val];
                }
            }

            if (!empty($selected_products)):
            ?>
            <table class="products-table">
                <?php
                $cols = 3;
                $total_sel = count($selected_products);
                for ($i = 0; $i < $total_sel; $i += $cols):
                ?>
                <tr>
                    <?php for ($j = $i; $j < $i + $cols && $j < $total_sel; $j++):
                        $prod = $selected_products[$j]['product'];
                        $qty_val = $selected_products[$j]['qty'];
                        $img_path = __DIR__ . '/../../../Images/hoodvent/' . $prod['image'];
                        $ext = pathinfo($prod['image'], PATHINFO_EXTENSION);
                        $mime = ($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg' : 'image/png';
                        $img_b64 = '';
                        if (file_exists($img_path)) {
                            $img_b64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($img_path));
                        }
                    ?>
                    <td style="width: 33.33%;">
                        <?php if ($img_b64): ?>
                            <img src="<?php echo $img_b64; ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                        <?php endif; ?>
                        <div class="product-name-cell"><?php echo htmlspecialchars($prod['name']); ?></div>
                        <div class="product-qty-cell">Qty: <?php echo htmlspecialchars($qty_val); ?></div>
                    </td>
                    <?php endfor; ?>
                    <?php
                    $remaining = ($i + $cols) - $total_sel;
                    if ($remaining > 0 && $i + $cols > $total_sel):
                        for ($k = 0; $k < $remaining; $k++):
                    ?>
                    <td style="width: 33.33%;"></td>
                    <?php
                        endfor;
                    endif;
                    ?>
                </tr>
                <?php endfor; ?>
            </table>
            <?php else: ?>
            <p style="font-size: 8px; color: #888; font-style: italic;">No products selected.</p>
            <?php endif; ?>

            <div class="authorization-block">
                <p>By signing below, the Customer acknowledges and agrees to the repair parts listed above and the recommended repairs described in the Technician Notes/Observations. The Customer authorizes Prime to proceed with the described repairs and installations.</p>
            </div>

            <table class="authorization-sig-table">
                <tr>
                    <td>
                        <span class="sig-label">Client / Manager:</span>
                        <span>Name: <?php echo htmlspecialchars($data['auth_client_name'] ?? '________________'); ?></span>
                    </td>
                    <td>
                        <span class="sig-label">Signature:</span>
                        <?php if (!empty($data['auth_client_signature'])): ?>
                            <img src="<?php echo $data['auth_client_signature']; ?>" style="max-height: 25px;">
                        <?php else: ?>
                            <div class="sig-line"></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="sig-label">Date:</span>
                        <span><?php echo htmlspecialchars($data['auth_client_date'] ?? '____/____/______'); ?></span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php endif; ?>


</body>
</html>
