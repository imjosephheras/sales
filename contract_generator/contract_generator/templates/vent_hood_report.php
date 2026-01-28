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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            padding: 15px;
        }

        .header {
            display: table;
            width: 100%;
            border-bottom: 3px solid #001f54;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header-left {
            display: table-cell;
            width: 30%;
            vertical-align: middle;
        }

        .header-center {
            display: table-cell;
            width: 40%;
            text-align: center;
            vertical-align: middle;
        }

        .header-right {
            display: table-cell;
            width: 30%;
            text-align: right;
            vertical-align: middle;
        }

        .company-logo {
            max-width: 120px;
            max-height: 60px;
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
            margin-bottom: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }

        .section-header {
            background: linear-gradient(135deg, #001f54 0%, #003080 100%);
            color: white;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 11px;
        }

        .section-content {
            padding: 10px;
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
        }

        .checklist-table th,
        .checklist-table td {
            border: 1px solid #ddd;
            padding: 5px 8px;
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
            margin-top: 15px;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            padding: 10px;
            vertical-align: top;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            height: 30px;
            margin-bottom: 5px;
        }

        .signature-label {
            font-size: 9px;
            color: #666;
        }

        .time-section {
            display: table;
            width: 100%;
        }

        .time-box {
            display: table-cell;
            width: 33.33%;
            padding: 5px;
            text-align: center;
        }

        .time-value {
            font-size: 14px;
            font-weight: bold;
            color: #001f54;
            padding: 5px;
            border: 1px solid #ddd;
            background: white;
            min-height: 25px;
        }

        .time-label {
            font-size: 8px;
            color: #666;
            margin-top: 3px;
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

        .work-order-box {
            background: #001f54;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            display: inline-block;
        }

        .work-order-label {
            font-size: 8px;
            opacity: 0.8;
        }

        .work-order-number {
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            <?php if ($logo_base64): ?>
                <img src="<?php echo $logo_base64; ?>" class="company-logo" alt="Logo">
            <?php endif; ?>
        </div>
        <div class="header-center">
            <div class="company-name"><?php echo htmlspecialchars($company_name); ?></div>
            <div class="company-info">
                <?php echo htmlspecialchars($company_address); ?><br>
                Phone: <?php echo htmlspecialchars($company_phone); ?> ~ Fax: <?php echo htmlspecialchars($company_fax); ?><br>
                <?php echo htmlspecialchars($company_website); ?>
            </div>
        </div>
        <div class="header-right">
            <div class="work-order-box">
                <div class="work-order-label">WORK ORDER #</div>
                <div class="work-order-number"><?php echo htmlspecialchars($work_order); ?></div>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin-bottom: 15px;">
        <div class="report-title">SERVICE REPORT / WORK ORDER</div>
        <div class="report-subtitle">Kitchen Exhaust Cleaning and Grease Gutter Service Report</div>
    </div>

    <!-- 1. ENCABEZADO - INFORMACION DE SERVICIO -->
    <div class="section">
        <div class="section-header">1. SERVICE INFORMATION</div>
        <div class="section-content">
            <div class="two-columns">
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Work Order #:</div>
                            <div class="info-cell info-value"><?php echo htmlspecialchars($work_order); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Invoice #:</div>
                            <div class="info-cell info-value"><?php echo htmlspecialchars($invoice_number); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Service Date:</div>
                            <div class="info-cell info-value"><?php echo htmlspecialchars($service_date); ?></div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Next Service Date:</div>
                            <div class="info-cell info-value">____/____/______</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Frequency:</div>
                            <div class="info-cell info-value">
                                <span class="inline-checkbox">&square; 30 days</span>
                                <span class="inline-checkbox">&square; 60 days</span>
                                <span class="inline-checkbox">&square; 90 days</span>
                                <span class="inline-checkbox">&square; 120 days</span>
                                <span class="inline-checkbox">&square; Other: ____</span>
                            </div>
                        </div>
                    </div>
                </div>
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
                    <div class="checkbox-item">&square; Main Hood (Campana principal)</div>
                    <div class="checkbox-item">&square; Extraction Ducts (Ductos de extraccion)</div>
                    <div class="checkbox-item">&square; Roof Fan (Ventilador en techo)</div>
                </div>
                <div class="column">
                    <div class="checkbox-item">&square; Grease Gutter</div>
                    <div class="checkbox-item">&square; Fire System (inspection only)</div>
                    <div class="checkbox-item">&square; Other: _______________________</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. CHECKLIST DE INSPECCION (ANTES DE LIMPIEZA) -->
    <div class="section">
        <div class="section-header">4. INSPECTION CHECKLIST (BEFORE CLEANING)</div>
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
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. SERVICIO REALIZADO (LIMPIEZA) -->
    <div class="section">
        <div class="section-header">5. SERVICE PERFORMED (CLEANING)</div>
        <div class="section-content">
            <div class="two-columns">
                <div class="column">
                    <div class="checkbox-item">&square; Complete hood cleaning</div>
                    <div class="checkbox-item">&square; Filter cleaning</div>
                    <div class="checkbox-item">&square; Duct cleaning</div>
                    <div class="checkbox-item">&square; Extractor/fan cleaning</div>
                </div>
                <div class="column">
                    <div class="checkbox-item">&square; Grease gutter cleaning</div>
                    <div class="checkbox-item">&square; Kitchen area cleaning (affected)</div>
                    <div class="checkbox-item">&square; Sticker placed on site</div>
                    <div class="checkbox-item">&square; Before/after photos taken</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. RESULTADO DESPUES DE LIMPIEZA -->
    <div class="section">
        <div class="section-header">6. RESULT AFTER CLEANING</div>
        <div class="section-content">
            <table class="checklist-table">
                <thead>
                    <tr>
                        <th>Element</th>
                        <th class="center">Yes</th>
                        <th class="center">No</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>System clean and operative</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Fan working at completion</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Work area delivered clean</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                    <tr>
                        <td>Client informed of final status</td>
                        <td class="center">&square;</td>
                        <td class="center">&square;</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PAGE BREAK -->
    <div class="page-break"></div>

    <!-- 7. INSPECCION DE TECHO (ROOF INSPECTION) -->
    <div class="section">
        <div class="section-header">7. ROOF INSPECTION</div>
        <div class="section-content">

            <div class="sub-section">
                <div class="sub-section-title">7.1 Grease on Roof</div>
                <p>Is there grease accumulation on the roof? <span class="inline-checkbox">&square; Yes</span> <span class="inline-checkbox">&square; No</span></p>
                <p style="margin-top: 5px;">If yes, describe: _______________________________________________</p>
            </div>

            <div class="sub-section">
                <div class="sub-section-title">7.2 Problem Severity</div>
                <p>Is it a severe problem? <span class="inline-checkbox">&square; Yes</span> <span class="inline-checkbox">&square; No</span></p>
            </div>

            <div class="sub-section">
                <div class="sub-section-title">7.3 Roof Drainage</div>
                <p>Is there adequate drainage? <span class="inline-checkbox">&square; Yes</span> <span class="inline-checkbox">&square; No</span></p>
                <p style="margin-top: 5px;">If no, recommended corrections: _________________________________</p>
            </div>

            <div class="sub-section">
                <div class="sub-section-title">7.4 Grease Absorption Units</div>
                <p>Is there an installed system?</p>
                <div style="margin-top: 5px;">
                    <span class="inline-checkbox">&square; Rock Box</span>
                    <span class="inline-checkbox">&square; Grease Guard</span>
                    <span class="inline-checkbox">&square; Grease Gutter</span>
                    <span class="inline-checkbox">&square; Other: _________</span>
                    <span class="inline-checkbox">&square; None</span>
                </div>
            </div>

            <div class="sub-section">
                <div class="sub-section-title">7.5 Preventive Recommendation</div>
                <p>Is absorption unit installation recommended? <span class="inline-checkbox">&square; Yes</span> <span class="inline-checkbox">&square; No</span></p>
            </div>

            <div class="sub-section">
                <div class="sub-section-title">7.6 Existing Damage</div>
                <p>Is there roof damage from grease? <span class="inline-checkbox">&square; Yes</span> <span class="inline-checkbox">&square; No</span></p>
                <p style="margin-top: 5px;">If yes, describe and attach photos: ________________________________</p>
            </div>

        </div>
    </div>

    <!-- 8. DATOS TECNICOS DEL SISTEMA -->
    <div class="section">
        <div class="section-header">8. TECHNICAL SYSTEM DATA</div>
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

    <!-- 9. TIEMPOS DEL SERVICIO -->
    <div class="section">
        <div class="section-header">9. SERVICE TIMES</div>
        <div class="section-content">
            <div class="time-section">
                <div class="time-box">
                    <div class="time-value"></div>
                    <div class="time-label">TIME IN (Hora de entrada)</div>
                </div>
                <div class="time-box">
                    <div class="time-value"></div>
                    <div class="time-label">TIME OUT (Hora de salida)</div>
                </div>
                <div class="time-box">
                    <div class="time-value"></div>
                    <div class="time-label">TOTAL HOURS (Total horas)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 10. NOTAS DEL TECNICO / OBSERVACIONES -->
    <div class="section">
        <div class="section-header">10. TECHNICIAN NOTES / OBSERVATIONS</div>
        <div class="section-content">
            <div class="notes-area" style="min-height: 80px;">

            </div>
        </div>
    </div>

    <!-- 11. FOTOS -->
    <div class="section">
        <div class="section-header">11. PHOTOS</div>
        <div class="section-content">
            <div class="two-columns">
                <div class="column">
                    <div class="checkbox-item">&square; Before photos taken</div>
                    <div class="checkbox-item">&square; After photos taken</div>
                </div>
                <div class="column">
                    <div class="checkbox-item">&square; Roof photos taken</div>
                    <div class="checkbox-item">&square; Sticker placement photos taken</div>
                </div>
            </div>
            <p style="margin-top: 10px; font-size: 9px; color: #666; font-style: italic;">(Attach in PDF or folder)</p>
        </div>
    </div>

    <!-- 12. FIRMAS Y CONFIRMACION -->
    <div class="section">
        <div class="section-header">12. SIGNATURES AND CONFIRMATION</div>
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
