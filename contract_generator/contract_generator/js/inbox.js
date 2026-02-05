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

    // ========================================
    // INITIALIZATION
    // ========================================

    document.addEventListener('DOMContentLoaded', function() {
        console.log('📥 Inbox module loaded - Pending Tasks & Generated Contracts Mode');

        // Load both pending tasks and completed requests
        loadPendingTasks();
        loadCompletedRequests();
    });

    // ========================================
    // LOAD PENDING TASKS
    // ========================================

    function loadPendingTasks() {
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

        // Fetch pending requests
        safeFetch('controllers/get_pending_requests.php')
        .then(requestsData => {
            let allItems = [];

            // Add requests if successful
            if (requestsData.success && requestsData.data) {
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

            // Sort by date (newest first)
            allItems.sort((a, b) => {
                const dateA = new Date(a.sort_date || 0);
                const dateB = new Date(b.sort_date || 0);
                return dateB - dateA;
            });

            currentTasks = allItems;
            renderPendingTasks(allItems);
            updatePendingCount(allItems.length);
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

        fetch('controllers/get_completed_requests.php')
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

        // Build completion progress bar for drafts
        let completionBar = '';
        if (task.is_draft && task.completion_info) {
            const percentage = task.completion_info.percentage || 0;
            const missingItems = task.completion_info.missing || [];
            const missingText = missingItems.length > 0 ? `Missing: ${missingItems.join(', ')}` : '';
            completionBar = `
                <div class="completion-info">
                    <div class="completion-bar">
                        <div class="completion-fill" style="width: ${percentage}%"></div>
                    </div>
                    <span class="completion-text">${percentage}% complete</span>
                    ${missingText ? `<span class="missing-items" title="${escapeHtml(missingText)}">${escapeHtml(missingText)}</span>` : ''}
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
                ${completionBar}
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
        `;

        // Event listener to select and open form
        div.addEventListener('click', function(e) {
            // Don't select if clicking on report buttons
            if (e.target.closest('.report-btn')) {
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
        `;

        // Event listener to select and view completed contract
        div.addEventListener('click', function() {
            selectCompletedTask(task);
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
    // REFRESH FUNCTION (called after mark ready)
    // ========================================

    function refreshAll() {
        console.log('🔄 Refreshing all inbox data...');
        loadPendingTasks();
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
