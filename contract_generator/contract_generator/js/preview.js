/**
 * PREVIEW.JS
 * Renderiza el preview en vivo del documento
 * (Se actualizará cuando reciba los templates de los 4 formatos)
 */

(function() {
    'use strict';

    // ========================================
    // INICIALIZACIÓN
    // ========================================

    document.addEventListener('DOMContentLoaded', function() {
        console.log('👁️ Preview module loaded');
    });

    // ========================================
    // RENDER PREVIEW
    // ========================================

    function render(data) {
        // Support both old and new preview content IDs
        const previewContent = document.getElementById('live-preview-content') || document.getElementById('preview-content');

        if (!previewContent) {
            console.warn('Preview content element not found');
            return;
        }

        if (!data || !data.Request_Type) {
            previewContent.innerHTML = `
                <div class="preview-loading">
                    <i class="fas fa-file-alt"></i>
                    <p>No data to preview</p>
                </div>
            `;
            return;
        }

        // Show loading state
        previewContent.innerHTML = `
            <div class="preview-loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Updating preview...</p>
            </div>
        `;

        // Determinar qué template usar
        const requestType = data.Request_Type.toLowerCase();
        let html = '';

        switch(requestType) {
            case 'quote':
                html = renderQuote(data);
                break;
            case 'jwo':
                html = renderJWO(data);
                break;
            case 'proposal':
                html = renderProposal(data);
                break;
            case 'contract':
                html = renderContract(data);
                break;
            default:
                html = '<p>Unknown document type</p>';
        }

        // Small delay to show loading state
        setTimeout(() => {
            previewContent.innerHTML = html;
        }, 100);
    }

    // ========================================
    // TEMPLATES (Placeholders - se actualizarán)
    // ========================================

    function renderQuote(data) {
        return `
            <div class="document-preview">
                <div class="doc-header-preview">
                    <h1>QUOTATION</h1>
                    <p class="doc-number">${data.docnum || 'DRAFT'}</p>
                </div>
                
                <div class="doc-section">
                    <h2>Client Information</h2>
                    <p><strong>Company Name:</strong> ${data.Company_Name || 'N/A'}</p>
                    <p><strong>Client Name:</strong> ${data.client_name || data.Client_Name || 'N/A'}</p>
                    <p><strong>Email:</strong> ${data.Email || 'N/A'}</p>
                    <p><strong>Phone:</strong> ${data.Number_Phone || 'N/A'}</p>
                </div>

                <div class="doc-section">
                    <h2>Service Details</h2>
                    <p><strong>Service Type:</strong> ${data.Service_Type || 'N/A'}</p>
                    <p><strong>Requested Service:</strong> ${data.Requested_Service || 'N/A'}</p>
                </div>

                <div class="doc-section">
                    <h2>Pricing</h2>
                    <p class="price-display"><strong>Total Price:</strong> $${formatPrice(data.Total_Price)} ${data.Currency || 'USD'}</p>
                </div>

                <div class="doc-footer">
                    <p><em>This is a placeholder template. Final template will be provided.</em></p>
                </div>
            </div>
        `;
    }

    function renderJWO(data) {
        // Use Company_Address or fallback (matching PDF template)
        const companyAddress = data.Company_Address || 'N/A';

        // Payment terms mapping (same as PDF)
        const termsMap = {
            '15': 'Net 15',
            '30': 'Net 30',
            '50_deposit': '50% Deposit',
            'completion': 'Upon Completion'
        };
        const paymentTerms = termsMap[data.Invoice_Frequency] || 'Upon Completion';

        // Get contact info with fallbacks (matching PDF field names)
        const clientName = data.client_name || data.Client_Name || 'N/A';
        const clientTitle = data.Client_Title || '';
        const clientEmail = data.Email || 'N/A';
        const clientPhone = data.Number_Phone || 'N/A';
        const seller = data.Seller || 'N/A';
        const companyName = data.Company_Name || 'N/A';
        const department = data.Service_Type || 'N/A';
        const woNumber = data.docnum || 'DRAFT';
        const workDate = new Date().toLocaleDateString('en-US');
        const requestedService = data.Requested_Service || 'Service';

        // ========================================
        // BUILD SERVICE ROWS from DB detail tables
        // ========================================
        let serviceRows = [];
        let runningTotal = 0;
        let hasDetailServices = false;

        // --- Janitorial Services (from detail table or JSON arrays) ---
        if (data.janitorial_services && Array.isArray(data.janitorial_services) && data.janitorial_services.length > 0) {
            hasDetailServices = true;
            data.janitorial_services.forEach(function(svc) {
                const svcSub = parseFloat(svc.subtotal || 0);
                runningTotal += svcSub;
                serviceRows.push({
                    type: svc.service_type || 'Janitorial',
                    time: svc.service_time || '',
                    freq: svc.frequency || '',
                    desc: svc.description || '',
                    subtotal: svcSub
                });
            });
        } else if (data.includeJanitorial === 'Yes' && data.type18 && Array.isArray(data.type18)) {
            hasDetailServices = true;
            data.type18.forEach(function(type, i) {
                if (!type) return;
                const svcSub = parseFloat((data.subtotal18 && data.subtotal18[i]) || 0);
                runningTotal += svcSub;
                serviceRows.push({
                    type: type,
                    time: (data.time18 && data.time18[i]) || '',
                    freq: (data.freq18 && data.freq18[i]) || '',
                    desc: (data.desc18 && data.desc18[i]) || '',
                    subtotal: svcSub
                });
            });
        }

        // --- Kitchen Cleaning Services ---
        if (data.kitchen_services && Array.isArray(data.kitchen_services) && data.kitchen_services.length > 0) {
            hasDetailServices = true;
            data.kitchen_services.forEach(function(svc) {
                const svcSub = parseFloat(svc.subtotal || 0);
                runningTotal += svcSub;
                serviceRows.push({
                    type: svc.service_type || 'Kitchen Cleaning',
                    time: svc.service_time || '',
                    freq: svc.frequency || '',
                    desc: svc.description || '',
                    subtotal: svcSub
                });
            });
        } else if (data.includeKitchen === 'Yes' && data.type19 && Array.isArray(data.type19)) {
            hasDetailServices = true;
            data.type19.forEach(function(type, i) {
                if (!type) return;
                const svcSub = parseFloat((data.subtotal19 && data.subtotal19[i]) || 0);
                runningTotal += svcSub;
                serviceRows.push({
                    type: type,
                    time: (data.time19 && data.time19[i]) || '',
                    freq: (data.freq19 && data.freq19[i]) || '',
                    desc: (data.desc19 && data.desc19[i]) || '',
                    subtotal: svcSub
                });
            });
        }

        // --- Hood Vent Services ---
        if (data.hood_vent_services && Array.isArray(data.hood_vent_services) && data.hood_vent_services.length > 0) {
            hasDetailServices = true;
            data.hood_vent_services.forEach(function(svc) {
                const svcSub = parseFloat(svc.subtotal || 0);
                runningTotal += svcSub;
                serviceRows.push({
                    type: svc.service_type || 'Hood Vent',
                    time: svc.service_time || '',
                    freq: svc.frequency || '',
                    desc: svc.description || '',
                    subtotal: svcSub
                });
            });
        }

        // Calculate totals
        let totalAmount;
        if (hasDetailServices && runningTotal > 0) {
            totalAmount = runningTotal;
        } else {
            totalAmount = parseFloat(data.Total_Price || data.Prime_Quoted_Price || data.PriceInput || 0);
        }

        // If no detail service rows, create a single generic row
        if (serviceRows.length === 0) {
            let serviceDescription = '';
            if (data.Site_Observation) {
                serviceDescription = escapeHtml(data.Site_Observation);
            } else {
                serviceDescription = 'Professional service as per client requirements.';
            }
            serviceRows.push({
                type: requestedService,
                time: data.Service_Time || 'One Day',
                freq: data.Service_Frequency || 'One Time',
                desc: serviceDescription,
                subtotal: totalAmount
            });
        }

        const taxRate = 0.0825;
        const taxes = totalAmount * taxRate;
        const grandTotal = totalAmount + taxes;

        // Build service rows HTML
        let serviceRowsHtml = '';
        serviceRows.forEach(function(row) {
            serviceRowsHtml += `
                <tr>
                    <td class="service-desc">${escapeHtml(row.type)}</td>
                    <td>${escapeHtml(row.time)}</td>
                    <td>${escapeHtml(row.freq)}</td>
                    <td class="service-desc">${escapeHtml(row.desc)}</td>
                    <td class="amount">$${formatPrice(row.subtotal)}</td>
                </tr>
            `;
        });

        // ========================================
        // BUILD SCOPE OF WORK
        // ========================================
        let scopeWorkHtml = '';
        if (data.scope_of_work_tasks && Array.isArray(data.scope_of_work_tasks) && data.scope_of_work_tasks.length > 0) {
            scopeWorkHtml = '<ul>' + data.scope_of_work_tasks.map(function(task) {
                return '<li>' + escapeHtml(task) + '</li>';
            }).join('') + '</ul>';
        } else if (data.Scope_Of_Work && Array.isArray(data.Scope_Of_Work) && data.Scope_Of_Work.length > 0) {
            scopeWorkHtml = '<ul>' + data.Scope_Of_Work.map(function(task) {
                return '<li>' + escapeHtml(task) + '</li>';
            }).join('') + '</ul>';
        } else {
            scopeWorkHtml = `
                <ul>
                    <li>Professional service as per client requirements</li>
                    <li>All work performed to industry standards with quality assurance</li>
                    <li>Final inspection to ensure satisfactory completion</li>
                </ul>
            `;
        }

        // Determine logo based on Service_Type (same as PDF)
        const deptLower = (data.Service_Type || '').toLowerCase();
        const logoSrc = deptLower.includes('hospitality') ? '/sales/Images/phospitality.png' : '/sales/Images/pfacility.png';

        return `
            <div class="document-preview jwo-preview-exact">
                <style>
                    .jwo-preview-exact {
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 10pt;
                        color: #000;
                        line-height: 1.3;
                        background: white;
                        padding: 20px;
                    }

                    /* Header - Exact match to PDF */
                    .jwo-header-exact {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        border-bottom: 3px solid #AC1E34;
                        padding-bottom: 10px;
                        margin-bottom: 10px;
                    }
                    .jwo-header-exact img {
                        max-height: 70px;
                        width: auto;
                    }
                    .jwo-header-exact .title-section {
                        text-align: left;
                        padding-left: 15px;
                    }
                    .jwo-header-exact .doc-title {
                        color: #AC1E34;
                        font-size: 22pt;
                        font-weight: bold;
                        margin-bottom: 5px;
                    }
                    .jwo-header-exact .doc-subtitle {
                        font-size: 10pt;
                        color: #000;
                        font-style: italic;
                    }

                    /* 7 Column Info Table - Exact match to PDF */
                    .jwo-info-columns {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 15px;
                        font-size: 7pt;
                    }
                    .jwo-info-columns td {
                        padding: 3px 5px;
                        vertical-align: top;
                        border: none;
                    }
                    .jwo-info-columns .col-header {
                        font-weight: bold;
                        text-transform: uppercase;
                        font-size: 7pt;
                        padding-bottom: 3px;
                        text-align: center;
                    }
                    .jwo-info-columns .col-content {
                        font-size: 7pt;
                        line-height: 1.3;
                        text-align: center;
                    }

                    /* Services Table - Exact match to PDF */
                    .jwo-services-exact {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 10px;
                    }
                    .jwo-services-exact th {
                        background-color: #AC1E34;
                        color: white;
                        font-weight: bold;
                        padding: 8px 6px;
                        text-align: center;
                        border: 1px solid #000;
                        font-size: 8pt;
                        text-transform: uppercase;
                    }
                    .jwo-services-exact td {
                        border: 1px solid #000;
                        padding: 6px 8px;
                        text-align: center;
                        font-size: 8pt;
                    }
                    .jwo-services-exact .service-desc {
                        text-align: left;
                    }
                    .jwo-services-exact .amount {
                        text-align: right;
                        font-weight: bold;
                    }

                    /* Totals Table - Exact match to PDF (3 rows only) */
                    .jwo-totals-exact {
                        width: 250px;
                        border-collapse: collapse;
                        margin-left: auto;
                        margin-bottom: 15px;
                    }
                    .jwo-totals-exact td {
                        padding: 6px 12px;
                        font-size: 9pt;
                        border: 1px solid #000;
                    }
                    .jwo-totals-exact .label-cell {
                        text-align: left;
                        font-weight: bold;
                        background-color: #f5f5f5;
                        text-transform: uppercase;
                        width: 120px;
                    }
                    .jwo-totals-exact .value-cell {
                        text-align: right;
                        width: 130px;
                        background-color: #fff;
                        font-weight: bold;
                    }
                    .jwo-totals-exact tr:last-child .label-cell,
                    .jwo-totals-exact tr:last-child .value-cell {
                        background-color: #AC1E34;
                        color: white;
                    }

                    /* Scope Section - Exact match to PDF */
                    .jwo-scope-exact {
                        margin-bottom: 15px;
                    }
                    .jwo-scope-header-exact {
                        background-color: #AC1E34;
                        color: white;
                        font-weight: bold;
                        padding: 6px 10px;
                        margin-bottom: 8px;
                        font-size: 9pt;
                        text-transform: uppercase;
                    }
                    .jwo-scope-content-exact {
                        border: 1px solid #000;
                        padding: 10px;
                        background-color: #fff;
                    }
                    .jwo-scope-content-exact h4 {
                        font-size: 9pt;
                        font-weight: bold;
                        margin: 8px 0 4px 0;
                        text-decoration: underline;
                        text-transform: uppercase;
                    }
                    .jwo-scope-content-exact ul {
                        margin-left: 20px;
                        margin-bottom: 8px;
                    }
                    .jwo-scope-content-exact li {
                        margin-bottom: 3px;
                        font-size: 9pt;
                    }
                    .jwo-scope-content-exact p {
                        margin-bottom: 6px;
                        font-size: 9pt;
                    }

                    /* Preview note */
                    .jwo-preview-note {
                        margin-top: 20px;
                        padding: 10px;
                        background: #f0f0f0;
                        border: 1px dashed #999;
                        text-align: center;
                        font-size: 8pt;
                        color: #666;
                    }
                </style>

                <!-- HEADER - Exact match to PDF -->
                <div class="jwo-header-exact">
                    <img src="${logoSrc}" alt="Prime Facility Services Group" onerror="this.style.display='none'">
                    <div class="title-section">
                        <div class="doc-title">JOB WORK ORDER</div>
                        <div class="doc-subtitle">"The best services in the industry or nothing at all"</div>
                    </div>
                </div>

                <!-- CLIENT & WORK INFO - 7 COLUMNS (Exact match to PDF) -->
                <table class="jwo-info-columns">
                    <tr>
                        <td class="col-header">BILL TO</td>
                        <td class="col-header">WORK SITE</td>
                        <td class="col-header">SALES PERSON</td>
                        <td class="col-header">WORK DATE</td>
                        <td class="col-header">DEPARTMENT</td>
                        <td class="col-header">PAYMENT TERMS</td>
                        <td class="col-header">W.O. NO.</td>
                    </tr>
                    <tr>
                        <td class="col-content">
                            ${escapeHtml(clientName)}<br>
                            ${clientTitle ? escapeHtml(clientTitle) + '<br>' : ''}
                            ${escapeHtml(clientEmail)}<br>
                            ${escapeHtml(clientPhone)}
                        </td>
                        <td class="col-content">
                            ${escapeHtml(companyName)}<br>
                            ${escapeHtml(companyAddress)}
                        </td>
                        <td class="col-content">${escapeHtml(seller)}</td>
                        <td class="col-content">${workDate}</td>
                        <td class="col-content">${escapeHtml(department)}</td>
                        <td class="col-content">${paymentTerms}</td>
                        <td class="col-content">${woNumber || '-'}</td>
                    </tr>
                </table>

                <!-- SERVICES TABLE - All services from DB -->
                <table class="jwo-services-exact">
                    <thead>
                        <tr>
                            <th style="width: 25%;">TYPE OF SERVICES</th>
                            <th style="width: 12%;">SERVICE TIME</th>
                            <th style="width: 12%;">FREQUENCY</th>
                            <th style="width: 36%;">SERVICE DESCRIPTION</th>
                            <th style="width: 15%;">SUBTOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${serviceRowsHtml}
                    </tbody>
                </table>

                <!-- TOTALS TABLE - Exact match to PDF (3 rows only) -->
                <table class="jwo-totals-exact">
                    <tr>
                        <td class="label-cell">TOTAL</td>
                        <td class="value-cell">$${formatPrice(totalAmount)}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">TAXES (8.25%)</td>
                        <td class="value-cell">$${formatPrice(taxes)}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">GRAND TOTAL</td>
                        <td class="value-cell">$${formatPrice(grandTotal)}</td>
                    </tr>
                </table>

                <!-- SCOPE OF WORK - Exact match to PDF -->
                <div class="jwo-scope-exact">
                    <div class="jwo-scope-header-exact">
                        SCOPE OF WORK - ${escapeHtml(requestedService).toUpperCase()}
                    </div>
                    <div class="jwo-scope-content-exact">
                        <h4>WORK TO BE PERFORMED:</h4>
                        ${scopeWorkHtml}

                        ${data.Additional_Comments ? `
                            <h4>ADDITIONAL NOTES:</h4>
                            <p>${escapeHtml(data.Additional_Comments).replace(/\n/g, '<br>')}</p>
                        ` : ''}
                    </div>
                </div>

                <!-- Preview Note -->
                <div class="jwo-preview-note">
                    <strong>PREVIEW</strong> - The PDF includes Terms & Conditions (page 2) and signature sections.
                </div>
            </div>
        `;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    }

    function renderProposal(data) {
        return `
            <div class="document-preview">
                <div class="doc-header-preview">
                    <h1>SERVICE PROPOSAL</h1>
                    <p class="doc-number">${data.docnum || 'DRAFT'}</p>
                </div>
                
                <div class="doc-section">
                    <h2>Proposal For</h2>
                    <p><strong>${data.Company_Name || 'N/A'}</strong></p>
                    <p>${data.Address || 'N/A'}</p>
                    <p>${data.City || ''}, ${data.State || ''} ${data.Zip_Code || ''}</p>
                </div>

                <div class="doc-section">
                    <h2>Proposed Services</h2>
                    <p><strong>Service Type:</strong> ${data.Service_Type || 'N/A'}</p>
                    <p><strong>Specific Service:</strong> ${data.Requested_Service || 'N/A'}</p>
                    <p><strong>Duration:</strong> ${formatDuration(data.Contract_Duration)}</p>
                </div>

                <div class="doc-section">
                    <h2>Investment</h2>
                    <p class="price-display"><strong>Total:</strong> $${formatPrice(data.Total_Price)} ${data.Currency || 'USD'}</p>
                </div>

                <div class="doc-footer">
                    <p><em>This is a placeholder template. Final template will be provided.</em></p>
                </div>
            </div>
        `;
    }

    function renderContract(data) {
        return `
            <div class="document-preview">
                <div class="doc-header-preview">
                    <h1>SERVICE CONTRACT</h1>
                    <p class="doc-number">${data.docnum || 'DRAFT'}</p>
                </div>
                
                <div class="doc-section">
                    <h2>Contract Between</h2>
                    <p><strong>Client:</strong> ${data.Company_Name || 'N/A'}</p>
                    <p><strong>Service Provider:</strong> [Company Name]</p>
                </div>

                <div class="doc-section">
                    <h2>Contract Terms</h2>
                    <p><strong>Start Date:</strong> ${data.startDateServices || 'N/A'}</p>
                    <p><strong>Duration:</strong> ${formatDuration(data.Contract_Duration)}</p>
                    <p><strong>Total Area:</strong> ${data.totalArea || 'N/A'} sq ft</p>
                    <p><strong>Buildings:</strong> ${data.buildingsIncluded || 'N/A'}</p>
                    <p><strong>Inflation Adjustment:</strong> ${data.inflationAdjustment || 'N/A'}</p>
                </div>

                <div class="doc-section">
                    <h2>Financial Terms</h2>
                    <p><strong>Contract Value:</strong> $${formatPrice(data.Total_Price)} ${data.Currency || 'USD'}</p>
                    <p><strong>Invoice Frequency:</strong> ${formatFrequency(data.Invoice_Frequency)}</p>
                </div>

                <div class="doc-footer">
                    <p><em>This is a placeholder template. Final template will be provided.</em></p>
                </div>
            </div>
        `;
    }

    // ========================================
    // UTILIDADES
    // ========================================

    function formatPrice(price) {
        if (!price) return '0.00';
        return parseFloat(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function formatFrequency(freq) {
        const map = {
            '15': 'Every 15 days',
            '30': 'Every 30 days',
            '50_deposit': '50% Deposit / 50% Upon Completion',
            'completion': 'Payment Upon Completion'
        };
        return map[freq] || freq || 'N/A';
    }

    function formatDuration(duration) {
        const map = {
            '6_months': '6 Months',
            '1_year': '1 Year',
            '1_5_years': '1.5 Years (18 Months)',
            '2_years': '2 Years',
            '3_years': '3 Years',
            '4_years': '4 Years',
            '5_years': '5 Years',
            'not_applicable': 'Not Applicable'
        };
        return map[duration] || duration || 'N/A';
    }

    // ========================================
    // EXPORTAR
    // ========================================

    window.PreviewModule = {
        render: render
    };

})();