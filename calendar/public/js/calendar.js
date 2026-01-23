/**
 * ============================================================
 * CALENDAR JAVASCRIPT - PROFESSIONAL SYSTEM
 * Theme toggle, functional switches, improved UX
 * ============================================================ */

let currentTheme = localStorage.getItem('theme') || 'light';

function toggleTheme() {
    console.log('🔘 Button clicked! Current theme before toggle:', currentTheme);
    
    // Toggle theme
    if (currentTheme === 'light') {
        currentTheme = 'dark';
    } else {
        currentTheme = 'light';
    }
    
    console.log('🔄 New theme after toggle:', currentTheme);
    
    // Save to localStorage
    localStorage.setItem('theme', currentTheme);
    
    // Apply to HTML and Body
    document.documentElement.setAttribute('data-theme', currentTheme);
    document.body.setAttribute('data-theme', currentTheme);
    
    // Apply colors directly
    applyThemeColors(currentTheme);
    
    console.log('✅ Theme applied:', currentTheme);
}

function applyThemeColors(theme) {
    console.log('🎨 Applying theme:', theme);
    
    const isDark = (theme === 'dark');
    
    // Colors
    const bgColor = isDark ? '#0a0a0a' : '#fafaf9';
    const surfaceColor = isDark ? '#1a1a1a' : '#ffffff';
    const textColor = isDark ? '#fafaf9' : '#1c1917';
    const borderColor = isDark ? '#2a2a2a' : '#e7e5e4';
    const hoverColor = isDark ? '#252525' : '#f5f5f4';
    
    // Apply to body
    document.body.style.backgroundColor = bgColor;
    document.body.style.color = textColor;
    
    console.log('✅ Body background:', document.body.style.backgroundColor);
    
    // Apply to main header
    document.querySelectorAll('.main-header').forEach(el => {
        el.style.backgroundColor = surfaceColor;
        el.style.color = textColor;
        el.style.borderBottomColor = borderColor;
    });
    
    // Apply to calendar main
    document.querySelectorAll('.calendar-main').forEach(el => {
        el.style.backgroundColor = surfaceColor;
        el.style.color = textColor;
        el.style.borderColor = borderColor;
    });
    
    // Apply to calendar days
    document.querySelectorAll('.calendar-day').forEach(el => {
        if (!el.classList.contains('today')) {
            el.style.backgroundColor = surfaceColor;
            el.style.color = textColor;
            el.style.borderColor = borderColor;
        }
    });
    
    // Apply to day headers
    document.querySelectorAll('.day-header').forEach(el => {
        el.style.backgroundColor = hoverColor;
        el.style.color = textColor;
    });
    
    // Apply to calendar grid
    document.querySelectorAll('.calendar-grid').forEach(el => {
        el.style.backgroundColor = borderColor;
    });
    
    // Apply to sidebars
    document.querySelectorAll('.sidebar-right, .sidebar-left').forEach(el => {
        el.style.backgroundColor = surfaceColor;
        el.style.color = textColor;
        el.style.borderColor = borderColor;
    });
    
    // Apply to sections
    document.querySelectorAll('.work-section, .today-section').forEach(el => {
        el.style.backgroundColor = surfaceColor;
        el.style.color = textColor;
        el.style.borderColor = borderColor;
    });
    
    // Apply to work items and task items
    document.querySelectorAll('.work-item, .task-item, .today-event').forEach(el => {
        el.style.backgroundColor = hoverColor;
        el.style.color = textColor;
    });
    
    // Apply to inputs
    document.querySelectorAll('input, select, textarea').forEach(el => {
        el.style.backgroundColor = surfaceColor;
        el.style.color = textColor;
        el.style.borderColor = borderColor;
    });
    
    // Apply to modals
    document.querySelectorAll('.modal-content').forEach(el => {
        el.style.backgroundColor = surfaceColor;
        el.style.color = textColor;
    });
    
    console.log('✅ All colors applied for theme:', theme);
}

// Load theme on page load
function loadSavedTheme() {
    currentTheme = localStorage.getItem('theme') || 'light';
    
    console.log('📂 Loading theme on page load:', currentTheme);
    
    document.documentElement.setAttribute('data-theme', currentTheme);
    document.body.setAttribute('data-theme', currentTheme);
    
    // Apply colors after a tiny delay to ensure DOM is ready
    setTimeout(() => {
        applyThemeColors(currentTheme);
    }, 50);
}

// Load immediately
loadSavedTheme();

// Also load on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    loadSavedTheme();
    
    // Make sure drag and drop initializes
    if (typeof initializeDragAndDrop === 'function') {
        initializeDragAndDrop();
    }
    
    console.log('✅ Theme system ready! Current theme:', currentTheme);
});

// ============================================================
// FUNCTIONAL TASK SWITCHES (Status Control)
// ============================================================

function toggleTaskStatus(taskId, switchElement) {
    const isCompleting = !switchElement.classList.contains('on');
    const taskItem = switchElement.closest('.task-item');
    
    // Optimistic UI update
    if (isCompleting) {
        switchElement.classList.add('on');
        taskItem.style.opacity = '0.6';
    } else {
        switchElement.classList.remove('on');
        taskItem.style.opacity = '1';
    }
    
    // Send to server
    fetch('actions/toggle_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            task_id: taskId,
            is_completed: isCompleting
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isCompleting) {
                // Animate task completion
                setTimeout(() => {
                    taskItem.style.transition = 'all 0.5s ease';
                    taskItem.style.transform = 'translateX(20px)';
                    taskItem.style.opacity = '0';
                    
                    setTimeout(() => {
                        taskItem.remove();
                        
                        // Check if task list is empty
                        const tasksList = document.querySelector('.tasks-list');
                        if (tasksList && tasksList.children.length === 0) {
                            tasksList.innerHTML = '<div class="empty-state-small">No pending tasks</div>';
                        }
                    }, 500);
                }, 800);
            }
            
            showNotification('Task updated successfully', 'success');
        } else {
            // Revert on error
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
        
        // Revert on error
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
// WORK STATUS SWITCHES (Event Status Control)
// ============================================================

function toggleWorkStatus(eventId, switchElement) {
    const isCompleting = !switchElement.classList.contains('on');
    const workItem = switchElement.closest('.work-item');
    
    // Optimistic UI update
    if (isCompleting) {
        switchElement.classList.add('on');
        if (workItem) workItem.style.opacity = '0.7';
    } else {
        switchElement.classList.remove('on');
        if (workItem) workItem.style.opacity = '1';
    }
    
    // Send to server
    const newStatus = isCompleting ? 'completed' : 'pending';
    
    fetch('actions/toggle_work_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            event_id: eventId,
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Work item ${isCompleting ? 'completed' : 'reopened'}`, 'success');
            
            // Optional: fade out completed items after a delay
            if (isCompleting && workItem) {
                setTimeout(() => {
                    workItem.style.transition = 'all 0.5s ease';
                    workItem.style.transform = 'translateX(20px)';
                    workItem.style.opacity = '0';
                }, 1000);
            }
        } else {
            // Revert on error
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
        
        // Revert on error
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
// WORK MODAL
// ============================================================

function openWorkModal() {
    openEventModal();
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

// Close modal on outside click
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        closeModal(e.target.id);
    }
});

// Close modal on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const activeModal = document.querySelector('.modal.active');
        if (activeModal) {
            closeModal(activeModal.id);
        }
    }
});

// ============================================================
// EVENT MODAL
// ============================================================

function openEventModal(eventId = null) {
    const modal = document.getElementById('eventModal');
    const form = document.getElementById('eventForm');
    const title = document.getElementById('modalTitle');
    
    // Reset form
    form.reset();
    
    if (eventId) {
        // Edit mode
        title.textContent = 'Edit Event';
        document.getElementById('eventId').value = eventId;
        
        // Load event data via AJAX
        loadEventData(eventId);
    } else {
        // Create mode
        title.textContent = 'New Event';
        document.getElementById('eventId').value = '';
        
        // Set default dates
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('eventStartDate').value = today;
        document.getElementById('eventEndDate').value = today;
    }
    
    openModal('eventModal');
}

function loadEventData(eventId) {
    console.log('📂 Loading event:', eventId);
    
    fetch(`api/events.php?id=${eventId}`)
        .then(response => {
            console.log('📊 Response status:', response.status);
            
            // Check content type
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
            console.log('✅ Event data:', data);
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Populate form
            populateEventForm(data);
        })
        .catch(error => {
            console.error('❌ Error:', error);
            showNotification('Error loading event: ' + error.message, 'error');
            closeModal('eventModal');
        });
}

function populateEventForm(data) {
    // Basic fields
    const fields = {
        'eventTitle': data.title,
        'eventStartDate': data.start_date,
        'eventEndDate': data.end_date,
        'eventStartTime': data.start_time,
        'eventEndTime': data.end_time,
        'eventCategory': data.category_id,
        'eventLocation': data.location,
        'eventClient': data.client,
        'eventDescription': data.description,
        'isAllDay': data.is_all_day,
        'documentDate': data.document_date,
        'executionDate': data.execution_date,
        'frequencyMonths': data.frequency_months,
        'frequencyYears': data.frequency_years
    };
    
    // Populate each field
    for (const [fieldId, value] of Object.entries(fields)) {
        const element = document.getElementById(fieldId);
        
        if (!element) continue;
        
        if (element.type === 'checkbox') {
            element.checked = !!value;
        } else if (value !== null && value !== undefined) {
            element.value = value;
        }
    }
    
    // Toggle all day
    if (typeof toggleAllDay === 'function') {
        toggleAllDay();
    }
}
function toggleAllDay() {
    const isAllDay = document.getElementById('isAllDay').checked;
    const startTime = document.getElementById('eventStartTime');
    const endTime = document.getElementById('eventEndTime');
    
    if (isAllDay) {
        startTime.value = '';
        endTime.value = '';
        startTime.disabled = true;
        endTime.disabled = true;
        startTime.parentElement.style.opacity = '0.5';
        endTime.parentElement.style.opacity = '0.5';
    } else {
        startTime.disabled = false;
        endTime.disabled = false;
        startTime.parentElement.style.opacity = '1';
        endTime.parentElement.style.opacity = '1';
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
    console.log('✅ Drag & Drop initialized');
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
    console.log(`✅ ${events.length} events made draggable`);
}

function handleDragStart(e) {
    draggedEventId = this.getAttribute('data-event-id');
    draggedElement = this;
    this.style.opacity = '0.5';
    this.style.cursor = 'grabbing';
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
    console.log('🎯 Dragging event:', draggedEventId);
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
    console.log(`✅ ${calendarDays.length} drop zones initialized`);
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
        console.error('❌ No event ID found');
        return false;
    }
    
    const newDate = this.getAttribute('data-date');
    if (!newDate) {
        console.error('❌ No date found on drop target');
        showNotification('Error: Invalid drop target', 'error');
        return false;
    }
    
    console.log('📍 Dropped on date:', newDate);
    
    if (confirm(`Reschedule event to ${formatDateForDisplay(newDate)}?`)) {
        rescheduleEvent(draggedEventId, newDate);
    }
    
    return false;
}

function rescheduleEvent(eventId, newDate) {
    console.log('🔄 Rescheduling event', eventId, 'to', newDate);
    
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
            console.log('✅ Event rescheduled successfully');
            showNotification('Event rescheduled successfully!', 'success');
            setTimeout(() => window.location.reload(), 500);
        } else {
            console.error('❌ Reschedule failed:', data.error);
            showNotification('Error: ' + (data.error || 'Failed to reschedule'), 'error');
        }
    })
    .catch(error => {
        console.error('❌ Network error:', error);
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
    console.log('⬅️ Previous month:', month, year);
    window.location.href = `index.php?month=${month}&year=${year}`;
}

function navigateToNextMonth() {
    const urlParams = new URLSearchParams(window.location.search);
    let month = parseInt(urlParams.get('month')) || new Date().getMonth() + 1;
    let year = parseInt(urlParams.get('year')) || new Date().getFullYear();
    month++;
    if (month > 12) { month = 1; year++; }
    console.log('➡️ Next month:', month, year);
    window.location.href = `index.php?month=${month}&year=${year}`;
}

function formatDateForDisplay(dateString) {
    const date = new Date(dateString);
    const options = { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// ============================================================
// FORM VALIDATION
// ============================================================

document.getElementById('eventForm')?.addEventListener('submit', function(e) {
    const startDate = document.getElementById('eventStartDate').value;
    const endDate = document.getElementById('eventEndDate').value;
    
    if (new Date(endDate) < new Date(startDate)) {
        e.preventDefault();
        showNotification('End date cannot be before start date', 'error');
        return false;
    }
    
    const isAllDay = document.getElementById('isAllDay').checked;
    
    if (!isAllDay) {
        const startTime = document.getElementById('eventStartTime').value;
        const endTime = document.getElementById('eventEndTime').value;
        
        if (startDate === endDate && startTime && endTime) {
            if (endTime <= startTime) {
                e.preventDefault();
                showNotification('End time must be after start time', 'error');
                return false;
            }
        }
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
// DATE SYNCHRONIZATION
// ============================================================

document.getElementById('eventStartDate')?.addEventListener('change', function() {
    const endDateInput = document.getElementById('eventEndDate');
    if (!endDateInput.value || endDateInput.value < this.value) {
        endDateInput.value = this.value;
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

function formatTime(timeString) {
    if (!timeString) return '';
    
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    
    return `${hour12}:${minutes} ${ampm}`;
}

// ============================================================
// CONSOLE INFO
// ============================================================

console.log('%c📅 Calendar System v2.0', 'color: #dc2626; font-size: 16px; font-weight: bold;');
console.log('%cKeyboard Shortcuts:', 'color: #78716c; font-weight: bold;');
console.log('Ctrl/Cmd + N: New Event');
console.log('Ctrl/Cmd + D: Toggle Dark/Light Mode');
console.log('Escape: Close Modal');
console.log('💡 Drag events to reschedule');
console.log('⬅️ Drag to left edge (100px) for previous month');
console.log('➡️ Drag to right edge (100px) for next month');
function applyThemeColors(theme) {
    if (theme === 'dark') {
        document.body.style.backgroundColor = '#0a0a0a';
        document.body.style.color = '#fafaf9';
        document.querySelectorAll('.main-header, .calendar-main, .calendar-day').forEach(el => {
            el.style.backgroundColor = '#1a1a1a';
            el.style.color = '#fafaf9';
        });
    } else {
        document.body.style.backgroundColor = '#fafaf9';
        document.body.style.color = '#1c1917';
        document.querySelectorAll('.main-header, .calendar-main, .calendar-day').forEach(el => {
            el.style.backgroundColor = '#ffffff';
            el.style.color = '#1c1917';
        });
    }
}

// Modificar toggleTheme existente para agregar:
function toggleTheme() {
    const html = document.documentElement;
    const body = document.body;
    const currentTheme = body.getAttribute('data-theme') || 'light';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    html.setAttribute('data-theme', newTheme);
    body.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    applyThemeColors(newTheme); // ← AGREGAR ESTA LÍNEA
    
    console.log('✅ Theme changed to:', newTheme);
}