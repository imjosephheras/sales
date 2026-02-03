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
            margin: 15mm 12mm;
            size: letter;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            padding: 0;
        }

        .header {
            width: 100%;
            border-bottom: 3px solid #001f54;
            padding-bottom: 10px;
            margin-bottom: 12px;
            text-align: center;
        }

        .company-logo {
            max-width: 180px;
            max-height: 70px;
            margin-bottom: 8px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #001f54;
            margin-bottom: 4px;
        }

        .company-info {
            font-size: 10px;
            color: #666;
            line-height: 1.4;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #001f54;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-subtitle {
            font-size: 11px;
            color: #666;
            margin-top: 4px;
        }

        .section {
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .section-header {
            background: #001f54;
            color: #ffffff;
            padding: 6px 12px;
            font-weight: bold;
            font-size: 11px;
        }

        .section-content {
            padding: 10px 12px;
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
            padding-right: 15px;
        }

        .column:last-child {
            padding-right: 0;
            padding-left: 15px;
        }

        .checkbox-group {
            margin: 3px 0;
        }

        .checkbox-item {
            display: block;
            margin: 4px 0;
            padding-left: 18px;
            position: relative;
            font-size: 10px;
        }

        .checkbox-item:before {
            content: "\2610";
            position: absolute;
            left: 0;
            font-size: 14px;
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
            padding: 5px 8px;
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
            width: 45px;
        }

        .frequency-options {
            display: table;
            width: 100%;
        }

        .freq-option {
            display: table-cell;
            padding: 4px 10px;
            text-align: center;
        }

        .notes-area {
            min-height: 40px;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 6px;
            background: white;
            margin-top: 4px;
        }

        .signature-section {
            display: table;
            width: 100%;
            margin-top: 10px;
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
            margin-bottom: 4px;
        }

        .signature-label {
            font-size: 10px;
            color: #666;
        }

        .page-break {
            page-break-before: always;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        .sub-section {
            margin: 6px 0;
            padding: 6px;
            background: white;
            border: 1px solid #eee;
            border-radius: 3px;
            page-break-inside: avoid;
        }

        .sub-section-title {
            font-weight: bold;
            color: #001f54;
            font-size: 11px;
            margin-bottom: 4px;
            padding-bottom: 3px;
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
            margin-right: 15px;
            font-size: 10px;
        }

        .photos-grid {
            display: table;
            width: 100%;
        }

        .photo-item {
            display: table-cell;
            width: 25%;
            padding: 8px;
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

    <div style="text-align: center; margin-bottom: 12px;">
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
            <div style="margin-top: 8px;">
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
                    </tr>
                </thead>
                <tbody>
                    <!-- BEFORE CLEANING -->
                    <tr><td colspan="4" class="table-subheader">BEFORE CLEANING</td></tr>
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
                    <!-- AFTER CLEANING -->
                    <tr><td colspan="4" class="table-subheader">AFTER CLEANING</td></tr>
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
                    <!-- ROOF INSPECTION -->
                    <tr><td colspan="4" class="table-subheader">ROOF INSPECTION</td></tr>
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
                        <td>Is there proper drainage?</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
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
            <p style="font-weight: bold; color: #001f54; font-size: 10px; margin-bottom: 4px;">Notes / Observations:</p>
            <div class="notes-area"></div>

            <div class="signature-section">
                <div class="signature-box">
                    <p style="font-weight: bold; margin-bottom: 4px; color: #001f54; font-size: 10px;">Responsible Technician:</p>
                    <p style="font-size: 10px;">Name: _________________ Signature: _________________</p>
                    <p style="font-size: 10px; margin-top: 4px;">Date: ____/____/______</p>
                </div>
                <div class="signature-box">
                    <p style="font-weight: bold; margin-bottom: 4px; color: #001f54; font-size: 10px;">Client / Manager:</p>
                    <p style="font-size: 10px;">Name: _________________ Signature: _________________</p>
                    <p style="font-size: 10px; margin-top: 4px;">Date: ____/____/______</p>
                </div>
            </div>

            <div style="margin-top: 10px; padding: 8px; background: #e8f5e9; border-radius: 3px; border-left: 3px solid #28a745;">
                <p style="font-weight: bold; color: #1b5e20; font-size: 10px;">ACKNOWLEDGEMENT OF KITCHEN CONDITION & SERVICE COMPLETED</p>
                <p style="font-size: 9px; color: #333; margin-top: 4px;">By signing above, the customer acknowledges that the service was completed and the kitchen was left clean and in satisfactory condition.</p>
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
