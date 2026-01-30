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
$client_contact = $data['client_name'] ?? '';
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
            border-bottom: 3px solid #001f54;
            padding-bottom: 8px;
            margin-bottom: 10px;
            text-align: center;
        }

        .company-logo {
            max-width: 200px;
            max-height: 100px;
            margin-bottom: 8px;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #001f54;
            margin-bottom: 3px;
        }

        .company-info {
            font-size: 8px;
            color: #666;
            line-height: 1.3;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #001f54;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .report-subtitle {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }

        .section {
            margin-bottom: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .section-header {
            background: linear-gradient(135deg, #001f54 0%, #003080 100%);
            color: white;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 11px;
        }

        .section-content {
            padding: 8px;
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
            padding: 4px 8px;
            border-bottom: 1px solid #eee;
        }

        .info-label {
            font-weight: bold;
            color: #001f54;
            width: 40%;
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
            padding-right: 10px;
        }

        .column:last-child {
            padding-right: 0;
            padding-left: 10px;
        }

        .checkbox-group {
            margin: 5px 0;
        }

        .checkbox-item {
            display: block;
            margin: 3px 0;
            padding-left: 15px;
            position: relative;
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
            margin: 5px 0;
            page-break-inside: avoid;
        }

        .checklist-table th,
        .checklist-table td {
            border: 1px solid #ddd;
            padding: 3px 6px;
            text-align: left;
        }

        .checklist-table th {
            background: #e8e8e8;
            font-weight: bold;
            color: #001f54;
        }

        .checklist-table td.center {
            text-align: center;
            width: 40px;
        }

        .frequency-options {
            display: table;
            width: 100%;
        }

        .freq-option {
            display: table-cell;
            padding: 3px 8px;
            text-align: center;
        }

        .notes-area {
            min-height: 50px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px;
            background: white;
            margin-top: 5px;
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
            padding: 10px;
            vertical-align: top;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            height: 25px;
            margin-bottom: 3px;
        }

        .signature-label {
            font-size: 9px;
            color: #666;
        }

        .page-break {
            page-break-before: always;
        }

        .footer {
            position: fixed;
            bottom: 10px;
            left: 15px;
            right: 15px;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        .sub-section {
            margin: 8px 0;
            padding: 8px;
            background: white;
            border: 1px solid #eee;
            border-radius: 4px;
            page-break-inside: avoid;
        }

        .sub-section-title {
            font-weight: bold;
            color: #001f54;
            font-size: 10px;
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 1px solid #eee;
        }

        .inline-checkbox {
            display: inline-block;
            margin-right: 15px;
        }

        .photos-grid {
            display: table;
            width: 100%;
        }

        .photo-item {
            display: table-cell;
            width: 25%;
            padding: 5px;
            text-align: center;
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
            <?php echo htmlspecialchars($company_address); ?><br>
            Phone: <?php echo htmlspecialchars($company_phone); ?> ~ Fax: <?php echo htmlspecialchars($company_fax); ?><br>
            <?php echo htmlspecialchars($company_website); ?>
        </div>
    </div>

    <div style="text-align: center; margin-bottom: 15px;">
        <div class="report-title" style="font-size: 22px;">KITCHEN EXHAUST CLEANING AND GREASE GUTTER SERVICE REPORT</div>
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
                        <div class="info-row">
                            <div class="info-cell info-label">Invoice #:</div>
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
            <div style="margin-top: 10px;">
                <span style="font-weight: bold; color: #001f54;">Frecuency:</span>
                <span class="inline-checkbox">&square; 30 days</span>
                <span class="inline-checkbox">&square; 60 days</span>
                <span class="inline-checkbox">&square; 90 days</span>
                <span class="inline-checkbox">&square; 120 days</span>
                <span class="inline-checkbox">&square; Other: ______</span>
            </div>
        </div>
    </div>


    <!-- 2. INFORMACION DEL CLIENTE -->
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

    <!-- 3. SISTEMA SERVICIADO -->
    <div class="section">
        <div class="section-header">3. SYSTEM SERVICED</div>
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

    <!-- 4. INSPECTION CHECKLIST -->
    <div class="section">
        <div class="section-header">4. INSPECTION CHECKLIST</div>
        <div class="section-content">
            <table class="checklist-table">
                <thead>
                    <tr>
                        <th>Element</th>
                        <th class="center">Yes</th>
                        <th class="center">No</th>
                        <th class="center">N/A</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Before Cleaning -->
                    <tr style="background: #e3f2fd;">
                        <td colspan="4" style="font-weight: bold; color: #001f54;">Before Cleaning</td>
                    </tr>
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
                    <!-- After Cleaning -->
                    <tr style="background: #e8f5e9;">
                        <td colspan="4" style="font-weight: bold; color: #001f54;">After Cleaning</td>
                    </tr>
                    <tr>
                        <td>System clean and operative</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Fan working at completion</td>
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
                    <tr>
                        <td>Client informed of final status</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <!-- Roof Inspection -->
                    <tr style="background: #fff3e0;">
                        <td colspan="4" style="font-weight: bold; color: #001f54;">Roof Inspection</td>
                    </tr>
                    <tr>
                        <td>Grease accumulation on roof?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Is it a severe problem?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Absorption unit installation recommended?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Roof damage from grease?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Adequate drainage exists?</td>
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

    <!-- 6. DATOS TECNICOS DEL SISTEMA -->
    <div class="section">
        <div class="section-header">6. TECHNICAL SYSTEM DATA</div>
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

    <!-- 7. NOTAS DEL TECNICO / OBSERVACIONES -->
    <div class="section">
        <div class="section-header">7. TECHNICIAN NOTES / OBSERVATIONS</div>
        <div class="section-content">
            <div class="notes-area" style="min-height: 40px;">

            </div>
        </div>
    </div>

    <!-- 8. FIRMAS Y CONFIRMACION -->
    <div class="section">
        <div class="section-header">8. SIGNATURES AND CONFIRMATION</div>
        <div class="section-content">
            <div class="signature-section">
                <div class="signature-box">
                    <p style="font-weight: bold; margin-bottom: 10px; color: #001f54;">Responsible Technician:</p>
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Name:</div>
                            <div class="info-cell info-value">_________________________</div>
                        </div>
                    </div>
                    <p style="margin-top: 15px;">Signature:</p>
                    <div class="signature-line"></div>
                    <p style="margin-top: 10px;">Date: ____/____/______</p>
                </div>
                <div class="signature-box">
                    <p style="font-weight: bold; margin-bottom: 10px; color: #001f54;">Client / Manager (Acknowledgement):</p>
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Name:</div>
                            <div class="info-cell info-value">_________________________</div>
                        </div>
                    </div>
                    <p style="margin-top: 15px;">Signature:</p>
                    <div class="signature-line"></div>
                    <p style="margin-top: 10px;">Date: ____/____/______</p>
                </div>
            </div>

            <div style="margin-top: 15px; padding: 10px; background: #e8f5e9; border-radius: 5px; border-left: 4px solid #28a745;">
                <p style="font-weight: bold; color: #1b5e20; font-size: 10px;">ACKNOWLEDGEMENT OF KITCHEN CONDITION & SERVICE COMPLETED</p>
                <p style="font-size: 9px; color: #333; margin-top: 5px;">
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
