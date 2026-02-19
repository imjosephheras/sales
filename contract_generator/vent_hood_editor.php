<?php
/**
 * UNIVERSAL SERVICE REPORT - INTERACTIVE EDITOR / PREVIEWER
 * ==========================================================
 * Structural skeleton with 11 mandatory sections.
 * All sections contain empty placeholders ready for dynamic
 * configuration injection per service type.
 *
 * No service-specific content is hardcoded here.
 */

require_once 'config/db_config.php';

// Get request data if ID is provided
$data = [];
$request_id = $_GET['id'] ?? null;

if ($request_id) {
    $sql = "SELECT *, form_id AS id, company_name AS Company_Name, address AS Company_Address, client_name AS Client_Name, email AS Email, service_type AS Service_Type, phone AS Number_Phone FROM forms WHERE form_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $request_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

$client_name = $data['Company_Name'] ?? '';
$client_address = $data['Company_Address'] ?? '';
$client_contact = $data['Client_Name'] ?? '';
$client_email = $data['Email'] ?? '';
$client_phone = $data['Number_Phone'] ?? '';
$work_order = $data['docnum'] ?? '';

// Logo - dynamic based on Service_Type
$dept = strtolower(trim($data['Service_Type'] ?? ''));
if (strpos($dept, 'hospitality') !== false) {
    $logo_path = __DIR__ . '/../../Images/Hospitality.png';
} else {
    $logo_path = __DIR__ . '/../../Images/Facility.png';
}
$logo_base64 = '';
if (file_exists($logo_path)) {
    $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universal Service Report Editor</title>
    <style>
        /* =============================================
           RESET & BASE
           ============================================= */
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
            background: #525659;
        }

        /* =============================================
           TOOLBAR (hidden on print)
           ============================================= */
        .toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(135deg, #001f54 0%, #003080 100%);
            color: white;
            padding: 10px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        .toolbar-title {
            font-size: 15px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toolbar-actions {
            display: flex;
            gap: 10px;
        }

        .toolbar-btn {
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .toolbar-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .btn-print {
            background: #28a745;
            color: white;
        }

        .btn-clear {
            background: rgba(255,255,255,0.15);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .btn-download-pdf {
            background: #e65100;
            color: white;
        }

        /* =============================================
           PAGE CONTAINER
           ============================================= */
        .pages-container {
            padding: 60px 20px 20px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }

        .page {
            width: 8.5in;
            min-height: 11in;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            padding: 18mm 18mm 22mm 18mm;
            position: relative;
        }

        .page-label {
            position: absolute;
            top: -22px;
            left: 0;
            color: rgba(255,255,255,0.6);
            font-size: 12px;
            font-weight: bold;
        }

        /* =============================================
           HEADER
           ============================================= */
        .header {
            width: 100%;
            border-bottom: 2px solid #001f54;
            padding-bottom: 6px;
            margin-bottom: 6px;
            text-align: center;
        }

        .company-logo {
            max-width: 280px;
            max-height: 90px;
            margin-bottom: 6px;
        }

        .report-title {
            font-size: 15px;
            font-weight: bold;
            color: #001f54;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        /* =============================================
           SECTIONS
           ============================================= */
        .section {
            margin-bottom: 4px;
            border: 1px solid #ddd;
            border-radius: 2px;
            overflow: hidden;
        }

        .section-header {
            background: #001f54;
            color: #ffffff;
            padding: 3px 8px;
            font-weight: bold;
            font-size: 10px;
        }

        .section-content {
            padding: 5px 8px;
            background: #fafafa;
        }

        /* =============================================
           EDITABLE FIELDS
           ============================================= */
        .editable-field {
            border: none;
            border-bottom: 1px solid #aaa;
            background: transparent;
            font-family: inherit;
            font-size: 10px;
            color: #333;
            padding: 1px 4px;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }

        .editable-field:hover {
            background: #fffde7;
        }

        .editable-field:focus {
            border-bottom: 2px solid #001f54;
            background: #e8f0fe;
        }

        .editable-field.wide {
            width: 180px;
        }

        .editable-field.medium {
            width: 120px;
        }

        .editable-field.small {
            width: 60px;
        }

        .editable-field.full-width {
            width: 100%;
        }

        .editable-field.date-field {
            width: 110px;
            text-align: center;
        }

        .date-input-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            position: relative;
        }

        .date-input-wrapper input[type="date"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            pointer-events: none;
        }

        .calendar-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #f5f5f5;
            cursor: pointer;
            transition: all 0.2s;
            padding: 0;
            flex-shrink: 0;
        }

        .calendar-btn:hover {
            background: #e8f0fe;
            border-color: #001f54;
        }

        .calendar-btn svg {
            width: 14px;
            height: 14px;
            fill: #555;
        }

        .calendar-btn:hover svg {
            fill: #001f54;
        }

        /* =============================================
           TWO COLUMNS LAYOUT
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
            white-space: nowrap;
        }

        .info-value {
            color: #333;
        }

        /* =============================================
           DYNAMIC PLACEHOLDER CONTAINERS
           ============================================= */
        .dynamic-placeholder {
            min-height: 40px;
            border: 1px dashed #bbb;
            border-radius: 4px;
            padding: 10px;
            background: #fff;
            color: #999;
            font-size: 10px;
            font-style: italic;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        /* =============================================
           NOTES AREA
           ============================================= */
        .notes-area {
            width: 100%;
            min-height: 50px;
            border: 1px solid #ddd;
            border-radius: 2px;
            padding: 4px 6px;
            background: white;
            margin-top: 2px;
            font-family: inherit;
            font-size: 10px;
            resize: vertical;
            outline: none;
            line-height: 1.4;
        }

        .notes-area:focus {
            border-color: #001f54;
            background: #f8faff;
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

        .parts-table td {
            padding: 1px 3px;
        }

        .parts-table .row-input {
            width: 100%;
            border: none;
            border-bottom: 1px solid #ccc;
            background: transparent;
            font-family: inherit;
            font-size: 9px;
            padding: 2px 3px;
            outline: none;
        }

        .parts-table .row-input:hover {
            background: #fffde7;
        }

        .parts-table .row-input:focus {
            border-bottom: 1px solid #001f54;
            background: #e8f0fe;
        }

        .parts-table .qty-input {
            width: 40px;
            text-align: center;
        }

        .btn-add-row {
            margin-top: 4px;
            padding: 3px 10px;
            border: 1px dashed #aaa;
            border-radius: 3px;
            background: transparent;
            color: #666;
            font-size: 9px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-add-row:hover {
            background: #e8f0fe;
            border-color: #001f54;
            color: #001f54;
        }

        /* =============================================
           SIGNATURE SECTION
           ============================================= */
        .signature-section {
            display: table;
            width: 100%;
            margin-top: 4px;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            padding: 4px;
            vertical-align: top;
        }

        .signature-canvas-wrapper {
            border: 1px solid #ccc;
            border-radius: 2px;
            background: white;
            margin: 4px 0;
            position: relative;
        }

        .signature-canvas {
            width: 100%;
            height: 50px;
            cursor: crosshair;
            display: block;
        }

        .clear-sig-btn {
            position: absolute;
            top: 2px;
            right: 2px;
            background: rgba(220,53,69,0.8);
            color: white;
            border: none;
            border-radius: 2px;
            font-size: 8px;
            padding: 1px 4px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .signature-canvas-wrapper:hover .clear-sig-btn {
            opacity: 1;
        }

        .signature-label {
            font-size: 9px;
            color: #666;
        }

        /* =============================================
           ACKNOWLEDGEMENT
           ============================================= */
        .acknowledgement {
            margin-top: 4px;
            padding: 4px 6px;
            background: #e8f5e9;
            border-radius: 2px;
            border-left: 2px solid #28a745;
        }

        .acknowledgement p:first-child {
            font-weight: bold;
            color: #1b5e20;
            font-size: 9px;
        }

        .acknowledgement p:last-child {
            font-size: 8px;
            color: #333;
            margin-top: 2px;
        }

        /* =============================================
           FOOTER
           ============================================= */
        .page-footer {
            position: absolute;
            bottom: 10mm;
            left: 18mm;
            right: 18mm;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 4px;
        }

        .page-footer .footer-location {
            font-weight: bold;
            color: #001f54;
            font-size: 8.5px;
            margin-bottom: 1px;
        }

        .page-footer .footer-contact {
            font-size: 8px;
            color: #444;
            margin-bottom: 2px;
        }

        .page-footer .footer-legal {
            font-size: 7px;
            color: #999;
        }

        /* =============================================
           PRINT STYLES
           ============================================= */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            body {
                background: white;
            }

            .toolbar {
                display: none !important;
            }

            .pages-container {
                padding: 0;
                gap: 0;
            }

            .page {
                width: 100%;
                min-height: auto;
                box-shadow: none;
                margin: 0;
                padding: 15mm 18mm 25mm 18mm;
                page-break-after: always;
            }

            .page:last-child {
                page-break-after: auto;
            }

            .page-label {
                display: none;
            }

            .editable-field,
            .parts-table .row-input {
                border-bottom: none !important;
                background: transparent !important;
            }

            .calendar-btn,
            .date-input-wrapper input[type="date"] {
                display: none !important;
            }

            .notes-area {
                border: none !important;
                background: transparent !important;
            }

            .signature-canvas-wrapper {
                border: none !important;
            }

            .clear-sig-btn {
                display: none !important;
            }

            .dynamic-placeholder {
                border: 1px dashed #ccc !important;
            }

            .btn-add-row {
                display: none !important;
            }

            @page {
                margin: 0;
                size: letter portrait;
            }
        }
    </style>
</head>
<body>

<!-- =============================================
     TOOLBAR
     ============================================= -->
<div class="toolbar">
    <div class="toolbar-title">
        UNIVERSAL SERVICE REPORT EDITOR
    </div>
    <div class="toolbar-actions">
        <button class="toolbar-btn btn-clear" onclick="clearAllFields()">Clear All</button>
        <button class="toolbar-btn btn-print" onclick="window.print()">Print Report</button>
        <?php if ($request_id): ?>
        <button class="toolbar-btn btn-download-pdf" onclick="window.open('controllers/generate_vent_hood_report.php?id=<?php echo htmlspecialchars($request_id); ?>', '_blank')">Download PDF</button>
        <?php endif; ?>
    </div>
</div>

<!-- =============================================
     PAGES CONTAINER
     ============================================= -->
<div class="pages-container">

    <!-- =========================================
         PAGE 1
         ========================================= -->
    <div class="page" id="page1">
        <div class="page-label">Page 1</div>

        <!-- HEADER -->
        <div class="header">
            <?php if ($logo_base64): ?>
                <img src="<?php echo $logo_base64; ?>" class="company-logo" alt="Prime Facility Services Group">
            <?php endif; ?>
            <div class="report-title">UNIVERSAL SERVICE REPORT</div>
        </div>

        <!-- SECTION 1: SERVICE REPORT / WORK ORDER -->
        <div class="section">
            <div class="section-header">SERVICE REPORT / WORK ORDER</div>
            <div class="section-content">
                <div class="two-columns">
                    <div class="column">
                        <div class="info-grid">
                            <div class="info-row">
                                <div class="info-cell info-label">Work Order #:</div>
                                <div class="info-cell info-value">
                                    <input type="text" class="editable-field wide" id="work_order" value="<?php echo htmlspecialchars($work_order); ?>" placeholder="Enter work order #">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="info-grid">
                            <div class="info-row">
                                <div class="info-cell info-label">Service Date:</div>
                                <div class="info-cell info-value">
                                    <div class="date-input-wrapper">
                                        <input type="text" class="editable-field date-field" id="service_date" placeholder="MM/DD/YYYY" maxlength="10">
                                        <input type="date" id="service_date_picker" tabindex="-1">
                                        <button type="button" class="calendar-btn" data-picker="service_date_picker" data-target="service_date" title="Pick date">
                                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zM7 12h5v5H7z"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-cell info-label">Next Recommended Date:</div>
                                <div class="info-cell info-value">
                                    <div class="date-input-wrapper">
                                        <input type="text" class="editable-field date-field" id="next_service_date" placeholder="MM/DD/YYYY" maxlength="10">
                                        <input type="date" id="next_service_date_picker" tabindex="-1">
                                        <button type="button" class="calendar-btn" data-picker="next_service_date_picker" data-target="next_service_date" title="Pick date">
                                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zM7 12h5v5H7z"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: CLIENT INFORMATION -->
        <div class="section">
            <div class="section-header">CLIENT INFORMATION</div>
            <div class="section-content">
                <div class="two-columns">
                    <div class="column">
                        <div class="info-grid">
                            <div class="info-row">
                                <div class="info-cell info-label">Client Name:</div>
                                <div class="info-cell info-value">
                                    <input type="text" class="editable-field wide" id="client_name" value="<?php echo htmlspecialchars($client_name); ?>" placeholder="Enter client name">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-cell info-label">Address:</div>
                                <div class="info-cell info-value">
                                    <input type="text" class="editable-field wide" id="client_address" value="<?php echo htmlspecialchars($client_address); ?>" placeholder="Enter address">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-cell info-label">Contact Person:</div>
                                <div class="info-cell info-value">
                                    <input type="text" class="editable-field wide" id="client_contact" value="<?php echo htmlspecialchars($client_contact); ?>" placeholder="Enter contact person">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="info-grid">
                            <div class="info-row">
                                <div class="info-cell info-label">Phone:</div>
                                <div class="info-cell info-value">
                                    <input type="text" class="editable-field medium" id="client_phone" value="<?php echo htmlspecialchars($client_phone); ?>" placeholder="Enter phone">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-cell info-label">Email:</div>
                                <div class="info-cell info-value">
                                    <input type="text" class="editable-field wide" id="client_email" value="<?php echo htmlspecialchars($client_email); ?>" placeholder="Enter email">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: SERVICE CATEGORY -->
        <div class="section">
            <div class="section-header">SERVICE CATEGORY</div>
            <div class="section-content">
                <div class="dynamic-placeholder" id="service-category-container">
                    Dynamic content: Service type configuration will be loaded here
                </div>
            </div>
        </div>

        <!-- SECTION 4: SCOPE OF WORK -->
        <div class="section">
            <div class="section-header">SCOPE OF WORK</div>
            <div class="section-content">
                <div class="dynamic-placeholder" id="scope-of-work-container">
                    Dynamic content: Scope of work items will be loaded here
                </div>
            </div>
        </div>

        <!-- SECTION 5: INITIAL CONDITION / INSPECTION -->
        <div class="section">
            <div class="section-header">INITIAL CONDITION / INSPECTION</div>
            <div class="section-content">
                <div class="dynamic-placeholder" id="initial-condition-container">
                    Dynamic content: Initial inspection checklist will be loaded here
                </div>
            </div>
        </div>

        <!-- SECTION 6: SERVICE PERFORMED -->
        <div class="section">
            <div class="section-header">SERVICE PERFORMED</div>
            <div class="section-content">
                <div class="dynamic-placeholder" id="service-performed-container">
                    Dynamic content: Service tasks and checklist will be loaded here
                </div>
            </div>
        </div>

        <!-- PAGE 1 FOOTER -->
        <div class="page-footer">
            <div class="footer-location">Houston, TX 77063</div>
            <div class="footer-contact">Phone: 713-338-2553 | www.primefacilityservicesgroup.com</div>
            <div class="footer-legal">This document is confidential and intended solely for the addressee. &copy; <?php echo date('Y'); ?> Prime Facility Services Group</div>
        </div>
    </div>

    <!-- =========================================
         PAGE 2
         ========================================= -->
    <div class="page" id="page2">
        <div class="page-label">Page 2</div>

        <!-- Repeat header for page 2 -->
        <div class="header">
            <?php if ($logo_base64): ?>
                <img src="<?php echo $logo_base64; ?>" class="company-logo" alt="Prime Facility Services Group">
            <?php endif; ?>
            <div class="report-title">UNIVERSAL SERVICE REPORT</div>
        </div>

        <!-- SECTION 7: POST-SERVICE CONDITION -->
        <div class="section">
            <div class="section-header">POST-SERVICE CONDITION</div>
            <div class="section-content">
                <div class="dynamic-placeholder" id="post-service-condition-container">
                    Dynamic content: Post-service verification items will be loaded here
                </div>
            </div>
        </div>

        <!-- SECTION 8: TECHNICAL FINDINGS & OBSERVATIONS -->
        <div class="section">
            <div class="section-header">TECHNICAL FINDINGS &amp; OBSERVATIONS</div>
            <div class="section-content">
                <textarea class="notes-area" id="findings" rows="4" placeholder="Enter technical findings and observations here..."></textarea>
            </div>
        </div>

        <!-- SECTION 9: RECOMMENDATIONS -->
        <div class="section">
            <div class="section-header">RECOMMENDATIONS</div>
            <div class="section-content">
                <textarea class="notes-area" id="recommendations" rows="4" placeholder="Enter recommendations here..."></textarea>
            </div>
        </div>

        <!-- SECTION 10: PARTS / MATERIALS USED OR REQUIRED -->
        <div class="section">
            <div class="section-header">PARTS / MATERIALS USED OR REQUIRED</div>
            <div class="section-content">
                <table class="parts-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;">#</th>
                            <th>Description</th>
                            <th style="width: 50px;">Qty</th>
                            <th style="width: 80px;">Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody id="parts-table-body">
                        <tr>
                            <td style="text-align: center; color: #999;">1</td>
                            <td><input type="text" class="row-input" placeholder="Part or material description"></td>
                            <td><input type="number" class="row-input qty-input" min="0" placeholder="0"></td>
                            <td>
                                <select class="row-input" style="width: 100%; border: none; background: transparent; font-size: 9px;">
                                    <option value="">--</option>
                                    <option value="used">Used</option>
                                    <option value="required">Required</option>
                                    <option value="ordered">Ordered</option>
                                </select>
                            </td>
                            <td><input type="text" class="row-input" placeholder=""></td>
                        </tr>
                        <tr>
                            <td style="text-align: center; color: #999;">2</td>
                            <td><input type="text" class="row-input" placeholder="Part or material description"></td>
                            <td><input type="number" class="row-input qty-input" min="0" placeholder="0"></td>
                            <td>
                                <select class="row-input" style="width: 100%; border: none; background: transparent; font-size: 9px;">
                                    <option value="">--</option>
                                    <option value="used">Used</option>
                                    <option value="required">Required</option>
                                    <option value="ordered">Ordered</option>
                                </select>
                            </td>
                            <td><input type="text" class="row-input" placeholder=""></td>
                        </tr>
                        <tr>
                            <td style="text-align: center; color: #999;">3</td>
                            <td><input type="text" class="row-input" placeholder="Part or material description"></td>
                            <td><input type="number" class="row-input qty-input" min="0" placeholder="0"></td>
                            <td>
                                <select class="row-input" style="width: 100%; border: none; background: transparent; font-size: 9px;">
                                    <option value="">--</option>
                                    <option value="used">Used</option>
                                    <option value="required">Required</option>
                                    <option value="ordered">Ordered</option>
                                </select>
                            </td>
                            <td><input type="text" class="row-input" placeholder=""></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn-add-row" onclick="addPartsRow()">+ Add Row</button>
            </div>
        </div>

        <!-- SECTION 11: CLIENT ACKNOWLEDGEMENT & SIGNATURES -->
        <div class="section">
            <div class="section-header">CLIENT ACKNOWLEDGEMENT &amp; SIGNATURES</div>
            <div class="section-content">
                <div class="acknowledgement" style="margin-bottom: 6px;">
                    <p>SERVICE ACKNOWLEDGEMENT</p>
                    <p>By signing below, the client acknowledges that the service described in this report has been completed and the work area was left in satisfactory condition.</p>
                </div>

                <div class="signature-section">
                    <div class="signature-box">
                        <p style="font-weight: bold; margin-bottom: 2px; color: #001f54; font-size: 10px;">Technician:</p>
                        <div style="font-size: 9px; margin-bottom: 2px;">
                            Name: <input type="text" class="editable-field medium" id="tech_name" placeholder="Technician name">
                        </div>
                        <div style="font-size: 9px; margin-bottom: 2px;">
                            Signature:
                            <div class="signature-canvas-wrapper">
                                <canvas class="signature-canvas" id="sig-tech"></canvas>
                                <button class="clear-sig-btn" onclick="clearSignature('sig-tech')">Clear</button>
                            </div>
                        </div>
                        <div style="font-size: 9px;">
                            Date: <div class="date-input-wrapper">
                                <input type="text" class="editable-field date-field" id="tech_date" placeholder="MM/DD/YYYY" maxlength="10">
                                <input type="date" id="tech_date_picker" tabindex="-1">
                                <button type="button" class="calendar-btn" data-picker="tech_date_picker" data-target="tech_date" title="Pick date">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zM7 12h5v5H7z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="signature-box">
                        <p style="font-weight: bold; margin-bottom: 2px; color: #001f54; font-size: 10px;">Client / Authorized Representative:</p>
                        <div style="font-size: 9px; margin-bottom: 2px;">
                            Name: <input type="text" class="editable-field medium" id="client_sig_name" placeholder="Client / representative name">
                        </div>
                        <div style="font-size: 9px; margin-bottom: 2px;">
                            Signature:
                            <div class="signature-canvas-wrapper">
                                <canvas class="signature-canvas" id="sig-client"></canvas>
                                <button class="clear-sig-btn" onclick="clearSignature('sig-client')">Clear</button>
                            </div>
                        </div>
                        <div style="font-size: 9px;">
                            Date: <div class="date-input-wrapper">
                                <input type="text" class="editable-field date-field" id="client_sig_date" placeholder="MM/DD/YYYY" maxlength="10">
                                <input type="date" id="client_sig_date_picker" tabindex="-1">
                                <button type="button" class="calendar-btn" data-picker="client_sig_date_picker" data-target="client_sig_date" title="Pick date">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zM7 12h5v5H7z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE 2 FOOTER -->
        <div class="page-footer">
            <div class="footer-location">Houston, TX 77063</div>
            <div class="footer-contact">Phone: 713-338-2553 | www.primefacilityservicesgroup.com</div>
            <div class="footer-legal">This document is confidential and intended solely for the addressee. &copy; <?php echo date('Y'); ?> Prime Facility Services Group</div>
        </div>
    </div>

</div>

<!-- =============================================
     JAVASCRIPT
     ============================================= -->
<script>
(function() {
    'use strict';

    // =============================================
    // SIGNATURE PAD
    // =============================================
    var signaturePads = {};

    function applyContextStyles(ctx) {
        ctx.strokeStyle = '#001f54';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }

    function initSignaturePad(canvasId) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return;

        var ctx = canvas.getContext('2d');
        var drawing = false;
        var lastX = 0;
        var lastY = 0;
        var initialized = false;

        function resizeCanvas() {
            var rect = canvas.getBoundingClientRect();
            if (rect.width === 0 || rect.height === 0) {
                initialized = false;
                return;
            }
            var dpr = window.devicePixelRatio || 1;
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            applyContextStyles(ctx);
            initialized = true;
        }

        function ensureInitialized() {
            if (!initialized) {
                resizeCanvas();
            }
        }

        resizeCanvas();
        applyContextStyles(ctx);

        function getPos(e) {
            var rect = canvas.getBoundingClientRect();
            var clientX, clientY;
            if (e.touches && e.touches.length > 0) {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            } else {
                clientX = e.clientX;
                clientY = e.clientY;
            }
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function startDraw(e) {
            e.preventDefault();
            ensureInitialized();
            drawing = true;
            var pos = getPos(e);
            lastX = pos.x;
            lastY = pos.y;
            ctx.beginPath();
            ctx.arc(pos.x, pos.y, 0.5, 0, Math.PI * 2);
            ctx.fill();
        }

        function draw(e) {
            if (!drawing) return;
            e.preventDefault();
            var pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            lastX = pos.x;
            lastY = pos.y;
        }

        function stopDraw(e) {
            if (e) e.preventDefault();
            drawing = false;
        }

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDraw);
        canvas.addEventListener('mouseleave', stopDraw);

        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDraw, { passive: false });

        signaturePads[canvasId] = {
            canvas: canvas,
            ctx: ctx,
            resize: resizeCanvas,
            ensureInit: ensureInitialized
        };
    }

    window.clearSignature = function(canvasId) {
        var pad = signaturePads[canvasId];
        if (pad) {
            var dpr = window.devicePixelRatio || 1;
            pad.ctx.clearRect(0, 0, pad.canvas.width / dpr, pad.canvas.height / dpr);
        }
    };

    // =============================================
    // ADD PARTS ROW
    // =============================================
    var partsRowCount = 3;

    window.addPartsRow = function() {
        partsRowCount++;
        var tbody = document.getElementById('parts-table-body');
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td style="text-align: center; color: #999;">' + partsRowCount + '</td>' +
            '<td><input type="text" class="row-input" placeholder="Part or material description"></td>' +
            '<td><input type="number" class="row-input qty-input" min="0" placeholder="0"></td>' +
            '<td><select class="row-input" style="width: 100%; border: none; background: transparent; font-size: 9px;"><option value="">--</option><option value="used">Used</option><option value="required">Required</option><option value="ordered">Ordered</option></select></td>' +
            '<td><input type="text" class="row-input" placeholder=""></td>';
        tbody.appendChild(tr);
    };

    // =============================================
    // CLEAR ALL FIELDS
    // =============================================
    window.clearAllFields = function() {
        if (!confirm('Are you sure you want to clear all fields? This cannot be undone.')) return;

        document.querySelectorAll('.editable-field, .row-input').forEach(function(field) {
            if (field.tagName === 'SELECT') {
                field.selectedIndex = 0;
            } else {
                field.value = '';
            }
        });

        document.querySelectorAll('.notes-area').forEach(function(ta) {
            ta.value = '';
        });

        Object.keys(signaturePads).forEach(function(id) {
            clearSignature(id);
        });
    };

    // =============================================
    // DATE FIELD AUTO-FORMAT (MM/DD/YYYY)
    // =============================================
    function initDateFields() {
        document.querySelectorAll('.date-field').forEach(function(field) {
            field.addEventListener('input', function(e) {
                var value = this.value.replace(/[^0-9]/g, '');
                if (value.length > 8) value = value.substring(0, 8);
                if (value.length >= 5) {
                    this.value = value.substring(0, 2) + '/' + value.substring(2, 4) + '/' + value.substring(4);
                } else if (value.length >= 3) {
                    this.value = value.substring(0, 2) + '/' + value.substring(2);
                } else {
                    this.value = value;
                }
            });
        });
    }

    // =============================================
    // CALENDAR BUTTON FUNCTIONALITY
    // =============================================
    function initCalendarButtons() {
        document.querySelectorAll('.calendar-btn').forEach(function(btn) {
            var pickerId = btn.getAttribute('data-picker');
            var targetId = btn.getAttribute('data-target');
            var picker = document.getElementById(pickerId);
            var target = document.getElementById(targetId);

            btn.addEventListener('click', function() {
                picker.showPicker ? picker.showPicker() : picker.click();
            });

            picker.addEventListener('change', function() {
                if (this.value) {
                    var parts = this.value.split('-');
                    target.value = parts[1] + '/' + parts[2] + '/' + parts[0];
                }
            });
        });
    }

    // =============================================
    // INITIALIZE
    // =============================================
    document.addEventListener('DOMContentLoaded', function() {
        initSignaturePad('sig-tech');
        initSignaturePad('sig-client');

        initDateFields();
        initCalendarButtons();

        window.addEventListener('resize', function() {
            Object.keys(signaturePads).forEach(function(id) {
                signaturePads[id].resize();
            });
        });
    });

})();
</script>

</body>
</html>
