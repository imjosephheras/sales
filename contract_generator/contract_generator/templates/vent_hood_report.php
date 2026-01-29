<?php
/**
 * VENT HOOD SERVICE REPORT TEMPLATE
 * Template for PDF generation of Vent Hood service reports
 * Optimized for 2-page layout
 */

// Company info - Fixed values
$company_name = "PRIME FACILITY SERVICES GROUP";
$company_address = "8303 Westglen Dr ~ Houston, TX 77063";
$company_phone = "713-338-2553";
$company_fax = "713-574-3065";
$company_website = "www.primefacilityservicesgroup.com";

// Logo path (base64 encode for PDF)
$logo_path = __DIR__ . '/../../../Images/Facility.png';
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #333;
            padding: 10px 15px;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #001f54;
            padding-bottom: 8px;
            margin-bottom: 10px;
            text-align: center;
        }

        .company-logo {
            max-width: 150px;
            max-height: 70px;
            margin-bottom: 5px;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #001f54;
            margin-bottom: 2px;
        }

        .company-info {
            font-size: 7px;
            color: #666;
            line-height: 1.2;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #001f54;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .section {
            margin-bottom: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .section-header {
            background: linear-gradient(135deg, #001f54 0%, #003080 100%);
            color: white;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 10px;
        }

        .section-content {
            padding: 6px 8px;
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
            padding: 2px 6px;
            border-bottom: 1px solid #eee;
        }

        .info-label {
            font-weight: bold;
            color: #001f54;
            width: 35%;
            font-size: 8px;
        }

        .info-value {
            color: #333;
            font-size: 8px;
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

        .checkbox-item {
            display: block;
            margin: 2px 0;
            padding-left: 12px;
            position: relative;
            font-size: 8px;
        }

        .checkbox-item:before {
            content: "\2610";
            position: absolute;
            left: 0;
            font-size: 10px;
        }

        .checklist-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0;
            page-break-inside: avoid;
        }

        .checklist-table th,
        .checklist-table td {
            border: 1px solid #ddd;
            padding: 3px 6px;
            text-align: left;
            font-size: 8px;
        }

        .checklist-table th {
            background: #e8e8e8;
            font-weight: bold;
            color: #001f54;
        }

        .checklist-table td.center {
            text-align: center;
            width: 30px;
        }

        .inline-checkbox {
            display: inline-block;
            margin-right: 10px;
            font-size: 8px;
        }

        .notes-area {
            min-height: 40px;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 5px;
            background: white;
            margin-top: 3px;
        }

        .signature-section {
            display: table;
            width: 100%;
            margin-top: 8px;
            page-break-inside: avoid;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            padding: 8px;
            vertical-align: top;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            height: 25px;
            margin-bottom: 3px;
        }

        .signature-label {
            font-size: 8px;
            color: #666;
        }

        .page-break {
            page-break-before: always;
        }

        .footer {
            position: fixed;
            bottom: 8px;
            left: 15px;
            right: 15px;
            text-align: center;
            font-size: 7px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 3px;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <?php if ($logo_base64): ?>
            <img src="<?php echo $logo_base64; ?>" class="company-logo" alt="Logo">
        <?php endif; ?>
        <div class="company-name"><?php echo htmlspecialchars($company_name); ?></div>
        <div class="company-info">
            <?php echo htmlspecialchars($company_address); ?> | Phone: <?php echo htmlspecialchars($company_phone); ?> | Fax: <?php echo htmlspecialchars($company_fax); ?>
        </div>
    </div>

    <div style="text-align: center; margin-bottom: 10px;">
        <div class="report-title">KITCHEN EXHAUST CLEANING AND GREASE GUTTER SERVICE REPORT</div>
    </div>

    <!-- 1. SERVICE REPORT / WORK ORDER INFO -->
    <div class="section">
        <div class="section-header">1. SERVICE REPORT / WORK ORDER</div>
        <div class="section-content">
            <div class="two-columns">
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Work Order #:</div>
                            <div class="info-cell info-value"><?php echo htmlspecialchars($work_order) ?: '______________'; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Invoice #:</div>
                            <div class="info-cell info-value">______________</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Service Date:</div>
                            <div class="info-cell info-value">____ / ____ / ______</div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Next Service Date:</div>
                            <div class="info-cell info-value">____ / ____ / ______</div>
                        </div>
                    </div>
                    <div style="margin-top: 5px;">
                        <span style="font-weight: bold; color: #001f54; font-size: 8px;">Frequency:</span>
                        <span class="inline-checkbox">&square; 30d</span>
                        <span class="inline-checkbox">&square; 60d</span>
                        <span class="inline-checkbox">&square; 90d</span>
                        <span class="inline-checkbox">&square; 120d</span>
                        <span class="inline-checkbox">&square; Other: ____</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. CLIENT INFORMATION -->
    <div class="section">
        <div class="section-header">2. CLIENT INFORMATION</div>
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
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Contact Person:</div>
                            <div class="info-cell info-value"><?php echo htmlspecialchars($client_contact); ?></div>
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

    <!-- 3. SYSTEM SERVICED -->
    <div class="section">
        <div class="section-header">3. SYSTEM SERVICED</div>
        <div class="section-content">
            <div class="two-columns">
                <div class="column">
                    <div class="checkbox-item">Main Hood (Campana principal)</div>
                    <div class="checkbox-item">Extraction Ducts (Ductos de extraccion)</div>
                    <div class="checkbox-item">Roof Fan (Ventilador en techo)</div>
                </div>
                <div class="column">
                    <div class="checkbox-item">Grease Gutter</div>
                    <div class="checkbox-item">Fire System (inspection only)</div>
                    <div class="checkbox-item">Other: ___________________</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. UNIFIED INSPECTION CHECKLIST (Before & After Cleaning) -->
    <div class="section">
        <div class="section-header">4. INSPECTION CHECKLIST</div>
        <div class="section-content">
            <table class="checklist-table">
                <thead>
                    <tr>
                        <th>Element</th>
                        <th class="center">Before</th>
                        <th class="center">After</th>
                        <th class="center">N/A</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Fans working correctly?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Filters with grease accumulation?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Hood lights working?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Visible grease in ducts?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Grease container present?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Visible damage in system?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>System clean and operative</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Work area delivered clean</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. SERVICE PERFORMED (CLEANING) -->
    <div class="section">
        <div class="section-header">5. SERVICE PERFORMED (CLEANING)</div>
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

    <!-- 6. ROOF INSPECTION -->
    <div class="section">
        <div class="section-header">6. ROOF INSPECTION</div>
        <div class="section-content">
            <table class="checklist-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="center">Yes</th>
                        <th class="center">No</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Grease accumulation on roof?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Is it a severe problem?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Absorption unit installation recommended?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Roof damage from grease?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Adequate drainage exists?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 7. TECHNICAL SYSTEM DATA -->
    <div class="section">
        <div class="section-header">7. TECHNICAL SYSTEM DATA</div>
        <div class="section-content">
            <div class="two-columns">
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Number of Fans:</div>
                            <div class="info-cell info-value">________</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Number of Stacks:</div>
                            <div class="info-cell info-value">________</div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div style="font-weight: bold; color: #001f54; font-size: 8px; margin-bottom: 3px;">Fan Type:</div>
                    <span class="inline-checkbox">&square; Marshall</span>
                    <span class="inline-checkbox">&square; Upblast</span>
                    <span class="inline-checkbox">&square; Supreme</span>
                    <span class="inline-checkbox">&square; Other: ____</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 8. TECHNICIAN NOTES / OBSERVATIONS -->
    <div class="section">
        <div class="section-header">8. TECHNICIAN NOTES / OBSERVATIONS</div>
        <div class="section-content">
            <div class="notes-area"></div>
        </div>
    </div>

    <!-- 9. SIGNATURES AND CONFIRMATION -->
    <div class="section">
        <div class="section-header">9. SIGNATURES AND CONFIRMATION</div>
        <div class="section-content">
            <div class="signature-section">
                <div class="signature-box">
                    <p style="font-weight: bold; margin-bottom: 5px; color: #001f54; font-size: 9px;">Responsible Technician:</p>
                    <p style="font-size: 8px;">Name: _________________________</p>
                    <p style="margin-top: 8px; font-size: 8px;">Signature:</p>
                    <div class="signature-line"></div>
                    <p style="font-size: 8px;">Date: ____/____/______</p>
                </div>
                <div class="signature-box">
                    <p style="font-weight: bold; margin-bottom: 5px; color: #001f54; font-size: 9px;">Client / Manager:</p>
                    <p style="font-size: 8px;">Name: _________________________</p>
                    <p style="margin-top: 8px; font-size: 8px;">Signature:</p>
                    <div class="signature-line"></div>
                    <p style="font-size: 8px;">Date: ____/____/______</p>
                </div>
            </div>

            <div style="margin-top: 8px; padding: 6px; background: #e8f5e9; border-radius: 4px; border-left: 3px solid #28a745;">
                <p style="font-weight: bold; color: #1b5e20; font-size: 8px;">ACKNOWLEDGEMENT OF KITCHEN CONDITION & SERVICE COMPLETED</p>
                <p style="font-size: 7px; color: #333; margin-top: 3px;">
                    By signing below, the customer acknowledges that the service was completed and the kitchen was left clean and in satisfactory condition.
                </p>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <p><?php echo htmlspecialchars($company_name); ?> | <?php echo htmlspecialchars($company_address); ?> | <?php echo htmlspecialchars($company_phone); ?></p>
        <p>This document is confidential and intended solely for the addressee. &copy; <?php echo date('Y'); ?></p>
    </div>

</body>
</html>
