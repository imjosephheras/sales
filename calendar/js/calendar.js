/**
 * CALENDAR MODULE - Main JavaScript
 * Month-by-month calendar with scheduled request form events
 * Includes client filter sidebar
 */

document.addEventListener('DOMContentLoaded', () => {
    const calendar = new Calendar();
    calendar.init();
    initThemeToggle();
});

/**
 * Theme toggle - switches between light and dark mode
 * Persists preference in localStorage
 */
function initThemeToggle() {
    const toggleBtn = document.getElementById('theme-toggle');
    const icon = toggleBtn.querySelector('i');
    const saved = localStorage.getItem('calendar-theme');

    if (saved === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        icon.classList.replace('fa-moon', 'fa-sun');
    }

    toggleBtn.addEventListener('click', () => {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

        if (isDark) {
            document.documentElement.removeAttribute('data-theme');
            icon.classList.replace('fa-sun', 'fa-moon');
            localStorage.setItem('calendar-theme', 'light');
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
            icon.classList.replace('fa-moon', 'fa-sun');
            localStorage.setItem('calendar-theme', 'dark');
        }
    });
}

class Calendar {
    constructor() {
        this.currentDate = new Date();
        this.currentMonth = this.currentDate.getMonth();
        this.currentYear = this.currentDate.getFullYear();
        this.allEvents = {};   // all events keyed by day (unfiltered)
        this.events = {};      // filtered events keyed by day
        this.selectedClients = new Set();
        this.allClients = [];  // sorted unique client names for current month

        this.monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
    }

    init() {
        this.titleEl = document.getElementById('calendar-title');
        this.gridEl = document.getElementById('days-grid');
        this.prevBtn = document.getElementById('prev-month');
        this.nextBtn = document.getElementById('next-month');
        this.todayBtn = document.getElementById('today-btn');

        this.prevBtn.addEventListener('click', () => this.changeMonth(-1));
        this.nextBtn.addEventListener('click', () => this.changeMonth(1));
        this.todayBtn.addEventListener('click', () => this.goToToday());

        // Close detail panel when clicking outside
        document.addEventListener('click', (e) => {
            const panel = document.getElementById('event-detail-panel');
            if (panel && !panel.contains(e.target) && !e.target.closest('.day-cell')) {
                panel.classList.remove('visible');
            }
        });

        // Sidebar controls
        this.initSidebar();

        this.loadAndRender();
    }

    /**
     * Initialize sidebar elements and event listeners
     */
    initSidebar() {
        this.sidebarEl = document.getElementById('filter-sidebar');
        this.clientListEl = document.getElementById('client-list');
        this.clientSearchEl = document.getElementById('client-search');
        this.selectAllBtn = document.getElementById('select-all-btn');
        this.deselectAllBtn = document.getElementById('deselect-all-btn');
        this.filterCountEl = document.getElementById('filter-count');
        this.collapseBtn = document.getElementById('sidebar-collapse-btn');
        this.expandBtn = document.getElementById('sidebar-expand-btn');

        // Search clients
        this.clientSearchEl.addEventListener('input', () => {
            this.renderClientList();
        });

        // Select / Deselect all
        this.selectAllBtn.addEventListener('click', () => {
            this.allClients.forEach(c => this.selectedClients.add(c));
            this.renderClientList();
            this.applyFilter();
        });

        this.deselectAllBtn.addEventListener('click', () => {
            this.selectedClients.clear();
            this.renderClientList();
            this.applyFilter();
        });

        // Collapse / Expand sidebar
        const savedSidebar = localStorage.getItem('calendar-sidebar');
        if (savedSidebar === 'collapsed') {
            this.sidebarEl.classList.add('collapsed');
            this.expandBtn.classList.add('visible');
        }

        this.collapseBtn.addEventListener('click', () => {
            this.sidebarEl.classList.add('collapsed');
            this.expandBtn.classList.add('visible');
            localStorage.setItem('calendar-sidebar', 'collapsed');
        });

        this.expandBtn.addEventListener('click', () => {
            this.sidebarEl.classList.remove('collapsed');
            this.expandBtn.classList.remove('visible');
            localStorage.setItem('calendar-sidebar', 'expanded');
        });
    }

    changeMonth(delta) {
        this.currentMonth += delta;

        if (this.currentMonth > 11) {
            this.currentMonth = 0;
            this.currentYear++;
        } else if (this.currentMonth < 0) {
            this.currentMonth = 11;
            this.currentYear--;
        }

        this.loadAndRender();
    }

    goToToday() {
        const today = new Date();
        this.currentMonth = today.getMonth();
        this.currentYear = today.getFullYear();
        this.loadAndRender();
    }

    async loadAndRender() {
        await this.fetchEvents();
        this.extractClients();
        this.applyFilter();
    }

    async fetchEvents() {
        this.allEvents = {};
        try {
            // PHP month is 1-indexed
            const month = this.currentMonth + 1;
            const year = this.currentYear;
            const resp = await fetch(`get_events.php?month=${month}&year=${year}`);
            const data = await resp.json();

            if (data.success && data.events) {
                data.events.forEach(ev => {
                    // Extract day from Work_Date (YYYY-MM-DD)
                    const day = parseInt(ev.Work_Date.split('-')[2], 10);
                    if (!this.allEvents[day]) {
                        this.allEvents[day] = [];
                    }
                    this.allEvents[day].push({
                        id: ev.form_id,
                        client: ev.client_name || 'N/A',
                        company: ev.company_name || 'N/A',
                        status: ev.status || 'pending',
                        serviceType: ev.service_type || '',
                        requestType: ev.request_type || '',
                        priority: ev.priority || '',
                        requestedService: ev.requested_service || '',
                        nomenclature: ev.Order_Nomenclature || '',
                        seller: ev.seller || '',
                        documentDate: ev.Document_Date || ''
                    });
                });
            }
        } catch (err) {
            console.error('Error fetching calendar events:', err);
        }
    }

    /**
     * Extract unique client names from allEvents and populate sidebar
     */
    extractClients() {
        const clientSet = new Set();
        Object.values(this.allEvents).forEach(dayEvents => {
            dayEvents.forEach(ev => {
                clientSet.add(ev.client);
            });
        });

        this.allClients = Array.from(clientSet).sort((a, b) =>
            a.localeCompare(b, undefined, { sensitivity: 'base' })
        );

        // By default select all clients
        this.selectedClients = new Set(this.allClients);

        this.clientSearchEl.value = '';
        this.renderClientList();
    }

    /**
     * Render the client checkbox list in the sidebar
     */
    renderClientList() {
        const searchTerm = this.clientSearchEl.value.toLowerCase().trim();
        const filtered = searchTerm
            ? this.allClients.filter(c => c.toLowerCase().includes(searchTerm))
            : this.allClients;

        if (this.allClients.length === 0) {
            this.clientListEl.innerHTML = `
                <div class="client-list-empty">
                    <i class="fas fa-calendar-xmark"></i>
                    <span>No clients this month</span>
                </div>`;
            this.updateFilterCount();
            return;
        }

        if (filtered.length === 0) {
            this.clientListEl.innerHTML = `
                <div class="client-list-empty">
                    <i class="fas fa-search"></i>
                    <span>No matching clients</span>
                </div>`;
            return;
        }

        // Count events per client
        const eventCounts = {};
        Object.values(this.allEvents).forEach(dayEvents => {
            dayEvents.forEach(ev => {
                eventCounts[ev.client] = (eventCounts[ev.client] || 0) + 1;
            });
        });

        let html = '';
        filtered.forEach(client => {
            const isChecked = this.selectedClients.has(client);
            const count = eventCounts[client] || 0;
            html += `
                <label class="client-item ${isChecked ? 'active' : ''}">
                    <input type="checkbox" value="${this.escapeHtml(client)}" ${isChecked ? 'checked' : ''}>
                    <span class="client-checkbox-custom">
                        <i class="fas fa-check"></i>
                    </span>
                    <span class="client-name">${this.escapeHtml(client)}</span>
                    <span class="client-count">${count}</span>
                </label>`;
        });

        this.clientListEl.innerHTML = html;

        // Attach change listeners
        this.clientListEl.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', (e) => {
                const clientName = e.target.value;
                if (e.target.checked) {
                    this.selectedClients.add(clientName);
                } else {
                    this.selectedClients.delete(clientName);
                }
                // Update active class on label
                e.target.closest('.client-item').classList.toggle('active', e.target.checked);
                this.applyFilter();
            });
        });

        this.updateFilterCount();
    }

    /**
     * Update the filter count text in the sidebar footer
     */
    updateFilterCount() {
        const total = this.allClients.length;
        const selected = this.selectedClients.size;

        if (selected === total) {
            this.filterCountEl.textContent = `Showing all (${total})`;
        } else {
            this.filterCountEl.textContent = `${selected} of ${total} selected`;
        }
    }

    /**
     * Apply the client filter and re-render calendar
     */
    applyFilter() {
        this.events = {};

        if (this.selectedClients.size === this.allClients.length) {
            // No filter active - show all
            this.events = { ...this.allEvents };
        } else {
            // Filter events to only selected clients
            Object.entries(this.allEvents).forEach(([day, dayEvents]) => {
                const filtered = dayEvents.filter(ev => this.selectedClients.has(ev.client));
                if (filtered.length > 0) {
                    this.events[day] = filtered;
                }
            });
        }

        this.updateFilterCount();
        this.render();
    }

    /**
     * Get CSS class for request type color coding
     */
    getRequestTypeClass(requestType) {
        const type = (requestType || '').toLowerCase();
        if (type === 'contract') return 'chip-contract';
        if (type === 'proposal') return 'chip-proposal';
        if (type === 'jwo') return 'chip-jwo';
        if (type === 'quote') return 'chip-quote';
        return 'chip-default';
    }

    /**
     * Get short label for request type
     */
    getRequestTypeShort(requestType) {
        const type = (requestType || '').toLowerCase();
        if (type === 'contract') return 'CTR';
        if (type === 'proposal') return 'PRP';
        if (type === 'jwo') return 'JWO';
        if (type === 'quote') return 'QTE';
        return '';
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    render() {
        this.titleEl.textContent = `${this.monthNames[this.currentMonth]} ${this.currentYear}`;

        const firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
        const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();

        const today = new Date();
        const isCurrentMonth = today.getMonth() === this.currentMonth && today.getFullYear() === this.currentYear;
        const todayDate = today.getDate();

        let html = '';

        // Empty cells before the 1st
        for (let i = 0; i < firstDay; i++) {
            html += '<div class="day-cell empty"></div>';
        }

        // Day cells
        for (let day = 1; day <= daysInMonth; day++) {
            const isToday = isCurrentMonth && day === todayDate;
            const hasEvents = this.events[day] && this.events[day].length > 0;
            const classes = ['day-cell'];

            if (isToday) classes.push('today');
            if (hasEvents) classes.push('has-events');

            html += `<div class="${classes.join(' ')}" data-day="${day}">`;
            html += `<span class="day-number">${day}</span>`;

            if (hasEvents) {
                const eventsForDay = this.events[day];
                const maxVisible = 2;

                html += '<div class="day-events">';
                eventsForDay.slice(0, maxVisible).forEach(ev => {
                    const typeClass = this.getRequestTypeClass(ev.requestType);
                    const typeShort = this.getRequestTypeShort(ev.requestType);
                    const priorityClass = ev.priority === 'Rush' ? 'priority-rush' : '';

                    html += `<div class="event-chip ${typeClass} ${priorityClass}" title="${this.escapeHtml(ev.client)} - ${this.escapeHtml(ev.company)} | ${ev.requestType} | ${ev.requestedService}">`;
                    if (typeShort) {
                        html += `<span class="chip-type-badge">${typeShort}</span>`;
                    }
                    html += `<span class="event-client">${this.escapeHtml(ev.client)}</span>`;
                    html += `<span class="event-service">${this.escapeHtml(ev.requestedService || ev.company)}</span>`;
                    html += `</div>`;
                });

                if (eventsForDay.length > maxVisible) {
                    html += `<div class="event-more">+${eventsForDay.length - maxVisible} more</div>`;
                }
                html += '</div>';
            }

            html += '</div>';
        }

        this.gridEl.innerHTML = html;

        // Attach click listeners for days with events
        this.gridEl.querySelectorAll('.day-cell.has-events').forEach(cell => {
            cell.addEventListener('click', (e) => {
                e.stopPropagation();
                const day = parseInt(cell.dataset.day, 10);
                this.showDayDetail(day, cell);
            });
        });
    }

    showDayDetail(day, cell) {
        let panel = document.getElementById('event-detail-panel');
        if (!panel) {
            panel = document.createElement('div');
            panel.id = 'event-detail-panel';
            panel.className = 'event-detail-panel';
            document.querySelector('.calendar-wrapper').appendChild(panel);
        }

        const eventsForDay = this.events[day] || [];
        const dateStr = `${this.monthNames[this.currentMonth]} ${day}, ${this.currentYear}`;

        let html = `<div class="detail-header">`;
        html += `<h3><i class="fas fa-calendar-day"></i> ${dateStr}</h3>`;
        html += `<span class="detail-count">${eventsForDay.length} schedule${eventsForDay.length !== 1 ? 's' : ''}</span>`;
        html += `</div>`;
        html += `<div class="detail-list">`;

        eventsForDay.forEach(ev => {
            const statusClass = ev.status === 'submitted' ? 'status-submitted' :
                                ev.status === 'draft' ? 'status-draft' : 'status-pending';
            const typeClass = this.getRequestTypeClass(ev.requestType);
            const isPriorityRush = ev.priority === 'Rush';

            html += `<div class="detail-item ${typeClass}-border">`;

            // Header row: nomenclature + priority + status
            html += `<div class="detail-item-header">`;
            if (ev.nomenclature) {
                html += `<span class="detail-nomenclature">${this.escapeHtml(ev.nomenclature)}</span>`;
            }
            if (isPriorityRush) {
                html += `<span class="detail-priority-rush"><i class="fas fa-bolt"></i> Rush</span>`;
            }
            html += `<span class="detail-status ${statusClass}">${ev.status}</span>`;
            html += `</div>`;

            // Request type + Service type badges
            html += `<div class="detail-badges">`;
            if (ev.requestType) {
                html += `<span class="detail-badge ${typeClass}">${ev.requestType}</span>`;
            }
            if (ev.serviceType) {
                html += `<span class="detail-badge badge-service">${ev.serviceType}</span>`;
            }
            html += `</div>`;

            // Client & Company
            html += `<div class="detail-client"><i class="fas fa-user"></i> ${this.escapeHtml(ev.client)}</div>`;
            html += `<div class="detail-company"><i class="fas fa-building"></i> ${this.escapeHtml(ev.company)}</div>`;

            // Requested service
            if (ev.requestedService) {
                html += `<div class="detail-service"><i class="fas fa-concierge-bell"></i> ${this.escapeHtml(ev.requestedService)}</div>`;
            }

            // Seller
            if (ev.seller) {
                html += `<div class="detail-seller"><i class="fas fa-user-tie"></i> ${this.escapeHtml(ev.seller)}</div>`;
            }

            html += `</div>`;
        });

        html += `</div>`;
        panel.innerHTML = html;
        panel.classList.add('visible');
    }
}
