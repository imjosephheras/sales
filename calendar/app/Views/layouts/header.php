<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Calendar System' ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=DM+Sans:wght@400;500;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="public/css/calendar.css">
    <link rel="stylesheet" href="public/css/components.css">
    <link rel="stylesheet" href="public/css/sidebar-filters.css">
    <link rel="stylesheet" href="public/css/print.css" media="print">
</head>
<body>

<!-- Print Header (hidden on screen) -->
<?php if (isset($monthName)): ?>
    <?php component('print-header', [
        'title' => $monthName,
        'year' => $year ?? date('Y'),
        'user' => $currentUser ?? getCurrentUser()
    ]); ?>
<?php endif; ?>

<!-- Main Header -->
<header class="main-header">
    <div class="header-left">
        <div class="logo">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                <rect x="4" y="8" width="24" height="20" rx="2" stroke="currentColor" stroke-width="2"/>
                <line x1="4" y1="14" x2="28" y2="14" stroke="currentColor" stroke-width="2"/>
                <line x1="10" y1="5" x2="10" y2="11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <line x1="22" y1="5" x2="22" y2="11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <h1>Calendar System</h1>
        </div>
    </div>
    
    <div class="header-right">
        <!-- Theme Toggle -->
        <button id="themeToggle" class="theme-toggle" onclick="toggleTheme()" title="Toggle theme">
            <svg class="sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1" x2="12" y2="3"/>
                <line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1" y1="12" x2="3" y2="12"/>
                <line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <svg class="moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </button>
    </div>
</header>

<!-- Flash Messages -->
<?php if (isset($flash) && $flash): ?>
    <?php component('flash-message', ['flash' => $flash]); ?>
<?php endif; ?>