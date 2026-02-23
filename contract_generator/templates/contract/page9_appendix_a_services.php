<!-- PAGE 9 - APPENDIX A: SERVICE PRICES -->
<div class="page-break"></div>

<div class="appendix-title">APPENDIX (A)</div>
<div class="appendix-subtitle">Service Prices</div>

<div class="appendix-content">
    <?php
    // Build service pricing table from DB detail tables
    $hasServices = false;
    $allServices = [];

    if (!empty($janitorialServices)) {
        $hasServices = true;
        foreach ($janitorialServices as $svc) {
            $allServices[] = $svc;
        }
    }
    if (!empty($kitchenServices)) {
        $hasServices = true;
        foreach ($kitchenServices as $svc) {
            $allServices[] = $svc;
        }
    }
    if (!empty($hoodVentServices)) {
        $hasServices = true;
        foreach ($hoodVentServices as $svc) {
            $allServices[] = $svc;
        }
    }

    // Pre-compute bundle groups for merged subtotal cells
    $bundleGroupInfo = [];
    foreach ($allServices as $idx => $svc) {
        $bg = $svc['bundle_group'] ?? '';
        if ($bg !== '') {
            if (!isset($bundleGroupInfo[$bg])) {
                $bundleGroupInfo[$bg] = [
                    'count' => 0,
                    'first_index' => $idx,
                    'total' => 0.0,
                ];
            }
            $bundleGroupInfo[$bg]['count']++;
            $svcSub = floatval($svc['subtotal'] ?? 0);
            if ($svcSub > 0) {
                $bundleGroupInfo[$bg]['total'] = $svcSub;
            }
        }
    }

    if ($hasServices && !empty($allServices)):
    ?>
    <?php
    $isProductMode = isset($salesMode) && $salesMode === 'product';
    $hdrType = $isProductMode ? 'Product' : 'Type of Service';
    $hdrTime = $isProductMode ? 'Quantity' : 'Service Time';
    $hdrFreq = $isProductMode ? 'Unit Price' : 'Frequency';
    ?>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr>
                <th style="background-color: #CC0000; color: white; padding: 8px 6px; text-align: center; border: 1px solid #000; font-size: 8pt; text-transform: uppercase;"><?php echo $hdrType; ?></th>
                <th style="background-color: #CC0000; color: white; padding: 8px 6px; text-align: center; border: 1px solid #000; font-size: 8pt; text-transform: uppercase;"><?php echo $hdrTime; ?></th>
                <th style="background-color: #CC0000; color: white; padding: 8px 6px; text-align: center; border: 1px solid #000; font-size: 8pt; text-transform: uppercase;"><?php echo $hdrFreq; ?></th>
                <th style="background-color: #CC0000; color: white; padding: 8px 6px; text-align: center; border: 1px solid #000; font-size: 8pt; text-transform: uppercase;">Description</th>
                <th style="background-color: #CC0000; color: white; padding: 8px 6px; text-align: center; border: 1px solid #000; font-size: 8pt; text-transform: uppercase;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allServices as $idx => $svc):
                $bg = $svc['bundle_group'] ?? '';
                $isBundle = ($bg !== '' && isset($bundleGroupInfo[$bg]) && $bundleGroupInfo[$bg]['count'] > 1);
                $isFirstInBundle = $isBundle && ($bundleGroupInfo[$bg]['first_index'] === $idx);
                $isSecondaryInBundle = $isBundle && !$isFirstInBundle;
            ?>
            <tr<?php if ($isBundle): ?> style="border-left: 3px solid #0066cc;"<?php endif; ?>>
                <td style="border: 1px solid #000; padding: 6px 8px; font-size: 8pt; text-align: left;"><?php echo htmlspecialchars($svc['service_type'] ?? ''); ?></td>
                <td style="border: 1px solid #000; padding: 6px 8px; font-size: 8pt; text-align: center;"><?php echo htmlspecialchars($svc['service_time'] ?? ''); ?></td>
                <td style="border: 1px solid #000; padding: 6px 8px; font-size: 8pt; text-align: center;"><?php echo htmlspecialchars($svc['frequency'] ?? ''); ?></td>
                <td style="border: 1px solid #000; padding: 6px 8px; font-size: 8pt; text-align: left;"><?php echo htmlspecialchars($svc['description'] ?? ''); ?></td>
                <?php if ($isFirstInBundle): ?>
                <td style="border: 1px solid #000; padding: 6px 8px; font-size: 8pt; text-align: right; font-weight: bold; vertical-align: middle; background: linear-gradient(135deg, #e8f4fd, #d0ebff);" rowspan="<?php echo $bundleGroupInfo[$bg]['count']; ?>">
                    $<?php echo number_format($bundleGroupInfo[$bg]['total'], 2); ?>
                    <br><span style="font-size: 6pt; color: #0066cc; font-weight: normal;">BUNDLE PRICE</span>
                </td>
                <?php elseif (!$isSecondaryInBundle): ?>
                <td style="border: 1px solid #000; padding: 6px 8px; font-size: 8pt; text-align: right; font-weight: bold;">$<?php echo number_format(floatval($svc['subtotal'] ?? 0), 2); ?></td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p style="text-align: center; color: #666; font-style: italic; margin-top: 30px;">(Service prices to be detailed here)</p>
    <?php endif; ?>
</div>
