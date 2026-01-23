/**
 * ============================================================
 * DELETE EVENT - IMPROVED VERSION
 * Mejor detección de series y UI
 * ============================================================
 */

// Variable global para almacenar info del evento actual
let currentEventInfo = null;

// Función principal de borrado
function confirmDeleteEvent(eventId, eventTitle) {
    console.log('🗑️ Confirm delete:', eventId, eventTitle);
    
    // Verificar si es parte de una serie
    checkIfPartOfSeriesAsync(eventTitle).then(isPartOfSeries => {
        showDeleteModal(eventId, eventTitle, isPartOfSeries);
    });
}

// Verificar si es parte de una serie (versión mejorada)
function checkIfPartOfSeriesAsync(eventTitle) {
    return new Promise((resolve) => {
        // Método 1: Contar en el DOM
        const eventsInDOM = document.querySelectorAll('.event-dot, .work-item, .today-event');
        let countInDOM = 0;
        
        eventsInDOM.forEach(el => {
            const title = el.getAttribute('title') || 
                         el.querySelector('.event-label')?.textContent ||
                         el.querySelector('.event-title')?.textContent;
            
            if (title && title.trim() === eventTitle.trim()) {
                countInDOM++;
            }
        });
        
        console.log('Events with same title in DOM:', countInDOM);
        
        // Si hay más de 1, es serie
        if (countInDOM > 1) {
            resolve(true);
            return;
        }
        
        // Método 2: Consultar al servidor
        fetch(`api/check_series.php?title=${encodeURIComponent(eventTitle)}`)
            .then(r => r.json())
            .then(data => {
                resolve(data.count > 1);
            })
            .catch(() => {
                // Si falla, asumir que es serie si el título tiene formato de nomenclatura
                const hasNomenclatureFormat = /^[A-Z]+-[A-Z0-9]+\d{8}-\d{1,2}-\d{1,2}$/i.test(eventTitle);
                resolve(hasNomenclatureFormat);
            });
    });
}

// Mostrar modal de borrado
function showDeleteModal(eventId, eventTitle, isPartOfSeries) {
    // Remover modal anterior si existe
    const oldModal = document.getElementById('deleteModal');
    if (oldModal) oldModal.remove();
    
    const modal = document.createElement('div');
    modal.className = 'modal delete-modal active';
    modal.id = 'deleteModal';
    
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header" style="border-bottom: 1px solid #e5e7eb; padding-bottom: 15px;">
                <h2 style="margin: 0; font-size: 20px;">Delete Event</h2>
                <button class="modal-close" onclick="closeDeleteModal()" 
                        style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">&times;</button>
            </div>
            
            <div class="modal-body" style="padding: 20px;">
                <p style="margin-bottom: 15px; font-size: 14px;">
                    <strong>Event:</strong> ${eventTitle}
                </p>
                
                ${isPartOfSeries ? `
                    <div style="background: #fef3c7; padding: 12px; border-radius: 6px; margin-bottom: 20px; border-left: 3px solid #f59e0b;">
                        <p style="margin: 0; font-size: 14px; color: #92400e;">
                            ⚠️ <strong>This is a recurring event</strong><br>
                            <span style="font-size: 13px;">Multiple events share this title</span>
                        </p>
                    </div>
                    
                    <p style="margin-bottom: 15px; color: #374151; font-size: 14px;">
                        What would you like to delete?
                    </p>
                    
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <button class="btn-delete-option" onclick="executeDelete(${eventId}, false)" 
                                style="padding: 12px 20px; background: #6b7280; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; transition: all 0.2s;">
                            📅 Delete this occurrence only
                        </button>
                        <button class="btn-delete-option btn-delete-series" onclick="executeDelete(${eventId}, true)" 
                                style="padding: 12px 20px; background: #dc2626; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; transition: all 0.2s;">
                            🗑️ Delete entire series
                        </button>
                    </div>
                    
                    <p style="margin-top: 15px; font-size: 12px; color: #6b7280; font-style: italic;">
                        Deleting the series will remove all events with this title
                    </p>
                ` : `
                    <p style="margin-bottom: 20px; color: #6b7280; font-size: 14px;">
                        This action cannot be undone.
                    </p>
                    
                    <div style="display: flex; gap: 12px; justify-content: flex-end;">
                        <button onclick="closeDeleteModal()" 
                                style="padding: 10px 20px; background: #e5e7eb; color: #374151; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                            Cancel
                        </button>
                        <button onclick="executeDelete(${eventId}, false)" 
                                style="padding: 10px 20px; background: #dc2626; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                            Delete Event
                        </button>
                    </div>
                `}
            </div>
        </div>
    `;
    
    // Agregar estilos de hover
    const style = document.createElement('style');
    style.textContent = `
        .btn-delete-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-delete-series:hover {
            background: #b91c1c !important;
        }
        .delete-modal {
            z-index: 10000;
        }
    `;
    modal.appendChild(style);
    
    document.body.appendChild(modal);
    
    // Cerrar con click fuera
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeDeleteModal();
        }
    });
}

// Cerrar modal
function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => modal.remove(), 200);
    }
}

// Ejecutar borrado
function executeDelete(eventId, deleteSeries) {
    console.log(`🗑️ Deleting event ${eventId}, series: ${deleteSeries}`);
    
    // Mostrar loading
    const modal = document.getElementById('deleteModal');
    if (modal) {
        const modalBody = modal.querySelector('.modal-body');
        modalBody.innerHTML = `
            <div style="text-align: center; padding: 30px;">
                <div style="font-size: 40px; margin-bottom: 15px;">⏳</div>
                <p style="color: #6b7280;">Deleting...</p>
            </div>
        `;
    }
    
    fetch('actions/delete_event.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            event_id: eventId,
            delete_series: deleteSeries
        })
    })
    .then(response => response.json())
    .then(data => {
        closeDeleteModal();
        
        if (data.success) {
            const count = data.deleted_count || 1;
            const message = deleteSeries 
                ? `✅ Series deleted: ${count} event${count > 1 ? 's' : ''} removed`
                : '✅ Event deleted successfully';
            
            showNotification(message, 'success');
            
            // Cerrar modal de evento si está abierto
            const eventModal = document.getElementById('eventModal');
            if (eventModal) {
                closeModal('eventModal');
            }
            
            // Recargar después de 800ms
            setTimeout(() => {
                window.location.reload();
            }, 800);
        } else {
            showNotification('❌ Error: ' + (data.error || 'Failed to delete'), 'error');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        closeDeleteModal();
        showNotification('❌ Error: Could not connect to server', 'error');
    });
}

// Agregar botón de borrado al modal de evento
function addDeleteButton() {
    // Observer para detectar cuando se abre el modal
    const observer = new MutationObserver(function() {
        const eventModal = document.getElementById('eventModal');
        const eventIdInput = document.getElementById('eventId');
        
        if (eventModal && eventModal.classList.contains('active') && eventIdInput && eventIdInput.value) {
            // Solo agregar si no existe
            if (!eventModal.querySelector('.btn-delete-event')) {
                const eventId = parseInt(eventIdInput.value);
                const titleInput = document.getElementById('eventTitle');
                const eventTitle = titleInput ? titleInput.value : 'this event';
                
                // Buscar donde agregar el botón
                const modalFooter = eventModal.querySelector('.modal-footer') || 
                                   eventModal.querySelector('form');
                
                if (modalFooter) {
                    const deleteBtn = document.createElement('button');
                    deleteBtn.type = 'button';
                    deleteBtn.className = 'btn btn-danger btn-delete-event';
                    deleteBtn.textContent = '🗑️ Delete';
                    deleteBtn.style.cssText = 'background: #dc2626; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; margin-right: 10px;';
                    
                    deleteBtn.onclick = function() {
                        confirmDeleteEvent(eventId, eventTitle);
                    };
                    
                    deleteBtn.onmouseover = function() {
                        this.style.background = '#b91c1c';
                    };
                    
                    deleteBtn.onmouseout = function() {
                        this.style.background = '#dc2626';
                    };
                    
                    // Insertar al inicio
                    modalFooter.insertBefore(deleteBtn, modalFooter.firstChild);
                }
            }
        }
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class']
    });
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    addDeleteButton();
    console.log('✅ Delete event system loaded');
});

console.log('🗑️ Delete Event UI Module Loaded');