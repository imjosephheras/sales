/**
 * CALENDAR MODULE - Main JavaScript
 * Month-by-month calendar with scheduled form events
 */

document.addEventListener('DOMContentLoaded', () => {
    const calendar = new Calendar();
    calendar.init();
});

class Calendar {
    constructor() {
        this.currentDate = new Date();
        this.currentMonth = this.currentDate.getMonth();
        this.currentYear = this.currentDate.getFullYear();
        this.events = {}; // keyed by day number

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

        this.loadAndRender();
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
        this.render();
    }

    async fetchEvents() {
        this.events = {};
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
                    if (!this.events[day]) {
                        this.events[day] = [];
                    }
                    this.events[day].push({
                        id: ev.form_id,
                        client: ev.client_name || 'N/A',
                        company: ev.company_name || 'N/A',
                        status: ev.status || 'pending'
                    });
                });
            }
        } catch (err) {
            console.error('Error fetching calendar events:', err);
        }
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
                    html += `<div class="event-chip" title="${ev.client} - ${ev.company}">`;
                    html += `<span class="event-client">${ev.client}</span>`;
                    html += `<span class="event-company">${ev.company}</span>`;
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
        html += `<span class="detail-count">${eventsForDay.length} form${eventsForDay.length !== 1 ? 's' : ''}</span>`;
        html += `</div>`;
        html += `<div class="detail-list">`;

        eventsForDay.forEach(ev => {
            const statusClass = ev.status === 'submitted' ? 'status-submitted' :
                                ev.status === 'draft' ? 'status-draft' : 'status-pending';
            html += `<div class="detail-item">`;
            html += `<div class="detail-client"><i class="fas fa-user"></i> ${ev.client}</div>`;
            html += `<div class="detail-company"><i class="fas fa-building"></i> ${ev.company}</div>`;
            html += `<span class="detail-status ${statusClass}">${ev.status}</span>`;
            html += `</div>`;
        });

        html += `</div>`;
        panel.innerHTML = html;
        panel.classList.add('visible');
    }
}
