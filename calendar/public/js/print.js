/**
 * ============================================================
 * PRINT FUNCTIONALITY
 * Handles calendar printing with proper formatting
 * ============================================================
 */

/**
 * Open print dialog with optimized view
 */
function printCalendar() {
    // Show print-specific elements before printing
    const printHeader = document.querySelector('.print-header');
    const printFooter = document.querySelector('.print-footer');
    
    if (printHeader) {
        printHeader.style.display = 'block';
    }
    
    if (printFooter) {
        printFooter.style.display = 'block';
    }
    
    // Trigger print dialog
    window.print();
    
    // Hide print elements after printing (if user cancels)
    setTimeout(() => {
        if (printHeader) {
            printHeader.style.display = 'none';
        }
        if (printFooter) {
            printFooter.style.display = 'none';
        }
    }, 100);
}

/**
 * Prepare calendar for print
 * This is called automatically when the page loads
 */
function preparePrintView() {
    // Get all calendar days
    const calendarDays = document.querySelectorAll('.calendar-day:not(.empty)');
    
    calendarDays.forEach(day => {
        // Get the date for this day
        const date = day.getAttribute('data-date');
        if (!date) return;
        
        // Get all events for this day from the screen display
        const dayEvents = day.querySelectorAll('.event-dot');
        
        // Create print events container if it doesn't exist
        let printEventsContainer = day.querySelector('.print-events');
        if (!printEventsContainer) {
            printEventsContainer = document.createElement('div');
            printEventsContainer.className = 'print-events';
            day.appendChild(printEventsContainer);
        }
        
        // Clear existing print events
        printEventsContainer.innerHTML = '';
        
        // Create print-friendly event items
        dayEvents.forEach(eventDot => {
            const eventId = eventDot.getAttribute('data-event-id');
            
            // Get event data from the event dot
            const title = eventDot.getAttribute('title') || eventDot.querySelector('.event-label')?.textContent || '';
            
            // Create print event element
            const printEvent = document.createElement('div');
            printEvent.className = 'print-event';
            printEvent.setAttribute('data-event-id', eventId);
            
            // Store event data in the print element for later enrichment
            printEvent.innerHTML = `
                <span class="print-event-title">${escapeHtml(title)}</span>
                <span class="print-event-loading">Loading details...</span>
            `;
            
            printEventsContainer.appendChild(printEvent);
        });
    });
    
    // Load full event details for print
    loadEventDetailsForPrint();
}

/**
 * Load full event details including location
 */
async function loadEventDetailsForPrint() {
    const printEvents = document.querySelectorAll('.print-event');
    
    for (const printEvent of printEvents) {
        const eventId = printEvent.getAttribute('data-event-id');
        if (!eventId) continue;
        
        try {
            const response = await fetch(`api/events.php?id=${eventId}`);
            if (!response.ok) continue;
            
            const eventData = await response.json();
            
            // Build print event HTML with all details
            let html = '';
            
            // Time
            if (eventData.start_time) {
                html += `<span class="print-event-time">${formatTime12h(eventData.start_time)}</span>`;
            }
            
            // Title
            html += `<span class="print-event-title">${escapeHtml(eventData.title)}</span>`;
            
            // Location (if exists)
            if (eventData.location && eventData.location.trim() !== '') {
                html += `<span class="print-event-location">📍 ${escapeHtml(eventData.location)}</span>`;
            }
            
            // Category badge
            if (eventData.category_name) {
                html += `<span class="print-event-category">${escapeHtml(eventData.category_name)}</span>`;
                
                // Add category-specific class for color coding
                const categoryClass = getCategoryClass(eventData.category_name);
                if (categoryClass) {
                    printEvent.classList.add(categoryClass);
                }
            }
            
            // Mark as completed if applicable
            if (eventData.status === 'completed') {
                printEvent.classList.add('completed');
            }
            
            printEvent.innerHTML = html;
            
        } catch (error) {
            console.error(`Error loading event ${eventId}:`, error);
            // Keep the basic title on error
            printEvent.querySelector('.print-event-loading')?.remove();
        }
    }
}

/**
 * Get category class for styling
 */
function getCategoryClass(categoryName) {
    const name = categoryName.toLowerCase();
    
    if (name.includes('jwo')) return 'jwo';
    if (name.includes('contract')) return 'contract';
    if (name.includes('proposal')) return 'proposal';
    if (name.includes('hoodvent')) return 'hoodvent';
    if (name.includes('janitorial')) return 'janitorial';
    
    return '';
}

/**
 * Format time to 12-hour format
 */
function formatTime12h(time) {
    if (!time) return '';
    
    const [hours, minutes] = time.split(':');
    const h = parseInt(hours);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const displayHours = h % 12 || 12;
    
    return `${displayHours}:${minutes} ${ampm}`;
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Initialize print functionality when page loads
 */
document.addEventListener('DOMContentLoaded', function() {
    // Prepare print view
    preparePrintView();
    
    // Add keyboard shortcut for printing (Ctrl/Cmd + P)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            printCalendar();
        }
    });
});

/**
 * Before print event - ensure everything is ready
 */
window.addEventListener('beforeprint', function() {
    console.log('Preparing calendar for print...');
    
    // Make sure print elements are visible
    const printHeader = document.querySelector('.print-header');
    const printFooter = document.querySelector('.print-footer');
    
    if (printHeader) printHeader.style.display = 'block';
    if (printFooter) printFooter.style.display = 'block';
});

/**
 * After print event - restore screen view
 */
window.addEventListener('afterprint', function() {
    console.log('Print completed or cancelled');
    
    // Hide print-only elements
    const printHeader = document.querySelector('.print-header');
    const printFooter = document.querySelector('.print-footer');
    
    if (printHeader) printHeader.style.display = 'none';
    if (printFooter) printFooter.style.display = 'none';
});