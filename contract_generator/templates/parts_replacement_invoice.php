<?php
/**
 * PARTS REPLACEMENT INVOICE TEMPLATE
 * ====================================
 * Independent invoice document for parts replacement.
 * Used when sales_mode === 'product'.
 *
 * Required variables (passed by the controller):
 *   $data               - array with form/client data
 *   $allItems           - contract_items rows (service_type=Product, service_time=Qty, frequency=UnitPrice)
 *   $janitorialServices - items with category 'janitorial'
 *   $kitchenServices    - items with category 'kitchen'
 *   $hoodVentServices   - items with category 'hood_vent'
 *
 * In product mode the existing columns map as follows:
 *   service_type  => Product name
 *   service_time  => Quantity
 *   frequency     => Unit Price
 *   description   => Description
 *   subtotal      => Subtotal (Qty x Unit Price)
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
    $logo_path = __DIR__ . '/../../Images/Hospitality.png';
} else {
    $logo_path = __DIR__ . '/../../Images/Facility.png';
}
$logo_base64 = '';
if (file_exists($logo_path)) {
    $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
}

// Client info
$client_name    = $data['Company_Name'] ?? '';
$client_address = $data['Company_Address'] ?? '';
$client_contact = $data['client_name'] ?? $data['Client_Name'] ?? '';
$client_phone   = $data['Number_Phone'] ?? '';
$client_email   = $data['Email'] ?? '';
$client_title   = $data['Client_Title'] ?? '';
$seller         = $data['Seller'] ?? '';
$wo_number      = $data['docnum'] ?? '';

// Collect all product rows from all service categories
$productRows = array_merge($janitorialServices, $kitchenServices, $hoodVentServices);

// Calculate totals
$subtotalSum = 0;
foreach ($productRows as $row) {
    $subtotalSum += floatval($row['subtotal'] ?? 0);
}
$taxes      = $subtotalSum * 0.0825;
$grandTotal = $subtotalSum + $taxes;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Factura por Reemplazo de Piezas - <?php echo htmlspecialchars($client_name); ?></title>
    <style>
        @page {
            margin: 3cm 2cm 2.8cm 2cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        /* Header - fixed, repeats on every page */
        .header-wrapper {
            position: fixed;
            top: -2.5cm;
            left: 0;
            right: 0;
            overflow: visible;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px solid #CC0000;
        }

        .header-table td {
            vertical-align: middle;
        }

        .header-logo {
            max-height: 70px;
            width: auto;
        }

        .doc-title {
            color: #CC0000;
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .doc-subtitle {
            font-size: 9pt;
            color: #333;
            font-style: italic;
        }

        /* Footer - fixed, repeats on every page */
        .footer-wrapper {
            position: fixed;
            bottom: -2.3cm;
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

        /* Invoice Info Table */
        .invoice-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9pt;
        }

        .invoice-info td {
            padding: 4px 8px;
            vertical-align: top;
        }

        .invoice-info .label {
            font-weight: bold;
            color: #CC0000;
            width: 25%;
        }

        .invoice-info .value {
            width: 25%;
        }

        /* Description Box */
        .description-box {
            background-color: #f8f8f8;
            border-left: 4px solid #CC0000;
            padding: 10px 14px;
            margin-bottom: 15px;
            font-size: 9pt;
            line-height: 1.5;
        }

        .description-box strong {
            color: #CC0000;
        }

        /* Products Table */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .products-table th {
            background-color: #CC0000;
            color: white;
            font-weight: bold;
            padding: 8px 6px;
            text-align: center;
            border: 1px solid #000;
            font-size: 8pt;
            text-transform: uppercase;
        }

        .products-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 8pt;
        }

        .products-table .product-name {
            text-align: left;
            word-wrap: break-word;
        }

        .products-table .qty-cell {
            text-align: center;
        }

        .products-table .price-cell {
            text-align: right;
        }

        .products-table .subtotal-cell {
            text-align: right;
            font-weight: bold;
        }

        .products-table tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        /* Row number */
        .products-table .row-num {
            text-align: center;
            width: 30px;
            color: #666;
        }

        /* Totals Table */
        .totals-table {
            width: 280px;
            border-collapse: collapse;
            margin-left: auto;
            margin-bottom: 20px;
        }

        .totals-table td {
            padding: 6px 8px;
            font-size: 9pt;
            border: none;
        }

        .totals-table .value-cell {
            text-align: right;
            font-weight: bold;
            border: 1px solid #000;
        }

        .totals-table .label-cell {
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
        }

        .totals-table tr:last-child .label-cell {
            color: #CC0000;
        }

        .totals-table tr:last-child .value-cell {
            background-color: #CC0000;
            color: white;
            border: 1px solid #CC0000;
        }

        /* Signatures Section */
        .signatures-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .signatures-title {
            color: #CC0000;
            font-weight: bold;
            font-size: 10pt;
            text-transform: uppercase;
            margin-bottom: 15px;
            border-bottom: 2px solid #CC0000;
            padding-bottom: 4px;
        }

        .signatures-grid {
            display: table;
            width: 100%;
        }

        .signature-col {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding: 0 10px;
        }

        .signature-col:first-child {
            padding-left: 0;
        }

        .signature-col:last-child {
            padding-right: 0;
        }

        .sig-box {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 10px;
            height: 70px;
        }

        .sig-label {
            font-weight: bold;
            font-size: 9pt;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .sig-line {
            border-top: 1px solid #000;
            margin-top: 35px;
            padding-top: 3px;
            font-size: 8pt;
        }
    </style>
</head>
<body>

    <!-- HEADER (repeats on every page) -->
    <div class="header-wrapper">
        <table class="header-table">
            <tr>
                <td style="width: 30%;">
                    <?php if ($logo_base64): ?>
                        <img src="<?php echo $logo_base64; ?>" class="header-logo" alt="Logo">
                    <?php endif; ?>
                </td>
                <td style="width: 70%; text-align: right;">
                    <div class="doc-title">Factura por Reemplazo de Piezas</div>
                    <div class="doc-subtitle">Parts Replacement Invoice</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER (repeats on every page) -->
    <div class="footer-wrapper">
        <div class="footer-top">PRIME FACILITY SERVICES GROUP, INC.</div>
        <div class="footer-bottom">
            <strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>
            <a href="http://www.primefacilityservicesgroup.com">www.primefacilityservicesgroup.com</a>
        </div>
    </div>

    <!-- INVOICE INFO -->
    <table class="invoice-info">
        <tr>
            <td class="label">W.O. No.:</td>
            <td class="value"><?php echo htmlspecialchars($wo_number); ?></td>
            <td class="label">Date:</td>
            <td class="value"><?php echo date('m/d/Y'); ?></td>
        </tr>
        <tr>
            <td class="label">Client / Company:</td>
            <td class="value"><?php echo htmlspecialchars($client_name); ?></td>
            <td class="label">Contact:</td>
            <td class="value"><?php echo htmlspecialchars($client_contact); ?></td>
        </tr>
        <tr>
            <td class="label">Address:</td>
            <td class="value"><?php echo htmlspecialchars($client_address); ?></td>
            <td class="label">Phone:</td>
            <td class="value"><?php echo htmlspecialchars($client_phone); ?></td>
        </tr>
        <tr>
            <td class="label">Sales Person:</td>
            <td class="value"><?php echo htmlspecialchars($seller); ?></td>
            <td class="label">Email:</td>
            <td class="value"><?php echo htmlspecialchars($client_email); ?></td>
        </tr>
    </table>

    <!-- DESCRIPTION -->
    <div class="description-box">
        <strong>Diagnostic Report:</strong> Based on the diagnostic assessment performed, parts with deterioration requiring replacement were identified.
        The following is a detailed list of the parts, quantities, and associated costs for the replacement.<br><br>
        <strong>Reporte de Diagn&oacute;stico:</strong> Con base en el diagn&oacute;stico realizado, se detectaron piezas con deterioro que requieren reemplazo.
        A continuaci&oacute;n se detalla la lista de piezas, cantidades y costos asociados al reemplazo.
    </div>

    <!-- PRODUCTS TABLE -->
    <table class="products-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">PRODUCT / PRODUCTO</th>
                <th style="width: 10%;">QTY / CANTIDAD</th>
                <th style="width: 18%;">UNIT PRICE / PRECIO UNIT.</th>
                <th style="width: 17%;">SUBTOTAL</th>
                <th style="width: 15%;">DESCRIPTION</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($productRows)): ?>
                <?php foreach ($productRows as $i => $row):
                    $qty       = floatval($row['service_time'] ?? 0);
                    $unitPrice = floatval($row['frequency'] ?? 0);
                    $rowSub    = floatval($row['subtotal'] ?? 0);
                    // If subtotal is 0 but qty and price exist, calculate
                    if ($rowSub == 0 && $qty > 0 && $unitPrice > 0) {
                        $rowSub = $qty * $unitPrice;
                    }
                ?>
                <tr>
                    <td class="row-num"><?php echo $i + 1; ?></td>
                    <td class="product-name"><?php echo htmlspecialchars($row['service_type'] ?? ''); ?></td>
                    <td class="qty-cell"><?php echo $qty > 0 ? number_format($qty, 0) : ''; ?></td>
                    <td class="price-cell"><?php echo $unitPrice > 0 ? '$' . number_format($unitPrice, 2) : ''; ?></td>
                    <td class="subtotal-cell">$<?php echo number_format($rowSub, 2); ?></td>
                    <td class="product-name"><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #666;">No products registered</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- TOTALS -->
    <table class="totals-table">
        <tr>
            <td class="label-cell">TOTAL</td>
            <td class="value-cell">$<?php echo number_format($subtotalSum, 2); ?></td>
        </tr>
        <tr>
            <td class="label-cell">TAXES (8.25%)</td>
            <td class="value-cell">$<?php echo number_format($taxes, 2); ?></td>
        </tr>
        <tr>
            <td class="label-cell">GRAND TOTAL</td>
            <td class="value-cell">$<?php echo number_format($grandTotal, 2); ?></td>
        </tr>
    </table>

    <?php if (!empty($data['Additional_Comments'])): ?>
    <div class="description-box" style="margin-top: 10px;">
        <strong>Additional Notes:</strong><br>
        <?php echo nl2br(htmlspecialchars($data['Additional_Comments'])); ?>
    </div>
    <?php endif; ?>

    <!-- SIGNATURES (no authorization checkbox) -->
    <div class="signatures-section">
        <div class="signatures-title">Signatures / Firmas</div>
        <div class="signatures-grid">
            <div class="signature-col">
                <div class="sig-box">
                    <div class="sig-label">Authorized by / Autorizado por:</div>
                    <div class="sig-line">Signature &amp; Date / Firma y Fecha</div>
                </div>
                <div class="sig-box">
                    <div class="sig-label">Print Name / Nombre:</div>
                    <div class="sig-line">Name &amp; Title / Nombre y Cargo</div>
                </div>
            </div>
            <div class="signature-col">
                <div class="sig-box">
                    <div class="sig-label">Received by / Recibido por:</div>
                    <div class="sig-line">Signature &amp; Date / Firma y Fecha</div>
                </div>
                <div class="sig-box">
                    <div class="sig-label">Print Name / Nombre:</div>
                    <div class="sig-line">Name &amp; Title / Nombre y Cargo</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
