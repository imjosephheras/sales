<?php
/**
 * ============================================================
 * CALENDAR VIEW - MAIN INDEX
 * Clean separation of concerns
 * ============================================================
 */

// Set page-specific variables
$pageTitle = e($monthName) . ' ' . $year . ' | Work Calendar';
$showPrintHeader = true;
$showPrintFooter = true;

// Include header
include VIEWS_PATH . '/layouts/header.php';
?>

<!-- Main Calendar Layout (3 columns) -->
<div class="calendar-layout">
    
    <!-- Left Sidebar: Client Filters (Excel-style) -->
    <?php include VIEWS_PATH . '/calendar/sidebar-left.php'; ?>
    
    <!-- Calendar Main Section (Center) -->
    <main class="calendar-main">
        
        <!-- Calendar Header -->
        <div class="calendar-header">
            <div class="month-navigation">
                <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="nav-arrow">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M12 4L6 10L12 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </a>
                
                <h1 class="month-title">
                    <span class="month"><?= $monthName ?></span>
                    <span class="year"><?= $year ?></span>
                </h1>
                
                <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="nav-arrow">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M8 4L14 10L8 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </a>
            </div>
            
            <div class="calendar-actions">
                <a href="?month=<?= date('n') ?>&year=<?= date('Y') ?>" class="btn-today">Today</a>
                
                <!-- Print Button -->
                <button class="btn-print" onclick="printCalendar()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9V2h12v7"/>
                        <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                        <rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    Print
                </button>
                
                <button class="btn-primary" onclick="openEventModal()">+ New Event</button>
            </div>
        </div>

        <!-- Calendar Grid -->
        <?php include VIEWS_PATH . '/calendar/grid.php'; ?>
        
    </main>

    <!-- Right Sidebar: Today + Work/Jobs -->
    <?php include VIEWS_PATH . '/calendar/sidebar-right.php'; ?>
    
</div>

<!-- Modals -->
<?php include VIEWS_PATH . '/calendar/modals.php'; ?>

<?php
// Include footer
include VIEWS_PATH . '/layouts/footer.php';
?>