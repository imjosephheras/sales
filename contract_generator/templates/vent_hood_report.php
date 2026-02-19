<?php
/**
 * UNIVERSAL SERVICE REPORT - PDF TEMPLATE
 * =========================================
 * Structural skeleton for PDF generation.
 * Contains 11 mandatory sections with empty placeholders
 * ready for dynamic configuration injection per service type.
 *
 * Sections:
 *  1. Service Report / Work Order
 *  2. Client Information
 *  3. Service Category
 *  4. Scope of Work
 *  5. Initial Condition / Inspection
 *  6. Service Performed
 *  7. Post-Service Condition
 *  8. Technical Findings & Observations
 *  9. Recommendations
 * 10. Parts / Materials Used or Required
 * 11. Client Acknowledgement & Signatures
 */

// Company info
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

// Data from request
$work_order = $data['docnum'] ?? '';
$client_name = $data['Company_Name'] ?? '';
$client_address = $data['Company_Address'] ?? '';
$client_contact = $data['Client_Name'] ?? '';
$client_email = $data['Email'] ?? '';
$client_phone = $data['Number_Phone'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Universal Service Report - <?php echo htmlspecialchars($client_name); ?></title>
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

        /* =============================================
           HEADER - fixed, repeats on every page
           ============================================= */
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

        /* =============================================
           FOOTER - fixed, repeats on every page
           ============================================= */
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

        /* =============================================
           SECTIONS
           ============================================= */
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

        /* =============================================
           INFO GRID
           ============================================= */
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

        /* =============================================
           LAYOUT HELPERS
           ============================================= */
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

        /* =============================================
           DYNAMIC CONTENT PLACEHOLDERS
           ============================================= */
        .dynamic-placeholder {
            min-height: 30px;
            border: 1px dashed #ccc;
            border-radius: 2px;
            padding: 4px 6px;
            background: #fff;
            color: #999;
            font-size: 9px;
            font-style: italic;
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

        /* =============================================
           NOTES AREA
           ============================================= */
        .notes-area {
            min-height: 30px;
            border: 1px solid #ddd;
            border-radius: 2px;
            padding: 3px;
            background: white;
            margin-top: 2px;
        }

        /* =============================================
           PARTS TABLE
           ============================================= */
        .parts-table {
            width: 100%;
            border-collapse: collapse;
        }

        .parts-table th,
        .parts-table td {
            border: 1px solid #ddd;
            padding: 3px 6px;
            font-size: 10px;
            text-align: left;
        }

        .parts-table th {
            background: #e8e8e8;
            font-weight: bold;
            color: #001f54;
        }

        /* =============================================
           SIGNATURES
           ============================================= */
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

        .acknowledgement-block {
            margin-top: 4px;
            padding: 4px 6px;
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
        <div class="report-title">UNIVERSAL SERVICE REPORT</div>
    </div>

    <!-- =============================================
         SECTION 1: SERVICE REPORT / WORK ORDER
         ============================================= -->
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
        </div>
    </div>

    <!-- =============================================
         SECTION 2: CLIENT INFORMATION
         ============================================= -->
    <div class="section">
        <div class="section-header">CLIENT INFORMATION</div>
        <div class="section-content">
            <div class="two-columns">
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Client Name:</div>
                            <div class="info-cell info-value">__________________________</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Address:</div>
                            <div class="info-cell info-value">__________________________</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Contact Person:</div>
                            <div class="info-cell info-value">__________________________</div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Phone:</div>
                            <div class="info-cell info-value">__________________________</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Email:</div>
                            <div class="info-cell info-value">__________________________</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =============================================
         SECTION 3: SERVICE CATEGORY
         ============================================= -->
    <div class="section">
        <div class="section-header">SERVICE CATEGORY</div>
        <div class="section-content">
            <div class="dynamic-placeholder" id="service-category-container">
                <!-- Dynamic: Service type options will be injected here per configuration -->
            </div>
        </div>
    </div>

    <!-- =============================================
         SECTION 4: SCOPE OF WORK
         ============================================= -->
    <div class="section">
        <div class="section-header">SCOPE OF WORK</div>
        <div class="section-content">
            <div class="dynamic-placeholder" id="scope-of-work-container">
                <!-- Dynamic: Scope items will be injected here per configuration -->
            </div>
        </div>
    </div>

    <!-- =============================================
         SECTION 5: INITIAL CONDITION / INSPECTION
         ============================================= -->
    <div class="section">
        <div class="section-header">INITIAL CONDITION / INSPECTION</div>
        <div class="section-content">
            <div class="dynamic-placeholder" id="initial-condition-container">
                <!-- Dynamic: Inspection checklist items will be injected here per configuration -->
            </div>
        </div>
    </div>

    <!-- =============================================
         SECTION 6: SERVICE PERFORMED
         ============================================= -->
    <div class="section">
        <div class="section-header">SERVICE PERFORMED</div>
        <div class="section-content">
            <div class="dynamic-placeholder" id="service-performed-container">
                <!-- Dynamic: Service tasks/checklist will be injected here per configuration -->
            </div>
        </div>
    </div>

    <!-- =============================================
         SECTION 7: POST-SERVICE CONDITION
         ============================================= -->
    <div class="section">
        <div class="section-header">POST-SERVICE CONDITION</div>
        <div class="section-content">
            <div class="dynamic-placeholder" id="post-service-condition-container">
                <!-- Dynamic: Post-service verification items will be injected here per configuration -->
            </div>
        </div>
    </div>

    <!-- =============================================
         SECTION 8: TECHNICAL FINDINGS & OBSERVATIONS
         ============================================= -->
    <div class="section">
        <div class="section-header">TECHNICAL FINDINGS &amp; OBSERVATIONS</div>
        <div class="section-content">
            <div class="notes-area" id="findings-container"></div>
        </div>
    </div>

    <!-- =============================================
         SECTION 9: RECOMMENDATIONS
         ============================================= -->
    <div class="section">
        <div class="section-header">RECOMMENDATIONS</div>
        <div class="section-content">
            <div class="notes-area" id="recommendations-container"></div>
        </div>
    </div>

    <!-- =============================================
         SECTION 10: PARTS / MATERIALS USED OR REQUIRED
         ============================================= -->
    <div class="section">
        <div class="section-header">PARTS / MATERIALS USED OR REQUIRED</div>
        <div class="section-content">
            <table class="parts-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody id="parts-table-body">
                    <!-- Dynamic: Parts/materials rows will be injected here per configuration -->
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- =============================================
         SECTION 11: CLIENT ACKNOWLEDGEMENT & SIGNATURES
         ============================================= -->
    <div class="section">
        <div class="section-header">CLIENT ACKNOWLEDGEMENT &amp; SIGNATURES</div>
        <div class="section-content">
            <div class="acknowledgement-block" style="margin-bottom: 6px;">
                <p style="font-weight: bold; color: #1b5e20; font-size: 9px;">SERVICE ACKNOWLEDGEMENT</p>
                <p style="font-size: 8px; color: #333; margin-top: 2px;">By signing below, the client acknowledges that the service described in this report has been completed and the work area was left in satisfactory condition.</p>
            </div>

            <div class="signature-section">
                <div class="signature-box">
                    <p style="font-weight: bold; margin-bottom: 2px; color: #001f54; font-size: 10px;">Technician:</p>
                    <p style="font-size: 9px;">Name: _________________________________</p>
                    <p style="font-size: 9px; margin-top: 2px;">Signature:</p>
                    <div class="signature-line"></div>
                    <p style="font-size: 9px; margin-top: 2px;">Date: ____/____/______</p>
                </div>
                <div class="signature-box">
                    <p style="font-weight: bold; margin-bottom: 2px; color: #001f54; font-size: 10px;">Client / Authorized Representative:</p>
                    <p style="font-size: 9px;">Name: _________________________________</p>
                    <p style="font-size: 9px; margin-top: 2px;">Signature:</p>
                    <div class="signature-line"></div>
                    <p style="font-size: 9px; margin-top: 2px;">Date: ____/____/______</p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
