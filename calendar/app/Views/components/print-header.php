<?php
/**
 * Print Header Component
 * Displayed only when printing
 * 
 * Optional: $title, $year, $user
 */

$title = $title ?? 'Calendar';
$year = $year ?? date('Y');
$userName = $user['full_name'] ?? 'User';
$generatedDate = date('F d, Y g:i A');
?>

<div class="print-header" style="display: none;">
    <h1><?= e($title) ?> <?= $year ?> - Work Calendar</h1>
    <div class="print-meta">
        <span>Generated: <?= $generatedDate ?></span>
        <span>Prepared by: <?= e($userName) ?></span>
    </div>
</div>