/**
 * ============================================================
 * SERIES MANAGEMENT - MASTER-CHILD SYSTEM
 * When master moves, all children are regenerated automatically
 * ============================================================
 */

// Reschedule with master awareness
async function rescheduleWithMasterAwareness(eventId, newDate) {
    const seriesInfo = await checkIfSeries(eventId);
    
    console.log('📊 Series info:', seriesInfo);
    
    if (seriesInfo.is_master) {
        // Moving MASTER event - all children will be regenerated
        const message = `🔵 EVENTO MAESTRO\n\n` +
                       `Este es el evento maestro de una serie de ${seriesInfo.total} eventos.\n\n` +
                       `Si lo mueves a ${formatDateForDisplay(newDate)}:\n` +
                       `• Se eliminan los ${seriesInfo.total - 1} eventos actuales\n` +
                       `• Se regeneran ${seriesInfo.total - 1} nuevos eventos desde ${formatDateForDisplay(newDate)}\n\n` +
                       `Fecha de creación del documento: ${seriesInfo.master_info?.document_date || 'Original'}\n\n` +
                       `¿Continuar?`;
        
        if (!confirm(message)) return;
        
        // Move master - backend will regenerate children
        await rescheduleEvent(eventId, newDate, false);
        
    } else if (seriesInfo.is_child) {
        // Moving CHILD event
        const message = `⚪ EVENTO HIJO\n\n` +
                       `Este es el evento #${seriesInfo.index} de ${seriesInfo.total} en la serie.\n\n` +
                       `⚠️ ADVERTENCIA: Si mueves este evento hijo, se desvinculará de la serie.\n` +
                       `El evento maestro (#${seriesInfo.master_id}) no cambiará.\n\n` +
                       `¿Deseas moverlo a ${formatDateForDisplay(newDate)}?`;
        
        if (!confirm(message)) return;
        
        // Move just this child
        await rescheduleEvent(eventId, newDate, false);
        
    } else {
        // Standalone event
        const message = `¿Mover este evento a ${formatDateForDisplay(newDate)}?`;
        if (!confirm(message)) return;
        
        await rescheduleEvent(eventId, newDate, false);
    }
}

// Delete with master awareness
async function deleteWithMasterAwareness(eventId) {
    const seriesInfo = await checkIfSeries(eventId);
    
    if (seriesInfo.is_master) {
        // Deleting MASTER - cascades to all children
        const message = `🔵 ELIMINAR SERIE COMPLETA\n\n` +
                       `Este es el evento MAESTRO.\n\n` +
                       `Si lo eliminas:\n` +
                       `• Se eliminan TODOS los ${seriesInfo.total} eventos de la serie\n` +
                       `• Esta acción NO se puede deshacer\n\n` +
                       `Fecha de creación: ${seriesInfo.master_info?.document_date || 'Original'}\n\n` +
                       `¿Estás SEGURO?`;
        
        if (!confirm(message)) return;
        
        await deleteEvent(eventId);
        
    } else if (seriesInfo.is_child) {
        // Deleting CHILD
        const message = `⚪ ELIMINAR EVENTO HIJO\n\n` +
                       `Este es el evento #${seriesInfo.index} de ${seriesInfo.total}.\n\n` +
                       `⚠️ Solo este evento será eliminado.\n` +
                       `El maestro y los demás eventos permanecerán.\n\n` +
                       `¿Continuar?`;
        
        if (!confirm(message)) return;
        
        await deleteEvent(eventId);
        
    } else {
        // Standalone
        if (!confirm('¿Eliminar este evento?')) return;
        await deleteEvent(eventId);
    }
}

// Actual delete function
async function deleteEvent(eventId) {
    try {
        const response = await fetch('/sales/calendar/actions/event/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ event_id: eventId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message || 'Eliminado exitosamente', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification('Error al eliminar', 'error');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showNotification('Error de conexión', 'error');
    }
}

// Actual reschedule function
async function rescheduleEvent(eventId, newDate, moveSeries) {
    try {
        const response = await fetch('/sales/calendar/actions/event/reschedule.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                event_id: eventId,
                new_date: newDate,
                move_series: moveSeries
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (data.is_master) {
                showNotification(`✅ Serie regenerada: ${data.message}`, 'success');
            } else {
                showNotification(data.message || 'Evento movido', 'success');
            }
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showNotification('Error al mover', 'error');
        }
    } catch (error) {
        console.error('Reschedule error:', error);
        showNotification('Error de conexión', 'error');
    }
}

// Add badges to event cards
function addMasterChildBadges() {
    document.querySelectorAll('.event-card').forEach(card => {
        const seriesIndex = card.getAttribute('data-series-index');
        const seriesTotal = card.getAttribute('data-series-total');
        
        if (seriesTotal && parseInt(seriesTotal) > 1) {
            const index = parseInt(seriesIndex);
            const total = parseInt(seriesTotal);
            
            const badge = document.createElement('span');
            badge.className = 'series-badge';
            
            if (index === 0) {
                badge.classList.add('master-badge');
                badge.textContent = `🔵 MAESTRO (${total})`;
                badge.title = `Evento maestro de serie de ${total} eventos`;
            } else {
                badge.classList.add('child-badge');
                badge.textContent = `⚪ #${index}/${total}`;
                badge.title = `Evento hijo ${index} de ${total}`;
            }
            
            card.querySelector('.event-card-header')?.appendChild(badge);
        }
    });
}

// Update drag and drop handler
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
    
    // Use master-aware reschedule
    rescheduleWithMasterAwareness(draggedEventId, newDate);
    
    return false;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    addMasterChildBadges();
    console.log('✅ Master-Child series management initialized');
});