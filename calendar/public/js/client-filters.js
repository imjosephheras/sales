/**
 * ============================================================
 * CLIENT FILTERS - EXCEL STYLE
 * Filtros con checkboxes como en Excel
 * ============================================================
 */

// Array para almacenar clientes seleccionados
let selectedClients = [];

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    initializeClientFilters();
});

/**
 * Inicializar filtros
 */
function initializeClientFilters() {
    // Marcar todos los clientes como seleccionados al inicio
    const checkboxes = document.querySelectorAll('.client-checkbox');
    selectedClients = [];
    
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            selectedClients.push(checkbox.value);
        }
    });
    
    console.log('✅ Client filters initialized:', selectedClients.length, 'clients');
}

/**
 * Toggle todos los clientes (Select All checkbox)
 */
function toggleAllClients(checked) {
    const checkboxes = document.querySelectorAll('.client-checkbox');
    selectedClients = [];
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = checked;
        if (checked) {
            selectedClients.push(checkbox.value);
        }
    });
    
    updateClientFilter();
    console.log(checked ? '✅ All clients selected' : '❌ All clients deselected');
}

/**
 * Actualizar filtro cuando se cambia un checkbox
 */
function updateClientFilter() {
    const checkboxes = document.querySelectorAll('.client-checkbox');
    selectedClients = [];
    
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            selectedClients.push(checkbox.value);
        }
    });
    
    // Actualizar el estado del "Select All"
    const selectAllCheckbox = document.getElementById('selectAllClients');
    if (selectAllCheckbox) {
        const totalCheckboxes = checkboxes.length;
        const checkedCheckboxes = selectedClients.length;
        
        selectAllCheckbox.checked = checkedCheckboxes === totalCheckboxes;
        selectAllCheckbox.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
    }
    
    console.log('🔄 Filter updated:', selectedClients.length, 'clients selected');
}

/**
 * Aplicar filtro (mostrar/ocultar eventos)
 */
function applyClientFilter() {
    const allEvents = document.querySelectorAll('.event-card');
    let hiddenCount = 0;
    let visibleCount = 0;
    
    allEvents.forEach(eventCard => {
        const eventClient = eventCard.getAttribute('data-client');
        
        // Si no tiene cliente, siempre mostrar
        if (!eventClient || eventClient.trim() === '') {
            eventCard.style.display = '';
            visibleCount++;
            return;
        }
        
        // Si el cliente está en la lista de seleccionados, mostrar
        if (selectedClients.includes(eventClient)) {
            eventCard.style.display = '';
            visibleCount++;
        } else {
            eventCard.style.display = 'none';
            hiddenCount++;
        }
    });
    
    // Mostrar tags de filtros activos
    displayActiveFilters();
    
    // Mostrar notificación
    if (selectedClients.length === 0) {
        showNotification('⚠️ No clients selected - showing all events', 'warning');
    } else if (hiddenCount > 0) {
        showNotification(`✅ Filter applied: ${visibleCount} events shown, ${hiddenCount} hidden`, 'success');
    } else {
        showNotification(`✅ Showing all ${visibleCount} events`, 'success');
    }
    
    console.log(`📊 Filter applied: ${visibleCount} visible, ${hiddenCount} hidden`);
}

/**
 * Mostrar filtros activos como tags
 */
function displayActiveFilters() {
    const activeFiltersDiv = document.getElementById('activeFilters');
    const tagsContainer = document.getElementById('activeFiltersTags');
    
    if (!activeFiltersDiv || !tagsContainer) return;
    
    const allCheckboxes = document.querySelectorAll('.client-checkbox');
    const totalClients = allCheckboxes.length;
    
    // Si todos están seleccionados, ocultar la sección
    if (selectedClients.length === totalClients || selectedClients.length === 0) {
        activeFiltersDiv.style.display = 'none';
        return;
    }
    
    // Mostrar sección y crear tags
    activeFiltersDiv.style.display = 'block';
    tagsContainer.innerHTML = '';
    
    selectedClients.forEach(client => {
        const tag = document.createElement('div');
        tag.className = 'filter-tag';
        tag.innerHTML = `
            <span>${escapeHtml(client)}</span>
            <button class="filter-tag-remove" onclick="removeClientFromFilter('${escapeHtml(client)}')" title="Remove">×</button>
        `;
        tagsContainer.appendChild(tag);
    });
}

/**
 * Remover un cliente del filtro (desde tag)
 */
function removeClientFromFilter(clientName) {
    // Desmarcar el checkbox
    const checkboxes = document.querySelectorAll('.client-checkbox');
    checkboxes.forEach(checkbox => {
        if (checkbox.value === clientName) {
            checkbox.checked = false;
        }
    });
    
    // Actualizar y aplicar
    updateClientFilter();
    applyClientFilter();
}

/**
 * Limpiar todos los filtros
 */
function clearClientFilter() {
    // Marcar todos los checkboxes
    const checkboxes = document.querySelectorAll('.client-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    
    // Actualizar "Select All"
    const selectAllCheckbox = document.getElementById('selectAllClients');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = true;
    }
    
    // Mostrar todos los eventos
    const allEvents = document.querySelectorAll('.event-card');
    allEvents.forEach(eventCard => {
        eventCard.style.display = '';
    });
    
    // Ocultar filtros activos
    const activeFiltersDiv = document.getElementById('activeFilters');
    if (activeFiltersDiv) {
        activeFiltersDiv.style.display = 'none';
    }
    
    // Actualizar array
    selectedClients = Array.from(checkboxes).map(cb => cb.value);
    
    showNotification('🔄 Filters cleared - showing all events', 'info');
    console.log('🔄 All filters cleared');
}

/**
 * Filtrar la lista de clientes (búsqueda)
 */
function filterClientsList(searchTerm) {
    const clientItems = document.querySelectorAll('.client-checkbox-item');
    const term = searchTerm.toLowerCase().trim();
    
    let visibleCount = 0;
    
    clientItems.forEach(item => {
        const clientName = item.getAttribute('data-client').toLowerCase();
        
        if (clientName.includes(term)) {
            item.classList.remove('hidden');
            visibleCount++;
        } else {
            item.classList.add('hidden');
        }
    });
    
    console.log(`🔍 Search: "${searchTerm}" - ${visibleCount} clients found`);
}

/**
 * Escape HTML para prevenir XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Shortcut: Aplicar filtro con Enter
 */
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('clientSearchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyClientFilter();
            }
        });
    }
});

/**
 * Auto-aplicar filtro cuando se cambia un checkbox
 */
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('client-checkbox')) {
        updateClientFilter();
        // Aplicar inmediatamente sin delay
        applyClientFilter();
    }
});

// Logging
console.log('📊 Excel-style client filters loaded');
console.log('✨ Features:');
console.log('  • Checkbox filters');
console.log('  • Select/Deselect All');
console.log('  • Search clients');
console.log('  • Active filter tags');
console.log('  • Auto-apply on change');