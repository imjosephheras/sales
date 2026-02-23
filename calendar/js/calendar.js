/**
 * CALENDAR MODULE - Main JavaScript
 * Month-by-month calendar with scheduled agendas (base + recurring events)
 * Features: Client filter sidebar, Mini form, Drag & Drop, Recurrence,
 *           Compact/Expanded view modes, Day orders modal
 */

document.addEventListener('DOMContentLoaded', () => {
    const calendar = new Calendar();
    calendar.init();
    initThemeToggle();
});

/**
 * Theme toggle - switches between light and dark mode
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
        this.allEvents = {};       // all events keyed by day (unfiltered)
        this.events = {};          // filtered events keyed by day
        this.rawEvents = [];       // flat array of all events for the month
        this.selectedClients = new Set();
        this.allClients = [];

        // Service type filter state
        this.selectedServiceTypes = new Set();
        this.allServiceTypes = [];

        // Drag state
        this.draggedEvent = null;
        this.draggedElement = null;

        // View mode: 'compact' or 'expanded'
        this.viewMode = localStorage.getItem('calendar-view-mode') || 'compact';

        // Track if mini form was opened from day modal (to re-open modal after save)
        this.openedFromDayModal = false;
        this.dayModalDay = null;

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
        this.printBtn = document.getElementById('print-btn');
        this.miniFormOverlay = document.getElementById('mini-form-overlay');
        this.miniFormPanel = document.getElementById('mini-form-panel');

        this.prevBtn.addEventListener('click', () => this.changeMonth(-1));
        this.nextBtn.addEventListener('click', () => this.changeMonth(1));
        this.todayBtn.addEventListener('click', () => this.goToToday());
        this.printBtn.addEventListener('click', () => window.print());

        // Mini form close
        if (this.miniFormOverlay) {
            this.miniFormOverlay.addEventListener('click', () => this.closeMiniForm());
        }
        const miniFormClose = document.getElementById('mini-form-close');
        if (miniFormClose) {
            miniFormClose.addEventListener('click', () => this.closeMiniForm());
        }

        // Mini form save
        const miniFormSave = document.getElementById('mini-form-save');
        if (miniFormSave) {
            miniFormSave.addEventListener('click', () => this.saveMiniForm());
        }

        // Sidebar controls
        this.initSidebar();
        this.initServiceSidebar();
        this.initNavToggles();
        this.initViewModeToggle();
        this.initDayModal();

        this.loadAndRender();
    }

    // ===== VIEW MODE TOGGLE =====

    initViewModeToggle() {
        this.viewModeBtn = document.getElementById('toggle-view-mode');
        this.viewModeIcon = document.getElementById('view-mode-icon');
        this.viewModeLabel = document.getElementById('view-mode-label');

        this.updateViewModeUI();

        this.viewModeBtn.addEventListener('click', () => {
            this.viewMode = this.viewMode === 'compact' ? 'expanded' : 'compact';
            localStorage.setItem('calendar-view-mode', this.viewMode);
            this.updateViewModeUI();
            this.render();
        });
    }

    updateViewModeUI() {
        if (this.viewMode === 'expanded') {
            this.viewModeIcon.className = 'fas fa-expand-alt';
            this.viewModeLabel.textContent = 'Expanded';
            this.viewModeBtn.classList.add('active');
        } else {
            this.viewModeIcon.className = 'fas fa-compress-alt';
            this.viewModeLabel.textContent = 'Compact';
            this.viewModeBtn.classList.remove('active');
        }
    }

    // ===== DAY ORDERS MODAL =====

    initDayModal() {
        this.dayModalOverlay = document.getElementById('day-modal-overlay');
        this.dayModal = document.getElementById('day-modal');
        this.dayModalTitle = document.getElementById('day-modal-title');
        this.dayModalCount = document.getElementById('day-modal-count');
        this.dayModalBody = document.getElementById('day-modal-body');
        this.dayModalClose = document.getElementById('day-modal-close');

        this.dayModalOverlay.addEventListener('click', () => this.closeDayModal());
        this.dayModalClose.addEventListener('click', () => this.closeDayModal());
    }

    showDayModal(day) {
        const eventsForDay = this.events[day] || [];
        if (eventsForDay.length === 0) return;

        this.dayModalDay = day;
        const dateStr = `${this.monthNames[this.currentMonth]} ${day}, ${this.currentYear}`;
        this.dayModalTitle.textContent = dateStr;
        this.dayModalCount.textContent = `${eventsForDay.length} order${eventsForDay.length !== 1 ? 's' : ''}`;

        let html = '';
        eventsForDay.forEach(ev => {
            const typeClass = this.getRequestTypeClass(ev.requestType);
            const typeShort = this.getRequestTypeShort(ev.requestType);
            const svcDetailClass = this.getServiceStatusDetailClass(ev.serviceStatus);
            const svcLabel = this.getServiceStatusLabel(ev.serviceStatus);
            const isPriorityRush = ev.priority === 'Rush';

            html += `<div class="day-modal-card ${typeClass}-border" data-event-id="${ev.eventId}">`;

            // Card header row: WO# + badges
            html += `<div class="day-modal-card-header">`;
            html += `<div class="day-modal-card-wo">`;
            if (ev.nomenclature) {
                html += `<span class="day-modal-wo-number">${this.escapeHtml(ev.nomenclature)}</span>`;
            } else {
                html += `<span class="day-modal-wo-number">WO #${ev.formId}</span>`;
            }
            if (typeShort) {
                html += `<span class="chip-type-badge ${typeClass}">${typeShort}</span>`;
            }
            if (isPriorityRush) {
                html += `<span class="detail-priority-rush"><i class="fas fa-bolt"></i> Rush</span>`;
            }
            html += `</div>`;
            // Service status
            html += `<span class="detail-svc-status ${svcDetailClass}"><i class="fas fa-circle" style="font-size:0.4rem;margin-right:4px;"></i>${svcLabel}</span>`;
            html += `</div>`;

            // Client
            html += `<div class="day-modal-card-client"><i class="fas fa-user"></i> ${this.escapeHtml(ev.client)}</div>`;

            // Site (company)
            html += `<div class="day-modal-card-site"><i class="fas fa-building"></i> ${this.escapeHtml(ev.company)}</div>`;

            // Status row
            html += `<div class="day-modal-card-footer">`;
            const statusClass = ev.status === 'submitted' ? 'status-submitted' :
                                ev.status === 'draft' ? 'status-draft' : 'status-pending';
            html += `<span class="detail-status ${statusClass}">${ev.status}</span>`;
            html += ev.isBaseEvent
                ? `<span class="detail-badge-type badge-base-sm">BASE</span>`
                : `<span class="detail-badge-type badge-recurring-sm"><i class="fas fa-sync-alt"></i> REC</span>`;
            html += `<span class="day-modal-card-edit"><i class="fas fa-pen"></i> Edit</span>`;
            html += `</div>`;

            html += `</div>`;
        });

        this.dayModalBody.innerHTML = html;

        // Show modal
        this.dayModalOverlay.classList.add('visible');
        this.dayModal.classList.add('visible');

        // Click on card opens mini form
        this.dayModalBody.querySelectorAll('.day-modal-card[data-event-id]').forEach(card => {
            card.addEventListener('click', (e) => {
                e.stopPropagation();
                const eid = parseInt(card.dataset.eventId, 10);
                this.openedFromDayModal = true;
                this.openMiniForm(eid);
            });
        });
    }

    closeDayModal() {
        this.dayModalOverlay.classList.remove('visible');
        this.dayModal.classList.remove('visible');
        this.dayModalDay = null;
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

        this.clientSearchEl.addEventListener('input', () => this.renderClientList());

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

        const savedSidebar = localStorage.getItem('calendar-sidebar');
        if (savedSidebar === 'collapsed') {
            this.sidebarEl.classList.add('collapsed');
            this.expandBtn.classList.add('visible');
        }

        this.collapseBtn.addEventListener('click', () => {
            this.sidebarEl.classList.add('collapsed');
            this.expandBtn.classList.add('visible');
            localStorage.setItem('calendar-sidebar', 'collapsed');
            if (this.toggleClientBtn) this.toggleClientBtn.classList.remove('active');
        });

        this.expandBtn.addEventListener('click', () => {
            this.sidebarEl.classList.remove('collapsed');
            this.expandBtn.classList.remove('visible');
            localStorage.setItem('calendar-sidebar', 'expanded');
            if (this.toggleClientBtn) this.toggleClientBtn.classList.add('active');
        });
    }

    /**
     * Initialize service type filter sidebar elements and event listeners
     */
    initServiceSidebar() {
        this.serviceSidebarEl = document.getElementById('service-filter-sidebar');
        this.serviceTypeListEl = document.getElementById('service-type-list');
        this.serviceSearchEl = document.getElementById('service-search');
        this.serviceSelectAllBtn = document.getElementById('service-select-all-btn');
        this.serviceDeselectAllBtn = document.getElementById('service-deselect-all-btn');
        this.serviceFilterCountEl = document.getElementById('service-filter-count');
        this.serviceCollapseBtn = document.getElementById('service-sidebar-collapse-btn');
        this.serviceExpandBtn = document.getElementById('service-sidebar-expand-btn');

        this.serviceSearchEl.addEventListener('input', () => this.renderServiceTypeList());

        this.serviceSelectAllBtn.addEventListener('click', () => {
            this.allServiceTypes.forEach(s => this.selectedServiceTypes.add(s));
            this.renderServiceTypeList();
            this.applyFilter();
        });

        this.serviceDeselectAllBtn.addEventListener('click', () => {
            this.selectedServiceTypes.clear();
            this.renderServiceTypeList();
            this.applyFilter();
        });

        const savedServiceSidebar = localStorage.getItem('calendar-service-sidebar');
        if (savedServiceSidebar === 'collapsed') {
            this.serviceSidebarEl.classList.add('collapsed');
            this.serviceExpandBtn.classList.add('visible');
        }

        this.serviceCollapseBtn.addEventListener('click', () => {
            this.serviceSidebarEl.classList.add('collapsed');
            this.serviceExpandBtn.classList.add('visible');
            localStorage.setItem('calendar-service-sidebar', 'collapsed');
            if (this.toggleServiceBtn) this.toggleServiceBtn.classList.remove('active');
        });

        this.serviceExpandBtn.addEventListener('click', () => {
            this.serviceSidebarEl.classList.remove('collapsed');
            this.serviceExpandBtn.classList.remove('visible');
            localStorage.setItem('calendar-service-sidebar', 'expanded');
            if (this.toggleServiceBtn) this.toggleServiceBtn.classList.add('active');
        });
    }

    /**
     * Initialize nav toggle buttons for sidebar visibility
     */
    initNavToggles() {
        this.toggleClientBtn = document.getElementById('toggle-client-sidebar');
        this.toggleServiceBtn = document.getElementById('toggle-service-sidebar');

        // Sync initial state from localStorage
        const clientCollapsed = localStorage.getItem('calendar-sidebar') === 'collapsed';
        const serviceCollapsed = localStorage.getItem('calendar-service-sidebar') === 'collapsed';

        if (clientCollapsed) {
            this.toggleClientBtn.classList.remove('active');
        }
        if (serviceCollapsed) {
            this.toggleServiceBtn.classList.remove('active');
        }

        this.toggleClientBtn.addEventListener('click', () => {
            const isCollapsed = this.sidebarEl.classList.contains('collapsed');
            if (isCollapsed) {
                this.sidebarEl.classList.remove('collapsed');
                this.expandBtn.classList.remove('visible');
                this.toggleClientBtn.classList.add('active');
                localStorage.setItem('calendar-sidebar', 'expanded');
            } else {
                this.sidebarEl.classList.add('collapsed');
                this.expandBtn.classList.add('visible');
                this.toggleClientBtn.classList.remove('active');
                localStorage.setItem('calendar-sidebar', 'collapsed');
            }
        });

        this.toggleServiceBtn.addEventListener('click', () => {
            const isCollapsed = this.serviceSidebarEl.classList.contains('collapsed');
            if (isCollapsed) {
                this.serviceSidebarEl.classList.remove('collapsed');
                this.serviceExpandBtn.classList.remove('visible');
                this.toggleServiceBtn.classList.add('active');
                localStorage.setItem('calendar-service-sidebar', 'expanded');
            } else {
                this.serviceSidebarEl.classList.add('collapsed');
                this.serviceExpandBtn.classList.add('visible');
                this.toggleServiceBtn.classList.remove('active');
                localStorage.setItem('calendar-service-sidebar', 'collapsed');
            }
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
        await this.fetchServiceTypes();
        this.applyFilter();
    }

    async fetchEvents() {
        this.allEvents = {};
        this.rawEvents = [];
        try {
            const month = this.currentMonth + 1;
            const year = this.currentYear;
            const resp = await fetch(`get_events.php?month=${month}&year=${year}`);
            const data = await resp.json();

            if (data.success && data.events) {
                this.rawEvents = data.events;
                data.events.forEach(ev => {
                    const day = parseInt(ev.event_date.split('-')[2], 10);
                    if (!this.allEvents[day]) {
                        this.allEvents[day] = [];
                    }

                    // Parse service types from janitorial, kitchen and hood vent costs
                    const serviceTypesList = [];
                    if (ev.janitorial_services) {
                        ev.janitorial_services.split('||').forEach(s => {
                            const trimmed = s.trim();
                            if (trimmed) serviceTypesList.push(trimmed);
                        });
                    }
                    if (ev.kitchen_services) {
                        ev.kitchen_services.split('||').forEach(s => {
                            const trimmed = s.trim();
                            if (trimmed) serviceTypesList.push(trimmed);
                        });
                    }
                    if (ev.hood_vent_services) {
                        ev.hood_vent_services.split('||').forEach(s => {
                            const trimmed = s.trim();
                            if (trimmed) serviceTypesList.push(trimmed);
                        });
                    }

                    this.allEvents[day].push({
                        eventId: parseInt(ev.event_id, 10),
                        formId: parseInt(ev.form_id, 10),
                        parentEventId: ev.parent_event_id ? parseInt(ev.parent_event_id, 10) : null,
                        isBaseEvent: parseInt(ev.is_base_event, 10) === 1,
                        eventDate: ev.event_date,
                        description: ev.description || '',
                        frequencyMonths: parseInt(ev.frequency_months, 10) || 0,
                        frequencyYears: parseInt(ev.frequency_years, 10) || 0,
                        client: ev.client_name || 'N/A',
                        company: ev.company_name || 'N/A',
                        workDate: ev.Work_Date || '',
                        status: ev.status || 'pending',
                        serviceType: ev.service_type || '',
                        requestType: ev.request_type || '',
                        priority: ev.priority || '',
                        requestedService: ev.requested_service || '',
                        nomenclature: ev.Order_Nomenclature || '',
                        seller: ev.seller || '',
                        documentDate: ev.Document_Date || '',
                        serviceStatus: ev.service_status || 'pending',
                        serviceTypesList: serviceTypesList
                    });
                });
            }
        } catch (err) {
            console.error('Error fetching calendar events:', err);
        }
    }

    extractClients() {
        const clientSet = new Set();
        Object.values(this.allEvents).forEach(dayEvents => {
            dayEvents.forEach(ev => clientSet.add(ev.client));
        });

        this.allClients = Array.from(clientSet).sort((a, b) =>
            a.localeCompare(b, undefined, { sensitivity: 'base' })
        );

        this.selectedClients = new Set(this.allClients);
        this.clientSearchEl.value = '';
        this.renderClientList();
    }

    /**
     * Fetch all distinct service types from the database
     * (hood_vent_costs, janitorial_services_costs, kitchen_cleaning_costs)
     */
    async fetchServiceTypes() {
        try {
            const resp = await fetch('get_service_types.php');
            const data = await resp.json();
            if (data.success && data.service_types) {
                this.allServiceTypes = data.service_types;
            } else {
                this.allServiceTypes = [];
            }
        } catch (err) {
            console.error('Error fetching service types:', err);
            this.allServiceTypes = [];
        }

        this.selectedServiceTypes = new Set(this.allServiceTypes);
        this.serviceSearchEl.value = '';
        this.renderServiceTypeList();
    }

    /**
     * Render service type checkboxes in the service sidebar
     */
    renderServiceTypeList() {
        const searchTerm = this.serviceSearchEl.value.toLowerCase().trim();
        const filtered = searchTerm
            ? this.allServiceTypes.filter(s => s.toLowerCase().includes(searchTerm))
            : this.allServiceTypes;

        if (this.allServiceTypes.length === 0) {
            this.serviceTypeListEl.innerHTML = `
                <div class="client-list-empty">
                    <i class="fas fa-concierge-bell"></i>
                    <span>No services this month</span>
                </div>`;
            this.updateServiceFilterCount();
            return;
        }

        if (filtered.length === 0) {
            this.serviceTypeListEl.innerHTML = `
                <div class="client-list-empty">
                    <i class="fas fa-search"></i>
                    <span>No matching services</span>
                </div>`;
            return;
        }

        // Count events per service type
        const serviceCounts = {};
        Object.values(this.allEvents).forEach(dayEvents => {
            dayEvents.forEach(ev => {
                if (ev.serviceTypesList) {
                    ev.serviceTypesList.forEach(s => {
                        serviceCounts[s] = (serviceCounts[s] || 0) + 1;
                    });
                }
            });
        });

        let html = '';
        filtered.forEach(service => {
            const isChecked = this.selectedServiceTypes.has(service);
            const count = serviceCounts[service] || 0;
            html += `
                <label class="client-item ${isChecked ? 'active' : ''}">
                    <input type="checkbox" value="${this.escapeHtml(service)}" ${isChecked ? 'checked' : ''}>
                    <span class="client-checkbox-custom">
                        <i class="fas fa-check"></i>
                    </span>
                    <span class="client-name">${this.escapeHtml(service)}</span>
                    <span class="client-count">${count}</span>
                </label>`;
        });

        this.serviceTypeListEl.innerHTML = html;

        this.serviceTypeListEl.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', (e) => {
                const serviceName = e.target.value;
                if (e.target.checked) {
                    this.selectedServiceTypes.add(serviceName);
                } else {
                    this.selectedServiceTypes.delete(serviceName);
                }
                e.target.closest('.client-item').classList.toggle('active', e.target.checked);
                this.applyFilter();
            });
        });

        this.updateServiceFilterCount();
    }

    /**
     * Update the service filter count display in the sidebar footer
     */
    updateServiceFilterCount() {
        const total = this.allServiceTypes.length;
        const selected = this.selectedServiceTypes.size;
        if (selected === total) {
            this.serviceFilterCountEl.textContent = `Showing all (${total})`;
        } else {
            this.serviceFilterCountEl.textContent = `${selected} of ${total} selected`;
        }
    }

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

        this.clientListEl.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', (e) => {
                const clientName = e.target.value;
                if (e.target.checked) {
                    this.selectedClients.add(clientName);
                } else {
                    this.selectedClients.delete(clientName);
                }
                e.target.closest('.client-item').classList.toggle('active', e.target.checked);
                this.applyFilter();
            });
        });

        this.updateFilterCount();
    }

    updateFilterCount() {
        const total = this.allClients.length;
        const selected = this.selectedClients.size;
        if (selected === total) {
            this.filterCountEl.textContent = `Showing all (${total})`;
        } else {
            this.filterCountEl.textContent = `${selected} of ${total} selected`;
        }
    }

    applyFilter() {
        this.events = {};

        const allClientsSelected = this.selectedClients.size === this.allClients.length;
        const allServicesSelected = this.selectedServiceTypes.size === this.allServiceTypes.length;

        Object.entries(this.allEvents).forEach(([day, dayEvents]) => {
            const filtered = dayEvents.filter(ev => {
                // Client filter
                const clientPass = allClientsSelected || this.selectedClients.has(ev.client);

                // Service type filter: at least one event service must be selected
                let servicePass;
                if (allServicesSelected) {
                    servicePass = true;
                } else if (!ev.serviceTypesList || ev.serviceTypesList.length === 0) {
                    // Events with no service types pass only when all services are selected
                    servicePass = false;
                } else {
                    servicePass = ev.serviceTypesList.some(s => this.selectedServiceTypes.has(s));
                }

                return clientPass && servicePass;
            });
            if (filtered.length > 0) {
                this.events[day] = filtered;
            }
        });

        this.updateFilterCount();
        this.updateServiceFilterCount();
        this.render();
    }

    getRequestTypeClass(requestType) {
        const type = (requestType || '').toLowerCase();
        if (type === 'contract') return 'chip-contract';
        if (type === 'proposal') return 'chip-proposal';
        if (type === 'jwo') return 'chip-jwo';
        if (type === 'quote') return 'chip-quote';
        return 'chip-default';
    }

    getRequestTypeShort(requestType) {
        const type = (requestType || '').toLowerCase();
        if (type === 'contract') return 'CTR';
        if (type === 'proposal') return 'PRP';
        if (type === 'jwo') return 'JWO';
        if (type === 'quote') return 'QTE';
        return '';
    }

    getServiceStatusClass(serviceStatus) {
        const s = (serviceStatus || '').toLowerCase();
        if (s === 'completed') return 'chip-status-completed';
        if (s === 'not_completed') return 'chip-status-not-completed';
        if (s === 'pending') return 'chip-status-pending';
        // scheduled, confirmed, in_progress → blue
        return 'chip-status-other';
    }

    getServiceStatusLabel(serviceStatus) {
        const s = (serviceStatus || '').toLowerCase();
        if (s === 'completed') return 'Completed';
        if (s === 'not_completed') return 'Not Completed';
        if (s === 'pending') return 'Pending';
        if (s === 'scheduled') return 'Scheduled';
        if (s === 'confirmed') return 'Confirmed';
        if (s === 'in_progress') return 'In Progress';
        return serviceStatus || 'Pending';
    }

    getServiceStatusDetailClass(serviceStatus) {
        const s = (serviceStatus || '').toLowerCase();
        if (s === 'completed') return 'svc-completed';
        if (s === 'not_completed') return 'svc-not-completed';
        if (s === 'pending') return 'svc-pending';
        return 'svc-other';
    }

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

        const isExpanded = this.viewMode === 'expanded';

        let html = '';

        // Empty cells before the 1st
        for (let i = 0; i < firstDay; i++) {
            html += '<div class="day-cell empty" data-drop-target="true"></div>';
        }

        // Day cells
        for (let day = 1; day <= daysInMonth; day++) {
            const isToday = isCurrentMonth && day === todayDate;
            const hasEvents = this.events[day] && this.events[day].length > 0;
            const classes = ['day-cell'];

            if (isToday) classes.push('today');
            if (hasEvents) classes.push('has-events');
            if (isExpanded) classes.push('view-expanded');

            // Build full date string for drop target
            const monthStr = String(this.currentMonth + 1).padStart(2, '0');
            const dayStr = String(day).padStart(2, '0');
            const fullDate = `${this.currentYear}-${monthStr}-${dayStr}`;

            html += `<div class="${classes.join(' ')}" data-day="${day}" data-date="${fullDate}" data-drop-target="true">`;
            html += `<span class="day-number">${day}</span>`;

            if (hasEvents) {
                const eventsForDay = this.events[day];
                const maxVisible = isExpanded ? eventsForDay.length : 3;

                html += `<div class="day-events${isExpanded ? ' day-events-expanded' : ''}">`;
                eventsForDay.slice(0, maxVisible).forEach(ev => {
                    const typeClass = this.getRequestTypeClass(ev.requestType);
                    const typeShort = this.getRequestTypeShort(ev.requestType);
                    const statusClass = this.getServiceStatusClass(ev.serviceStatus);
                    const priorityClass = ev.priority === 'Rush' ? 'priority-rush' : '';
                    const baseClass = ev.isBaseEvent ? 'event-base' : 'event-recurring';

                    html += `<div class="event-chip ${statusClass} ${priorityClass} ${baseClass}"
                                  draggable="true"
                                  data-event-id="${ev.eventId}"
                                  title="${this.escapeHtml(ev.client)} - ${this.escapeHtml(ev.company)} | ${ev.requestType} | ${ev.requestedService}${ev.isBaseEvent ? ' [BASE]' : ' [Recurring]'}">`;

                    if (isExpanded) {
                        // Expanded mode: Line 1 = Badge + WO# + Client
                        html += `<div class="event-chip-row">`;
                        if (typeShort) {
                            html += `<span class="chip-type-badge ${typeClass}">${typeShort}</span>`;
                        }
                        const woLabel = ev.nomenclature ? this.escapeHtml(ev.nomenclature) : `WO #${ev.formId}`;
                        html += `<span class="event-wo-number">${woLabel}</span>`;
                        if (!ev.isBaseEvent) {
                            html += `<span class="chip-recurring-badge"><i class="fas fa-sync-alt"></i></span>`;
                        }
                        html += `</div>`;
                        // Line 2 = Client
                        html += `<span class="event-client">${this.escapeHtml(ev.client)}</span>`;
                        // Line 3 = Site (company)
                        html += `<span class="event-company-name">${this.escapeHtml(ev.company)}</span>`;
                        // Line 4 = Status
                        const statusLabelClass = ev.status === 'submitted' ? 'status-submitted' :
                                                 ev.status === 'draft' ? 'status-draft' : 'status-pending';
                        html += `<span class="event-status-label ${statusLabelClass}">${ev.status}</span>`;
                    } else {
                        // Compact mode: Badge + Client + Company
                        if (typeShort) {
                            html += `<span class="chip-type-badge ${typeClass}">${typeShort}</span>`;
                        }
                        if (!ev.isBaseEvent) {
                            html += `<span class="chip-recurring-badge"><i class="fas fa-sync-alt"></i></span>`;
                        }
                        html += `<span class="event-client">${this.escapeHtml(ev.client)}</span>`;
                        html += `<span class="event-company-name">${this.escapeHtml(ev.company)}</span>`;
                    }

                    html += `</div>`;
                });

                if (!isExpanded && eventsForDay.length > maxVisible) {
                    html += `<div class="event-more">+${eventsForDay.length - maxVisible} more</div>`;
                }
                html += '</div>';
            }

            html += '</div>';
        }

        this.gridEl.innerHTML = html;

        // Attach click listeners for day cells (open day modal)
        this.gridEl.querySelectorAll('.day-cell:not(.empty)').forEach(cell => {
            cell.addEventListener('click', (e) => {
                // Don't open modal if we clicked on a chip (that opens mini form directly)
                if (e.target.closest('.event-chip')) return;
                e.stopPropagation();
                const day = parseInt(cell.dataset.day, 10);
                if (this.events[day] && this.events[day].length > 0) {
                    this.showDayModal(day);
                }
            });
        });

        // Attach click listeners on event chips to open mini form directly
        this.gridEl.querySelectorAll('.event-chip').forEach(chip => {
            chip.addEventListener('click', (e) => {
                e.stopPropagation();
                const eventId = parseInt(chip.dataset.eventId, 10);
                this.openedFromDayModal = false;
                this.openMiniForm(eventId);
            });
        });

        // Drag & Drop setup
        this.setupDragAndDrop();
    }

    // ===== DRAG & DROP =====

    setupDragAndDrop() {
        // Drag start on event chips
        this.gridEl.querySelectorAll('.event-chip[draggable="true"]').forEach(chip => {
            chip.addEventListener('dragstart', (e) => {
                const eventId = parseInt(chip.dataset.eventId, 10);
                this.draggedEvent = this.findEventById(eventId);
                this.draggedElement = chip;
                chip.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', eventId.toString());
            });

            chip.addEventListener('dragend', () => {
                chip.classList.remove('dragging');
                this.draggedEvent = null;
                this.draggedElement = null;
                // Remove all drop-over highlights
                this.gridEl.querySelectorAll('.day-cell.drop-over').forEach(cell => {
                    cell.classList.remove('drop-over');
                });
            });
        });

        // Drop targets (all day cells)
        this.gridEl.querySelectorAll('.day-cell[data-drop-target="true"]').forEach(cell => {
            cell.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                cell.classList.add('drop-over');
            });

            cell.addEventListener('dragleave', () => {
                cell.classList.remove('drop-over');
            });

            cell.addEventListener('drop', (e) => {
                e.preventDefault();
                cell.classList.remove('drop-over');

                const newDate = cell.dataset.date;
                if (!newDate || !this.draggedEvent) return;

                // Don't drop on empty cells without a date
                if (cell.classList.contains('empty') && !newDate) return;

                this.handleDrop(this.draggedEvent, newDate);
            });
        });
    }

    findEventById(eventId) {
        for (const dayEvents of Object.values(this.allEvents)) {
            const found = dayEvents.find(ev => ev.eventId === eventId);
            if (found) return found;
        }
        return null;
    }

    async handleDrop(event, newDate) {
        if (!event || !newDate) return;

        try {
            const formData = new FormData();
            formData.append('event_id', event.eventId);
            formData.append('new_date', newDate);

            const resp = await fetch('update_work_date.php', {
                method: 'POST',
                body: formData
            });
            const data = await resp.json();

            if (data.success) {
                // Reload calendar
                await this.loadAndRender();
            } else {
                console.error('Drop failed:', data.message);
            }
        } catch (err) {
            console.error('Error updating work date:', err);
        }
    }

    // ===== MINI FORM =====

    async openMiniForm(eventId) {
        const event = this.findEventById(eventId);
        if (!event) return;

        // Find the base event for this form to get frequency settings
        let baseEvent = event;
        if (!event.isBaseEvent) {
            // First try to find in current month's raw data
            const base = this.rawEvents.find(e =>
                parseInt(e.form_id, 10) === event.formId && parseInt(e.is_base_event, 10) === 1
            );
            if (base) {
                baseEvent = {
                    eventId: parseInt(base.event_id, 10),
                    formId: parseInt(base.form_id, 10),
                    isBaseEvent: true,
                    eventDate: base.event_date,
                    description: base.description || '',
                    frequencyMonths: parseInt(base.frequency_months, 10) || 0,
                    frequencyYears: parseInt(base.frequency_years, 10) || 0,
                    workDate: base.Work_Date || '',
                    client: base.client_name || 'N/A',
                    company: base.company_name || 'N/A'
                };
            } else {
                // Base event not in current month - fetch from server
                try {
                    const resp = await fetch(`get_base_event.php?form_id=${event.formId}`);
                    const data = await resp.json();
                    if (data.success && data.base_event) {
                        const b = data.base_event;
                        baseEvent = {
                            eventId: parseInt(b.event_id, 10),
                            formId: parseInt(b.form_id, 10),
                            isBaseEvent: true,
                            eventDate: b.event_date,
                            description: b.description || '',
                            frequencyMonths: parseInt(b.frequency_months, 10) || 0,
                            frequencyYears: parseInt(b.frequency_years, 10) || 0,
                            workDate: b.Work_Date || '',
                            client: b.client_name || 'N/A',
                            company: b.company_name || 'N/A'
                        };
                    }
                } catch (err) {
                    console.error('Error fetching base event:', err);
                }
            }
        }

        // Populate mini form fields
        document.getElementById('mini-form-event-id').value = event.eventId;
        document.getElementById('mini-form-form-id').value = event.formId;

        // Work Date = form.Work_Date (the base event's date)
        document.getElementById('mini-form-work-date').value = baseEvent.workDate || baseEvent.eventDate;

        // Notes = this specific event's description
        document.getElementById('mini-form-notes').value = event.description || '';

        // Service Status
        document.getElementById('mini-form-service-status').value = event.serviceStatus || 'pending';

        // Frequency from base event
        document.getElementById('mini-form-freq-months').value = baseEvent.frequencyMonths;
        document.getElementById('mini-form-freq-years').value = baseEvent.frequencyYears;

        // Display info
        document.getElementById('mini-form-client-name').textContent = event.client;
        document.getElementById('mini-form-company-name').textContent = event.company;
        document.getElementById('mini-form-event-type').textContent = event.isBaseEvent ? 'Base Event' : 'Recurring Agenda';
        document.getElementById('mini-form-event-type').className = 'mini-form-badge ' + (event.isBaseEvent ? 'badge-base' : 'badge-recurring');

        // Show
        this.miniFormOverlay.classList.add('visible');
        this.miniFormPanel.classList.add('visible');
    }

    closeMiniForm() {
        if (this.miniFormOverlay) this.miniFormOverlay.classList.remove('visible');
        if (this.miniFormPanel) this.miniFormPanel.classList.remove('visible');
    }

    async saveMiniForm() {
        const eventId = document.getElementById('mini-form-event-id').value;
        const workDate = document.getElementById('mini-form-work-date').value;
        const description = document.getElementById('mini-form-notes').value;
        const serviceStatus = document.getElementById('mini-form-service-status').value;
        const freqMonths = document.getElementById('mini-form-freq-months').value;
        const freqYears = document.getElementById('mini-form-freq-years').value;

        if (!eventId) return;

        // Validate frequency values
        const fm = parseInt(freqMonths, 10);
        const fy = parseInt(freqYears, 10);
        if (fm < 0 || fm > 6) {
            alert('Frequency Months must be between 0 and 6');
            return;
        }
        if (fy < 0 || fy > 5) {
            alert('Frequency Years must be between 0 and 5');
            return;
        }

        const saveBtn = document.getElementById('mini-form-save');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        // Remember the day we came from (for refreshing the day modal)
        const reopenDay = this.openedFromDayModal ? this.dayModalDay : null;

        try {
            const formData = new FormData();
            formData.append('event_id', eventId);
            formData.append('work_date', workDate);
            formData.append('description', description);
            formData.append('service_status', serviceStatus);
            formData.append('frequency_months', fm);
            formData.append('frequency_years', fy);

            const resp = await fetch('update_event.php', {
                method: 'POST',
                body: formData
            });
            const data = await resp.json();

            if (data.success) {
                this.closeMiniForm();

                // Refresh events data and re-render calendar
                await this.loadAndRender();

                // If opened from day modal, re-open the day modal with refreshed data
                if (reopenDay && this.events[reopenDay] && this.events[reopenDay].length > 0) {
                    this.showDayModal(reopenDay);
                } else {
                    // Close the day modal if no more events on that day
                    this.closeDayModal();
                }

                this.openedFromDayModal = false;
            } else {
                alert('Error: ' + (data.message || 'Failed to save'));
            }
        } catch (err) {
            console.error('Error saving mini form:', err);
            alert('Error saving changes');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save';
        }
    }
}
