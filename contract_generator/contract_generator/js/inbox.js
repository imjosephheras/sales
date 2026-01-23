/**
 * INBOX.JS
 * Manages pending tasks inbox: loading, selection, and form opening
 */

(function() {
    'use strict';

    // ========================================
    // GLOBAL VARIABLES
    // ========================================

    let currentTasks = [];
    let selectedTaskId = null;

    // ========================================
    // INITIALIZATION
    // ========================================

    document.addEventListener('DOMContentLoaded', function() {
        console.log('📥 Inbox module loaded - Pending Tasks Mode');

        // Load pending tasks
        loadPendingTasks();
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
            renderTasks(allItems);
            updateTaskCount(allItems.length);
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Database error: ' + error.message + '<br><br>Please ensure MySQL is running and the database schema is imported.');
        });
    }

    // ========================================
    // RENDER TASKS
    // ========================================

    function renderTasks(tasks) {
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
    // CREATE TASK ITEM
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
    // UPDATE TASK COUNT
    // ========================================

    function updateTaskCount(count) {
        const countElement = document.querySelector('.task-count');
        if (countElement) {
            countElement.textContent = count === 0
                ? 'No pending tasks'
                : count === 1
                ? '1 pending task'
                : `${count} pending tasks`;
        }
    }

    // ========================================
    // UTILITY FUNCTIONS
    // ========================================

    function showError(message) {
        const inboxList = document.getElementById('inbox-list');
        inboxList.innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
                <p>${message}</p>
            </div>
        `;
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
    // EXPORT FUNCTIONS
    // ========================================

    window.InboxModule = {
        refresh: loadPendingTasks,
        getCurrentTaskId: () => selectedTaskId
    };

})();