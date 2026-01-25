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

        // Fetch both tasks and requests in parallel
        Promise.all([
            fetch('controllers/get_pending_tasks.php').then(r => r.json()),
            fetch('controllers/get_pending_requests.php').then(r => r.json())
        ])
        .then(([tasksData, requestsData]) => {
            let allItems = [];

            // Add tasks if successful
            if (tasksData.success && tasksData.data) {
                tasksData.data.forEach(task => {
                    allItems.push({
                        ...task,
                        type: 'task',
                        item_id: task.task_id,
                        sort_date: task.due_date || task.created_at
                    });
                });
            }

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

        // Show loading
        completedList.innerHTML = `
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading generated contracts...</p>
            </div>
        `;

        fetch('controllers/get_completed_requests.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    completedTasks = data.data || [];
                    renderCompletedTasks(completedTasks);
                    updateCompletedCount(completedTasks.length);
                } else {
                    showError('completed-list', 'Error loading contracts: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error loading completed requests:', error);
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
        div.dataset.id = task.task_id;
        div.dataset.type = task.type || 'task';
        div.dataset.eventId = task.event_id || '';

        // Classes for badges
        const priorityClass = (task.priority || task.Priority || 'normal').toLowerCase();
        const categoryClass = (task.category_name || 'default').toLowerCase();

        // Mark as active if selected
        if (selectedTaskId == task.task_id) {
            div.classList.add('active');
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
            // For tasks from calendar
            const categoryIcon = task.category_icon || '📋';
            categoryBadge = `<span class="task-category-badge ${categoryClass}" style="background-color: ${task.category_color || '#999'}">
                ${categoryIcon} ${task.category_name}
            </span>`;
        }

        // Get date formatted
        const dateFormatted = task.due_date_formatted || task.created_at_formatted || 'No date';

        div.innerHTML = `
            <div class="task-header">
                <span class="task-priority-badge ${priorityClass}">${formatPriority(task.priority || task.Priority)}</span>
                ${categoryBadge}
            </div>
            <div class="task-body">
                <h3 class="task-title">${escapeHtml(task.title)}</h3>
                <p class="task-description">${escapeHtml(task.description)}</p>
                ${task.event_title ? `<p class="event-ref"><i class="fas fa-link"></i> ${escapeHtml(task.event_title)}</p>` : ''}
                ${(task.client || task.client_name) ? `<p class="client-name"><i class="fas fa-user"></i> ${escapeHtml(task.client || task.client_name)}</p>` : ''}
                <div class="task-meta">
                    <span class="due-date">
                        <i class="far fa-calendar"></i>
                        <span class="date-text">${dateFormatted}</span>
                    </span>
                </div>
            </div>
        `;

        // Event listener to select and open form
        div.addEventListener('click', function() {
            selectTask(task);
        });

        return div;
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

        // For calendar tasks, try to find existing request linked to this task/event
        if (task.event_id) {
            // Check if a request exists for this event
            fetch(`controllers/find_request_by_event.php?event_id=${task.event_id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.request) {
                        // Load existing request
                        loadExistingRequest(data.request.id);
                    } else {
                        // Create new request from task
                        createNewRequestFromTask(task);
                    }
                })
                .catch(error => {
                    console.error('Error checking for existing request:', error);
                    // Fallback to creating new
                    createNewRequestFromTask(task);
                });
        } else {
            // No event, create new request
            createNewRequestFromTask(task);
        }
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
