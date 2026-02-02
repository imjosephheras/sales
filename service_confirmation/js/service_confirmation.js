/**
 * Admin Panel - Task Tracking Module
 * Manages service task tracking with multiple checkboxes
 */

document.addEventListener('DOMContentLoaded', function() {
    // State
    let currentPage = 1;
    let totalPages = 1;
    let selectedServiceId = null;
    let services = [];
    let historyData = [];
    let saveTimeout = null;

    // Task definitions
    const TASKS = [
        'site_visit',
        'quote_sent',
        'contract_signed',
        'staff_assigned',
        'equipment_ready',
        'work_started',
        'work_completed',
        'client_approved',
        'invoice_ready'
    ];

    // Initialize
    init();

    function init() {
        loadServices();
        loadHistory();
        setupEventListeners();
    }

    // ========================================
    // Event Listeners
    // ========================================
    function setupEventListeners() {
        // Search services
        document.getElementById('search-pending').addEventListener('input', debounce(function(e) {
            loadServices({ search: e.target.value });
        }, 300));

        // Filter by seller
        document.getElementById('filter-seller').addEventListener('change', function(e) {
            loadServices({ seller: e.target.value });
        });

        // Filter by progress
        document.getElementById('filter-progress').addEventListener('change', function(e) {
            loadServices({ progress: e.target.value });
        });

        // Search history
        document.getElementById('search-history').addEventListener('input', debounce(function(e) {
            loadHistory({ search: e.target.value });
        }, 300));

        // Filter history status
        document.getElementById('filter-history-status').addEventListener('change', function(e) {
            loadHistory({ status: e.target.value });
        });

        // Pagination
        document.getElementById('prev-page').addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                loadHistory({ page: currentPage });
            }
        });

        document.getElementById('next-page').addEventListener('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                loadHistory({ page: currentPage });
            }
        });
    }

    // ========================================
    // Load Services
    // ========================================
    function loadServices(filters = {}) {
        const pendingList = document.getElementById('pending-list');
        pendingList.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

        const params = new URLSearchParams(filters);

        fetch(`controllers/get_pending_services.php?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    services = data.services;
                    renderServices(data.services);
                    updateServiceCount(data.total);
                    populateSellerFilter(data.services);
                } else {
                    pendingList.innerHTML = `<div class="error-message">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error loading services:', error);
                pendingList.innerHTML = '<div class="error-message">Error loading services</div>';
            });
    }

    function renderServices(serviceList) {
        const pendingList = document.getElementById('pending-list');
        const template = document.getElementById('service-card-template');

        if (serviceList.length === 0) {
            pendingList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-clipboard-check"></i>
                    <p>No services found</p>
                </div>
            `;
            return;
        }

        pendingList.innerHTML = '';

        serviceList.forEach(service => {
            const card = template.content.cloneNode(true);
            const cardEl = card.querySelector('.service-card');

            cardEl.dataset.id = service.id;
            card.querySelector('.nomenclature').textContent = service.Order_Nomenclature || `#${service.id}`;
            card.querySelector('.work-date').textContent = formatDate(service.Work_Date) || 'No date';
            card.querySelector('.company-name').textContent = service.Company_Name || 'N/A';
            card.querySelector('.client-name').textContent = service.client_name || '';
            card.querySelector('.service-type').textContent = service.Service_Type || '';
            card.querySelector('.seller').textContent = service.Seller || '';
            card.querySelector('.price').textContent = service.PriceInput || '';

            // Calculate progress
            const tracking = parseTaskTracking(service.task_tracking);
            const completedTasks = countCompletedTasks(tracking);
            const progressPercent = (completedTasks / TASKS.length) * 100;

            // Update progress bar
            const progressFill = card.querySelector('.progress-fill-mini');
            const progressLabel = card.querySelector('.progress-label');
            progressFill.style.width = `${progressPercent}%`;
            progressLabel.textContent = `${completedTasks}/${TASKS.length}`;

            // Color based on progress
            if (progressPercent === 100) {
                progressFill.classList.add('complete');
                cardEl.classList.add('card-complete');
            } else if (progressPercent > 0) {
                progressFill.classList.add('in-progress');
            }

            // Click to select
            cardEl.addEventListener('click', function() {
                selectService(service.id);
            });

            pendingList.appendChild(card);
        });
    }

    function updateServiceCount(count) {
        document.querySelector('.pending-count').textContent = count;
    }

    function populateSellerFilter(serviceList) {
        const select = document.getElementById('filter-seller');
        const sellers = [...new Set(serviceList.map(s => s.Seller).filter(Boolean))];

        // Keep current selection
        const currentValue = select.value;

        select.innerHTML = '<option value="">All Sellers</option>';
        sellers.forEach(seller => {
            const option = document.createElement('option');
            option.value = seller;
            option.textContent = seller;
            select.appendChild(option);
        });

        select.value = currentValue;
    }

    // ========================================
    // Load History
    // ========================================
    function loadHistory(filters = {}) {
        const historyList = document.getElementById('history-list');
        historyList.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

        const params = new URLSearchParams({
            page: currentPage,
            limit: 20,
            ...filters
        });

        fetch(`controllers/get_service_history.php?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    historyData = data.records;
                    renderHistory(data.records);
                    updateStats(data.stats);
                    updatePagination(data.pagination);
                } else {
                    historyList.innerHTML = `<div class="error-message">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error loading history:', error);
                historyList.innerHTML = '<div class="error-message">Error loading history</div>';
            });
    }

    function renderHistory(records) {
        const historyList = document.getElementById('history-list');
        const template = document.getElementById('history-item-template');

        if (records.length === 0) {
            historyList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No records found</p>
                </div>
            `;
            return;
        }

        historyList.innerHTML = '';

        records.forEach(record => {
            const item = template.content.cloneNode(true);
            const itemEl = item.querySelector('.history-item');

            itemEl.dataset.id = record.id;

            // Calculate progress for status
            const tracking = parseTaskTracking(record.task_tracking);
            const completedTasks = countCompletedTasks(tracking);
            const progressPercent = (completedTasks / TASKS.length) * 100;

            const statusIcon = item.querySelector('.status-icon');
            const statusBadge = item.querySelector('.status-badge');

            if (progressPercent === 100) {
                statusIcon.className = 'status-icon fas fa-check-circle text-success';
                statusBadge.textContent = 'Complete';
                statusBadge.className = 'status-badge badge-success';
            } else if (progressPercent > 0) {
                statusIcon.className = 'status-icon fas fa-spinner text-warning';
                statusBadge.textContent = `${completedTasks}/${TASKS.length}`;
                statusBadge.className = 'status-badge badge-warning';
            } else {
                statusIcon.className = 'status-icon fas fa-circle text-muted';
                statusBadge.textContent = 'Not Started';
                statusBadge.className = 'status-badge badge-secondary';
            }

            item.querySelector('.nomenclature').textContent = record.Order_Nomenclature || `#${record.id}`;
            item.querySelector('.history-company').textContent = record.Company_Name || 'N/A';
            item.querySelector('.date-value').textContent = formatDate(record.created_at);
            item.querySelector('.seller-value').textContent = record.Seller || '';

            // View details
            item.querySelector('.btn-view').addEventListener('click', function() {
                selectService(record.id);
            });

            // PDF link
            const pdfBtn = item.querySelector('.btn-pdf');
            if (record.final_pdf_path) {
                pdfBtn.href = record.final_pdf_path;
            } else {
                pdfBtn.style.display = 'none';
            }

            historyList.appendChild(item);
        });
    }

    function updateStats(stats) {
        document.getElementById('stat-completed').textContent = stats.completed || 0;
        document.getElementById('stat-not-completed').textContent = stats.in_progress || 0;
        document.getElementById('stat-pending').textContent = stats.not_started || 0;
        document.getElementById('stat-invoice').textContent = stats.ready_to_invoice || 0;
        document.querySelector('.history-count').textContent = stats.total || 0;
    }

    function updatePagination(pagination) {
        currentPage = pagination.page;
        totalPages = pagination.total_pages;

        document.getElementById('current-page').textContent = currentPage;
        document.getElementById('total-pages').textContent = totalPages;

        document.getElementById('prev-page').disabled = currentPage <= 1;
        document.getElementById('next-page').disabled = currentPage >= totalPages;
    }

    // ========================================
    // Select Service (View Details)
    // ========================================
    function selectService(serviceId) {
        selectedServiceId = serviceId;

        // Highlight selected card
        document.querySelectorAll('.service-card, .history-item').forEach(el => {
            el.classList.remove('selected');
        });
        const selectedEl = document.querySelector(`[data-id="${serviceId}"]`);
        if (selectedEl) {
            selectedEl.classList.add('selected');
        }

        // Load details
        loadServiceDetails(serviceId);
    }

    function loadServiceDetails(serviceId) {
        const detailContent = document.getElementById('detail-content');
        detailContent.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

        fetch(`controllers/get_service_detail.php?id=${serviceId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderServiceDetails(data.request);
                } else {
                    detailContent.innerHTML = `<div class="error-message">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error loading details:', error);
                detailContent.innerHTML = '<div class="error-message">Error loading details</div>';
            });
    }

    function renderServiceDetails(request) {
        const detailContent = document.getElementById('detail-content');
        const template = document.getElementById('detail-template');
        const detail = template.content.cloneNode(true);

        // Nomenclature
        detail.querySelector('.nomenclature-badge').textContent = request.Order_Nomenclature || `#${request.id}`;

        // Parse task tracking
        const tracking = parseTaskTracking(request.task_tracking);
        const completedTasks = countCompletedTasks(tracking);
        const progressPercent = (completedTasks / TASKS.length) * 100;

        // Update progress indicator
        detail.querySelector('.progress-text').textContent = `${completedTasks}/${TASKS.length} tasks`;
        detail.querySelector('.progress-fill').style.width = `${progressPercent}%`;

        // Set checkbox states
        const checkboxes = detail.querySelectorAll('.task-checklist input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            const taskName = checkbox.dataset.task;
            checkbox.checked = tracking[taskName] || false;

            // Add change event
            checkbox.addEventListener('change', function() {
                saveTaskTracking(request.id);
            });
        });

        // Service info
        detail.querySelector('.service-type').textContent = request.Service_Type || '-';
        detail.querySelector('.request-type').textContent = request.Request_Type || '-';
        detail.querySelector('.seller').textContent = request.Seller || '-';
        detail.querySelector('.price').textContent = request.PriceInput || '-';

        // Client info
        detail.querySelector('.company-name').textContent = request.Company_Name || '-';
        detail.querySelector('.client-name').textContent = request.client_name || '-';
        detail.querySelector('.email').textContent = request.Email || '-';
        detail.querySelector('.phone').textContent = request.Number_Phone || '-';

        // Dates
        detail.querySelector('.work-date').textContent = formatDate(request.Work_Date) || '-';
        detail.querySelector('.created-at').textContent = formatDateTime(request.created_at) || '-';

        // Admin notes
        const notesInput = detail.querySelector('#adminNotes');
        notesInput.value = request.admin_notes || '';
        notesInput.dataset.id = request.id;
        notesInput.addEventListener('input', debounce(function() {
            saveAdminNotes(request.id, this.value);
        }, 500));

        // Observations from seller
        const observations = [];
        if (request.Site_Observation) observations.push(request.Site_Observation);
        if (request.Additional_Comments) observations.push(request.Additional_Comments);
        detail.querySelector('.observations-content').textContent = observations.join('\n\n') || 'No notes from seller';

        detailContent.innerHTML = '';
        detailContent.appendChild(detail);
    }

    // ========================================
    // Save Task Tracking
    // ========================================
    function saveTaskTracking(serviceId) {
        // Collect all checkbox states
        const checkboxes = document.querySelectorAll('.task-checklist input[type="checkbox"]');
        const tracking = {};

        checkboxes.forEach(checkbox => {
            tracking[checkbox.dataset.task] = checkbox.checked;
        });

        // Update progress indicator immediately
        const completedTasks = countCompletedTasks(tracking);
        const progressPercent = (completedTasks / TASKS.length) * 100;
        document.querySelector('.progress-text').textContent = `${completedTasks}/${TASKS.length} tasks`;
        document.querySelector('.progress-fill').style.width = `${progressPercent}%`;

        // Show saving indicator
        showSaveIndicator('saving');

        // Debounce the actual save
        if (saveTimeout) clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            fetch('controllers/save_task_tracking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    request_id: serviceId,
                    task_tracking: tracking
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSaveIndicator('saved');
                    // Refresh the services list to update progress bars
                    loadServices();
                } else {
                    showSaveIndicator('error');
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error saving:', error);
                showSaveIndicator('error');
            });
        }, 300);
    }

    function saveAdminNotes(serviceId, notes) {
        fetch('controllers/save_admin_notes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                request_id: serviceId,
                admin_notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSaveIndicator('saved');
            }
        })
        .catch(error => {
            console.error('Error saving notes:', error);
        });
    }

    function showSaveIndicator(state) {
        const indicator = document.getElementById('saveIndicator');
        if (!indicator) return;

        indicator.classList.remove('saving', 'saved', 'error');

        if (state === 'saving') {
            indicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            indicator.classList.add('saving');
        } else if (state === 'saved') {
            indicator.innerHTML = '<i class="fas fa-check-circle"></i> Saved';
            indicator.classList.add('saved');
            setTimeout(() => indicator.classList.remove('saved'), 2000);
        } else if (state === 'error') {
            indicator.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error saving';
            indicator.classList.add('error');
        }
    }

    // ========================================
    // Utility Functions
    // ========================================
    function parseTaskTracking(trackingData) {
        if (!trackingData) return {};
        if (typeof trackingData === 'string') {
            try {
                return JSON.parse(trackingData);
            } catch (e) {
                return {};
            }
        }
        return trackingData;
    }

    function countCompletedTasks(tracking) {
        return TASKS.filter(task => tracking[task] === true).length;
    }

    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function formatDateTime(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            month: 'short', day: 'numeric', year: 'numeric',
            hour: 'numeric', minute: '2-digit'
        });
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;

        document.body.appendChild(notification);
        setTimeout(() => notification.classList.add('show'), 10);
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }
});
