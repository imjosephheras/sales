/**
 * INBOX.JS
 * Manages pending tasks inbox and generated contracts: loading, selection, and form opening
 */

(function() {
    'use strict';

    // ========================================
    // GLOBAL VARIABLES
    // ========================================

    let currentTasks = [];
    let completedTasks = [];
    let selectedTaskId = null;

    // Pagination state
    let currentPage = 1;
    const itemsPerPage = 20;
    let totalItems = 0;
    let totalPages = 1;

    // Search state
    let currentSearchQuery = '';
    let searchDebounceTimer = null;

    // ========================================
    // INITIALIZATION
    // ========================================

    document.addEventListener('DOMContentLoaded', function() {
        console.log('📥 Inbox module loaded - Pending Tasks & Generated Contracts Mode');

        // Initialize search
        initSearch();

        // Load both pending tasks and completed requests
        loadPendingTasks();
        loadCompletedRequests();
    });

    // ========================================
    // SEARCH FUNCTIONALITY
    // ========================================

    function initSearch() {
        const searchInput = document.getElementById('client-search-input');
        const clearBtn = document.getElementById('search-clear-btn');

        if (!searchInput) return;

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();

            // Show/hide clear button
            if (clearBtn) {
                clearBtn.style.display = query.length > 0 ? 'flex' : 'none';
            }

            // Debounce search
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(function() {
                currentSearchQuery = query;
                currentPage = 1;
                loadPendingTasks();
                loadCompletedRequests();
            }, 300);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                currentSearchQuery = '';
                clearBtn.style.display = 'none';
                currentPage = 1;
                loadPendingTasks();
                loadCompletedRequests();
                searchInput.focus();
            });
        }
    }

    // ========================================
    // LOAD PENDING TASKS
    // ========================================

    function loadPendingTasks(page) {
        if (page !== undefined) {
            currentPage = page;
        }

        const inboxList = document.getElementById('inbox-list');

        // Show loading
        inboxList.innerHTML = `
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading pending items...</p>
            </div>
        `;

        // Helper function to safely fetch and parse JSON
        const safeFetch = (url) => {
            return fetch(url)
                .then(r => r.json())
                .catch(err => {
                    console.warn(`Warning: ${url} failed:`, err);
                    return { success: false, data: [], error: err.message };
                });
        };

        // Fetch pending requests with pagination and search
        const searchParam = currentSearchQuery ? `&search=${encodeURIComponent(currentSearchQuery)}` : '';
        safeFetch(`controllers/get_pending_requests.php?page=${currentPage}&limit=${itemsPerPage}${searchParam}`)
        .then(requestsData => {
            let allItems = [];

            // Add requests if successful
            if (requestsData.success && requestsData.data) {
                // Update pagination state from server response
                if (requestsData.pagination) {
                    totalItems = requestsData.pagination.total_count;
                    totalPages = requestsData.pagination.total_pages;
                    currentPage = requestsData.pagination.page;
                }

                requestsData.data.forEach(request => {
                    allItems.push({
                        ...request,
                        type: 'request',
                        item_id: request.id,
                        task_id: 'req_' + request.id, // Unique identifier
                        sort_date: request.created_at
                    });
                });
            }

            currentTasks = allItems;
            renderPendingTasks(allItems);
            updatePendingCount(totalItems);
            renderPagination();
        })
        .catch(error => {
            console.error('Error:', error);
            showError('inbox-list', 'Database error: ' + error.message + '<br><br>Please ensure MySQL is running and the database schema is imported.');
        });
    }

    // ========================================
    // LOAD COMPLETED REQUESTS
    // ========================================

    function loadCompletedRequests() {
        const completedList = document.getElementById('completed-list');
        console.log('📋 Loading completed requests...');

        // Show loading
        completedList.innerHTML = `
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading generated contracts...</p>
            </div>
        `;

        const completedSearchParam = currentSearchQuery ? `?search=${encodeURIComponent(currentSearchQuery)}` : '';
        fetch('controllers/get_completed_requests.php' + completedSearchParam)
            .then(response => {
                console.log('📥 Completed requests response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('📥 Completed requests data:', data);
                if (data.success) {
                    completedTasks = data.data || [];
                    console.log('✅ Found', completedTasks.length, 'completed contracts');
                    renderCompletedTasks(completedTasks);
                    updateCompletedCount(completedTasks.length);
                } else {
                    console.error('❌ Error in completed requests:', data.error);
                    showError('completed-list', 'Error loading contracts: ' + data.error);
                }
            })
            .catch(error => {
                console.error('❌ Error loading completed requests:', error);
                showError('completed-list', 'Connection error. Please try again.');
            });
    }

    // ========================================
    // RENDER PENDING TASKS
    // ========================================

    function renderPendingTasks(tasks) {
        const inboxList = document.getElementById('inbox-list');

        if (tasks.length === 0) {
            inboxList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>No pending tasks</p>
                    <small>All tasks are completed!</small>
                </div>
            `;
            return;
        }

        inboxList.innerHTML = '';

        tasks.forEach(task => {
            const item = createTaskItem(task);
            inboxList.appendChild(item);
        });
    }

    // ========================================
    // RENDER COMPLETED TASKS
    // ========================================

    function renderCompletedTasks(tasks) {
        const completedList = document.getElementById('completed-list');

        if (tasks.length === 0) {
            completedList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>No contracts generated yet</p>
                    <small>Complete pending tasks to see them here</small>
                </div>
            `;
            return;
        }

        completedList.innerHTML = '';

        tasks.forEach(task => {
            const item = createCompletedItem(task);
            completedList.appendChild(item);
        });
    }

    // ========================================
    // CREATE TASK ITEM (PENDING)
    // ========================================

    function createTaskItem(task) {
        const div = document.createElement('div');
        div.className = 'task-item';
        if (task.is_draft) {
            div.classList.add('draft-item');
        }
        div.dataset.id = task.task_id;
        div.dataset.type = task.type || 'task';
        div.dataset.eventId = task.event_id || '';
        div.dataset.formId = task.form_id || '';

        // Classes for badges
        const priorityClass = (task.priority || task.Priority || 'normal').toLowerCase();
        const categoryClass = (task.category_name || 'default').toLowerCase();

        // Mark as active if selected
        if (selectedTaskId == task.task_id) {
            div.classList.add('active');
        }

        // Build status badge for drafts
        let statusBadge = '';
        if (task.type === 'request' && task.status_label) {
            statusBadge = `<span class="task-status-badge" style="background-color: ${task.status_color || '#ffc107'}">
                ${task.status_icon || '📝'} ${task.status_label}
            </span>`;
        }

        // Build category badge
        let categoryBadge = '';
        if (task.type === 'request') {
            // For requests from form_contract
            const categoryIcon = task.category_icon || '📋';
            categoryBadge = `<span class="task-category-badge" style="background-color: ${task.category_color || '#999'}">
                ${categoryIcon} ${task.Service_Type || 'Request'}
            </span>`;
        } else if (task.category_name) {
            const categoryIcon = task.category_icon || '📋';
            categoryBadge = `<span class="task-category-badge ${categoryClass}" style="background-color: ${task.category_color || '#999'}">
                ${categoryIcon} ${task.category_name}
            </span>`;
        }

        // Get date formatted - prefer work date for requests
        let dateFormatted = task.due_date_formatted || task.created_at_formatted || 'No date';
        let dateIcon = 'far fa-calendar';
        let dateLabel = '';
        if (task.type === 'request') {
            if (task.work_date_formatted) {
                dateFormatted = task.work_date_formatted;
                dateIcon = 'fas fa-calendar-check';
                dateLabel = 'Work: ';
            } else if (task.start_date_formatted) {
                dateFormatted = task.start_date_formatted;
                dateIcon = 'fas fa-calendar-alt';
                dateLabel = 'Start: ';
            }
        }

        // Build service status chip
        let serviceStatusChip = '';
        if (task.service_status_label) {
            serviceStatusChip = `
                <div class="service-status-chip" style="background-color: ${task.service_status_color || '#d97706'}">
                    <i class="${task.service_status_icon || 'fas fa-clock'}"></i>
                    <span>${escapeHtml(task.service_status_label)}</span>
                </div>
            `;
        }

        // Build order nomenclature display
        let orderInfo = '';
        if (task.Order_Nomenclature) {
            orderInfo = `<p class="order-nomenclature"><i class="fas fa-hashtag"></i> ${escapeHtml(task.Order_Nomenclature)}</p>`;
        }

        // Build report buttons
        let reportButtons = '';
        if (task.available_reports && task.available_reports.length > 0) {
            const buttons = task.available_reports.map(report => {
                let icon = '📄';
                let label = report;
                if (report === 'hood_vent') { icon = '🔥'; label = 'Hood Vent'; }
                else if (report === 'kitchen') { icon = '🍳'; label = 'Kitchen'; }
                else if (report === 'janitorial') { icon = '🧹'; label = 'Janitorial'; }
                else if (report === 'staff') { icon = '👥'; label = 'Staff'; }
                return `<button class="report-btn" data-report="${report}" data-request-id="${task.id}" title="Print ${label} Report">
                    ${icon}
                </button>`;
            }).join('');
            reportButtons = `<div class="report-buttons">${buttons}</div>`;
        }

        // Build pricing info
        let pricingInfo = '';
        if (task.type === 'request') {
            const totalCost = task.total_cost || task.PriceInput || '';
            const grand18 = task.grand18 || '';
            const grand19 = task.grand19 || '';
            if (totalCost || grand18 || grand19) {
                let priceDisplay = totalCost ? `$${formatNumber(totalCost)}` : '';
                if (grand18) priceDisplay += (priceDisplay ? ' + ' : '') + `J:$${formatNumber(grand18)}`;
                if (grand19) priceDisplay += (priceDisplay ? ' + ' : '') + `K:$${formatNumber(grand19)}`;
                pricingInfo = `<span class="pricing-info"><i class="fas fa-dollar-sign"></i> ${priceDisplay}</span>`;
            }
        }

        div.innerHTML = `
            <div class="task-header">
                ${statusBadge}
                <span class="task-priority-badge ${priorityClass}">${formatPriority(task.priority || task.Priority)}</span>
                ${categoryBadge}
            </div>
            <div class="task-body">
                <h3 class="task-title">${escapeHtml(task.title)}</h3>
                ${orderInfo}
                <p class="task-description">${escapeHtml(task.description)}</p>
                ${serviceStatusChip}
                ${task.event_title ? `<p class="event-ref"><i class="fas fa-link"></i> ${escapeHtml(task.event_title)}</p>` : ''}
                ${(task.client || task.client_name) ? `<p class="client-name"><i class="fas fa-user"></i> ${escapeHtml(task.client || task.client_name)}</p>` : ''}
                ${task.Seller ? `<p class="seller-name"><i class="fas fa-user-tie"></i> ${escapeHtml(task.Seller)}</p>` : ''}
                <div class="task-meta">
                    <span class="due-date">
                        <i class="${dateIcon}"></i>
                        <span class="date-text">${dateLabel}${dateFormatted}</span>
                    </span>
                    ${pricingInfo}
                </div>
                ${reportButtons}
            </div>
            <button class="delete-btn" data-id="${task.id}" title="Delete this task">
                <i class="fas fa-trash-alt"></i>
            </button>
        `;

        // Event listener to select and open form
        div.addEventListener('click', function(e) {
            // Don't select if clicking on report buttons or delete button
            if (e.target.closest('.report-btn') || e.target.closest('.delete-btn')) {
                return;
            }
            selectTask(task);
        });

        // Add event listeners for report buttons
        div.querySelectorAll('.report-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const reportType = this.dataset.report;
                const requestId = this.dataset.requestId;
                generateReport(reportType, requestId);
            });
        });

        // Add event listener for delete button
        div.querySelector('.delete-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            const requestId = this.dataset.id;
            const taskTitle = task.title || task.Company_Name || 'this task';
            deleteRequest(requestId, taskTitle);
        });

        return div;
    }

    // ========================================
    // GENERATE REPORT
    // ========================================

    function generateReport(reportType, requestId) {
        console.log(`📄 Generating ${reportType} report for request #${requestId}`);

        // Open report in new window
        const reportUrl = `controllers/generate_report.php?type=${reportType}&id=${requestId}`;
        window.open(reportUrl, '_blank', 'width=800,height=600');
    }

    // ========================================
    // FORMAT NUMBER
    // ========================================

    function formatNumber(num) {
        if (!num) return '0';
        const n = parseFloat(num.toString().replace(/[^0-9.-]/g, ''));
        return n.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    // ========================================
    // CREATE COMPLETED ITEM
    // ========================================

    function createCompletedItem(task) {
        const div = document.createElement('div');
        div.className = 'task-item completed-item';
        div.dataset.id = task.id;
        div.dataset.type = 'completed';

        // Mark as active if selected
        if (selectedTaskId == 'completed_' + task.id) {
            div.classList.add('active');
        }

        // Build type badge
        const typeIcon = task.type_icon || '📋';
        const typeColor = task.type_color || '#28a745';
        const requestType = task.Request_Type || 'JWO';

        // Build category badge
        const categoryIcon = task.category_icon || '📋';
        const categoryColor = task.category_color || '#999';
        const serviceType = task.Service_Type || 'Service';

        // Get date formatted
        const dateFormatted = task.completed_at_formatted || task.created_at_formatted || 'No date';

        // Get company name
        const companyName = task.Company_Name || 'Unknown';

        div.innerHTML = `
            <div class="task-header">
                <span class="task-type-badge" style="background-color: ${typeColor}">
                    ${typeIcon} ${requestType}
                </span>
                <span class="task-category-badge" style="background-color: ${categoryColor}">
                    ${categoryIcon} ${serviceType}
                </span>
            </div>
            <div class="task-body">
                <h3 class="task-title">${escapeHtml(companyName)}</h3>
                ${task.docnum ? `<p class="task-docnum"><i class="fas fa-hashtag"></i> ${escapeHtml(task.docnum)}</p>` : ''}
                <p class="task-description">${escapeHtml(task.description || task.Requested_Service || '')}</p>
                <div class="task-meta">
                    <span class="completed-date">
                        <i class="fas fa-check-circle" style="color: #28a745;"></i>
                        <span class="date-text">${dateFormatted}</span>
                    </span>
                </div>
            </div>
            <button class="delete-btn" data-id="${task.id}" title="Delete this contract">
                <i class="fas fa-trash-alt"></i>
            </button>
        `;

        // Event listener to select and view completed contract
        div.addEventListener('click', function(e) {
            if (e.target.closest('.delete-btn')) {
                return;
            }
            selectCompletedTask(task);
        });

        // Add event listener for delete button
        div.querySelector('.delete-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            const requestId = this.dataset.id;
            const taskTitle = companyName || 'this contract';
            deleteRequest(requestId, taskTitle);
        });

        return div;
    }

    // ========================================
    // SELECT TASK AND OPEN FORM
    // ========================================

    function selectTask(task) {
        selectedTaskId = task.task_id;

        // Update inbox UI (mark active)
        document.querySelectorAll('.task-item').forEach(item => {
            item.classList.remove('active');
            if (item.dataset.id == task.task_id) {
                item.classList.add('active');
            }
        });

        // Open form and auto-generate contract
        openFormForTask(task);
    }

    // ========================================
    // SELECT COMPLETED TASK
    // ========================================

    function selectCompletedTask(task) {
        selectedTaskId = 'completed_' + task.id;

        // Update UI (mark active)
        document.querySelectorAll('.task-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelectorAll('.completed-item').forEach(item => {
            if (item.dataset.id == task.id) {
                item.classList.add('active');
            }
        });

        // Load the completed request in the editor (view mode)
        loadExistingRequest(task.id);
    }

    // ========================================
    // OPEN FORM FOR TASK
    // ========================================

    function openFormForTask(task) {
        // Dispatch custom event with task data
        const event = new CustomEvent('taskSelected', {
            detail: {
                taskId: task.task_id,
                eventId: task.event_id,
                task: task
            }
        });
        document.dispatchEvent(event);

        console.log('📋 Opening form for task:', task.title);

        // Load or create request form based on task
        loadOrCreateRequestForm(task);
    }

    // ========================================
    // LOAD OR CREATE REQUEST FORM
    // ========================================

    function loadOrCreateRequestForm(task) {
        // If this is a request from form_contract, load it directly
        if (task.type === 'request' && task.id) {
            loadExistingRequest(task.id);
            return;
        }

        // No matching request, create new
        createNewRequestFromTask(task);
    }

    // ========================================
    // LOAD EXISTING REQUEST
    // ========================================

    function loadExistingRequest(requestId) {
        const event = new CustomEvent('requestSelected', { detail: { id: requestId } });
        document.dispatchEvent(event);
    }

    // ========================================
    // CREATE NEW REQUEST FROM TASK
    // ========================================

    function createNewRequestFromTask(task) {
        // Dispatch event to create new form with task data
        const event = new CustomEvent('createNewRequest', {
            detail: {
                taskId: task.task_id,
                eventId: task.event_id,
                title: task.title,
                description: task.description,
                priority: task.priority,
                client: task.client,
                categoryName: task.category_name,
                eventTitle: task.event_title
            }
        });
        document.dispatchEvent(event);
    }

    // ========================================
    // UPDATE COUNTS
    // ========================================

    function updatePendingCount(count) {
        const countElement = document.getElementById('pending-count');
        if (countElement) {
            countElement.textContent = count === 0
                ? 'No pending tasks'
                : count === 1
                ? '1 pending task'
                : `${count} pending tasks`;
        }
    }

    function updateCompletedCount(count) {
        const countElement = document.getElementById('completed-count');
        if (countElement) {
            countElement.textContent = count === 0
                ? 'No contracts yet'
                : count === 1
                ? '1 contract'
                : `${count} contracts`;
        }
    }

    // ========================================
    // PAGINATION
    // ========================================

    function goToPage(page) {
        if (page < 1 || page > totalPages || page === currentPage) return;
        loadPendingTasks(page);

        // Scroll inbox list to top
        const inboxList = document.getElementById('inbox-list');
        if (inboxList) inboxList.scrollTop = 0;
    }

    function renderPagination() {
        const container = document.getElementById('pagination-container');
        if (!container) return;

        // Hide pagination if only one page
        if (totalPages <= 1) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'block';

        const startItem = ((currentPage - 1) * itemsPerPage) + 1;
        const endItem = Math.min(currentPage * itemsPerPage, totalItems);

        // Build page numbers with ellipsis
        let pages = [];
        const maxVisible = 5;

        if (totalPages <= maxVisible + 2) {
            // Show all pages
            for (let i = 1; i <= totalPages; i++) pages.push(i);
        } else {
            // Always show first page
            pages.push(1);

            let start = Math.max(2, currentPage - 1);
            let end = Math.min(totalPages - 1, currentPage + 1);

            // Adjust range to always show maxVisible middle pages when near edges
            if (currentPage <= 3) {
                start = 2;
                end = Math.min(maxVisible, totalPages - 1);
            } else if (currentPage >= totalPages - 2) {
                start = Math.max(2, totalPages - maxVisible + 1);
                end = totalPages - 1;
            }

            if (start > 2) pages.push('...');
            for (let i = start; i <= end; i++) pages.push(i);
            if (end < totalPages - 1) pages.push('...');

            // Always show last page
            pages.push(totalPages);
        }

        const pageButtons = pages.map(p => {
            if (p === '...') {
                return '<span class="pagination-ellipsis">...</span>';
            }
            const activeClass = p === currentPage ? ' active' : '';
            return `<button class="pagination-btn pagination-num${activeClass}" data-page="${p}">${p}</button>`;
        }).join('');

        container.innerHTML = `
            <div class="pagination-info">
                Showing ${startItem.toLocaleString()}-${endItem.toLocaleString()} of ${totalItems.toLocaleString()} orders
            </div>
            <div class="pagination-controls">
                <button class="pagination-btn pagination-arrow" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i> Prev
                </button>
                ${pageButtons}
                <button class="pagination-btn pagination-arrow" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}>
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        `;

        // Event delegation for pagination buttons
        container.querySelectorAll('.pagination-btn[data-page]').forEach(btn => {
            btn.addEventListener('click', function() {
                const page = parseInt(this.dataset.page);
                if (!isNaN(page)) goToPage(page);
            });
        });
    }

    // ========================================
    // UTILITY FUNCTIONS
    // ========================================

    function showError(containerId, message) {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
                    <p>${message}</p>
                </div>
            `;
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatPriority(priority) {
        const priorityMap = {
            'urgent': 'Urgent',
            'high': 'High',
            'normal': 'Normal',
            'low': 'Low'
        };
        return priorityMap[priority] || priority || 'Normal';
    }

    // ========================================
    // DELETE REQUEST
    // ========================================

    function deleteRequest(requestId, title) {
        if (!confirm(`Are you sure you want to delete "${title}"?\n\nThis action cannot be undone.`)) {
            return;
        }

        fetch('controllers/delete_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: parseInt(requestId) })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // If the deleted item was selected, clear the editor
                if (selectedTaskId == 'req_' + requestId || selectedTaskId == 'completed_' + requestId) {
                    selectedTaskId = null;
                    // Dispatch event to clear editor
                    document.dispatchEvent(new CustomEvent('requestDeleted', { detail: { id: requestId } }));
                }
                // Refresh both lists
                refreshAll();
            } else {
                alert('Error deleting: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error deleting request:', error);
            alert('Connection error. Please try again.');
        });
    }

    // ========================================
    // REFRESH FUNCTION (called after mark ready)
    // ========================================

    function refreshAll() {
        console.log('🔄 Refreshing all inbox data...');
        loadPendingTasks(currentPage);
        loadCompletedRequests();
    }

    // ========================================
    // EXPORT FUNCTIONS
    // ========================================

    window.InboxModule = {
        refresh: refreshAll,
        refreshPending: loadPendingTasks,
        refreshCompleted: loadCompletedRequests,
        getCurrentTaskId: () => selectedTaskId
    };

})();
