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

    if ($hasServices && !empty($allServices)):
    ?>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr>
                <th style="background-color: #CC0000; color: white; padding: 8px 6px; text-align: center; border: 1px solid #000; font-size: 8pt; text-transform: uppercase;">Type of Service</th>
                <th style="background-color: #CC0000; color: white; padding: 8px 6px; text-align: center; border: 1px solid #000; font-size: 8pt; text-transform: uppercase;">Service Time</th>
                <th style="background-color: #CC0000; color: white; padding: 8px 6px; text-align: center; border: 1px solid #000; font-size: 8pt; text-transform: uppercase;">Frequency</th>
                <th style="background-color: #CC0000; color: white; padding: 8px 6px; text-align: center; border: 1px solid #000; font-size: 8pt; text-transform: uppercase;">Description</th>
                <th style="background-color: #CC0000; color: white; padding: 8px 6px; text-align: center; border: 1px solid #000; font-size: 8pt; text-transform: uppercase;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allServices as $svc): ?>
            <tr>
                <td style="border: 1px solid #000; padding: 6px 8px; font-size: 8pt; text-align: left;"><?php echo htmlspecialchars($svc['service_type'] ?? ''); ?></td>
                <td style="border: 1px solid #000; padding: 6px 8px; font-size: 8pt; text-align: center;"><?php echo htmlspecialchars($svc['service_time'] ?? ''); ?></td>
                <td style="border: 1px solid #000; padding: 6px 8px; font-size: 8pt; text-align: center;"><?php echo htmlspecialchars($svc['frequency'] ?? ''); ?></td>
                <td style="border: 1px solid #000; padding: 6px 8px; font-size: 8pt; text-align: left;"><?php echo htmlspecialchars($svc['description'] ?? ''); ?></td>
                <td style="border: 1px solid #000; padding: 6px 8px; font-size: 8pt; text-align: right; font-weight: bold;">$<?php echo number_format(floatval($svc['subtotal'] ?? 0), 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p style="text-align: center; color: #666; font-style: italic; margin-top: 30px;">(Service prices to be detailed here)</p>
    <?php endif; ?>
</div>
