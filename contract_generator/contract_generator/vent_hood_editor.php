<?php
/**
 * VENT HOOD REPORT - INTERACTIVE EDITOR / PREVIEWER
 * Opens a full-page interactive editor for the Vent Hood Service Report.
 * Template is fixed; only fields are editable.
 */

require_once 'config/db_config.php';

// Get request data if ID is provided
$data = [];
$request_id = $_GET['id'] ?? null;

if ($request_id) {
    $sql = "SELECT * FROM requests WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $request_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

$client_name = $data['Company_Name'] ?? '';
$client_address = $data['Company_Address'] ?? '';
$client_contact = $data['Client_Name'] ?? '';
$client_email = $data['Email'] ?? '';
$work_order = $data['docnum'] ?? '';

// Logo - dynamic based on Service_Type (Facility.png / Hospitality.png)
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
    <title>Vent Hood Report Editor</title>
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

        .editable-field.tiny {
            width: 35px;
        }

        .editable-field.date-field {
            width: 100px;
        }

        .editable-field.full-width {
            width: 100%;
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
           CHECKBOXES (interactive)
           ============================================= */
        .cb {
            display: inline-block;
            width: 13px;
            height: 13px;
            border: 1.5px solid #555;
            border-radius: 2px;
            cursor: pointer;
            vertical-align: middle;
            margin-right: 3px;
            position: relative;
            transition: all 0.15s;
            background: white;
        }

        .cb:hover {
            border-color: #001f54;
            background: #e8f0fe;
        }

        .cb.checked {
            background: #001f54;
            border-color: #001f54;
        }

        .cb.checked::after {
            content: "\2713";
            position: absolute;
            top: -1px;
            left: 1px;
            font-size: 11px;
            color: white;
            font-weight: bold;
        }

        .checkbox-item {
            display: block;
            margin: 2px 0;
            font-size: 10px;
            cursor: pointer;
        }

        .checkbox-item:hover {
            color: #001f54;
        }

        .inline-checkbox {
            display: inline-block;
            margin-right: 10px;
            font-size: 10px;
            cursor: pointer;
        }

        .inline-checkbox:hover {
            color: #001f54;
        }

        /* =============================================
           CHECKLIST TABLE
           ============================================= */
        .checklist-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
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
            padding: 1px 2px;
        }

        .checklist-table .comment-input {
            width: 100%;
            border: none;
            border-bottom: 1px solid #ccc;
            background: transparent;
            font-family: inherit;
            font-size: 9px;
            color: #333;
            padding: 1px 3px;
            outline: none;
        }

        .checklist-table .comment-input:hover {
            background: #fffde7;
        }

        .checklist-table .comment-input:focus {
            border-bottom: 1px solid #001f54;
            background: #e8f0fe;
        }

        .table-subheader {
            background: #d0e4f7 !important;
            font-weight: bold;
            color: #001f54;
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
           FOOTER (fixed at bottom of each page)
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
            .checklist-table .comment-input {
                border-bottom: none !important;
                background: transparent !important;
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

            .cb {
                border: 1.5px solid #555;
            }

            .cb.checked {
                background: #001f54 !important;
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
        VENT HOOD REPORT EDITOR
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

        <!-- HEADER: Logo + Title only (no company info) -->
        <div class="header">
            <?php if ($logo_base64): ?>
                <img src="<?php echo $logo_base64; ?>" class="company-logo" alt="Prime Facility Services Group">
            <?php endif; ?>
            <div class="report-title">KITCHEN EXHAUST CLEANING AND GREASE GUTTER SERVICE REPORT</div>
        </div>

        <!-- SERVICE REPORT / WORK ORDER -->
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
                                <div class="info-cell info-label">Service date:</div>
                                <div class="info-cell info-value">
                                    <input type="date" class="editable-field date-field" id="service_date">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-cell info-label">Next recommended date:</div>
                                <div class="info-cell info-value">
                                    <input type="date" class="editable-field date-field" id="next_service_date">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="margin-top: 3px;">
                    <span style="font-weight: bold; color: #001f54; font-size: 10px;">Frequency:</span>
                    <span class="inline-checkbox" onclick="toggleCheckbox(this)"><span class="cb" data-group="freq"></span> 30 days</span>
                    <span class="inline-checkbox" onclick="toggleCheckbox(this)"><span class="cb" data-group="freq"></span> 60 days</span>
                    <span class="inline-checkbox" onclick="toggleCheckbox(this)"><span class="cb" data-group="freq"></span> 90 days</span>
                    <span class="inline-checkbox" onclick="toggleCheckbox(this)"><span class="cb" data-group="freq"></span> 120 days</span>
                    <span class="inline-checkbox"><span class="cb" data-group="freq" onclick="toggleCheckbox(this.parentElement)"></span> Other: <input type="text" class="editable-field small" placeholder="___"></span>
                </div>
            </div>
        </div>

        <!-- 1. CLIENT INFORMATION -->
        <div class="section">
            <div class="section-header">1. CLIENT INFORMATION</div>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-cell info-label">Client / Restaurant:</div>
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
                </div>
            </div>
        </div>

        <!-- 2. SYSTEM SERVICED -->
        <div class="section">
            <div class="section-header">2. SYSTEM SERVICED</div>
            <div class="section-content">
                <div class="two-columns">
                    <div class="column">
                        <div class="checkbox-item" onclick="toggleCheckbox(this)"><span class="cb"></span> Main Hood</div>
                        <div class="checkbox-item" onclick="toggleCheckbox(this)"><span class="cb"></span> Extraction Ducts</div>
                        <div class="checkbox-item" onclick="toggleCheckbox(this)"><span class="cb"></span> Roof Fan</div>
                    </div>
                    <div class="column">
                        <div class="checkbox-item" onclick="toggleCheckbox(this)"><span class="cb"></span> Grease Gutter</div>
                        <div class="checkbox-item" onclick="toggleCheckbox(this)"><span class="cb"></span> Fire System (inspection only)</div>
                        <div class="checkbox-item"><span class="cb" onclick="toggleCheckbox(this.parentElement)"></span> Other: <input type="text" class="editable-field medium" placeholder="Specify"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. INSPECTION CHECKLIST, RESULTS & ROOF INSPECTION -->
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
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc1')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc1')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc1')"></span></td>
                            <td class="comment-cell"><input type="text" class="comment-input" id="comment_bc1" placeholder=""></td>
                        </tr>
                        <tr>
                            <td>Filters with grease accumulation?</td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc2')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc2')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc2')"></span></td>
                            <td class="comment-cell"><input type="text" class="comment-input" id="comment_bc2" placeholder=""></td>
                        </tr>
                        <tr>
                            <td>Hood lights working?</td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc3')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc3')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc3')"></span></td>
                            <td class="comment-cell"><input type="text" class="comment-input" id="comment_bc3" placeholder=""></td>
                        </tr>
                        <tr>
                            <td>Visible grease in ducts?</td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc4')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc4')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc4')"></span></td>
                            <td class="comment-cell"><input type="text" class="comment-input" id="comment_bc4" placeholder=""></td>
                        </tr>
                        <tr>
                            <td>Grease container present?</td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc5')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc5')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc5')"></span></td>
                            <td class="comment-cell"><input type="text" class="comment-input" id="comment_bc5" placeholder=""></td>
                        </tr>
                        <tr>
                            <td>Visible damage in system?</td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc6')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc6')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'bc6')"></span></td>
                            <td class="comment-cell"><input type="text" class="comment-input" id="comment_bc6" placeholder=""></td>
                        </tr>
                        <!-- AFTER CLEANING -->
                        <tr><td colspan="5" class="table-subheader">AFTER CLEANING</td></tr>
                        <tr>
                            <td>System clean and operative</td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ac1')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ac1')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ac1')"></span></td>
                            <td class="comment-cell"><input type="text" class="comment-input" id="comment_ac1" placeholder=""></td>
                        </tr>
                        <tr>
                            <td>Fan working at completion</td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ac2')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ac2')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ac2')"></span></td>
                            <td class="comment-cell"><input type="text" class="comment-input" id="comment_ac2" placeholder=""></td>
                        </tr>
                        <tr>
                            <td>Work area delivered clean</td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ac3')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ac3')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ac3')"></span></td>
                            <td class="comment-cell"><input type="text" class="comment-input" id="comment_ac3" placeholder=""></td>
                        </tr>
                        <tr>
                            <td>Client informed of final status</td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ac4')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ac4')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ac4')"></span></td>
                            <td class="comment-cell"><input type="text" class="comment-input" id="comment_ac4" placeholder=""></td>
                        </tr>
                        <!-- ROOF INSPECTION -->
                        <tr><td colspan="5" class="table-subheader">ROOF INSPECTION</td></tr>
                        <tr>
                            <td>Grease accumulation on roof?</td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ri1')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ri1')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ri1')"></span></td>
                            <td class="comment-cell"><input type="text" class="comment-input" id="comment_ri1" placeholder=""></td>
                        </tr>
                        <tr>
                            <td>Is it a severe problem?</td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ri2')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ri2')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ri2')"></span></td>
                            <td class="comment-cell"><input type="text" class="comment-input" id="comment_ri2" placeholder=""></td>
                        </tr>
                        <tr>
                            <td>Absorption unit installation recommended?</td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ri3')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ri3')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ri3')"></span></td>
                            <td class="comment-cell"><input type="text" class="comment-input" id="comment_ri3" placeholder=""></td>
                        </tr>
                        <tr>
                            <td>Roof damage from grease?</td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ri4')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ri4')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ri4')"></span></td>
                            <td class="comment-cell"><input type="text" class="comment-input" id="comment_ri4" placeholder=""></td>
                        </tr>
                        <tr>
                            <td>Is there proper drainage?</td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ri5')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ri5')"></span></td>
                            <td class="center"><span class="cb" onclick="toggleRadio(this, 'ri5')"></span></td>
                            <td class="comment-cell"><input type="text" class="comment-input" id="comment_ri5" placeholder=""></td>
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
                        <div class="checkbox-item" onclick="toggleCheckbox(this)"><span class="cb"></span> Complete hood cleaning</div>
                        <div class="checkbox-item" onclick="toggleCheckbox(this)"><span class="cb"></span> Filter cleaning</div>
                        <div class="checkbox-item" onclick="toggleCheckbox(this)"><span class="cb"></span> Duct cleaning</div>
                        <div class="checkbox-item" onclick="toggleCheckbox(this)"><span class="cb"></span> Extractor/fan cleaning</div>
                    </div>
                    <div class="column">
                        <div class="checkbox-item" onclick="toggleCheckbox(this)"><span class="cb"></span> Grease gutter cleaning</div>
                        <div class="checkbox-item" onclick="toggleCheckbox(this)"><span class="cb"></span> Kitchen area cleaning (affected)</div>
                        <div class="checkbox-item" onclick="toggleCheckbox(this)"><span class="cb"></span> Sticker placed on site</div>
                        <div class="checkbox-item" onclick="toggleCheckbox(this)"><span class="cb"></span> Before/after photos taken</div>
                    </div>
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
            <div class="report-title">KITCHEN EXHAUST CLEANING AND GREASE GUTTER SERVICE REPORT</div>
        </div>

        <!-- 5. TECHNICAL SYSTEM DATA (moved to page 2) -->
        <div class="section">
            <div class="section-header">5. TECHNICAL SYSTEM DATA</div>
            <div class="section-content">
                <div class="two-columns">
                    <div class="column">
                        <div class="info-grid">
                            <div class="info-row">
                                <div class="info-cell info-label">Number of Fans:</div>
                                <div class="info-cell info-value">
                                    <input type="number" class="editable-field small" id="num_fans" min="0" placeholder="0">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-cell info-label">Number of Stacks:</div>
                                <div class="info-cell info-value">
                                    <input type="number" class="editable-field small" id="num_stacks" min="0" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="info-grid">
                            <div class="info-row">
                                <div class="info-cell info-label">Fan Type:</div>
                                <div class="info-cell info-value">
                                    <span class="inline-checkbox" onclick="toggleCheckbox(this)"><span class="cb"></span> Marshall</span>
                                    <span class="inline-checkbox" onclick="toggleCheckbox(this)"><span class="cb"></span> Upblast</span>
                                    <span class="inline-checkbox" onclick="toggleCheckbox(this)"><span class="cb"></span> Supreme</span>
                                    <span class="inline-checkbox"><span class="cb" onclick="toggleCheckbox(this.parentElement)"></span> Other: <input type="text" class="editable-field small" placeholder="___"></span>
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
                <textarea class="notes-area" id="notes" rows="4" placeholder="Enter notes and observations here..."></textarea>

                <div class="signature-section">
                    <div class="signature-box">
                        <p style="font-weight: bold; margin-bottom: 2px; color: #001f54; font-size: 10px;">Responsible Technician:</p>
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
                            Date: <input type="date" class="editable-field date-field" id="tech_date">
                        </div>
                    </div>
                    <div class="signature-box">
                        <p style="font-weight: bold; margin-bottom: 2px; color: #001f54; font-size: 10px;">Client / Manager:</p>
                        <div style="font-size: 9px; margin-bottom: 2px;">
                            Name: <input type="text" class="editable-field medium" id="client_sig_name" placeholder="Client / Manager name">
                        </div>
                        <div style="font-size: 9px; margin-bottom: 2px;">
                            Signature:
                            <div class="signature-canvas-wrapper">
                                <canvas class="signature-canvas" id="sig-client"></canvas>
                                <button class="clear-sig-btn" onclick="clearSignature('sig-client')">Clear</button>
                            </div>
                        </div>
                        <div style="font-size: 9px;">
                            Date: <input type="date" class="editable-field date-field" id="client_sig_date">
                        </div>
                    </div>
                </div>

                <div class="acknowledgement">
                    <p>ACKNOWLEDGEMENT OF KITCHEN CONDITION & SERVICE COMPLETED</p>
                    <p>By signing above, the customer acknowledges that the service was completed and the kitchen was left clean and in satisfactory condition.</p>
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
    // CHECKBOX TOGGLE
    // =============================================
    window.toggleCheckbox = function(el) {
        var cb = el.querySelector('.cb');
        if (!cb) {
            // If el itself is the cb (for inline cases)
            if (el.classList.contains('cb')) cb = el;
            else return;
        }
        cb.classList.toggle('checked');
    };

    // =============================================
    // RADIO-STYLE TOGGLE (Yes/No/NA in same row)
    // =============================================
    window.toggleRadio = function(cb, group) {
        // Uncheck all in same row group
        var allInGroup = document.querySelectorAll('.cb[onclick*="' + group + '"]');
        allInGroup.forEach(function(item) {
            if (item !== cb) {
                item.classList.remove('checked');
            }
        });
        cb.classList.toggle('checked');
    };

    // =============================================
    // SIGNATURE PAD
    // =============================================
    var signaturePads = {};

    function initSignaturePad(canvasId) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return;

        var ctx = canvas.getContext('2d');
        var drawing = false;
        var lastX = 0;
        var lastY = 0;

        // Set canvas internal resolution to match display
        function resizeCanvas() {
            var rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
        }
        resizeCanvas();

        ctx.strokeStyle = '#001f54';
        ctx.lineWidth = 1.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        function getPos(e) {
            var rect = canvas.getBoundingClientRect();
            var clientX, clientY;
            if (e.touches) {
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
            drawing = true;
            var pos = getPos(e);
            lastX = pos.x;
            lastY = pos.y;
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

        // Mouse events
        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDraw);
        canvas.addEventListener('mouseleave', stopDraw);

        // Touch events
        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDraw, { passive: false });

        signaturePads[canvasId] = { canvas: canvas, ctx: ctx, resize: resizeCanvas };
    }

    window.clearSignature = function(canvasId) {
        var pad = signaturePads[canvasId];
        if (pad) {
            pad.ctx.clearRect(0, 0, pad.canvas.width, pad.canvas.height);
        }
    };

    // =============================================
    // CLEAR ALL FIELDS
    // =============================================
    window.clearAllFields = function() {
        if (!confirm('Are you sure you want to clear all fields? This cannot be undone.')) return;

        // Clear all input fields
        document.querySelectorAll('.editable-field, .comment-input').forEach(function(field) {
            field.value = '';
        });

        // Clear textareas
        document.querySelectorAll('.notes-area').forEach(function(ta) {
            ta.value = '';
        });

        // Uncheck all checkboxes
        document.querySelectorAll('.cb.checked').forEach(function(cb) {
            cb.classList.remove('checked');
        });

        // Clear all signatures
        Object.keys(signaturePads).forEach(function(id) {
            clearSignature(id);
        });
    };

    // =============================================
    // INITIALIZE
    // =============================================
    document.addEventListener('DOMContentLoaded', function() {
        initSignaturePad('sig-tech');
        initSignaturePad('sig-client');

        // Re-init on window resize for signature canvases
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
