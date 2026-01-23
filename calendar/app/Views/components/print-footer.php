<?php
/**
 * Print Footer Component
 * Displayed only when printing
 * Shows legend and footer info
 * 
 * Optional: $year
 */

$year = $year ?? date('Y');
?>

<div class="print-footer" style="display: none;">
    <div class="print-legend">
        <div class="print-legend-item">
            <div class="print-legend-color" style="background: #3b82f6;"></div>
            <span>JWO</span>
        </div>
        <div class="print-legend-item">
            <div class="print-legend-color" style="background: #10b981;"></div>
            <span>Contract</span>
        </div>
        <div class="print-legend-item">
            <div class="print-legend-color" style="background: #f59e0b;"></div>
            <span>Proposal</span>
        </div>
        <div class="print-legend-item">
            <div class="print-legend-color" style="background: #ef4444;"></div>
            <span>Hoodvent</span>
        </div>
        <div class="print-legend-item">
            <div class="print-legend-color" style="background: #8b5cf6;"></div>
            <span>Janitorial</span>
        </div>
    </div>
    <p style="margin-top: 0.5rem;">Work Calendar System - <?= $year ?></p>
</div>