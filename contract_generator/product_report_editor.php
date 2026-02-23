<?php
/**
 * PRODUCT REPORT - INVOICE FOR REPLACEMENT PARTS
 * Independent document with its own invoice-style design.
 * Uses the same product catalog as the Service Report.
 * No authorization checkbox - only signature block.
 */

require_once 'config/db_config.php';

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

// Logo - dynamic based on Service_Type
$dept = strtolower(trim($data['Service_Type'] ?? ''));
if (strpos($dept, 'hospitality') !== false) {
    $logo_path = __DIR__ . '/../Images/Hospitality.png';
} else {
    $logo_path = __DIR__ . '/../Images/Facility.png';
}
$logo_base64 = '';
if (file_exists($logo_path)) {
    $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
}

// Product catalog (same as Service Report)
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice for Replacement Parts</title>
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
            background: linear-gradient(135deg, #4a1a6b 0%, #6f42c1 100%);
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

        .btn-add-products {
            background: #e65100;
            color: white;
        }

        .btn-back {
            background: rgba(255,255,255,0.15);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            text-decoration: none;
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
            border-bottom: 3px solid #4a1a6b;
            padding-bottom: 8px;
            margin-bottom: 10px;
            text-align: center;
        }

        .company-logo {
            max-width: 280px;
            max-height: 90px;
            margin-bottom: 6px;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #4a1a6b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 4px;
        }

        .report-description {
            font-size: 9px;
            color: #555;
            margin-top: 4px;
            font-style: italic;
            line-height: 1.4;
            max-width: 90%;
            margin-left: auto;
            margin-right: auto;
        }

        /* =============================================
           SECTIONS
           ============================================= */
        .section {
            margin-bottom: 6px;
            border: 1px solid #ddd;
            border-radius: 2px;
            overflow: hidden;
        }

        .section-header {
            background: #4a1a6b;
            color: #ffffff;
            padding: 4px 10px;
            font-weight: bold;
            font-size: 10px;
        }

        .section-content {
            padding: 6px 10px;
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
            background: #f3e8ff;
        }

        .editable-field:focus {
            border-bottom: 2px solid #4a1a6b;
            background: #f3e8ff;
        }

        .editable-field.wide { width: 180px; }
        .editable-field.medium { width: 120px; }
        .editable-field.small { width: 60px; }

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
            background: #f3e8ff;
            border-color: #4a1a6b;
        }

        .calendar-btn svg {
            width: 14px;
            height: 14px;
            fill: #555;
        }

        .calendar-btn:hover svg {
            fill: #4a1a6b;
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
            color: #4a1a6b;
            width: 35%;
            white-space: nowrap;
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

        /* =============================================
           INVOICE TABLE
           ============================================= */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }

        .invoice-table th {
            background: #4a1a6b;
            color: white;
            padding: 5px 8px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .invoice-table th.text-right {
            text-align: right;
        }

        .invoice-table th.text-center {
            text-align: center;
        }

        .invoice-table td {
            border: 1px solid #ddd;
            padding: 4px 8px;
            font-size: 9px;
            vertical-align: middle;
        }

        .invoice-table td.text-right {
            text-align: right;
        }

        .invoice-table td.text-center {
            text-align: center;
        }

        .invoice-table tbody tr:nth-child(even) {
            background: #f9f6fd;
        }

        .invoice-table tbody tr:hover {
            background: #f3e8ff;
        }

        .invoice-table .product-cell {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .invoice-table .product-cell img {
            width: 36px;
            height: 36px;
            object-fit: contain;
            border: 1px solid #eee;
            border-radius: 3px;
            flex-shrink: 0;
        }

        .invoice-table .product-cell .product-name {
            font-weight: 600;
            color: #333;
        }

        .invoice-table .price-input {
            width: 80px;
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 3px 6px;
            font-size: 9px;
            text-align: right;
            outline: none;
            font-family: inherit;
        }

        .invoice-table .price-input:focus {
            border-color: #4a1a6b;
            background: #f3e8ff;
        }

        .invoice-table .qty-input {
            width: 50px;
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 3px 6px;
            font-size: 9px;
            text-align: center;
            outline: none;
            font-family: inherit;
        }

        .invoice-table .qty-input:focus {
            border-color: #4a1a6b;
            background: #f3e8ff;
        }

        .invoice-table .subtotal-cell {
            font-weight: bold;
            color: #4a1a6b;
        }

        .invoice-table .remove-btn {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 14px;
            padding: 2px 4px;
            line-height: 1;
            border-radius: 3px;
            transition: background 0.2s;
        }

        .invoice-table .remove-btn:hover {
            background: #fee;
        }

        /* Empty state */
        .invoice-empty {
            text-align: center;
            padding: 20px;
            color: #999;
            font-size: 11px;
            font-style: italic;
        }

        /* =============================================
           TOTALS BLOCK
           ============================================= */
        .totals-block {
            margin-top: 8px;
            display: flex;
            justify-content: flex-end;
        }

        .totals-table {
            width: 260px;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 4px 10px;
            font-size: 10px;
        }

        .totals-table .totals-label {
            text-align: right;
            font-weight: bold;
            color: #555;
        }

        .totals-table .totals-value {
            text-align: right;
            color: #333;
            min-width: 80px;
        }

        .totals-table .grand-total-row td {
            border-top: 2px solid #4a1a6b;
            padding-top: 6px;
            font-size: 12px;
        }

        .totals-table .grand-total-row .totals-label {
            color: #4a1a6b;
        }

        .totals-table .grand-total-row .totals-value {
            color: #4a1a6b;
            font-weight: bold;
        }

        .tax-rate-input {
            width: 50px;
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 2px 4px;
            font-size: 9px;
            text-align: right;
            outline: none;
            font-family: inherit;
        }

        .tax-rate-input:focus {
            border-color: #4a1a6b;
            background: #f3e8ff;
        }

        /* =============================================
           SIGNATURE SECTION
           ============================================= */
        .signature-section {
            display: table;
            width: 100%;
            margin-top: 6px;
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
            color: #4a1a6b;
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
           SIDE PANEL - PRODUCT CATALOG
           ============================================= */
        .side-panel {
            position: fixed;
            top: 0;
            right: -380px;
            width: 380px;
            height: 100vh;
            background: white;
            box-shadow: -4px 0 20px rgba(0,0,0,0.2);
            z-index: 999;
            display: flex;
            flex-direction: column;
            transition: right 0.3s ease;
        }

        .side-panel.open {
            right: 0;
        }

        .side-panel-header {
            background: #4a1a6b;
            color: white;
            padding: 14px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .side-panel-header h3 {
            font-size: 14px;
            margin: 0;
        }

        .side-panel-close {
            background: none;
            border: none;
            color: white;
            font-size: 22px;
            cursor: pointer;
            line-height: 1;
            padding: 0 4px;
        }

        .side-panel-close:hover {
            opacity: 0.7;
        }

        .side-panel-body {
            flex: 1;
            overflow-y: auto;
            padding: 14px;
        }

        .panel-hint {
            font-size: 12px;
            color: #666;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .side-panel .product-preview-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .side-panel .product-preview-tile {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            background: white;
        }

        .side-panel .product-preview-tile:hover {
            border-color: #6f42c1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(111,66,193,0.15);
        }

        .side-panel .product-preview-tile.selected {
            border-color: #4a1a6b;
            background: #f9f0ff;
        }

        .side-panel .product-preview-tile.selected::after {
            content: "\2713";
            position: absolute;
            top: 6px;
            right: 8px;
            background: #4a1a6b;
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            font-size: 12px;
            line-height: 20px;
            text-align: center;
            font-weight: bold;
        }

        .side-panel .product-preview-tile img {
            width: 100%;
            height: 80px;
            object-fit: contain;
            margin-bottom: 6px;
        }

        .side-panel .product-preview-tile .preview-name {
            font-size: 10px;
            font-weight: 600;
            color: #333;
            line-height: 1.2;
        }

        .side-panel-count {
            background: #f5f5f5;
            padding: 10px 18px;
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
        }

        .side-panel-count.has-items {
            background: #f9f0ff;
            color: #4a1a6b;
        }

        /* =============================================
           MAIN CONTENT
           ============================================= */
        .main-content {
            transition: margin-right 0.3s ease;
        }

        .main-content.panel-open {
            margin-right: 380px;
        }

        /* =============================================
           EMBEDDED MODE (inside iframe)
           ============================================= */
        body.embedded .toolbar {
            position: sticky;
        }

        body.embedded .pages-container {
            padding-top: 20px;
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
            .invoice-table .price-input,
            .invoice-table .qty-input,
            .tax-rate-input {
                border: none !important;
                border-bottom: none !important;
                background: transparent !important;
            }

            .calendar-btn,
            .date-input-wrapper input[type="date"] {
                display: none !important;
            }

            .signature-canvas-wrapper {
                border: none !important;
            }

            .clear-sig-btn {
                display: none !important;
            }

            .side-panel {
                display: none !important;
            }

            .main-content.panel-open {
                margin-right: 0 !important;
            }

            .invoice-table .remove-btn {
                display: none !important;
            }

            @page {
                margin: 0;
                size: letter portrait;
            }
        }

        /* =============================================
           RESPONSIVE
           ============================================= */
        @media (max-width: 768px) {
            .page {
                width: 100%;
                padding: 12px;
                min-height: auto;
            }

            .side-panel {
                width: 100%;
                right: -100%;
            }

            .main-content.panel-open {
                margin-right: 0;
            }

            .toolbar {
                flex-wrap: wrap;
                gap: 8px;
                padding: 8px 12px;
            }

            .toolbar-actions {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body<?php if (isset($_GET['embedded'])) echo ' class="embedded"'; ?>>

<!-- =============================================
     TOOLBAR
     ============================================= -->
<div class="toolbar">
    <div class="toolbar-title">
        INVOICE FOR REPLACEMENT PARTS
    </div>
    <div class="toolbar-actions">
        <button class="toolbar-btn btn-add-products" id="btnToggleProducts" onclick="toggleProductsPanel()">Select Products</button>
        <button class="toolbar-btn btn-clear" onclick="clearAllFields()">Clear All</button>
        <button class="toolbar-btn btn-print" onclick="window.print()">Print Invoice</button>
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
        <p class="panel-hint">Select the products needed for replacement. Selected items will appear in the invoice table.</p>
        <div class="product-preview-grid" id="productPreviewGrid">
            <?php foreach ($products as $index => $product): ?>
            <div class="product-preview-tile" data-index="<?php echo $index; ?>" data-image="../Images/hoodvent/<?php echo htmlspecialchars($product['image']); ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>" onclick="toggleProductSelection(this)">
                <img src="../Images/hoodvent/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
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
            <div class="report-title">INVOICE FOR REPLACEMENT PARTS</div>
            <div class="report-description">
                Based on the diagnosis performed, parts with deterioration that require replacement have been identified.
                This document details the items needed to restore the system to optimal operating condition.
            </div>
        </div>

        <!-- INVOICE INFO -->
        <div class="section">
            <div class="section-header">INVOICE INFORMATION</div>
            <div class="section-content">
                <div class="two-columns">
                    <div class="column">
                        <div class="info-grid">
                            <div class="info-row">
                                <div class="info-cell info-label">Invoice #:</div>
                                <div class="info-cell info-value">
                                    <input type="text" class="editable-field wide" id="invoice_number" value="" placeholder="Enter invoice number">
                                </div>
                            </div>
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
                                <div class="info-cell info-label">Date:</div>
                                <div class="info-cell info-value">
                                    <div class="date-input-wrapper">
                                        <input type="text" class="editable-field date-field" id="invoice_date" placeholder="MM/DD/YYYY" maxlength="10">
                                        <input type="date" id="invoice_date_picker" tabindex="-1">
                                        <button type="button" class="calendar-btn" data-picker="invoice_date_picker" data-target="invoice_date" title="Pick date">
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

        <!-- CLIENT INFORMATION -->
        <div class="section">
            <div class="section-header">CLIENT INFORMATION</div>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-cell info-label">Client / Company:</div>
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
                        <div class="info-cell info-label">Contact:</div>
                        <div class="info-cell info-value">
                            <input type="text" class="editable-field wide" id="client_contact" value="<?php echo htmlspecialchars($client_contact); ?>" placeholder="Enter contact person">
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

        <!-- PRODUCTS / ITEMS TABLE -->
        <div class="section">
            <div class="section-header">REPLACEMENT PARTS DETAIL</div>
            <div class="section-content" style="padding: 4px 0;">
                <div id="invoiceTableWrapper">
                    <div class="invoice-empty" id="invoiceEmpty">
                        No products selected. Click <strong>"Select Products"</strong> in the toolbar to open the product catalog.
                    </div>
                    <table class="invoice-table" id="invoiceTable" style="display: none;">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Product</th>
                                <th class="text-center" style="width: 12%;">Qty</th>
                                <th class="text-right" style="width: 18%;">Unit Price</th>
                                <th class="text-right" style="width: 18%;">Subtotal</th>
                                <th class="text-center" style="width: 12%;">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody id="invoiceTableBody">
                        </tbody>
                    </table>
                </div>

                <!-- TOTALS -->
                <div class="totals-block" id="totalsBlock" style="display: none;">
                    <table class="totals-table">
                        <tr>
                            <td class="totals-label">Total:</td>
                            <td class="totals-value" id="totalAmount">$0.00</td>
                        </tr>
                        <tr>
                            <td class="totals-label">
                                Taxes (<input type="number" class="tax-rate-input" id="taxRate" value="8.25" min="0" max="100" step="0.01">%):
                            </td>
                            <td class="totals-value" id="taxAmount">$0.00</td>
                        </tr>
                        <tr class="grand-total-row">
                            <td class="totals-label">Grand Total:</td>
                            <td class="totals-value" id="grandTotal">$0.00</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- SIGNATURES -->
        <div class="section">
            <div class="section-header">SIGNATURES</div>
            <div class="section-content">
                <div class="signature-section">
                    <div class="signature-box">
                        <p style="font-weight: bold; margin-bottom: 2px; color: #4a1a6b; font-size: 10px;">Client / Manager:</p>
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
                    <div class="signature-box">
                        <p style="font-weight: bold; margin-bottom: 2px; color: #4a1a6b; font-size: 10px;">Responsible Technician:</p>
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
                                <input type="text" class="editable-field date-field" id="tech_sig_date" placeholder="MM/DD/YYYY" maxlength="10">
                                <input type="date" id="tech_sig_date_picker" tabindex="-1">
                                <button type="button" class="calendar-btn" data-picker="tech_sig_date_picker" data-target="tech_sig_date" title="Pick date">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zM7 12h5v5H7z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE FOOTER -->
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
    // SIGNATURE PAD
    // =============================================
    var signaturePads = {};

    function applyContextStyles(ctx) {
        ctx.strokeStyle = '#4a1a6b';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }

    function initSignaturePad(canvasId) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return;

        var ctx = canvas.getContext('2d');
        var drawing = false;
        var initialized = false;
        var lastX, lastY;

        function ensureInit() {
            if (initialized && canvas.width > 0) return;
            var rect = canvas.getBoundingClientRect();
            if (rect.width === 0) return;
            var dpr = window.devicePixelRatio || 1;
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            applyContextStyles(ctx);
            initialized = true;
        }

        function resize() {
            var imgData = null;
            if (initialized) {
                try { imgData = canvas.toDataURL(); } catch(e) {}
            }
            var rect = canvas.getBoundingClientRect();
            if (rect.width === 0) return;
            var dpr = window.devicePixelRatio || 1;
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            applyContextStyles(ctx);
            initialized = true;
            if (imgData) {
                var img = new Image();
                img.onload = function() {
                    ctx.drawImage(img, 0, 0, rect.width, rect.height);
                    applyContextStyles(ctx);
                };
                img.src = imgData;
            }
        }

        function getPos(e) {
            var rect = canvas.getBoundingClientRect();
            var touch = e.touches ? e.touches[0] : e;
            return {
                x: touch.clientX - rect.left,
                y: touch.clientY - rect.top
            };
        }

        function startDraw(e) {
            ensureInit();
            drawing = true;
            var pos = getPos(e);
            lastX = pos.x;
            lastY = pos.y;
            e.preventDefault();
        }

        function draw(e) {
            if (!drawing) return;
            var pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            lastX = pos.x;
            lastY = pos.y;
            e.preventDefault();
        }

        function stopDraw() {
            drawing = false;
        }

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDraw);
        canvas.addEventListener('mouseleave', stopDraw);
        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDraw);

        signaturePads[canvasId] = {
            canvas: canvas,
            ctx: ctx,
            ensureInit: ensureInit,
            resize: resize
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
        btn.textContent = 'Close Catalog';
        btn.style.background = '#d63031';
    }

    window.closeSidePanel = function() {
        var panel = document.getElementById('sidePanel');
        var main = document.getElementById('mainContent');
        var btn = document.getElementById('btnToggleProducts');
        panel.classList.remove('open');
        main.classList.remove('panel-open');
        btn.textContent = 'Select Products';
        btn.style.background = '#e65100';
    };

    window.toggleProductsPanel = function() {
        var panel = document.getElementById('sidePanel');
        if (panel.classList.contains('open')) {
            closeSidePanel();
        } else {
            openSidePanel();
        }
    };

    // =============================================
    // PRODUCT SELECTION
    // =============================================
    window.toggleProductSelection = function(tile) {
        tile.classList.toggle('selected');
        updatePanelCount();
        updateInvoiceTable();
    };

    function updatePanelCount() {
        var count = document.querySelectorAll('.product-preview-tile.selected').length;
        var countEl = document.getElementById('sidePanelCount');
        if (count > 0) {
            countEl.textContent = count + ' product' + (count > 1 ? 's' : '') + ' selected';
            countEl.classList.add('has-items');
        } else {
            countEl.textContent = '0 products selected';
            countEl.classList.remove('has-items');
        }
    }

    // Store price and qty values to preserve them when rebuilding
    var productData = {};

    function updateInvoiceTable() {
        var selectedTiles = document.querySelectorAll('.product-preview-tile.selected');
        var tableEl = document.getElementById('invoiceTable');
        var emptyEl = document.getElementById('invoiceEmpty');
        var totalsEl = document.getElementById('totalsBlock');
        var tbody = document.getElementById('invoiceTableBody');

        if (selectedTiles.length === 0) {
            tableEl.style.display = 'none';
            emptyEl.style.display = 'block';
            totalsEl.style.display = 'none';
            tbody.innerHTML = '';
            recalculateTotals();
            return;
        }

        tableEl.style.display = 'table';
        emptyEl.style.display = 'none';
        totalsEl.style.display = 'flex';

        // Save current values before rebuilding
        saveCurrentValues();

        tbody.innerHTML = '';

        selectedTiles.forEach(function(tile) {
            var index = tile.getAttribute('data-index');
            var name = tile.getAttribute('data-name');
            var image = tile.getAttribute('data-image');

            var savedQty = productData[index] ? productData[index].qty : '';
            var savedPrice = productData[index] ? productData[index].price : '';

            var tr = document.createElement('tr');
            tr.setAttribute('data-index', index);
            tr.innerHTML =
                '<td>' +
                    '<div class="product-cell">' +
                        '<img src="' + escapeAttr(image) + '" alt="' + escapeAttr(name) + '">' +
                        '<span class="product-name">' + escapeHtml(name) + '</span>' +
                    '</div>' +
                '</td>' +
                '<td class="text-center">' +
                    '<input type="number" class="qty-input" data-index="' + index + '" min="1" value="' + escapeAttr(savedQty) + '" placeholder="0" oninput="recalculateRow(this)">' +
                '</td>' +
                '<td class="text-right">' +
                    '<input type="number" class="price-input" data-index="' + index + '" min="0" step="0.01" value="' + escapeAttr(savedPrice) + '" placeholder="0.00" oninput="recalculateRow(this)">' +
                '</td>' +
                '<td class="text-right subtotal-cell" id="subtotal_' + index + '">$0.00</td>' +
                '<td class="text-center">' +
                    '<button class="remove-btn" onclick="removeProduct(' + index + ')" title="Remove">&times;</button>' +
                '</td>';
            tbody.appendChild(tr);

            // Calculate subtotal for this row if values exist
            if (savedQty && savedPrice) {
                var subtotal = parseFloat(savedQty) * parseFloat(savedPrice);
                document.getElementById('subtotal_' + index).textContent = '$' + subtotal.toFixed(2);
            }
        });

        recalculateTotals();
    }

    function saveCurrentValues() {
        var rows = document.querySelectorAll('#invoiceTableBody tr');
        rows.forEach(function(row) {
            var index = row.getAttribute('data-index');
            var qtyInput = row.querySelector('.qty-input');
            var priceInput = row.querySelector('.price-input');
            if (index !== null) {
                productData[index] = {
                    qty: qtyInput ? qtyInput.value : '',
                    price: priceInput ? priceInput.value : ''
                };
            }
        });
    }

    window.recalculateRow = function(input) {
        var index = input.getAttribute('data-index');
        var row = input.closest('tr');
        var qtyInput = row.querySelector('.qty-input');
        var priceInput = row.querySelector('.price-input');
        var subtotalCell = document.getElementById('subtotal_' + index);

        var qty = parseFloat(qtyInput.value) || 0;
        var price = parseFloat(priceInput.value) || 0;
        var subtotal = qty * price;

        subtotalCell.textContent = '$' + subtotal.toFixed(2);

        // Update stored data
        productData[index] = {
            qty: qtyInput.value,
            price: priceInput.value
        };

        recalculateTotals();
    };

    function recalculateTotals() {
        var rows = document.querySelectorAll('#invoiceTableBody tr');
        var total = 0;

        rows.forEach(function(row) {
            var qtyInput = row.querySelector('.qty-input');
            var priceInput = row.querySelector('.price-input');
            var qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
            var price = parseFloat(priceInput ? priceInput.value : 0) || 0;
            total += qty * price;
        });

        var taxRate = parseFloat(document.getElementById('taxRate').value) || 0;
        var taxes = total * (taxRate / 100);
        var grandTotal = total + taxes;

        document.getElementById('totalAmount').textContent = '$' + total.toFixed(2);
        document.getElementById('taxAmount').textContent = '$' + taxes.toFixed(2);
        document.getElementById('grandTotal').textContent = '$' + grandTotal.toFixed(2);
    }

    window.removeProduct = function(index) {
        // Deselect the tile in the side panel
        var tile = document.querySelector('.product-preview-tile[data-index="' + index + '"]');
        if (tile) {
            tile.classList.remove('selected');
        }
        // Remove stored data
        delete productData[index];
        updatePanelCount();
        updateInvoiceTable();
    };

    // Recalculate when tax rate changes
    document.getElementById('taxRate').addEventListener('input', function() {
        recalculateTotals();
    });

    // =============================================
    // CLEAR ALL FIELDS
    // =============================================
    window.clearAllFields = function() {
        if (!confirm('Are you sure you want to clear all fields? This cannot be undone.')) return;

        // Clear all input fields
        document.querySelectorAll('.editable-field').forEach(function(field) {
            field.value = '';
        });

        // Clear all signatures
        Object.keys(signaturePads).forEach(function(id) {
            clearSignature(id);
        });

        // Clear product selections
        document.querySelectorAll('.product-preview-tile.selected').forEach(function(tile) {
            tile.classList.remove('selected');
        });
        productData = {};
        updatePanelCount();
        updateInvoiceTable();

        // Reset tax rate
        document.getElementById('taxRate').value = '8.25';

        // Close side panel
        closeSidePanel();
    };

    // =============================================
    // DATE FIELD AUTO-FORMAT (MM/DD/YYYY)
    // =============================================
    function initDateFields() {
        document.querySelectorAll('.date-field').forEach(function(field) {
            field.addEventListener('input', function() {
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
    // UTILITY
    // =============================================
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function escapeAttr(str) {
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // =============================================
    // INITIALIZE
    // =============================================
    document.addEventListener('DOMContentLoaded', function() {
        initSignaturePad('sig-client');
        initSignaturePad('sig-tech');
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
