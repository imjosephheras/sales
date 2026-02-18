<?php
/**
 * VENT HOOD REPORT - INTERACTIVE EDITOR / PREVIEWER
 * Opens a full-page interactive editor for the Vent Hood Service Report.
 * Template is fixed; only fields are editable.
 */

require_once __DIR__ . '/config/init.php';
$pdo = Database::getConnection();

// Get request data if ID is provided
$data = [];
$request_id = $_GET['id'] ?? null;

if ($request_id) {
    $sql = "SELECT *, form_id AS id, company_name AS Company_Name, address AS Company_Address, client_name AS Client_Name, email AS Email, service_type AS Service_Type FROM forms WHERE form_id = :id";
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

        .calendar-btn:active {
            background: #d0e0fd;
        }

        .calendar-btn svg {
            width: 14px;
            height: 14px;
            fill: #555;
        }

        .calendar-btn:hover svg {
            fill: #001f54;
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

        /* Section 7 detail (hidden until products selected) */
        .section-7-detail {
            margin-top: 8px;
            display: none;
        }

        .section-7-detail.visible {
            display: block;
        }

        .selected-products-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 4px 0;
        }

        .selected-product-card {
            width: calc(33.33% - 6px);
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 6px;
            text-align: center;
            background: white;
        }

        .selected-product-card img {
            width: 100%;
            height: 70px;
            object-fit: contain;
            margin-bottom: 4px;
        }

        .selected-product-card .product-name {
            font-size: 8px;
            font-weight: bold;
            color: #001f54;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .selected-product-card .qty-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            font-size: 9px;
            color: #333;
        }

        .selected-product-card .qty-input {
            width: 40px;
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 2px 4px;
            font-size: 9px;
            text-align: center;
            outline: none;
            font-family: inherit;
        }

        .selected-product-card .qty-input:focus {
            border-color: #001f54;
            background: #e8f0fe;
        }

        .authorization-block {
            margin-top: 10px;
            padding: 8px 10px;
            background: #f0f4fa;
            border: 1px solid #c0d0e0;
            border-radius: 4px;
            font-size: 9px;
            color: #333;
            line-height: 1.5;
        }

        .authorization-block p {
            margin-bottom: 6px;
        }

        .authorization-signature {
            display: table;
            width: 100%;
            margin-top: 10px;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }

        .authorization-sig-box {
            display: table-cell;
            width: 33.33%;
            padding: 4px 8px;
            vertical-align: top;
            font-size: 9px;
            color: #555;
        }

        .authorization-sig-box label {
            display: block;
            font-weight: bold;
            color: #001f54;
            margin-bottom: 3px;
        }

        /* Authorization Checkbox */
        .authorization-checkbox-wrapper {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 10px;
            padding: 8px 10px;
            background: #fff8e1;
            border: 1.5px solid #f0c040;
            border-radius: 4px;
            cursor: pointer;
            user-select: none;
            transition: background 0.2s, border-color 0.2s;
        }

        .authorization-checkbox-wrapper:hover {
            background: #fff3c4;
        }

        .authorization-checkbox-wrapper.checked {
            background: #e8f5e9;
            border-color: #4caf50;
        }

        .authorization-checkbox-wrapper input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin-top: 1px;
            accent-color: #001f54;
            cursor: pointer;
            flex-shrink: 0;
        }

        .authorization-checkbox-label {
            font-size: 9px;
            font-weight: 600;
            color: #333;
            line-height: 1.5;
        }

        /* Disabled signature overlay */
        .authorization-signature.disabled {
            position: relative;
            opacity: 0.4;
            pointer-events: none;
        }

        .authorization-signature.disabled::after {
            content: 'Please check the authorization box above to enable signing';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #999;
            font-style: italic;
            background: rgba(255,255,255,0.5);
        }

        .section-7-hidden {
            display: none;
        }

        .btn-add-products {
            background: #6c5ce7;
            color: white;
        }

        /* =============================================
           SIDE PANEL - PRODUCT CATALOG
           ============================================= */
        .app-layout {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            transition: margin-right 0.3s ease;
        }

        .main-content.panel-open {
            margin-right: 370px;
        }

        .side-panel {
            position: fixed;
            top: 0;
            right: -370px;
            width: 370px;
            height: 100vh;
            background: #f5f6fa;
            box-shadow: -3px 0 15px rgba(0,0,0,0.2);
            z-index: 999;
            transition: right 0.3s ease;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .side-panel.open {
            right: 0;
        }

        .side-panel-header {
            background: linear-gradient(135deg, #001f54 0%, #003080 100%);
            color: white;
            padding: 52px 16px 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .side-panel-header h3 {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
        }

        .side-panel-close {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            border-radius: 4px;
            width: 28px;
            height: 28px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .side-panel-close:hover {
            background: rgba(255,255,255,0.25);
        }

        .side-panel-body {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
        }

        .side-panel-body p.panel-hint {
            font-size: 10px;
            color: #666;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .side-panel .product-preview-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .side-panel .product-preview-tile {
            width: 100%;
            aspect-ratio: 1;
            border: 2px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            overflow: hidden;
            position: relative;
            background: white;
            transition: all 0.2s;
        }

        .side-panel .product-preview-tile:hover {
            border-color: #003080;
            box-shadow: 0 2px 10px rgba(0,31,84,0.2);
            transform: translateY(-1px);
        }

        .side-panel .product-preview-tile.selected {
            border-color: #001f54;
            box-shadow: 0 0 0 2px #001f54, 0 2px 10px rgba(0,31,84,0.3);
        }

        .side-panel .product-preview-tile.selected::after {
            content: "\2713";
            position: absolute;
            top: 3px;
            right: 3px;
            background: #001f54;
            color: white;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .side-panel .product-preview-tile img {
            width: 100%;
            height: 65%;
            object-fit: contain;
            padding: 4px;
        }

        .side-panel .product-preview-tile .preview-name {
            font-size: 8px;
            font-weight: bold;
            color: #001f54;
            text-align: center;
            padding: 0 4px 4px 4px;
            line-height: 1.2;
            height: 35%;
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }

        .side-panel-count {
            background: #001f54;
            color: white;
            padding: 8px 16px;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            flex-shrink: 0;
            display: none;
        }

        .side-panel-count.has-items {
            display: block;
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

            .cb {
                border: 1.5px solid #555;
            }

            .cb.checked {
                background: #001f54 !important;
            }

            .selected-product-card .qty-input {
                border: none !important;
                background: transparent !important;
            }

            .side-panel {
                display: none !important;
            }

            .main-content.panel-open {
                margin-right: 0 !important;
            }

            .section-7-detail.visible {
                display: block !important;
            }

            .authorization-block {
                background: #f0f4fa !important;
                border: 1px solid #c0d0e0 !important;
            }

            .section-7-hidden {
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
        VENT HOOD REPORT EDITOR
    </div>
    <div class="toolbar-actions">
        <button class="toolbar-btn btn-add-products" id="btnToggleProducts" onclick="toggleProductsSection()">Add Products Section</button>
        <button class="toolbar-btn btn-clear" onclick="clearAllFields()">Clear All</button>
        <button class="toolbar-btn btn-print" onclick="window.print()">Print Report</button>
        <?php if ($request_id): ?>
        <button class="toolbar-btn btn-download-pdf" onclick="window.open('controllers/generate_vent_hood_report.php?id=<?php echo htmlspecialchars($request_id); ?>', '_blank')">Download PDF</button>
        <?php endif; ?>
    </div>
</div>

<!-- =============================================
     SIDE PANEL - PRODUCT CATALOG
     ============================================= -->
<div class="side-panel" id="sidePanel">
    <div class="side-panel-header">
        <h3>PRODUCT CATALOG</h3>
        <button class="side-panel-close" onclick="closeSidePanel()" title="Close panel">&times;</button>
    </div>
    <div class="side-panel-body">
        <p class="panel-hint">Select the products needed by clicking on them. Selected items will appear in Section 7 of the document.</p>
        <div class="product-preview-grid" id="productPreviewGrid">
            <?php
            $products = [
                ['image' => '5 16 inch chain.png', 'name' => '16" Chain (5 ft)'],
                ['image' => '6 Break-Away Stud Doors.png', 'name' => 'Break-Away Stud Doors'],
                ['image' => 'Canopy Hood Light Fixture with Clear Coated Tempered Glass Globe and Wire Guard.png', 'name' => 'Canopy Hood Light Fixture – Clear Coated Tempered Glass Globe & Wire Guard'],
                ['image' => 'Canopy Hood Light Fixture with Clear Tempered Glass Globe.png', 'name' => 'Canopy Hood Light Fixture – Clear Tempered Glass Globe'],
                ['image' => 'Canopy Hood Lights.png', 'name' => 'Canopy Hood Lights'],
                ['image' => 'CaptiveAire Hinge Kits.png', 'name' => 'Captive Aire Hinge Kits'],
                ['image' => 'Centrifugal Fan Impeller.png', 'name' => 'Centrifugal Fan Impeller'],
                ['image' => 'Clear Coated Tempered Glass Globe for L50 L55 Hood Lights.png', 'name' => 'Clear Coated Tempered Glass Globe for L50/L55 Hood Lights'],
                ['image' => 'Downblast HVAC Exhaust Fans.png', 'name' => 'Downblast HVAC Exhaust Fans'],
                ['image' => 'Driploc Hinge Kits.png', 'name' => 'DripLoc Hinge Kits'],
                ['image' => 'DRIPLOC Type 1-S Exhaust Fan Hinge Kit.png', 'name' => 'DripLoc Type 1-S Exhaust Fan Hinge Kit'],
                ['image' => 'Driploc Grease Containment.png', 'name' => 'DripLoc Grease Containment'],
                ['image' => 'Duct Wrap Kit.png', 'name' => 'Duct Wrap Kit'],
                ['image' => 'Ductmate Moist Drain Fitting – Galvanized – 3-4.png', 'name' => 'Ductmate Moist Drain Fitting – Galvanized (3–4")'],
                ['image' => 'Exhaust Fan Accessories.png', 'name' => 'Exhaust Fan Accessories'],
                ['image' => 'Exhaust Fan Grease Box.png', 'name' => 'Exhaust Fan Grease Box'],
                ['image' => 'Exhaust Fan Motor and Blower Assembly.png', 'name' => 'Exhaust Fan Motor & Blower Assembly'],
                ['image' => 'Exhaust Hood Fan Motor (Replacement).png', 'name' => 'Exhaust Hood Fan Motor (Replacement)'],
                ['image' => 'Exhaust Hood.png', 'name' => 'Exhaust Hood'],
                ['image' => 'EZ Kleen Industrial Air Filter.png', 'name' => 'EZ Kleen Industrial Air Filter'],
                ['image' => 'Fire Suppression Blow-Off Caps.png', 'name' => 'Fire Suppression Blow-Off Caps'],
                ['image' => 'Food Truck Exhaust Fans.png', 'name' => 'Food Truck Exhaust Fans'],
                ['image' => 'Galvanized Conical Duct Transition.png', 'name' => 'Galvanized Conical Duct Transition'],
                ['image' => 'Grease Away Rooftop Grease Neutralize – 16oz Shaker.png', 'name' => 'Grease Away Rooftop Grease Neutralizer – 16 oz Shaker'],
                ['image' => 'Grease Catcher.png', 'name' => 'Grease Catcher'],
                ['image' => 'Grease containment ring.png', 'name' => 'Grease Containment Ring'],
                ['image' => 'Grease Cups & Drains.png', 'name' => 'Grease Cups & Drains'],
                ['image' => 'Grease Drain Kit.png', 'name' => 'Grease Drain Kit'],
                ['image' => 'Grease Hood Filter (Mesh 320).png', 'name' => 'Grease Hood Filter (Mesh 320)'],
                ['image' => 'Hinge Kit Exhaust Fan Hinge.png', 'name' => 'Exhaust Fan Hinge Kit'],
                ['image' => 'Hinge Kits.png', 'name' => 'Hinge Kits'],
                ['image' => 'Hood Filters with Bottom Hooks – All Brands.png', 'name' => 'Hood Filters with Bottom Hooks – All Brands'],
                ['image' => 'HVAC duct insulation.png', 'name' => 'HVAC Duct Insulation'],
                ['image' => 'Kason Welded Grease Filters.png', 'name' => 'Kason Welded Grease Filters'],
                ['image' => 'Mavrik Stainless Steel Hood Filters.jpg', 'name' => 'Mavrik Stainless Steel Hood Filters'],
                ['image' => 'Metal Electrical Junction Box with Terminal Block.png', 'name' => 'Metal Electrical Junction Box with Terminal Block'],
                ['image' => 'Omni Super Hinge.png', 'name' => 'Omni Super Hinge'],
                ['image' => 'Optional Wire Guard Replacement for Canopy Lighting.png', 'name' => 'Optional Wire Guard for Canopy Lighting'],
                ['image' => 'PVC 90-degree elbow.png', 'name' => 'PVC 90-degree elbow'],
                ['image' => 'Replacement Grease Pillows.png', 'name' => 'Replacement Grease Pillows'],
                ['image' => 'Restaurant Upblast Exhaust.png', 'name' => 'Restaurant Upblast Exhaust'],
                ['image' => 'Roof Curbs.png', 'name' => 'Roof Curbs'],
                ['image' => 'Roof Exhaust Fan Grease Containment Mat.jpg', 'name' => 'Roof Exhaust Fan Grease Containment Mat'],
                ['image' => 'Spark Arrestor Hood Filters.png', 'name' => 'Spark Arrestor Hood Filters'],
                ['image' => 'Spill Prevention and Clean Up.png', 'name' => 'Spill Prevention & Clean-Up'],
                ['image' => 'Standard Aluminum Grease Filters.png', 'name' => 'Standard Aluminum Grease Filters'],
                ['image' => 'Standard Galvanized Grease Filters.png', 'name' => 'Standard Galvanized Grease Filters'],
                ['image' => 'Start Stop Push Button Station.png', 'name' => 'Start/Stop Push Button Station'],
                ['image' => 'Upflow Installation Kit (Vertical Return Air Kit).png', 'name' => 'Upflow Installation Kit (Vertical Return Air Kit)'],
            ];
            foreach ($products as $index => $product):
            ?>
            <div class="product-preview-tile" data-index="<?php echo $index; ?>" data-image="../../Images/hoodvent/<?php echo htmlspecialchars($product['image']); ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>" onclick="toggleProductSelection(this)">
                <img src="../../Images/hoodvent/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <div class="preview-name"><?php echo htmlspecialchars($product['name']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="side-panel-count" id="sidePanelCount">0 products selected</div>
</div>

<!-- =============================================
     MAIN CONTENT
     ============================================= -->
<div class="main-content" id="mainContent">

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
                                <div class="info-cell info-label">Next recommended date:</div>
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

    <!-- =========================================
         PAGE 3 - PRODUCTS (hidden by default)
         ========================================= -->
    <div class="page section-7-hidden" id="page3">
        <div class="page-label">Page 3</div>

        <!-- Repeat header for page 3 -->
        <div class="header">
            <?php if ($logo_base64): ?>
                <img src="<?php echo $logo_base64; ?>" class="company-logo" alt="Prime Facility Services Group">
            <?php endif; ?>
            <div class="report-title">KITCHEN EXHAUST CLEANING AND GREASE GUTTER SERVICE REPORT</div>
        </div>

        <!-- 7. ACCEPTANCE OF REPAIR PARTS AND AUTHORIZATION (appears when products selected from side panel) -->
        <div class="section section-7-detail" id="section7Detail">
            <div class="section-header">7. ACCEPTANCE OF REPAIR PARTS AND AUTHORIZATION</div>
            <div class="section-content">
                <p style="font-size: 9px; color: #555; margin-bottom: 6px;">If additional parts or products are needed, please indicate the quantity required for each item below.</p>
                <div class="selected-products-grid" id="selectedProductsGrid">
                    <!-- Dynamically populated by JavaScript -->
                </div>

                <div class="authorization-block">
                    <p>By signing below, the Customer acknowledges and agrees to the repair parts listed above and the recommended repairs described in the Technician Notes/Observations. The Customer authorizes Prime to proceed with the described repairs and installations.</p>

                    <label class="authorization-checkbox-wrapper" id="authCheckboxWrapper">
                        <input type="checkbox" id="authCheckbox">
                        <span class="authorization-checkbox-label">I authorize the additional repairs and parts listed above and approve the associated charges.</span>
                    </label>

                    <div class="authorization-signature disabled" id="authSignatureArea">
                        <div class="authorization-sig-box">
                            <label>Client / Manager:</label>
                            <span>Name:</span>
                            <input type="text" class="editable-field" id="auth_client_name" style="width: 140px;" placeholder="________________">
                        </div>
                        <div class="authorization-sig-box">
                            <label>Signature:</label>
                            <div class="signature-canvas-wrapper">
                                <canvas class="signature-canvas" id="sig-auth-client"></canvas>
                                <button class="clear-sig-btn" onclick="clearSignature('sig-auth-client')">Clear</button>
                            </div>
                        </div>
                        <div class="authorization-sig-box">
                            <label>Date:</label>
                            <div class="date-input-wrapper">
                                <input type="text" class="editable-field date-field" id="auth_client_date" placeholder="MM/DD/YYYY" maxlength="10">
                                <input type="date" id="auth_client_date_picker" tabindex="-1">
                                <button type="button" class="calendar-btn" data-picker="auth_client_date_picker" data-target="auth_client_date" title="Pick date">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zM7 12h5v5H7z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE 3 FOOTER -->
        <div class="page-footer">
            <div class="footer-location">Houston, TX 77063</div>
            <div class="footer-contact">Phone: 713-338-2553 | www.primefacilityservicesgroup.com</div>
            <div class="footer-legal">This document is confidential and intended solely for the addressee. &copy; <?php echo date('Y'); ?> Prime Facility Services Group</div>
        </div>
    </div>

</div>

</div><!-- /main-content -->

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

        // Set canvas internal resolution to match display size, accounting for devicePixelRatio
        function resizeCanvas() {
            var rect = canvas.getBoundingClientRect();
            // Skip if canvas is hidden (zero dimensions)
            if (rect.width === 0 || rect.height === 0) {
                initialized = false;
                return;
            }
            var dpr = window.devicePixelRatio || 1;
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            // Re-apply context styles after canvas dimension change (resets context)
            applyContextStyles(ctx);
            initialized = true;
        }

        // Ensure canvas is properly sized before first draw
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
            // Draw a dot for single taps/clicks
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

        // Mouse events
        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDraw);
        canvas.addEventListener('mouseleave', stopDraw);

        // Touch events - prevent scrolling while signing
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
    // SIDE PANEL - OPEN / CLOSE
    // =============================================
    function openSidePanel() {
        var panel = document.getElementById('sidePanel');
        var main = document.getElementById('mainContent');
        var btn = document.getElementById('btnToggleProducts');
        panel.classList.add('open');
        main.classList.add('panel-open');
        btn.textContent = 'Close Product Catalog';
        btn.style.background = '#d63031';
    }

    window.closeSidePanel = function() {
        var panel = document.getElementById('sidePanel');
        var main = document.getElementById('mainContent');
        var btn = document.getElementById('btnToggleProducts');
        panel.classList.remove('open');
        main.classList.remove('panel-open');
        btn.textContent = 'Add Products Section';
        btn.style.background = '#6c5ce7';
    };

    // =============================================
    // TOGGLE PRODUCTS SECTION (Section 7)
    // =============================================
    window.toggleProductsSection = function() {
        var panel = document.getElementById('sidePanel');
        if (panel.classList.contains('open')) {
            // Close panel and clear selections
            closeSidePanel();
            // Hide page 3 and clear selections
            var page3 = document.getElementById('page3');
            page3.classList.add('section-7-hidden');
            document.querySelectorAll('.product-preview-tile.selected').forEach(function(tile) {
                tile.classList.remove('selected');
            });
            document.getElementById('selectedProductsGrid').innerHTML = '';
            document.getElementById('section7Detail').classList.remove('visible');
            updatePanelCount();
            var authName = document.getElementById('auth_client_name');
            var authDate = document.getElementById('auth_client_date');
            if (authName) authName.value = '';
            if (authDate) authDate.value = '';
            clearSignature('sig-auth-client');
            // Reset authorization checkbox
            var authCheckbox = document.getElementById('authCheckbox');
            var authWrapper = document.getElementById('authCheckboxWrapper');
            var authSigArea = document.getElementById('authSignatureArea');
            if (authCheckbox) authCheckbox.checked = false;
            if (authWrapper) authWrapper.classList.remove('checked');
            if (authSigArea) authSigArea.classList.add('disabled');
        } else {
            // Open the side panel
            openSidePanel();
            // Show page 3 (for Section 7)
            var page3 = document.getElementById('page3');
            page3.classList.remove('section-7-hidden');
            // Re-initialize sig-auth-client canvas now that page 3 is visible
            if (signaturePads['sig-auth-client']) {
                signaturePads['sig-auth-client'].ensureInit();
            }
        }
    };

    // =============================================
    // PRODUCT SELECTION (Preview Grid)
    // =============================================
    window.toggleProductSelection = function(tile) {
        tile.classList.toggle('selected');
        updateSelectedProducts();
    };

    function updatePanelCount() {
        var count = document.querySelectorAll('.product-preview-tile.selected').length;
        var countEl = document.getElementById('sidePanelCount');
        if (count > 0) {
            countEl.textContent = count + ' product' + (count > 1 ? 's' : '') + ' selected';
            countEl.classList.add('has-items');
        } else {
            countEl.classList.remove('has-items');
        }
    }

    function updateSelectedProducts() {
        var selectedTiles = document.querySelectorAll('.product-preview-tile.selected');
        var grid = document.getElementById('selectedProductsGrid');
        var detail = document.getElementById('section7Detail');

        updatePanelCount();

        if (selectedTiles.length === 0) {
            detail.classList.remove('visible');
            grid.innerHTML = '';
            return;
        }

        detail.classList.add('visible');
        grid.innerHTML = '';

        selectedTiles.forEach(function(tile) {
            var index = tile.getAttribute('data-index');
            var name = tile.getAttribute('data-name');
            var image = tile.getAttribute('data-image');

            var card = document.createElement('div');
            card.className = 'selected-product-card';
            card.innerHTML =
                '<img src="' + image + '" alt="' + name + '">' +
                '<div class="product-name">' + name + '</div>' +
                '<div class="qty-row">' +
                    '<label>Qty:</label>' +
                    '<input type="number" class="qty-input" id="product_qty_' + index + '" min="0" value="" placeholder="0">' +
                '</div>';
            grid.appendChild(card);
        });

        detail.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

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

        // Reset authorization checkbox
        var authCheckbox = document.getElementById('authCheckbox');
        var authWrapper = document.getElementById('authCheckboxWrapper');
        var authSigArea = document.getElementById('authSignatureArea');
        if (authCheckbox) authCheckbox.checked = false;
        if (authWrapper) authWrapper.classList.remove('checked');
        if (authSigArea) authSigArea.classList.add('disabled');

        // Close side panel and hide products section
        closeSidePanel();
        var page3 = document.getElementById('page3');
        if (page3) {
            page3.classList.add('section-7-hidden');
        }
        // Clear product selections
        document.querySelectorAll('.product-preview-tile.selected').forEach(function(tile) {
            tile.classList.remove('selected');
        });
        document.getElementById('selectedProductsGrid').innerHTML = '';
        document.getElementById('section7Detail').classList.remove('visible');
        updatePanelCount();
    };

    // =============================================
    // AUTHORIZATION CHECKBOX LOGIC
    // =============================================
    function initAuthorizationCheckbox() {
        var checkbox = document.getElementById('authCheckbox');
        var wrapper = document.getElementById('authCheckboxWrapper');
        var sigArea = document.getElementById('authSignatureArea');

        if (!checkbox || !sigArea) return;

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                sigArea.classList.remove('disabled');
                wrapper.classList.add('checked');
                // Ensure signature canvas is initialized now that it's enabled
                if (signaturePads['sig-auth-client']) {
                    signaturePads['sig-auth-client'].ensureInit();
                }
            } else {
                sigArea.classList.add('disabled');
                wrapper.classList.remove('checked');
                // Clear signature when unchecking authorization
                clearSignature('sig-auth-client');
            }
        });
    }

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

            // Click calendar button -> open native date picker
            btn.addEventListener('click', function() {
                picker.showPicker ? picker.showPicker() : picker.click();
            });

            // When a date is selected from the picker, fill the text field
            picker.addEventListener('change', function() {
                if (this.value) {
                    var parts = this.value.split('-'); // YYYY-MM-DD
                    target.value = parts[1] + '/' + parts[2] + '/' + parts[0]; // MM/DD/YYYY
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
        initSignaturePad('sig-auth-client');

        // Initialize date field auto-formatting
        initDateFields();

        // Initialize calendar picker buttons
        initCalendarButtons();

        // Initialize authorization checkbox
        initAuthorizationCheckbox();

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
