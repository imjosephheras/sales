/**
 * ============================================================
 * DRAG & DROP - FIXED VERSION
 * Safe handling of dropIndicator
 * ============================================================
 */

// Use existing variables if already declared (to avoid conflicts with calendar.js)
if (typeof draggedEventId === 'undefined') var draggedEventId = null;
if (typeof draggedElement === 'undefined') var draggedElement = null;
if (typeof dropIndicator === 'undefined') var dropIndicator = null;
if (typeof monthChangeTimeout === 'undefined') var monthChangeTimeout = null;

function initializeDragAndDrop() {
    console.log('🎯 Initializing Drag & Drop...');
    
    // Create drop indicator
    createDropIndicator();
    
    // Initialize draggable events
    initializeDraggableEvents();
    
    // Initialize drop zones
    initializeDropZones();
    
    // Add month navigation on drag
    addMonthNavigationOnDrag();
    
    console.log('✅ Drag & Drop initialized');
}

function createDropIndicator() {
    // Remove existing indicator if any
    const existing = document.getElementById('drop-indicator');
    if (existing) {
        existing.remove();
    }
    
    // Create new indicator
    dropIndicator = document.createElement('div');
    dropIndicator.id = 'drop-indicator';
    dropIndicator.className = 'drop-indicator';
    dropIndicator.style.cssText = `
        position: fixed;
        background: rgba(37, 99, 235, 0.1);
        border: 2px dashed #2563eb;
        border-radius: 8px;
        pointer-events: none;
        display: none;
        z-index: 10000;
        transition: all 0.15s ease;
    `;
    
    document.body.appendChild(dropIndicator);
    console.log('✅ Drop indicator created');
}

function showDropIndicator(target) {
    if (!dropIndicator) {
        console.warn('⚠️ Drop indicator not found, creating...');
        createDropIndicator();
    }
    
    if (!target || !dropIndicator) return;
    
    const rect = target.getBoundingClientRect();
    dropIndicator.style.display = 'block';
    dropIndicator.style.left = rect.left + window.scrollX + 'px';
    dropIndicator.style.top = rect.top + window.scrollY + 'px';
    dropIndicator.style.width = rect.width + 'px';
    dropIndicator.style.height = rect.height + 'px';
}

function hideDropIndicator() {
    if (dropIndicator) {
        dropIndicator.style.display = 'none';
    }
}

function initializeDraggableEvents() {
    const events = document.querySelectorAll('.event-card, .event-dot');
    
    events.forEach(event => {
        event.setAttribute('draggable', 'true');
        event.style.cursor = 'grab';
        
        event.addEventListener('dragstart', handleDragStart);
        event.addEventListener('dragend', handleDragEnd);
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
    
    // Check if series-aware functions exist
    if (typeof rescheduleWithMasterAwareness === 'function') {
        // Use master-child system
        rescheduleWithMasterAwareness(draggedEventId, newDate);
    } else {
        // Fallback to simple reschedule
        const message = `¿Mover este evento a ${formatDateForDisplay(newDate)}?`;
        if (confirm(message)) {
            rescheduleEventSimple(draggedEventId, newDate);
        }
    }
    
    return false;
}

function rescheduleEventSimple(eventId, newDate) {
    console.log('🔄 Rescheduling event', eventId, 'to', newDate);
    
    fetch('actions/event/reschedule.php', {
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
            setTimeout(() => window.location.reload(), 1000);
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
    if (month < 1) { 
        month = 12; 
        year--; 
    }
    
    console.log('⬅️ Previous month:', month, year);
    window.location.href = `index.php?month=${month}&year=${year}`;
}

function navigateToNextMonth() {
    const urlParams = new URLSearchParams(window.location.search);
    let month = parseInt(urlParams.get('month')) || new Date().getMonth() + 1;
    let year = parseInt(urlParams.get('year')) || new Date().getFullYear();
    
    month++;
    if (month > 12) { 
        month = 1; 
        year++; 
    }
    
    console.log('➡️ Next month:', month, year);
    window.location.href = `index.php?month=${month}&year=${year}`;
}

function formatDateForDisplay(dateString) {
    const date = new Date(dateString);
    const options = { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    initializeDragAndDrop();
});

console.log('📦 Drag & Drop module loaded');