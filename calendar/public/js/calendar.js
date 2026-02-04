/**
 * ============================================================
 * CALENDAR JAVASCRIPT - SIMPLIFIED SYSTEM
 * Work Date based, no time ranges
 * Data comes from Request Form automatically
 * ============================================================ */

let currentTheme = localStorage.getItem('theme') || 'light';

function toggleTheme() {
    const html = document.documentElement;
    const body = document.body;
    const current = body.getAttribute('data-theme') || 'light';
    const newTheme = current === 'light' ? 'dark' : 'light';

    html.setAttribute('data-theme', newTheme);
    body.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    currentTheme = newTheme;

    applyThemeColors(newTheme);
    console.log('Theme changed to:', newTheme);
}

function applyThemeColors(theme) {
    const isDark = (theme === 'dark');

    const bgColor = isDark ? '#0a0a0a' : '#fafaf9';
    const surfaceColor = isDark ? '#1a1a1a' : '#ffffff';
    const textColor = isDark ? '#fafaf9' : '#1c1917';
    const borderColor = isDark ? '#2a2a2a' : '#e7e5e4';
    const hoverColor = isDark ? '#252525' : '#f5f5f4';

    document.body.style.backgroundColor = bgColor;
    document.body.style.color = textColor;

    document.querySelectorAll('.main-header').forEach(el => {
        el.style.backgroundColor = surfaceColor;
        el.style.color = textColor;
        el.style.borderBottomColor = borderColor;
    });

    document.querySelectorAll('.calendar-main').forEach(el => {
        el.style.backgroundColor = surfaceColor;
        el.style.color = textColor;
        el.style.borderColor = borderColor;
    });

    document.querySelectorAll('.calendar-day').forEach(el => {
        if (!el.classList.contains('today')) {
            el.style.backgroundColor = surfaceColor;
            el.style.color = textColor;
            el.style.borderColor = borderColor;
        }
    });

    document.querySelectorAll('.day-header').forEach(el => {
        el.style.backgroundColor = hoverColor;
        el.style.color = textColor;
    });

    document.querySelectorAll('.calendar-grid').forEach(el => {
        el.style.backgroundColor = borderColor;
    });

    document.querySelectorAll('.sidebar-right, .sidebar-left').forEach(el => {
        el.style.backgroundColor = surfaceColor;
        el.style.color = textColor;
        el.style.borderColor = borderColor;
    });

    document.querySelectorAll('.work-section, .today-section').forEach(el => {
        el.style.backgroundColor = surfaceColor;
        el.style.color = textColor;
        el.style.borderColor = borderColor;
    });

    document.querySelectorAll('.work-item, .task-item, .today-event').forEach(el => {
        el.style.backgroundColor = hoverColor;
        el.style.color = textColor;
    });

    document.querySelectorAll('input, select, textarea').forEach(el => {
        el.style.backgroundColor = surfaceColor;
        el.style.color = textColor;
        el.style.borderColor = borderColor;
    });

    document.querySelectorAll('.modal-content').forEach(el => {
        el.style.backgroundColor = surfaceColor;
        el.style.color = textColor;
    });
}

function loadSavedTheme() {
    currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);
    document.body.setAttribute('data-theme', currentTheme);

    setTimeout(() => {
        applyThemeColors(currentTheme);
    }, 50);
}

loadSavedTheme();

document.addEventListener('DOMContentLoaded', () => {
    loadSavedTheme();

    if (typeof initializeDragAndDrop === 'function') {
        initializeDragAndDrop();
    }

    console.log('Calendar System ready! Current theme:', currentTheme);
});

// ============================================================
// TASK STATUS SWITCHES
// ============================================================

function toggleTaskStatus(taskId, switchElement) {
    const isCompleting = !switchElement.classList.contains('on');
    const taskItem = switchElement.closest('.task-item');

    if (isCompleting) {
        switchElement.classList.add('on');
        taskItem.style.opacity = '0.6';
    } else {
        switchElement.classList.remove('on');
        taskItem.style.opacity = '1';
    }

    fetch('actions/toggle_task.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            task_id: taskId,
            is_completed: isCompleting
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isCompleting) {
                setTimeout(() => {
                    taskItem.style.transition = 'all 0.5s ease';
                    taskItem.style.transform = 'translateX(20px)';
                    taskItem.style.opacity = '0';

                    setTimeout(() => {
                        taskItem.remove();
                        const tasksList = document.querySelector('.tasks-list');
                        if (tasksList && tasksList.children.length === 0) {
                            tasksList.innerHTML = '<div class="empty-state-small">No pending tasks</div>';
                        }
                    }, 500);
                }, 800);
            }
            showNotification('Task updated successfully', 'success');
        } else {
            if (isCompleting) {
                switchElement.classList.remove('on');
            } else {
                switchElement.classList.add('on');
            }
            taskItem.style.opacity = '1';
            showNotification('Error updating task', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (isCompleting) {
            switchElement.classList.remove('on');
        } else {
            switchElement.classList.add('on');
        }
        taskItem.style.opacity = '1';
        showNotification('Error updating task', 'error');
    });
}

// ============================================================
// WORK STATUS SWITCHES
// ============================================================

function toggleWorkStatus(eventId, switchElement) {
    const isCompleting = !switchElement.classList.contains('on');
    const workItem = switchElement.closest('.work-item');

    if (isCompleting) {
        switchElement.classList.add('on');
        if (workItem) workItem.style.opacity = '0.7';
    } else {
        switchElement.classList.remove('on');
        if (workItem) workItem.style.opacity = '1';
    }

    const newStatus = isCompleting ? 'completed' : 'pending';

    fetch('actions/toggle_work_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            event_id: eventId,
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Work item ${isCompleting ? 'completed' : 'reopened'}`, 'success');
            if (isCompleting && workItem) {
                setTimeout(() => {
                    workItem.style.transition = 'all 0.5s ease';
                    workItem.style.transform = 'translateX(20px)';
                    workItem.style.opacity = '0';
                }, 1000);
            }
        } else {
            if (isCompleting) {
                switchElement.classList.remove('on');
            } else {
                switchElement.classList.add('on');
            }
            if (workItem) workItem.style.opacity = '1';
            showNotification('Error updating status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (isCompleting) {
            switchElement.classList.remove('on');
        } else {
            switchElement.classList.add('on');
        }
        if (workItem) workItem.style.opacity = '1';
        showNotification('Error updating status', 'error');
    });
}

// ============================================================
// MODAL MANAGEMENT
// ============================================================

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        closeModal(e.target.id);
    }
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const activeModal = document.querySelector('.modal.active');
        if (activeModal) {
            closeModal(activeModal.id);
        }
    }
});

// ============================================================
// EVENT MODAL - SIMPLIFIED (Work Date based)
// ============================================================

function openEventModal(eventId = null) {
    const modal = document.getElementById('eventModal');
    const form = document.getElementById('eventForm');
    const title = document.getElementById('modalTitle');

    form.reset();

    if (eventId) {
        title.textContent = 'Edit Event';
        document.getElementById('eventId').value = eventId;
        loadEventData(eventId);
    } else {
        title.textContent = 'New Event';
        document.getElementById('eventId').value = '';

        // Set default Work Date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('workDate').value = today;
    }

    openModal('eventModal');
}

function openWorkModal() {
    openEventModal();
}

function loadEventData(eventId) {
    console.log('Loading event:', eventId);

    fetch(`api/events.php?id=${eventId}`)
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned non-JSON response');
            }
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Event data loaded:', data);
            if (data.error) {
                throw new Error(data.error);
            }
            populateEventForm(data);
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading event: ' + error.message, 'error');
            closeModal('eventModal');
        });
}

function populateEventForm(data) {
    // Simplified field mapping - using Work Date instead of Start/End
    const fields = {
        'eventTitle': data.title,
        'eventCategory': data.category_id,
        'eventCompany': data.company || data.client,
        'eventClient': data.client,
        'eventLocation': data.location,
        'eventDescription': data.description,
        'workDate': data.start_date || data.execution_date || data.work_date,
        'documentDate': data.document_date,
        'eventStatus': data.status,
        'eventPriority': data.priority,
        'frequencyMonths': data.frequency_months,
        'frequencyYears': data.frequency_years,
        'requestId': data.form_id || data.request_id,
        'requestNumber': data.request_number
    };

    for (const [fieldId, value] of Object.entries(fields)) {
        const element = document.getElementById(fieldId);
        if (!element) continue;

        if (element.type === 'checkbox') {
            element.checked = !!value;
        } else if (value !== null && value !== undefined) {
            element.value = value;
        }
    }
}

function openEventDetail(eventId) {
    openEventModal(eventId);
}

// ============================================================
// NOTIFICATIONS
// ============================================================

function showNotification(message, type = 'info') {
    let notification = document.getElementById('flashMessage');

    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'flashMessage';
        notification.className = 'flash-message';
        document.body.insertBefore(notification, document.body.firstChild);
    }

    notification.className = `flash-message flash-${type}`;
    notification.textContent = message;
    notification.style.display = 'block';

    setTimeout(() => {
        notification.style.animation = 'slideUp 0.3s ease-out';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 300);
    }, 3000);
}

// ============================================================
// DRAG & DROP FUNCTIONALITY
// ============================================================

var draggedEventId = null;
let draggedElement = null;
let dropIndicator = null;
let monthChangeTimeout = null;

function initializeDragAndDrop() {
    createDropIndicator();
    initializeDraggableEvents();
    initializeDropZones();
    addMonthNavigationOnDrag();
    console.log('Drag & Drop initialized');
}

function createDropIndicator() {
    dropIndicator = document.createElement('div');
    dropIndicator.className = 'drop-indicator';
    dropIndicator.style.cssText = `
        position: absolute;
        background: rgba(37, 99, 235, 0.1);
        border: 2px dashed #2563eb;
        border-radius: 8px;
        pointer-events: none;
        display: none;
        z-index: 1000;
    `;
    document.body.appendChild(dropIndicator);
}

function showDropIndicator(target) {
    if (!target) return;
    const rect = target.getBoundingClientRect();
    dropIndicator.style.display = 'block';
    dropIndicator.style.left = rect.left + 'px';
    dropIndicator.style.top = rect.top + 'px';
    dropIndicator.style.width = rect.width + 'px';
    dropIndicator.style.height = rect.height + 'px';
}

function hideDropIndicator() {
    dropIndicator.style.display = 'none';
}

function initializeDraggableEvents() {
    const events = document.querySelectorAll('.event-dot');
    events.forEach(event => {
        event.setAttribute('draggable', 'true');
        event.addEventListener('dragstart', handleDragStart);
        event.addEventListener('dragend', handleDragEnd);
        event.style.cursor = 'grab';
    });
    console.log(`${events.length} events made draggable`);
}

function handleDragStart(e) {
    draggedEventId = this.getAttribute('data-event-id');
    draggedElement = this;
    this.style.opacity = '0.5';
    this.style.cursor = 'grabbing';
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
    console.log('Dragging event:', draggedEventId);
}

function handleDragEnd(e) {
    this.style.opacity = '1';
    this.style.cursor = 'grab';
    hideDropIndicator();
    if (monthChangeTimeout) {
        clearTimeout(monthChangeTimeout);
        monthChangeTimeout = null;
    }
}

function initializeDropZones() {
    const calendarDays = document.querySelectorAll('.calendar-day');
    calendarDays.forEach(day => {
        day.addEventListener('dragover', handleDragOver);
        day.addEventListener('dragenter', handleDragEnter);
        day.addEventListener('dragleave', handleDragLeave);
        day.addEventListener('drop', handleDrop);
    });
    console.log(`${calendarDays.length} drop zones initialized`);
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    showDropIndicator(this);
    return false;
}

function handleDragEnter(e) {
    e.preventDefault();
    this.classList.add('drag-over');
}

function handleDragLeave(e) {
    this.classList.remove('drag-over');
}

function handleDrop(e) {
    e.stopPropagation();
    e.preventDefault();
    this.classList.remove('drag-over');
    hideDropIndicator();

    if (!draggedEventId) {
        console.error('No event ID found');
        return false;
    }

    const newDate = this.getAttribute('data-date');
    if (!newDate) {
        console.error('No date found on drop target');
        showNotification('Error: Invalid drop target', 'error');
        return false;
    }

    console.log('Dropped on date:', newDate);

    if (confirm(`Reschedule event to ${formatDateForDisplay(newDate)}?`)) {
        rescheduleEvent(draggedEventId, newDate);
    }

    return false;
}

function rescheduleEvent(eventId, newDate) {
    console.log('Rescheduling event', eventId, 'to', newDate);

    fetch('actions/reschedule_event.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            event_id: eventId,
            new_date: newDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Event rescheduled successfully');
            showNotification('Event rescheduled successfully!', 'success');
            setTimeout(() => window.location.reload(), 500);
        } else {
            console.error('Reschedule failed:', data.error);
            showNotification('Error: ' + (data.error || 'Failed to reschedule'), 'error');
        }
    })
    .catch(error => {
        console.error('Network error:', error);
        showNotification('Error: Could not connect to server', 'error');
    });
}

function addMonthNavigationOnDrag() {
    document.addEventListener('dragover', function(e) {
        if (!draggedEventId) return;

        const x = e.clientX;
        const windowWidth = window.innerWidth;

        if (x < 100) {
            if (!monthChangeTimeout) {
                monthChangeTimeout = setTimeout(() => {
                    navigateToPreviousMonth();
                }, 800);
            }
        } else if (x > windowWidth - 100) {
            if (!monthChangeTimeout) {
                monthChangeTimeout = setTimeout(() => {
                    navigateToNextMonth();
                }, 800);
            }
        } else {
            if (monthChangeTimeout) {
                clearTimeout(monthChangeTimeout);
                monthChangeTimeout = null;
            }
        }
    });
}

function navigateToPreviousMonth() {
    const urlParams = new URLSearchParams(window.location.search);
    let month = parseInt(urlParams.get('month')) || new Date().getMonth() + 1;
    let year = parseInt(urlParams.get('year')) || new Date().getFullYear();
    month--;
    if (month < 1) { month = 12; year--; }
    window.location.href = `index.php?month=${month}&year=${year}`;
}

function navigateToNextMonth() {
    const urlParams = new URLSearchParams(window.location.search);
    let month = parseInt(urlParams.get('month')) || new Date().getMonth() + 1;
    let year = parseInt(urlParams.get('year')) || new Date().getFullYear();
    month++;
    if (month > 12) { month = 1; year++; }
    window.location.href = `index.php?month=${month}&year=${year}`;
}

function formatDateForDisplay(dateString) {
    const date = new Date(dateString);
    const options = { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// ============================================================
// FORM VALIDATION - SIMPLIFIED (only Work Date required)
// ============================================================

document.getElementById('eventForm')?.addEventListener('submit', function(e) {
    const workDate = document.getElementById('workDate').value;

    if (!workDate) {
        e.preventDefault();
        showNotification('Work Date is required', 'error');
        return false;
    }
});

// ============================================================
// AUTO-HIDE FLASH MESSAGES
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    const flashMessage = document.getElementById('flashMessage');

    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.animation = 'slideUp 0.3s ease-out';
            setTimeout(() => {
                flashMessage.style.display = 'none';
            }, 300);
        }, 3000);
    }
});

// ============================================================
// KEYBOARD SHORTCUTS
// ============================================================

document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + N: New event
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        openEventModal();
    }

    // Ctrl/Cmd + D: Toggle theme
    if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
        e.preventDefault();
        toggleTheme();
    }
});

// ============================================================
// UTILITY FUNCTIONS
// ============================================================

function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// ============================================================
// CONSOLE INFO
// ============================================================

console.log('%c Calendar System v3.0 (Simplified)', 'color: #dc2626; font-size: 16px; font-weight: bold;');
console.log('%cKeyboard Shortcuts:', 'color: #78716c; font-weight: bold;');
console.log('Ctrl/Cmd + N: New Event');
console.log('Ctrl/Cmd + D: Toggle Dark/Light Mode');
console.log('Escape: Close Modal');
console.log('Drag events to reschedule (Work Date based)');
console.log('Data comes from Request Form automatically');
