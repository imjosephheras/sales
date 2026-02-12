<?php
/**
 * CALENDAR MODULE
 * Month-by-month calendar with agendas, mini form, drag & drop, recurrence
 */
require_once __DIR__ . '/../app/bootstrap.php';
Middleware::module('calendar');
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
                <a href="<?= url('/') ?>" class="home-btn" title="Back to Home">
                    <i class="fas fa-home"></i> Home
                </a>
                <i class="fas fa-calendar-alt"></i>
                <h1>Calendar</h1>
            </div>
            <button class="theme-toggle" id="theme-toggle" title="Toggle dark/light mode">
                <i class="fas fa-moon"></i>
            </button>
        </div>
    </header>

    <!-- Calendar Layout -->
    <div class="calendar-layout">

        <!-- Client Filter Sidebar -->
        <aside class="filter-sidebar" id="filter-sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-filter"></i> Filter by Client</h3>
                <button class="sidebar-collapse-btn" id="sidebar-collapse-btn" title="Collapse sidebar">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>

            <div class="sidebar-search">
                <i class="fas fa-search"></i>
                <input type="text" id="client-search" placeholder="Search clients...">
            </div>

            <div class="sidebar-actions">
                <button class="sidebar-action-btn" id="select-all-btn">Select All</button>
                <button class="sidebar-action-btn" id="deselect-all-btn">Deselect All</button>
            </div>

            <div class="client-list" id="client-list">
                <div class="client-list-empty">
                    <i class="fas fa-calendar-xmark"></i>
                    <span>No clients this month</span>
                </div>
            </div>

            <div class="sidebar-footer" id="sidebar-footer">
                <span id="filter-count">Showing all</span>
            </div>
        </aside>

        <!-- Collapsed client sidebar toggle -->
        <button class="sidebar-expand-btn" id="sidebar-expand-btn" title="Show client filter">
            <i class="fas fa-filter"></i>
        </button>

        <!-- Service Type Filter Sidebar -->
        <aside class="filter-sidebar service-filter-sidebar" id="service-filter-sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-concierge-bell"></i> Type of Services</h3>
                <button class="sidebar-collapse-btn" id="service-sidebar-collapse-btn" title="Collapse sidebar">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>

            <div class="sidebar-search">
                <i class="fas fa-search"></i>
                <input type="text" id="service-search" placeholder="Search services...">
            </div>

            <div class="sidebar-actions">
                <button class="sidebar-action-btn" id="service-select-all-btn">Select All</button>
                <button class="sidebar-action-btn" id="service-deselect-all-btn">Deselect All</button>
            </div>

            <div class="client-list" id="service-type-list">
                <div class="client-list-empty">
                    <i class="fas fa-concierge-bell"></i>
                    <span>No services this month</span>
                </div>
            </div>

            <div class="sidebar-footer" id="service-sidebar-footer">
                <span id="service-filter-count">Showing all</span>
            </div>
        </aside>

        <!-- Collapsed service sidebar toggle -->
        <button class="sidebar-expand-btn" id="service-sidebar-expand-btn" title="Show service filter">
            <i class="fas fa-concierge-bell"></i>
        </button>

        <!-- Calendar -->
        <div class="calendar-wrapper">
            <div class="calendar-card">

                <!-- Logo -->
                <div class="calendar-logo">
                    <img src="../form_contract/Images/Facility.png" alt="Prime Facility Services Group">
                </div>

                <!-- Navigation -->
                <div class="calendar-nav">
                    <button class="nav-btn" id="prev-month" title="Previous month">
                        <i class="fas fa-chevron-left"></i>
                    </button>

                    <div style="display:flex; align-items:center; gap:16px;">
                        <h2 id="calendar-title"></h2>
                        <button class="today-btn" id="today-btn">Today</button>
                        <button class="print-btn" id="print-btn" title="Print calendar">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>

                    <button class="nav-btn" id="next-month" title="Next month">
                        <i class="fas fa-chevron-right"></i>
                    </button>

                    <div class="calendar-nav-toggles">
                        <button class="nav-toggle-btn active" id="toggle-client-sidebar" title="Toggle Client Filter">
                            <i class="fas fa-filter"></i>
                            <span>Clients</span>
                        </button>
                        <button class="nav-toggle-btn active" id="toggle-service-sidebar" title="Toggle Service Filter">
                            <i class="fas fa-concierge-bell"></i>
                            <span>Services</span>
                        </button>
                    </div>
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

    </div>

    <!-- Mini Form Overlay -->
    <div class="mini-form-overlay" id="mini-form-overlay"></div>

    <!-- Mini Form Panel -->
    <div class="mini-form-panel" id="mini-form-panel">
        <input type="hidden" id="mini-form-event-id">
        <input type="hidden" id="mini-form-form-id">

        <!-- Header -->
        <div class="mini-form-header">
            <div class="mini-form-header-left">
                <h3><i class="fas fa-edit"></i> Agenda Details</h3>
                <div class="mini-form-header-info">
                    <span class="mini-form-client-display" id="mini-form-client-name"></span>
                    <span class="mini-form-company-display" id="mini-form-company-name"></span>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="mini-form-badge badge-base" id="mini-form-event-type">Base Event</span>
                <button class="mini-form-close-btn" id="mini-form-close" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Body: Only 4 fields allowed -->
        <div class="mini-form-body">

            <!-- 1. Work Date -->
            <div class="mini-form-field">
                <label for="mini-form-work-date">
                    <i class="fas fa-calendar-day"></i> Work Date
                </label>
                <input type="date" id="mini-form-work-date">
            </div>

            <!-- 2. Notes -->
            <div class="mini-form-field">
                <label for="mini-form-notes">
                    <i class="fas fa-sticky-note"></i> Notes
                </label>
                <textarea id="mini-form-notes" placeholder="Add notes for this agenda..." rows="3"></textarea>
            </div>

            <!-- 3 & 4. Frequency: Months and Years -->
            <div class="mini-form-freq-row">
                <div class="mini-form-field">
                    <label for="mini-form-freq-months">
                        <i class="fas fa-calendar-alt"></i> Month (Frequency)
                    </label>
                    <select id="mini-form-freq-months">
                        <option value="0">0 - No recurrence</option>
                        <option value="1">1 - Every 1 month</option>
                        <option value="2">2 - Every 2 months</option>
                        <option value="3">3 - Every 3 months</option>
                        <option value="4">4 - Every 4 months</option>
                        <option value="5">5 - Every 5 months</option>
                        <option value="6">6 - Every 6 months</option>
                    </select>
                    <span class="field-hint">Interval between agendas</span>
                </div>
                <div class="mini-form-field">
                    <label for="mini-form-freq-years">
                        <i class="fas fa-history"></i> Year (Frequency)
                    </label>
                    <select id="mini-form-freq-years">
                        <option value="0">0 - No recurrence</option>
                        <option value="1">1 - Span 1 year</option>
                        <option value="2">2 - Span 2 years</option>
                        <option value="3">3 - Span 3 years</option>
                        <option value="4">4 - Span 4 years</option>
                        <option value="5">5 - Span 5 years</option>
                    </select>
                    <span class="field-hint">Total range for agendas</span>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="mini-form-footer">
            <button class="mini-form-btn mini-form-btn-cancel" id="mini-form-cancel-btn" onclick="document.getElementById('mini-form-overlay').classList.remove('visible'); document.getElementById('mini-form-panel').classList.remove('visible');">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button class="mini-form-btn mini-form-btn-save" id="mini-form-save">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>

    <script src="js/calendar.js"></script>
</body>
</html>
