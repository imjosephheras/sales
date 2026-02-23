/**
 * EDITOR.JS
 * Maneja el panel de edición: carga de datos, guardado, tabs
 *
 * Section 4 blocks (18, 19, 20) are conditionally rendered based on
 * which services are active in the Work Order. Each block is fully
 * editable - the Contract Generator is the final control point before
 * printing, so users can modify services, prices, frequencies, and
 * staff without returning to the Request Form.
 */

(function() {
    'use strict';

    // ========================================
    // VARIABLES GLOBALES
    // ========================================

    let currentRequestData = null;
    let editorSalesMode = 'service'; // 'service' or 'product'

    // Expose sales mode globally so preview.js can read the current value
    window.editorSalesMode = editorSalesMode;

    /**
     * SERVICE REPORT CONFIG CACHE
     * Loaded once from get_service_config.php and cached in memory.
     * Each key maps to the full section config (title, scope_of_work,
     * initial_condition, service_performed, post_service_condition, technical_data).
     */
    var serviceReportConfig = null;

    // ========================================
    // INICIALIZACIÓN
    // ========================================

    // ========================================
    // SALES MODE TOGGLE (editor side)
    // ========================================
    window.setEditorSalesMode = function(mode) {
        editorSalesMode = mode;
        window.editorSalesMode = mode;
        var btnService = document.getElementById('editorBtnModeService');
        var btnProduct = document.getElementById('editorBtnModeProduct');
        if (btnService) {
            btnService.style.background = mode === 'service' ? '#001f54' : '#fff';
            btnService.style.color = mode === 'service' ? '#fff' : '#001f54';
        }
        if (btnProduct) {
            btnProduct.style.background = mode === 'product' ? '#001f54' : '#fff';
            btnProduct.style.color = mode === 'product' ? '#fff' : '#001f54';
        }

        var labels = {
            service: { type: 'Type of Service', time: 'Service Time', freq: 'Frequency', desc: 'Description' },
            product: { type: 'Product', time: 'Quantity', freq: 'Unit Price', desc: 'Description' }
        };
        var l = labels[mode] || labels.service;
        document.querySelectorAll('.editor-svc-header-type').forEach(function(el) { el.textContent = l.type; });
        document.querySelectorAll('.editor-svc-header-time').forEach(function(el) { el.textContent = l.time; });
        document.querySelectorAll('.editor-svc-header-freq').forEach(function(el) { el.textContent = l.freq; });
        document.querySelectorAll('.editor-svc-header-desc').forEach(function(el) { el.textContent = l.desc; });

        // Persist per request
        var reqId = document.getElementById('request_id');
        if (reqId && reqId.value) {
            localStorage.setItem('sales_mode_' + reqId.value, mode);
        }

        // Update preview if available
        if (typeof updatePreview === 'function') {
            updatePreview();
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        console.log('Editor module loaded');

        // Event listeners para botones
        document.getElementById('btn-save').addEventListener('click', saveRequest);
        document.getElementById('btn-mark-ready').addEventListener('click', markAsReady);
        document.getElementById('btn-mark-completed').addEventListener('click', markAsCompleted);
        document.getElementById('btn-download-pdf').addEventListener('click', downloadPDF);

        // Event listener para botón de Vent Hood Report
        const ventHoodBtn = document.getElementById('btn-vent-hood-report');
        if (ventHoodBtn) {
            ventHoodBtn.addEventListener('click', downloadVentHoodReport);
        }

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
            console.log('Task selected:', e.detail);
        });

        // Mostrar/ocultar campos específicos de Contract
        document.getElementById('Request_Type').addEventListener('change', function() {
            toggleContractFields();
            updatePreview();
        });

        // Auto-actualizar preview cuando cambian los datos del formulario
        const form = document.getElementById('editor-form');
        if (form) {
            form.addEventListener('input', debounce(updatePreview, 500));
            form.addEventListener('change', updatePreview);
        }

        // Add service row buttons
        var btnAddJan = document.getElementById('btn-add-janitorial');
        if (btnAddJan) btnAddJan.addEventListener('click', function() { addServiceRow('janitorial'); });
        var btnAddKit = document.getElementById('btn-add-kitchen');
        if (btnAddKit) btnAddKit.addEventListener('click', function() { addServiceRow('kitchen'); });
        var btnAddStaff = document.getElementById('btn-add-staff');
        if (btnAddStaff) btnAddStaff.addEventListener('click', function() { addStaffRow(); });

        // ========================================
        // REPORT TYPE SELECTOR - Toggle buttons
        // ========================================
        loadServiceReportConfig();
        initReportTypeSelector();
    });

    // ========================================
    // LOAD SERVICE REPORT CONFIG (once)
    // ========================================

    function loadServiceReportConfig() {
        fetch('controllers/get_service_config.php')
            .then(function(response) { return response.json(); })
            .then(function(result) {
                if (result.success) {
                    serviceReportConfig = result.data;
                    console.log('Service report config loaded:', Object.keys(serviceReportConfig));
                    // Expose globally so preview.js can access it
                    window.ServiceReportConfig = serviceReportConfig;
                } else {
                    console.error('Error loading service config:', result.error);
                }
            })
            .catch(function(error) {
                console.error('Failed to load service config:', error);
            });
    }

    // ========================================
    // REPORT TYPE SELECTOR - Init & handlers
    // ========================================

    function initReportTypeSelector() {
        var buttons = document.querySelectorAll('#report-type-options .report-type-btn');
        buttons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var serviceKey = btn.dataset.serviceKey;
                selectReportType(serviceKey);
            });
        });
    }

    /**
     * selectReportType(key)
     * Called when the user clicks a report-type toggle button.
     * - Highlights the active button
     * - Syncs the hidden Service_Type <select>
     * - Updates the document title
     * - Triggers preview update
     */
    function selectReportType(serviceKey) {
        // 1. Highlight the active button
        var buttons = document.querySelectorAll('#report-type-options .report-type-btn');
        buttons.forEach(function(btn) {
            if (btn.dataset.serviceKey === serviceKey) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // 2. Sync the Service_Type dropdown (hidden inside the form)
        var serviceTypeSelect = document.getElementById('Service_Type');
        if (serviceTypeSelect) {
            serviceTypeSelect.value = serviceKey;
        }

        // 3. Update the document title to reflect the service type
        var cfg = serviceReportConfig ? serviceReportConfig[serviceKey] : null;
        var title = cfg ? cfg.title : serviceKey;
        var companyName = document.getElementById('Company_Name');
        var companyLabel = (companyName && companyName.value) ? companyName.value : '';
        var docTitleEl = document.getElementById('doc-title');
        if (docTitleEl) {
            docTitleEl.textContent = companyLabel ? companyLabel + ' — ' + title : title;
        }

        // 4. Trigger preview update so the live preview renders the new service type
        updatePreview();
    }

    /**
     * syncReportTypeSelectorFromValue(serviceKey)
     * Utility: highlight the correct button when loading existing data.
     */
    function syncReportTypeSelectorFromValue(serviceKey) {
        if (!serviceKey) return;
        var buttons = document.querySelectorAll('#report-type-options .report-type-btn');
        buttons.forEach(function(btn) {
            if (btn.dataset.serviceKey === serviceKey) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

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
        fetch('controllers/get_request_detail.php?id=' + id)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    currentRequestData = data.data;
                    populateForm(data.data);
                    // Restore sales mode from localStorage
                    var savedMode = localStorage.getItem('sales_mode_' + id) || 'service';
                    setEditorSalesMode(savedMode);
                    updatePreview();
                } else {
                    alert('Error loading request: ' + data.error);
                }
            })
            .catch(function(error) {
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

        console.log('New request form created from task');
    }

    // ========================================
    // DETERMINE REQUEST TYPE FROM CATEGORY
    // ========================================

    function determineRequestType(categoryName, eventTitle) {
        if (!categoryName) {
            if (eventTitle) {
                var title = eventTitle.toUpperCase();
                if (title.includes('JWO') || title.startsWith('JWO-')) return 'JWO';
                if (title.includes('CONTRACT') || title.startsWith('C-')) return 'Contract';
                if (title.includes('PROPOSAL') || title.startsWith('P-')) return 'Proposal';
                if (title.includes('QUOTE') || title.startsWith('Q-')) return 'Quote';
            }
            return 'JWO';
        }

        var category = categoryName.toUpperCase();
        if (category.includes('JWO')) return 'JWO';
        if (category.includes('CONTRACT')) return 'Contract';
        if (category.includes('PROPOSAL')) return 'Proposal';
        if (category.includes('QUOTE')) return 'Quote';

        return 'JWO';
    }

    // ========================================
    // CLEAR FORM
    // ========================================

    function clearForm() {
        var form = document.getElementById('editor-form');
        if (form) {
            form.reset();
        }

        // Clear specific fields that might not reset properly
        var fields = [
            'Service_Type', 'Request_Type', 'Priority', 'Requested_Service', 'Seller',
            'Company_Name', 'Is_New_Client', 'client_name', 'Client_Name', 'Client_Title',
            'Email', 'Number_Phone', 'Company_Address',
            'Site_Visit_Conducted', 'Invoice_Frequency', 'Contract_Duration',
            'total18', 'taxes18', 'grand18',
            'total19', 'taxes19', 'grand19',
            'inflationAdjustment', 'totalArea', 'buildingsIncluded',
            'startDateServices', 'Site_Observation', 'Additional_Comments'
        ];

        fields.forEach(function(fieldName) {
            var field = document.getElementById(fieldName);
            if (field) {
                if (field.type === 'checkbox') {
                    field.checked = false;
                } else {
                    field.value = '';
                }
            }
        });

        // Clear report type selector
        var rtButtons = document.querySelectorAll('#report-type-options .report-type-btn');
        rtButtons.forEach(function(btn) { btn.classList.remove('active'); });

        // Hide all Section 4 blocks and clear table bodies
        var janitorialSection = document.getElementById('janitorial-section');
        var kitchenSection = document.getElementById('kitchen-section');
        var staffSection = document.getElementById('staff-section');
        if (janitorialSection) janitorialSection.style.display = 'none';
        if (kitchenSection) kitchenSection.style.display = 'none';
        if (staffSection) staffSection.style.display = 'none';

        var janBody = document.getElementById('janitorial-table-body');
        var kitBody = document.getElementById('kitchen-table-body');
        var staffBody = document.getElementById('staff-table-body');
        if (janBody) janBody.innerHTML = '';
        if (kitBody) kitBody.innerHTML = '';
        if (staffBody) staffBody.innerHTML = '';
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
        setValue('Seller', data.Seller);

        // Section 2: Client Info
        setValue('Company_Name', data.Company_Name);
        setValue('Is_New_Client', data.Is_New_Client);
        setValue('client_name', data.Client_Name || data.client_name);
        setValue('Client_Title', data.Client_Title);
        setValue('Email', data.Email);
        setValue('Number_Phone', data.Number_Phone);
        setValue('Company_Address', data.Company_Address);

        // Section 3: Operational
        setValue('Site_Visit_Conducted', data.Site_Visit_Conducted);
        setValue('Invoice_Frequency', data.Invoice_Frequency);
        setValue('Contract_Duration', data.Contract_Duration);

        // Section 4: Conditional pricing blocks
        // Only show blocks that are active in the Work Order
        displayJanitorialServices(data);
        displayKitchenServices(data);
        displayStaffSection(data);

        // Section 5: Contract Specific
        setValue('inflationAdjustment', data.inflationAdjustment);
        setValue('totalArea', data.totalArea);
        setValue('buildingsIncluded', data.buildingsIncluded);
        setValue('startDateServices', data.startDateServices);

        // Section 6: Observations
        setValue('Site_Observation', data.Site_Observation);
        setValue('Additional_Comments', data.Additional_Comments);

        // Sync report type selector bar
        syncReportTypeSelectorFromValue(data.Service_Type);

        // Update header - show company name with service type title
        var serviceTitle = '';
        if (serviceReportConfig && data.Service_Type && serviceReportConfig[data.Service_Type]) {
            serviceTitle = serviceReportConfig[data.Service_Type].title;
        }
        var headerLabel = data.Company_Name || 'Untitled Request';
        if (serviceTitle) {
            headerLabel = headerLabel + ' — ' + serviceTitle;
        }
        document.getElementById('doc-title').textContent = headerLabel;
        document.getElementById('doc-number').textContent = data.docnum || 'Not generated yet';

        // Update badge de tipo de documento en preview panel
        var previewDocType = document.getElementById('preview-doc-type');
        if (previewDocType) {
            previewDocType.textContent = data.Request_Type || 'Document';
        }

        // Mostrar/ocultar campos de Contract
        toggleContractFields();

        // Update button visibility based on status
        updateButtonVisibility(data.status);
    }

    // ========================================
    // SECTION 18: JANITORIAL SERVICES
    // Only shown if active in Work Order
    // ========================================

    function displayJanitorialServices(data) {
        var section = document.getElementById('janitorial-section');
        var tbody = document.getElementById('janitorial-table-body');
        if (!section || !tbody) return;

        // Determine if this block is active based on actual data
        var services = data.janitorial_services || [];
        var hasData = services.length > 0;

        if (!hasData) {
            section.style.display = 'none';
            tbody.innerHTML = '';
            return;
        }

        section.style.display = 'block';
        tbody.innerHTML = '';

        services.forEach(function(item) {
            var tr = createServiceRow('janitorial', item);
            tbody.appendChild(tr);
        });

        recalcServiceTotals('janitorial');
    }

    // ========================================
    // SECTION 19: HOODVENT & KITCHEN CLEANING
    // Only shown if active in Work Order
    // ========================================

    function displayKitchenServices(data) {
        var section = document.getElementById('kitchen-section');
        var tbody = document.getElementById('kitchen-table-body');
        if (!section || !tbody) return;

        // Combine kitchen + hood_vent items (both fall under Section 19)
        var kitchenItems = data.kitchen_services || [];
        var hoodVentItems = data.hood_vent_services || [];
        var services = kitchenItems.concat(hoodVentItems);
        var hasData = services.length > 0;

        if (!hasData) {
            section.style.display = 'none';
            tbody.innerHTML = '';
            return;
        }

        section.style.display = 'block';
        tbody.innerHTML = '';

        services.forEach(function(item) {
            var tr = createServiceRow('kitchen', item);
            tbody.appendChild(tr);
        });

        recalcServiceTotals('kitchen');
    }

    // ========================================
    // SECTION 20: INCLUDE STAFF?
    // Only shown if active in Work Order
    // ========================================

    function displayStaffSection(data) {
        var section = document.getElementById('staff-section');
        var tbody = document.getElementById('staff-table-body');
        if (!section || !tbody) return;

        var staffItems = data.contract_staff || [];
        var isActive = data.includeStaff === 'Yes' || staffItems.length > 0;

        if (!isActive) {
            section.style.display = 'none';
            tbody.innerHTML = '';
            return;
        }

        section.style.display = 'block';
        tbody.innerHTML = '';

        staffItems.forEach(function(item) {
            var tr = createStaffRow(item);
            tbody.appendChild(tr);
        });
    }

    // ========================================
    // CREATE EDITABLE SERVICE ROW
    // ========================================

    function createServiceRow(sectionType, item) {
        var tr = document.createElement('tr');
        tr.className = 'editor-service-row';
        // Store original category for hood_vent vs kitchen distinction
        tr.dataset.originalCategory = (item && item.category) ? item.category : sectionType;

        var serviceType = (item && (item.service_type || item.service_name)) || '';
        var serviceTime = (item && item.service_time) || '';
        var frequency = (item && item.frequency) || '';
        var description = (item && item.description) || '';
        var subtotal = (item && item.subtotal) ? parseFloat(item.subtotal).toFixed(2) : '';

        tr.innerHTML =
            '<td><input type="text" class="svc-input svc-type" value="' + escapeAttr(serviceType) + '" placeholder="Service type"></td>' +
            '<td><input type="text" class="svc-input svc-time" value="' + escapeAttr(serviceTime) + '" placeholder="Time"></td>' +
            '<td><input type="text" class="svc-input svc-freq" value="' + escapeAttr(frequency) + '" placeholder="Frequency"></td>' +
            '<td><input type="text" class="svc-input svc-desc" value="' + escapeAttr(description) + '" placeholder="Description"></td>' +
            '<td><input type="number" class="svc-input svc-subtotal" value="' + escapeAttr(subtotal) + '" placeholder="0.00" step="0.01" min="0"></td>' +
            '<td><button type="button" class="btn-remove-row" title="Remove">&times;</button></td>';

        // Event: recalculate on subtotal change
        var subtotalInput = tr.querySelector('.svc-subtotal');
        subtotalInput.addEventListener('input', function() {
            recalcServiceTotals(sectionType);
        });

        // Event: remove row
        tr.querySelector('.btn-remove-row').addEventListener('click', function() {
            tr.remove();
            recalcServiceTotals(sectionType);
        });

        return tr;
    }

    // ========================================
    // ADD EMPTY SERVICE ROW
    // ========================================

    function addServiceRow(sectionType) {
        var tbodyId = sectionType === 'janitorial' ? 'janitorial-table-body' : 'kitchen-table-body';
        var tbody = document.getElementById(tbodyId);
        if (!tbody) return;

        var tr = createServiceRow(sectionType, null);
        tbody.appendChild(tr);

        // Focus the first input of the new row
        var firstInput = tr.querySelector('input');
        if (firstInput) firstInput.focus();
    }

    // ========================================
    // CREATE EDITABLE STAFF ROW
    // ========================================

    function createStaffRow(item) {
        var tr = document.createElement('tr');
        tr.className = 'editor-service-row';

        var department = (item && item.department) || '';
        var position = (item && item.position) || '';
        var baseRate = (item && item.base_rate) ? parseFloat(item.base_rate).toFixed(2) : '';
        var pctIncrease = (item && item.percent_increase) ? parseFloat(item.percent_increase).toFixed(2) : '';
        var billRate = (item && item.bill_rate) ? parseFloat(item.bill_rate).toFixed(2) : '';

        tr.innerHTML =
            '<td><input type="text" class="svc-input staff-department" value="' + escapeAttr(department) + '" placeholder="e.g. Housekeeping"></td>' +
            '<td><input type="text" class="svc-input staff-position" value="' + escapeAttr(position) + '" placeholder="Position name"></td>' +
            '<td><input type="number" class="svc-input staff-base" value="' + escapeAttr(baseRate) + '" placeholder="0.00" step="0.01" min="0"></td>' +
            '<td><input type="number" class="svc-input staff-increase" value="' + escapeAttr(pctIncrease) + '" placeholder="0" step="0.01" min="0"></td>' +
            '<td><input type="number" class="svc-input staff-bill" value="' + escapeAttr(billRate) + '" readonly tabindex="-1"></td>' +
            '<td><button type="button" class="btn-remove-row" title="Remove">&times;</button></td>';

        // Recalculate bill rate when base or increase changes
        var baseInput = tr.querySelector('.staff-base');
        var increaseInput = tr.querySelector('.staff-increase');
        var billInput = tr.querySelector('.staff-bill');

        function recalcBill() {
            var base = parseFloat(baseInput.value) || 0;
            var inc = parseFloat(increaseInput.value) || 0;
            var bill = base + (base * inc / 100);
            billInput.value = bill > 0 ? bill.toFixed(2) : '';
        }

        baseInput.addEventListener('input', recalcBill);
        increaseInput.addEventListener('input', recalcBill);

        // Remove row
        tr.querySelector('.btn-remove-row').addEventListener('click', function() {
            tr.remove();
        });

        return tr;
    }

    // ========================================
    // ADD EMPTY STAFF ROW
    // ========================================

    function addStaffRow() {
        var tbody = document.getElementById('staff-table-body');
        if (!tbody) return;

        var tr = createStaffRow(null);
        tbody.appendChild(tr);

        var firstInput = tr.querySelector('input');
        if (firstInput) firstInput.focus();
    }

    // ========================================
    // RECALCULATE SERVICE TOTALS (8.25% tax)
    // ========================================

    function recalcServiceTotals(sectionType) {
        var tbodyId = sectionType === 'janitorial' ? 'janitorial-table-body' : 'kitchen-table-body';
        var suffix = sectionType === 'janitorial' ? '18' : '19';
        var tbody = document.getElementById(tbodyId);
        if (!tbody) return;

        var total = 0;
        var subtotalInputs = tbody.querySelectorAll('.svc-subtotal');
        subtotalInputs.forEach(function(input) {
            var val = parseFloat(input.value);
            if (!isNaN(val)) total += val;
        });

        var taxes = total * 0.0825;
        var grand = total + taxes;

        setValue('total' + suffix, total > 0 ? total.toFixed(2) : '');
        setValue('taxes' + suffix, taxes > 0 ? taxes.toFixed(2) : '');
        setValue('grand' + suffix, grand > 0 ? grand.toFixed(2) : '');
    }

    // ========================================
    // COLLECT SERVICE TABLE DATA
    // ========================================

    function collectServiceTableData(sectionType) {
        var tbodyId = sectionType === 'janitorial' ? 'janitorial-table-body' : 'kitchen-table-body';
        var tbody = document.getElementById(tbodyId);
        if (!tbody) return [];

        var items = [];
        var rows = tbody.querySelectorAll('tr.editor-service-row');
        rows.forEach(function(tr) {
            var serviceType = tr.querySelector('.svc-type').value.trim();
            var serviceTime = tr.querySelector('.svc-time').value.trim();
            var frequency = tr.querySelector('.svc-freq').value.trim();
            var description = tr.querySelector('.svc-desc').value.trim();
            var subtotal = tr.querySelector('.svc-subtotal').value.trim();
            var originalCategory = tr.dataset.originalCategory || sectionType;

            // Only include rows that have at least a service type or subtotal
            if (serviceType || subtotal) {
                items.push({
                    category: originalCategory,
                    service_type: serviceType,
                    service_time: serviceTime,
                    frequency: frequency,
                    description: description,
                    subtotal: subtotal || '0.00'
                });
            }
        });
        return items;
    }

    // ========================================
    // COLLECT STAFF TABLE DATA
    // ========================================

    function collectStaffTableData() {
        var tbody = document.getElementById('staff-table-body');
        if (!tbody) return [];

        var items = [];
        var rows = tbody.querySelectorAll('tr.editor-service-row');
        rows.forEach(function(tr) {
            var department = tr.querySelector('.staff-department').value.trim();
            var position = tr.querySelector('.staff-position').value.trim();
            var baseRate = tr.querySelector('.staff-base').value.trim();
            var pctIncrease = tr.querySelector('.staff-increase').value.trim();
            var billRate = tr.querySelector('.staff-bill').value.trim();

            if (position || baseRate) {
                items.push({
                    department: department,
                    position: position,
                    base_rate: baseRate || '0.00',
                    percent_increase: pctIncrease || '0.00',
                    bill_rate: billRate || '0.00'
                });
            }
        });
        return items;
    }

    // ========================================
    // PARSE JSON SAFELY
    // ========================================

    function parseJSONSafe(value) {
        if (!value) return [];
        if (Array.isArray(value)) return value;
        try {
            return JSON.parse(value);
        } catch (e) {
            return [];
        }
    }

    // ========================================
    // GUARDAR CAMBIOS
    // ========================================

    function saveRequest() {
        console.log('Saving request...');

        var requestId = document.getElementById('request_id').value;
        if (!requestId || requestId.trim() === '') {
            showNotification('No active request selected. Please select a task before saving.', 'error');
            return;
        }

        var formData = getFormData();
        formData.id = requestId;

        // Collect editable service data from tables
        formData.contract_items_janitorial = collectServiceTableData('janitorial');
        formData.contract_items_kitchen = collectServiceTableData('kitchen');
        formData.contract_staff = collectStaffTableData();

        // Include staff flag based on staff section visibility
        var staffSection = document.getElementById('staff-section');
        if (staffSection && staffSection.style.display !== 'none') {
            formData.includeStaff = 'Yes';
        }

        fetch('controllers/update_request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                showNotification('Saved successfully', 'success');
                // Update currentRequestData with the latest from tables
                // so preview stays in sync
                if (currentRequestData) {
                    currentRequestData.janitorial_services = collectServiceTableData('janitorial');
                    var kitchenData = collectServiceTableData('kitchen');
                    var kitItems = [];
                    var hvItems = [];
                    kitchenData.forEach(function(item) {
                        if (item.category === 'hood_vent') {
                            hvItems.push(item);
                        } else {
                            kitItems.push(item);
                        }
                    });
                    currentRequestData.kitchen_services = kitItems;
                    currentRequestData.hood_vent_services = hvItems;
                    currentRequestData.contract_staff = collectStaffTableData();
                }
                updatePreview();
                window.InboxModule.refresh();
            } else {
                showNotification('Error: ' + data.error, 'error');
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            showNotification('Connection error', 'error');
        });
    }

    // ========================================
    // MARCAR COMO LISTO
    // ========================================

    function markAsReady() {
        var requestId = document.getElementById('request_id').value;

        if (!requestId || requestId.trim() === '') {
            showNotification('Please save the request first before marking it as ready.', 'error');
            return;
        }

        if (!confirm('Are you sure you want to mark this request as READY? This will generate the DOCNUM.')) {
            return;
        }

        console.log('Marking request as ready:', requestId);

        fetch('controllers/mark_ready.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ request_id: requestId })
        })
        .then(function(response) {
            console.log('Mark ready response status:', response.status);
            return response.json();
        })
        .then(function(data) {
            console.log('Mark ready response data:', data);
            if (data.success) {
                showNotification('Marked as ready! DOCNUM: ' + data.docnum, 'success');
                document.getElementById('doc-number').textContent = data.docnum;
                console.log('Refreshing inbox...');
                window.InboxModule.refresh();
            } else {
                showNotification('Error: ' + data.error, 'error');
            }
        })
        .catch(function(error) {
            console.error('Error marking as ready:', error);
            showNotification('Connection error: ' + error.message, 'error');
        });
    }

    // ========================================
    // MARCAR COMO COMPLETED (Triggers billing flow)
    // ========================================

    function markAsCompleted() {
        var requestId = document.getElementById('request_id').value;

        if (!requestId || requestId.trim() === '') {
            showNotification('Please save the request first.', 'error');
            return;
        }

        if (!confirm('Are you sure you want to mark this contract as COMPLETED?\n\nThis will:\n- Generate the final immutable PDF\n- Automatically send it to Accounting as Pending\n\nThis action cannot be undone.')) {
            return;
        }

        console.log('Marking request as completed:', requestId);

        var btn = document.getElementById('btn-mark-completed');
        var originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        btn.disabled = true;

        fetch('controllers/mark_completed.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ request_id: requestId, sales_mode: editorSalesMode })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                showNotification('Contract completed! Final PDF generated and sent to Accounting.', 'success');
                btn.style.display = 'none';
                document.getElementById('btn-mark-ready').style.display = 'none';
                document.getElementById('btn-save').style.display = 'none';
                window.InboxModule.refresh();
            } else {
                showNotification('Error: ' + data.error, 'error');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        })
        .catch(function(error) {
            console.error('Error marking as completed:', error);
            showNotification('Connection error: ' + error.message, 'error');
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }

    // ========================================
    // UPDATE BUTTON VISIBILITY BASED ON STATUS
    // ========================================

    function updateButtonVisibility(status) {
        var btnSave = document.getElementById('btn-save');
        var btnMarkReady = document.getElementById('btn-mark-ready');
        var btnMarkCompleted = document.getElementById('btn-mark-completed');
        var btnDownloadPdf = document.getElementById('btn-download-pdf');

        if (status === 'completed') {
            btnSave.style.display = 'none';
            btnMarkReady.style.display = 'none';
            btnMarkCompleted.style.display = 'none';
            btnDownloadPdf.style.display = 'flex';
        } else if (status === 'ready') {
            btnSave.style.display = 'flex';
            btnMarkReady.style.display = 'none';
            btnMarkCompleted.style.display = 'flex';
            btnDownloadPdf.style.display = 'flex';
        } else {
            btnSave.style.display = 'flex';
            btnMarkReady.style.display = 'flex';
            btnMarkCompleted.style.display = 'none';
            btnDownloadPdf.style.display = 'flex';
        }
    }

    // ========================================
    // DESCARGAR PDF
    // ========================================

    function downloadPDF() {
        var requestId = document.getElementById('request_id').value;

        if (!requestId) {
            alert('No request selected');
            return;
        }

        window.open('controllers/generate_pdf.php?id=' + requestId + '&sales_mode=' + encodeURIComponent(editorSalesMode), '_blank');
    }

    // ========================================
    // DESCARGAR VENT HOOD REPORT PDF
    // ========================================

    function downloadVentHoodReport() {
        var requestId = document.getElementById('request_id').value;

        if (!requestId) {
            alert('Please select a request first to generate the Vent Hood Report');
            return;
        }

        window.open('vent_hood_editor.php?id=' + requestId, '_blank');
    }

    // ========================================
    // PREVIEW
    // ========================================

    function updatePreview() {
        if (window.PreviewModule) {
            var formData = getFormData();

            // Build live service data from editor tables
            var janItems = collectServiceTableData('janitorial');
            if (janItems.length) formData.janitorial_services = janItems;

            var kitchenAllItems = collectServiceTableData('kitchen');
            if (kitchenAllItems.length) {
                var kitItems = [];
                var hvItems = [];
                kitchenAllItems.forEach(function(item) {
                    if (item.category === 'hood_vent') {
                        hvItems.push(item);
                    } else {
                        kitItems.push(item);
                    }
                });
                if (kitItems.length) formData.kitchen_services = kitItems;
                if (hvItems.length) formData.hood_vent_services = hvItems;
            }

            var staffItems = collectStaffTableData();
            if (staffItems.length) formData.contract_staff = staffItems;

            // Carry over non-editable data from loaded request
            if (currentRequestData) {
                // Fallback service data if tables are empty (shouldn't happen normally)
                if (!formData.janitorial_services && currentRequestData.janitorial_services) {
                    formData.janitorial_services = currentRequestData.janitorial_services;
                }
                if (!formData.kitchen_services && currentRequestData.kitchen_services) {
                    formData.kitchen_services = currentRequestData.kitchen_services;
                }
                if (!formData.hood_vent_services && currentRequestData.hood_vent_services) {
                    formData.hood_vent_services = currentRequestData.hood_vent_services;
                }

                // Fallback: split contract_items if separate arrays aren't available
                if (!formData.janitorial_services && !formData.kitchen_services && !formData.hood_vent_services
                    && currentRequestData.contract_items && Array.isArray(currentRequestData.contract_items)) {
                    var jItems = [], kItems = [], hItems = [];
                    currentRequestData.contract_items.forEach(function(item) {
                        switch (item.category) {
                            case 'janitorial': jItems.push(item); break;
                            case 'kitchen': kItems.push(item); break;
                            case 'hood_vent': hItems.push(item); break;
                        }
                    });
                    if (jItems.length) formData.janitorial_services = jItems;
                    if (kItems.length) formData.kitchen_services = kItems;
                    if (hItems.length) formData.hood_vent_services = hItems;
                }

                // Scope data
                if (currentRequestData.scope_of_work_tasks) {
                    formData.scope_of_work_tasks = currentRequestData.scope_of_work_tasks;
                }
                if (currentRequestData.Scope_Of_Work) {
                    formData.Scope_Of_Work = currentRequestData.Scope_Of_Work;
                }
                if (currentRequestData.scope_sections) {
                    formData.scope_sections = currentRequestData.scope_sections;
                }
                if (currentRequestData.Scope_Sections) {
                    formData.Scope_Sections = currentRequestData.Scope_Sections;
                }

                // Pricing fields not in form HTML but needed by preview
                if (currentRequestData.PriceInput && !formData.PriceInput) {
                    formData.PriceInput = currentRequestData.PriceInput;
                }
                if (currentRequestData.total_cost && !formData.total_cost) {
                    formData.total_cost = currentRequestData.total_cost;
                }

                // Document number
                if (currentRequestData.docnum && !formData.docnum) {
                    formData.docnum = currentRequestData.docnum;
                }

                // Include staff flag
                if (currentRequestData.includeStaff) {
                    formData.includeStaff = currentRequestData.includeStaff;
                }

                // Legacy JSON array fields for backwards compatibility
                ['type18','write18','time18','freq18','desc18','subtotal18',
                 'type19','time19','freq19','desc19','subtotal19',
                 'base_staff','increase_staff','bill_staff'].forEach(function(field) {
                    if (currentRequestData[field] && Array.isArray(currentRequestData[field])) {
                        formData[field] = currentRequestData[field];
                    }
                });
            }

            // Attach service config for the selected type so preview can render dynamic sections
            if (serviceReportConfig && formData.Service_Type && serviceReportConfig[formData.Service_Type]) {
                formData._serviceConfig = serviceReportConfig[formData.Service_Type];
            }

            window.PreviewModule.render(formData);
        }
    }

    // ========================================
    // UTILIDADES
    // ========================================

    function setValue(fieldId, value) {
        var field = document.getElementById(fieldId);
        if (field) {
            field.value = value || '';
        }
    }

    function getFormData() {
        var form = document.getElementById('editor-form');
        var formData = new FormData(form);
        var data = {};

        formData.forEach(function(value, key) {
            data[key] = value;
        });

        return data;
    }

    function toggleContractFields() {
        var requestType = document.getElementById('Request_Type').value;
        var contractSection = document.getElementById('contract-specific-section');
        var staffSection = document.getElementById('staff-section');

        if (requestType === 'Contract' || requestType === 'Proposal') {
            contractSection.style.display = 'block';
            // Show staff section for Proposal type even if no data loaded yet
            if (requestType === 'Proposal' && staffSection && staffSection.style.display === 'none') {
                staffSection.style.display = 'block';
            }
        } else {
            contractSection.style.display = 'none';
        }
    }

    function displayScope(scopeArray) {
        var scopeDisplay = document.getElementById('scope-display');

        if (!scopeArray || scopeArray.length === 0) {
            scopeDisplay.innerHTML = '<p style="color: var(--text-gray);">No scope of work defined</p>';
            return;
        }

        scopeDisplay.innerHTML = scopeArray.map(function(item) {
            return '<div class="scope-item"><i class="fas fa-check-circle"></i><span>' + escapeHtml(item) + '</span></div>';
        }).join('');
    }

    function showNotification(message, type) {
        alert(message);
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function escapeAttr(text) {
        if (!text) return '';
        return String(text).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function debounce(func, wait) {
        var timeout;
        return function() {
            var args = arguments;
            var context = this;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }

})();
