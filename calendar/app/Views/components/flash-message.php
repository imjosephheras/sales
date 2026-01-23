<?php
/**
 * Flash Message Component
 * Displays temporary notification messages
 * 
 * Required: $flash (array with 'message' and 'type')
 * Types: success, error, warning, info
 */

if (!isset($flash) || !is_array($flash)) {
    return;
}

$message = $flash['message'] ?? '';
$type = $flash['type'] ?? 'info';

$icons = [
    'success' => '✓',
    'error' => '✕',
    'warning' => '⚠',
    'info' => 'ℹ'
];

$icon = $icons[$type] ?? $icons['info'];
?>

<div class="flash-message flash-<?= $type ?>" id="flashMessage">
    <span class="flash-icon"><?= $icon ?></span>
    <span class="flash-text"><?= e($message) ?></span>
    <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
</div>

<style>
.flash-message {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
    min-width: 300px;
    max-width: 500px;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.flash-success {
    background: #ecfdf5;
    color: #065f46;
    border-left: 4px solid #10b981;
}

.flash-error {
    background: #fef2f2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

.flash-warning {
    background: #fffbeb;
    color: #92400e;
    border-left: 4px solid #f59e0b;
}

.flash-info {
    background: #eff6ff;
    color: #1e40af;
    border-left: 4px solid #3b82f6;
}

.flash-icon {
    font-size: 1.25rem;
    font-weight: bold;
}

.flash-text {
    flex: 1;
    font-size: 0.875rem;
    font-weight: 500;
}

.flash-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: inherit;
    opacity: 0.5;
    transition: opacity 0.2s;
    padding: 0;
    width: 24px;
    height: 24px;
}

.flash-close:hover {
    opacity: 1;
}
</style>