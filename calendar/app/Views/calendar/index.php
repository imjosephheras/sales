<?php
/**
 * Calendar View - BASIC
 * Single column layout. No sidebars. Just the calendar grid.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Calendar' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/calendar.css">
</head>
<body>

<!-- Header -->
<header class="main-header">
    <div class="header-left">
        <a href="../index.php" class="home-btn">Home</a>
        <span class="logo-text">Calendar</span>
    </div>
    <div class="header-right">
        <button class="theme-toggle" onclick="toggleTheme()" title="Toggle theme">
            <span class="theme-icon-light">&#9728;</span>
            <span class="theme-icon-dark">&#9790;</span>
        </button>
    </div>
</header>

<!-- Flash Messages -->
<?php if (isset($flash) && $flash): ?>
    <div class="flash-message flash-<?= e($flash['type']) ?>" id="flashMessage">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>

<!-- Calendar -->
<main class="calendar-main">

    <!-- Calendar Header -->
    <div class="calendar-header">
        <div class="month-navigation">
            <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="nav-arrow">&larr;</a>
            <h1 class="month-title">
                <span class="month"><?= $monthName ?></span>
                <span class="year"><?= $year ?></span>
            </h1>
            <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="nav-arrow">&rarr;</a>
        </div>
        <div class="calendar-actions">
            <a href="?month=<?= date('n') ?>&year=<?= date('Y') ?>" class="btn-today">Today</a>
            <button class="btn-primary" onclick="openEventModal()">+ New Event</button>
        </div>
    </div>

    <!-- Calendar Grid -->
    <?php include VIEWS_PATH . '/calendar/grid.php'; ?>

</main>

<!-- Event Modal -->
<?php include VIEWS_PATH . '/calendar/modals.php'; ?>

<!-- Single JS file -->
<script src="public/js/calendar.js"></script>

<script>
// Load saved theme
const savedTheme = localStorage.getItem('theme') || 'light';
document.body.setAttribute('data-theme', savedTheme);

// Auto-hide flash messages
const flashEl = document.getElementById('flashMessage');
if (flashEl) {
    setTimeout(() => {
        flashEl.style.opacity = '0';
        setTimeout(() => flashEl.remove(), 300);
    }, 4000);
}
</script>
</body>
</html>
