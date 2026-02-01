/**
 * Service Confirmation Module - JavaScript
 * Module 10: Handles service status confirmation workflow
 */

document.addEventListener('DOMContentLoaded', function() {
    // State
    let currentPage = 1;
    let totalPages = 1;
    let selectedServiceId = null;
    let pendingServices = [];
    let historyData = [];

    // Initialize
    init();

    function init() {
        loadPendingServices();
        loadHistory();
        setupEventListeners();
    }

    // ========================================
    // Event Listeners
    // ========================================
    function setupEventListeners() {
        // Search pending
        document.getElementById('search-pending').addEventListener('input', debounce(function(e) {
            loadPendingServices({ search: e.target.value });
        }, 300));

        // Filter by seller
        document.getElementById('filter-seller').addEventListener('change', function(e) {
            loadPendingServices({ seller: e.target.value });
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
    // Load Pending Services
    // ========================================
    function loadPendingServices(filters = {}) {
        const pendingList = document.getElementById('pending-list');
        pendingList.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

        const params = new URLSearchParams({
            filter: 'pending',
            ...filters
        });

        fetch(`controllers/get_pending_services.php?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    pendingServices = data.services;
                    renderPendingServices(data.services);
                    updatePendingCount(data.total);
                    populateSellerFilter(data.services);
                } else {
                    pendingList.innerHTML = `<div class="error-message">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error loading pending services:', error);
                pendingList.innerHTML = '<div class="error-message">Error loading services</div>';
            });
    }

    function renderPendingServices(services) {
        const pendingList = document.getElementById('pending-list');
        const template = document.getElementById('service-card-template');

        if (services.length === 0) {
            pendingList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-check-double"></i>
                    <p>No pending services</p>
                </div>
            `;
            return;
        }

        pendingList.innerHTML = '';

        services.forEach(service => {
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

            // Event: View details
            cardEl.addEventListener('click', function(e) {
                if (!e.target.closest('.btn-action')) {
                    selectService(service.id);
                }
            });

            // Event: Mark as completed
            card.querySelector('.btn-complete').addEventListener('click', function(e) {
                e.stopPropagation();
                updateServiceStatus(service.id, 'completed');
            });

            // Event: Mark as not completed
            card.querySelector('.btn-not-complete').addEventListener('click', function(e) {
                e.stopPropagation();
                updateServiceStatus(service.id, 'not_completed');
            });

            pendingList.appendChild(card);
        });
    }

    function updatePendingCount(count) {
        document.querySelector('.pending-count').textContent = count;
    }

    function populateSellerFilter(services) {
        const select = document.getElementById('filter-seller');
        const sellers = [...new Set(services.map(s => s.Seller).filter(Boolean))];

        // Keep first option
        select.innerHTML = '<option value="">All Sellers</option>';
        sellers.forEach(seller => {
            const option = document.createElement('option');
            option.value = seller;
            option.textContent = seller;
            select.appendChild(option);
        });
    }

    // ========================================
    // Load History
    // ========================================
    function loadHistory(filters = {}) {
        const historyList = document.getElementById('history-list');
        historyList.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Loading history...</div>';

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

            // Status icon and badge
            const status = record.service_status || 'pending';
            const statusIcon = item.querySelector('.status-icon');
            const statusBadge = item.querySelector('.status-badge');

            if (status === 'completed') {
                statusIcon.className = 'status-icon fas fa-check-circle text-success';
                statusBadge.textContent = 'Completed';
                statusBadge.className = 'status-badge badge-success';
            } else if (status === 'not_completed') {
                statusIcon.className = 'status-icon fas fa-times-circle text-danger';
                statusBadge.textContent = 'Not Done';
                statusBadge.className = 'status-badge badge-danger';
            } else {
                statusIcon.className = 'status-icon fas fa-clock text-warning';
                statusBadge.textContent = 'Pending';
                statusBadge.className = 'status-badge badge-warning';
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
        document.getElementById('stat-not-completed').textContent = stats.not_completed || 0;
        document.getElementById('stat-pending').textContent = stats.pending || 0;
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

        // Status
        const status = request.service_status || 'pending';
        const statusEl = detail.querySelector('.current-status');
        statusEl.textContent = status === 'completed' ? 'COMPLETED' :
                               status === 'not_completed' ? 'NOT COMPLETED' : 'PENDING';
        statusEl.className = `current-status status-${status}`;

        detail.querySelector('.nomenclature-badge').textContent = request.Order_Nomenclature || `#${request.id}`;

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
        detail.querySelector('.address').textContent = request.Company_Address || '-';

        // Dates
        detail.querySelector('.document-date').textContent = formatDate(request.Document_Date) || '-';
        detail.querySelector('.work-date').textContent = formatDate(request.Work_Date) || '-';
        detail.querySelector('.created-at').textContent = formatDateTime(request.created_at) || '-';
        detail.querySelector('.completed-at').textContent = formatDateTime(request.service_completed_at) || '-';

        // Observations
        const observations = [];
        if (request.Site_Observation) observations.push(request.Site_Observation);
        if (request.Additional_Comments) observations.push(request.Additional_Comments);
        detail.querySelector('.observations-content').textContent = observations.join('\n\n') || 'No observations';

        // Action buttons
        const markCompleteBtn = detail.querySelector('.btn-mark-complete');
        const markNotCompleteBtn = detail.querySelector('.btn-mark-not-complete');
        const resetPendingBtn = detail.querySelector('.btn-reset-pending');

        markCompleteBtn.dataset.id = request.id;
        markNotCompleteBtn.dataset.id = request.id;
        resetPendingBtn.dataset.id = request.id;

        markCompleteBtn.addEventListener('click', () => updateServiceStatus(request.id, 'completed'));
        markNotCompleteBtn.addEventListener('click', () => updateServiceStatus(request.id, 'not_completed'));
        resetPendingBtn.addEventListener('click', () => updateServiceStatus(request.id, 'pending'));

        // Show/hide buttons based on current status
        if (status === 'completed') {
            markCompleteBtn.style.display = 'none';
        } else if (status === 'not_completed') {
            markNotCompleteBtn.style.display = 'none';
        } else {
            resetPendingBtn.style.display = 'none';
        }

        detailContent.innerHTML = '';
        detailContent.appendChild(detail);
    }

    // ========================================
    // Update Service Status
    // ========================================
    function updateServiceStatus(serviceId, newStatus) {
        const statusLabels = {
            'completed': 'COMPLETED',
            'not_completed': 'NOT COMPLETED',
            'pending': 'PENDING'
        };

        if (!confirm(`Are you sure you want to mark this service as ${statusLabels[newStatus]}?`)) {
            return;
        }

        // Show loading
        const detailContent = document.getElementById('detail-content');
        const originalContent = detailContent.innerHTML;
        detailContent.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Updating...</div>';

        fetch('controllers/update_service_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                request_id: serviceId,
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');

                // Reload data
                loadPendingServices();
                loadHistory();

                // Reload details if still viewing same service
                if (selectedServiceId === serviceId) {
                    loadServiceDetails(serviceId);
                }
            } else {
                showNotification(data.message, 'error');
                detailContent.innerHTML = originalContent;
            }
        })
        .catch(error => {
            console.error('Error updating status:', error);
            showNotification('Error updating status', 'error');
            detailContent.innerHTML = originalContent;
        });
    }

    // ========================================
    // Utility Functions
    // ========================================
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
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => notification.classList.add('show'), 10);

        // Remove after delay
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }
});
