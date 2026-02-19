<?php
/**
 * UNIVERSAL SERVICE REPORT TEMPLATE
 * ==================================
 * Single dynamic layout that renders ANY service type.
 * All content is loaded from the service_report_config.php configuration.
 *
 * Required variables (passed by the controller):
 *   $serviceConfig  - array with keys: title, scope_of_work, initial_condition, etc.
 *   $data           - array with form/client data (Company_Name, docnum, etc.)
 *
 * NO hardcoded checklists. NO Photo Documentation.
 * The document title changes dynamically based on $serviceConfig['title'].
 */

// Company info
$company_name    = "PRIME FACILITY SERVICES GROUP";
$company_address = "8303 Westglen Dr ~ Houston, TX 77063";
$company_phone   = "713-338-2553";
$company_fax     = "713-574-3065";
$company_website = "www.primefacilityservicesgroup.com";

// Dynamic logo based on Service_Type
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

// Dynamic title from configuration
$report_title = strtoupper($serviceConfig['title'] ?? 'SERVICE REPORT');

// Client info from data
$client_name    = $data['Company_Name'] ?? '';
$client_address = $data['Company_Address'] ?? '';
$client_contact = $data['Client_Name'] ?? '';
$client_phone   = $data['Number_Phone'] ?? '';
$client_email   = $data['Email'] ?? '';

// Section data from configuration
$scopeItems          = $serviceConfig['scope_of_work'] ?? [];
$initialCondHeader   = $serviceConfig['initial_condition_header'] ?? 'BEFORE SERVICE';
$initialCondItems    = $serviceConfig['initial_condition'] ?? [];
$servicePerfHeader   = $serviceConfig['service_performed_header'] ?? 'SERVICE PERFORMED';
$servicePerfItems    = $serviceConfig['service_performed'] ?? [];
$postServiceHeader   = $serviceConfig['post_service_header'] ?? 'POST-SERVICE CONDITION';
$postServiceItems    = $serviceConfig['post_service_condition'] ?? [];
$technicalDataFields = $serviceConfig['technical_data'] ?? [];

// Helper: split array into two balanced columns
function splitIntoColumns(array $items): array {
    $mid = (int) ceil(count($items) / 2);
    return [array_slice($items, 0, $mid), array_slice($items, $mid)];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($serviceConfig['title'] ?? 'Service Report'); ?> - <?php echo htmlspecialchars($client_name); ?></title>
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

        /* ============================================================
           HEADER - position:fixed makes DomPDF repeat on every page
           ============================================================ */
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

        /* ============================================================
           FOOTER - position:fixed makes DomPDF repeat on every page
           ============================================================ */
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

        /* ============================================================
           TITLE
           ============================================================ */
        .report-title {
            font-size: 13px;
            font-weight: bold;
            color: #001f54;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            margin-bottom: 4px;
        }

        /* ============================================================
           SECTIONS
           ============================================================ */
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

        /* ============================================================
           INFO GRID (table-based for DomPDF)
           ============================================================ */
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

        /* ============================================================
           TWO-COLUMN LAYOUT
           ============================================================ */
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

        /* ============================================================
           CHECKBOXES
           ============================================================ */
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

        .inline-checkbox {
            display: inline-block;
            margin-right: 10px;
            font-size: 10px;
        }

        /* ============================================================
           CHECKLIST TABLE (Yes/No/N/A/Comment)
           ============================================================ */
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

        .table-subheader {
            background: #d0e4f7;
            font-weight: bold;
            color: #001f54;
            text-align: left;
        }

        /* ============================================================
           TECHNICAL DATA
           ============================================================ */
        .tech-data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .tech-data-table td {
            padding: 3px 6px;
            font-size: 10px;
            border-bottom: 1px solid #eee;
            vertical-align: bottom;
        }

        .tech-data-label {
            font-weight: bold;
            color: #001f54;
            width: 40%;
        }

        .tech-data-value {
            border-bottom: 1px dotted #999;
            color: #333;
        }

        /* ============================================================
           NOTES & SIGNATURES
           ============================================================ */
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

        .acknowledgement-block {
            margin-top: 4px;
            padding: 4px;
            background: #e8f5e9;
            border-radius: 2px;
            border-left: 2px solid #28a745;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    <!-- ================================================================
         HEADER (repeats on every page via position:fixed in DomPDF)
         ================================================================ -->
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

    <!-- ================================================================
         FOOTER (repeats on every page via position:fixed in DomPDF)
         ================================================================ -->
    <div class="footer-wrapper">
        <div class="footer-top">
            PRIME FACILITY SERVICES GROUP, INC.
        </div>
        <div class="footer-bottom">
            <strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>
            <a href="http://www.primefacilityservicesgroup.com">www.primefacilityservicesgroup.com</a>
        </div>
    </div>

    <!-- ================================================================
         DYNAMIC REPORT TITLE
         ================================================================ -->
    <div class="report-title"><?php echo htmlspecialchars($report_title); ?></div>

    <!-- ================================================================
         SECTION: SERVICE REPORT / WORK ORDER
         ================================================================ -->
    <div class="section">
        <div class="section-header">SERVICE REPORT / WORK ORDER</div>
        <div class="section-content">
            <div class="two-columns">
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Work Order #:</div>
                            <div class="info-cell info-value"><?php echo htmlspecialchars($data['docnum'] ?? ''); ?></div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Service Date:</div>
                            <div class="info-cell info-value">____ / ____ / ______</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Next Recommended Date:</div>
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

    <!-- ================================================================
         SECTION 1: CLIENT INFORMATION
         ================================================================ -->
    <div class="section">
        <div class="section-header">1. CLIENT INFORMATION</div>
        <div class="section-content">
            <div class="two-columns">
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Client / Company:</div>
                            <div class="info-cell info-value"><?php echo htmlspecialchars($client_name); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Address:</div>
                            <div class="info-cell info-value"><?php echo htmlspecialchars($client_address); ?></div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Contact:</div>
                            <div class="info-cell info-value"><?php echo htmlspecialchars($client_contact); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Phone:</div>
                            <div class="info-cell info-value"><?php echo htmlspecialchars($client_phone); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Email:</div>
                            <div class="info-cell info-value"><?php echo htmlspecialchars($client_email); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================
         SECTION 2: SYSTEM / AREA SERVICED (dynamic from config)
         ================================================================ -->
    <div class="section">
        <div class="section-header">2. SYSTEM / AREA SERVICED</div>
        <div class="section-content">
            <?php
            list($col1, $col2) = splitIntoColumns($scopeItems);
            ?>
            <div class="two-columns">
                <div class="column">
                    <?php foreach ($col1 as $item): ?>
                        <div class="checkbox-item"><?php echo htmlspecialchars($item); ?></div>
                    <?php endforeach; ?>
                </div>
                <div class="column">
                    <?php foreach ($col2 as $item): ?>
                        <div class="checkbox-item"><?php echo htmlspecialchars($item); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================
         SECTION 3: INITIAL CONDITION / INSPECTION (dynamic from config)
         ================================================================ -->
    <div class="section">
        <div class="section-header">3. INITIAL CONDITION / INSPECTION</div>
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
                    <tr>
                        <td colspan="5" class="table-subheader"><?php echo htmlspecialchars($initialCondHeader); ?></td>
                    </tr>
                    <?php foreach ($initialCondItems as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item); ?></td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="comment-cell"></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================================================================
         SECTION 4: SERVICE PERFORMED (dynamic from config)
         ================================================================ -->
    <div class="section">
        <div class="section-header">4. <?php echo htmlspecialchars($servicePerfHeader); ?></div>
        <div class="section-content">
            <?php
            list($perfCol1, $perfCol2) = splitIntoColumns($servicePerfItems);
            ?>
            <div class="two-columns">
                <div class="column">
                    <?php foreach ($perfCol1 as $item): ?>
                        <div class="checkbox-item"><?php echo htmlspecialchars($item); ?></div>
                    <?php endforeach; ?>
                </div>
                <div class="column">
                    <?php foreach ($perfCol2 as $item): ?>
                        <div class="checkbox-item"><?php echo htmlspecialchars($item); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================
         SECTION 5: POST-SERVICE CONDITION (dynamic from config)
         ================================================================ -->
    <div class="section">
        <div class="section-header">5. <?php echo htmlspecialchars($postServiceHeader); ?></div>
        <div class="section-content">
            <?php
            list($postCol1, $postCol2) = splitIntoColumns($postServiceItems);
            ?>
            <div class="two-columns">
                <div class="column">
                    <?php foreach ($postCol1 as $item): ?>
                        <div class="checkbox-item"><?php echo htmlspecialchars($item); ?></div>
                    <?php endforeach; ?>
                </div>
                <div class="column">
                    <?php foreach ($postCol2 as $item): ?>
                        <div class="checkbox-item"><?php echo htmlspecialchars($item); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================
         SECTION 6: TECHNICAL DATA (dynamic from config)
         ================================================================ -->
    <?php if (!empty($technicalDataFields)): ?>
    <div class="section">
        <div class="section-header">6. TECHNICAL DATA (If Applicable)</div>
        <div class="section-content">
            <table class="tech-data-table">
                <?php foreach ($technicalDataFields as $field): ?>
                <tr>
                    <td class="tech-data-label"><?php echo htmlspecialchars($field['label']); ?>:</td>
                    <td class="tech-data-value">
                        <?php if ($field['type'] === 'number'): ?>
                            ______
                        <?php else: ?>
                            __________________
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- ================================================================
         SECTION 7: TECHNICIAN NOTES & SIGNATURES
         ================================================================ -->
    <div class="section">
        <div class="section-header">7. TECHNICIAN NOTES & SIGNATURES</div>
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

            <div class="acknowledgement-block">
                <p style="font-weight: bold; color: #1b5e20; font-size: 9px;">ACKNOWLEDGEMENT OF SERVICE COMPLETED</p>
                <p style="font-size: 8px; color: #333; margin-top: 2px;">By signing above, the customer acknowledges that the service was completed and the area was left clean and in satisfactory condition.</p>
            </div>
        </div>
    </div>

</body>
</html>
