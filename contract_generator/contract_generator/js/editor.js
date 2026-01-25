/**
 * EDITOR.JS
 * Maneja el panel de edición: carga de datos, guardado, tabs
 */

(function() {
    'use strict';

    // ========================================
    // VARIABLES GLOBALES
    // ========================================
    
    let currentRequestData = null;

    // ========================================
    // INICIALIZACIÓN
    // ========================================

    document.addEventListener('DOMContentLoaded', function() {
        console.log('✏️ Editor module loaded');

        // Event listeners para botones
        document.getElementById('btn-save').addEventListener('click', saveRequest);
        document.getElementById('btn-mark-ready').addEventListener('click', markAsReady);
        document.getElementById('btn-download-pdf').addEventListener('click', downloadPDF);

        // Event listener para refresh preview button
        const refreshBtn = document.getElementById('btn-refresh-preview');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', updatePreview);
        }

        // Event listener para selección de request desde inbox
        document.addEventListener('requestSelected', function(e) {
            loadRequestData(e.detail.id);
        });

        // Event listener para crear nuevo request desde tarea
        document.addEventListener('createNewRequest', function(e) {
            createNewRequestFromTask(e.detail);
        });

        // Event listener para task selected (for logging/debugging)
        document.addEventListener('taskSelected', function(e) {
            console.log('📋 Task selected:', e.detail);
        });

        // Mostrar/ocultar campos específicos de Contract
        document.getElementById('Request_Type').addEventListener('change', function() {
            toggleContractFields();
            updatePreview(); // Update preview when request type changes
        });

        // Auto-actualizar preview cuando cambian los datos del formulario
        const form = document.getElementById('editor-form');
        if (form) {
            form.addEventListener('input', debounce(updatePreview, 500));
            form.addEventListener('change', updatePreview);
        }
    });

    // ========================================
    // CARGAR DATOS DE SOLICITUD
    // ========================================

    function loadRequestData(id) {
        console.log('Loading request:', id);

        // Mostrar estado activo en editor, ocultar empty state
        document.getElementById('no-selection-state').style.display = 'none';
        document.getElementById('editor-active-state').style.display = 'block';

        // Mostrar estado activo en preview panel
        const previewNoSelection = document.getElementById('preview-no-selection');
        const previewActive = document.getElementById('preview-active');
        if (previewNoSelection) previewNoSelection.style.display = 'none';
        if (previewActive) previewActive.style.display = 'flex';

        // Fetch datos
        fetch(`controllers/get_request_detail.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentRequestData = data.data;
                    populateForm(data.data);
                    updatePreview();
                } else {
                    alert('Error loading request: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Connection error. Please try again.');
            });
    }

    // ========================================
    // CREATE NEW REQUEST FROM TASK
    // ========================================

    function createNewRequestFromTask(taskData) {
        console.log('Creating new request from task:', taskData);

        // Show active state in editor, hide empty state
        document.getElementById('no-selection-state').style.display = 'none';
        document.getElementById('editor-active-state').style.display = 'block';

        // Show active state in preview panel
        const previewNoSelection = document.getElementById('preview-no-selection');
        const previewActive = document.getElementById('preview-active');
        if (previewNoSelection) previewNoSelection.style.display = 'none';
        if (previewActive) previewActive.style.display = 'flex';

        // Clear form and set to new request
        document.getElementById('request_id').value = '';
        clearForm();

        // Pre-populate form with task data
        const requestType = determineRequestType(taskData.categoryName, taskData.eventTitle);
        setValue('Request_Type', requestType);

        // Set priority based on task priority
        const priorityMapping = {
            'urgent': 'Urgent',
            'high': 'High',
            'normal': 'Standard',
            'low': 'Low'
        };
        setValue('Priority', priorityMapping[taskData.priority] || 'Standard');

        // Set company name from client
        setValue('Company_Name', taskData.client);

        // Set requested service from task title or description
        setValue('Requested_Service', taskData.title);

        // Set additional comments from task description
        setValue('Additional_Comments', taskData.description);

        // Update header
        document.getElementById('doc-title').textContent = taskData.client || 'New Request';
        document.getElementById('doc-number').textContent = 'Not saved yet';

        // Update preview doc type badge
        const previewDocType = document.getElementById('preview-doc-type');
        if (previewDocType) {
            previewDocType.textContent = requestType;
        }

        // Show/hide contract fields based on type
        toggleContractFields();

        // Update preview
        updatePreview();

        console.log('✅ New request form created from task');
    }

    // ========================================
    // DETERMINE REQUEST TYPE FROM CATEGORY
    // ========================================

    function determineRequestType(categoryName, eventTitle) {
        if (!categoryName) {
            // Try to determine from event title
            if (eventTitle) {
                const title = eventTitle.toUpperCase();
                if (title.includes('JWO') || title.startsWith('JWO-')) return 'JWO';
                if (title.includes('CONTRACT') || title.startsWith('C-')) return 'Contract';
                if (title.includes('PROPOSAL') || title.startsWith('P-')) return 'Proposal';
                if (title.includes('QUOTE') || title.startsWith('Q-')) return 'Quote';
            }
            return 'JWO'; // Default
        }

        const category = categoryName.toUpperCase();
        if (category.includes('JWO')) return 'JWO';
        if (category.includes('CONTRACT')) return 'Contract';
        if (category.includes('PROPOSAL')) return 'Proposal';
        if (category.includes('QUOTE')) return 'Quote';

        return 'JWO'; // Default
    }

    // ========================================
    // CLEAR FORM
    // ========================================

    function clearForm() {
        const form = document.getElementById('editor-form');
        if (form) {
            form.reset();
        }

        // Clear specific fields that might not reset properly
        const fields = [
            'Service_Type', 'Request_Type', 'Priority', 'Requested_Service',
            'Company_Name', 'Contact_Name', 'Contact_Email', 'Contact_Phone',
            'Address', 'City', 'State', 'Zip_Code', 'Site_Visit_Conducted',
            'Invoice_Frequency', 'Contract_Duration', 'Total_Price', 'Currency',
            'inflationAdjustment', 'totalArea', 'buildingsIncluded',
            'startDateServices', 'Site_Observation', 'Additional_Comments'
        ];

        fields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                if (field.type === 'checkbox') {
                    field.checked = false;
                } else {
                    field.value = '';
                }
            }
        });

        // Clear scope of work
        const scopeContainer = document.getElementById('scope-items-container');
        if (scopeContainer) {
            scopeContainer.innerHTML = '';
        }
    }

    // ========================================
    // POBLAR FORMULARIO
    // ========================================

    function populateForm(data) {
        // Hidden ID
        document.getElementById('request_id').value = data.id;

        // Section 1: Request Info
        setValue('Service_Type', data.Service_Type);
        setValue('Request_Type', data.Request_Type);
        setValue('Priority', data.Priority);
        setValue('Requested_Service', data.Requested_Service);

        // Section 2: Client Info
        setValue('Company_Name', data.Company_Name);
        setValue('Contact_Name', data.Contact_Name);
        setValue('Contact_Email', data.Contact_Email);
        setValue('Contact_Phone', data.Contact_Phone);
        setValue('Address', data.Address);
        setValue('City', data.City);
        setValue('State', data.State);
        setValue('Zip_Code', data.Zip_Code);

        // Section 3: Operational
        setValue('Site_Visit_Conducted', data.Site_Visit_Conducted);
        setValue('Invoice_Frequency', data.Invoice_Frequency);
        setValue('Contract_Duration', data.Contract_Duration);

        // Section 4: Economic
        setValue('Total_Price', data.Total_Price);
        setValue('Currency', data.Currency);

        // Section 5: Contract Specific
        setValue('inflationAdjustment', data.inflationAdjustment);
        setValue('totalArea', data.totalArea);
        setValue('buildingsIncluded', data.buildingsIncluded);
        setValue('startDateServices', data.startDateServices);

        // Section 6: Observations
        setValue('Site_Observation', data.Site_Observation);
        setValue('Additional_Comments', data.Additional_Comments);

        // Scope of Work
        displayScope(data.Scope_Of_Work);

        // Actualizar header
        document.getElementById('doc-title').textContent = data.Company_Name || 'Untitled Request';
        document.getElementById('doc-number').textContent = data.docnum || 'Not generated yet';

        // Actualizar badge de tipo de documento en preview panel
        const previewDocType = document.getElementById('preview-doc-type');
        if (previewDocType) {
            previewDocType.textContent = data.Request_Type || 'Document';
        }

        // Mostrar/ocultar campos de Contract
        toggleContractFields();
    }

    // ========================================
    // GUARDAR CAMBIOS
    // ========================================

    function saveRequest() {
        console.log('Saving request...');

        const formData = getFormData();

        fetch('controllers/update_request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('✅ Saved successfully', 'success');
                // Actualizar preview
                updatePreview();
                // Refrescar inbox
                window.InboxModule.refresh();
            } else {
                showNotification('❌ Error: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('❌ Connection error', 'error');
        });
    }

    // ========================================
    // MARCAR COMO LISTO
    // ========================================

    function markAsReady() {
        const requestId = document.getElementById('request_id').value;

        // Check if request has been saved first
        if (!requestId || requestId.trim() === '') {
            showNotification('❌ Please save the request first before marking it as ready.', 'error');
            return;
        }

        if (!confirm('Are you sure you want to mark this request as READY? This will generate the DOCNUM.')) {
            return;
        }

        console.log('📤 Marking request as ready:', requestId);

        fetch('controllers/mark_ready.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ request_id: requestId })
        })
        .then(response => {
            console.log('📥 Mark ready response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('📥 Mark ready response data:', data);
            if (data.success) {
                showNotification('✅ Marked as ready! DOCNUM: ' + data.docnum, 'success');
                // Actualizar docnum en pantalla
                document.getElementById('doc-number').textContent = data.docnum;
                // Refrescar inbox para mover el item a Generated Contracts
                console.log('🔄 Refreshing inbox...');
                window.InboxModule.refresh();
            } else {
                showNotification('❌ Error: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('❌ Error marking as ready:', error);
            showNotification('❌ Connection error: ' + error.message, 'error');
        });
    }

    // ========================================
    // DESCARGAR PDF
    // ========================================

    function downloadPDF() {
        const requestId = document.getElementById('request_id').value;
        
        if (!requestId) {
            alert('No request selected');
            return;
        }

        // Abrir en nueva ventana
        window.open(`controllers/generate_pdf.php?id=${requestId}`, '_blank');
    }

    // ========================================
    // PREVIEW
    // ========================================

    function updatePreview() {
        // Esta función se implementará en preview.js
        if (window.PreviewModule) {
            window.PreviewModule.render(getFormData());
        }
    }

    // ========================================
    // UTILIDADES
    // ========================================

    function setValue(fieldId, value) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = value || '';
        }
    }

    function getFormData() {
        const form = document.getElementById('editor-form');
        const formData = new FormData(form);
        const data = {};
        
        formData.forEach((value, key) => {
            data[key] = value;
        });

        return data;
    }

    function toggleContractFields() {
        const requestType = document.getElementById('Request_Type').value;
        const contractSection = document.getElementById('contract-specific-section');
        
        if (requestType === 'Contract') {
            contractSection.style.display = 'block';
        } else {
            contractSection.style.display = 'none';
        }
    }

    function displayScope(scopeArray) {
        const scopeDisplay = document.getElementById('scope-display');
        
        if (!scopeArray || scopeArray.length === 0) {
            scopeDisplay.innerHTML = '<p style="color: var(--text-gray);">No scope of work defined</p>';
            return;
        }

        scopeDisplay.innerHTML = scopeArray.map(item => `
            <div class="scope-item">
                <i class="fas fa-check-circle"></i>
                <span>${escapeHtml(item)}</span>
            </div>
        `).join('');
    }

    function showNotification(message, type) {
        // Simple alert por ahora (puedes mejorar con toast notifications)
        alert(message);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

})();