<?php
/**
 * CALENDAR MODULE
 * Basic month-by-month calendar view
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar</title>
    <link rel="stylesheet" href="styles/calendar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Header -->
    <header class="main-header">
        <div class="header-content">
            <div class="logo-section">
                <a href="../index.php" class="home-btn" title="Back to Home">
                    <i class="fas fa-home"></i> Home
                </a>
                <i class="fas fa-calendar-alt"></i>
                <h1>Calendar</h1>
            </div>
        </div>
    </header>

    <!-- Calendar -->
    <div class="calendar-wrapper">
        <div class="calendar-card">

            <!-- Navigation -->
            <div class="calendar-nav">
                <button class="nav-btn" id="prev-month" title="Previous month">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <div style="display:flex; align-items:center; gap:16px;">
                    <h2 id="calendar-title"></h2>
                    <button class="today-btn" id="today-btn">Today</button>
                </div>

                <button class="nav-btn" id="next-month" title="Next month">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <!-- Grid -->
            <div class="calendar-grid">
                <div class="weekday-header">
                    <span>Sun</span>
                    <span>Mon</span>
                    <span>Tue</span>
                    <span>Wed</span>
                    <span>Thu</span>
                    <span>Fri</span>
                    <span>Sat</span>
                </div>
                <div class="days-grid" id="days-grid"></div>
            </div>

        </div>
    </div>

    <script src="js/calendar.js"></script>
</body>
</html>
