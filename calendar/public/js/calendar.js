/**
 * Calendar JavaScript - BASIC
 * Only: modal open/close, load event data, drag & drop, theme toggle, delete.
 * No series, no filters, no sidebar logic, no duplicate code.
 */

// ============================================================
// THEME
// ============================================================
function toggleTheme() {
    const current = document.body.getAttribute('data-theme') || 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    document.body.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
}

// ============================================================
// MODAL
// ============================================================
function openModal() {
    document.getElementById('eventModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('eventModal').classList.remove('active');
    document.body.style.overflow = '';
}

// Close on backdrop click
document.addEventListener('click', function(e) {
    if (e.target.id === 'eventModal') closeModal();
});

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});

// ============================================================
// EVENT MODAL (create / edit)
// ============================================================
var currentEditId = null;

function openEventModal(eventId) {
    var form = document.getElementById('eventForm');
    form.reset();
    document.getElementById('eventId').value = '';
    document.getElementById('deleteBtn').style.display = 'none';
    currentEditId = null;

    if (eventId) {
        // Edit mode
        currentEditId = eventId;
        document.getElementById('modalTitle').textContent = 'Edit Event';
        document.getElementById('eventId').value = eventId;
        document.getElementById('deleteBtn').style.display = 'inline-block';
        loadEventData(eventId);
    } else {
        // Create mode
        document.getElementById('modalTitle').textContent = 'New Event';
        document.getElementById('eventStartDate').value = new Date().toISOString().split('T')[0];
    }

    openModal();
}

function loadEventData(eventId) {
    fetch('api/events.php?id=' + eventId)
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function(data) {
            if (data.error) throw new Error(data.error);
            document.getElementById('eventTitle').value = data.title || '';
            document.getElementById('eventClient').value = data.client || '';
            document.getElementById('eventLocation').value = data.location || '';
            document.getElementById('eventStartDate').value = data.start_date || '';
            document.getElementById('eventStatus').value = data.status || 'pending';
            document.getElementById('eventDescription').value = data.description || '';
            document.getElementById('eventCategoryId').value = data.category_id || '';
        })
        .catch(function(err) {
            showNotification('Error loading event: ' + err.message, 'error');
            closeModal();
        });
}

// ============================================================
// DELETE EVENT
// ============================================================
function deleteEvent() {
    if (!currentEditId) return;
    if (!confirm('Delete this event?')) return;

    fetch('actions/event/delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ event_id: currentEditId })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showNotification('Event deleted', 'success');
            setTimeout(function() { window.location.reload(); }, 500);
        } else {
            showNotification('Error: ' + (data.error || 'Could not delete'), 'error');
        }
    })
    .catch(function() {
        showNotification('Network error', 'error');
    });
}

// ============================================================
// DRAG & DROP
// ============================================================
var draggedEventId = null;

function handleDragStart(e, eventId) {
    draggedEventId = eventId;
    e.dataTransfer.effectAllowed = 'move';
    e.target.style.opacity = '0.5';
}

function handleDrop(e, dayEl) {
    e.preventDefault();
    e.stopPropagation();
    dayEl.classList.remove('drag-over');

    if (!draggedEventId) return;

    var newDate = dayEl.getAttribute('data-date');
    if (!newDate) return;

    if (confirm('Move event to ' + newDate + '?')) {
        fetch('actions/event/reschedule.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ event_id: draggedEventId, new_date: newDate })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                showNotification('Event moved', 'success');
                setTimeout(function() { window.location.reload(); }, 500);
            } else {
                showNotification('Error: ' + (data.error || 'Could not move'), 'error');
            }
        })
        .catch(function() {
            showNotification('Network error', 'error');
        });
    }

    draggedEventId = null;
}

// Reset opacity on drag end
document.addEventListener('dragend', function(e) {
    e.target.style.opacity = '1';
    draggedEventId = null;
});

// ============================================================
// NOTIFICATIONS
// ============================================================
function showNotification(message, type) {
    var existing = document.getElementById('jsNotification');
    if (existing) existing.remove();

    var el = document.createElement('div');
    el.id = 'jsNotification';
    el.className = 'flash-message flash-' + (type || 'info');
    el.textContent = message;
    document.body.insertBefore(el, document.body.firstChild);

    setTimeout(function() {
        el.style.opacity = '0';
        setTimeout(function() { el.remove(); }, 300);
    }, 3000);
}
