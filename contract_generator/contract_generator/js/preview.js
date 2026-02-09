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
                        border-bottom: 3px solid #CC0000;
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
                        color: #CC0000;
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
                        background-color: #CC0000;
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
                    }
                    .jwo-totals-exact .label-cell {
                        text-align: right;
                        font-weight: bold;
                        text-transform: uppercase;
                        width: 120px;
                        border: none;
                    }
                    .jwo-totals-exact .value-cell {
                        text-align: right;
                        width: 130px;
                        background-color: #fff;
                        font-weight: bold;
                        border: 1px solid #000;
                    }
                    .jwo-totals-exact tr:last-child .label-cell {
                        color: #CC0000;
                    }
                    .jwo-totals-exact tr:last-child .value-cell {
                        background-color: #CC0000;
                        color: white;
                    }

                    /* Scope Section - Exact match to PDF */
                    .jwo-scope-exact {
                        margin-bottom: 15px;
                    }
                    .jwo-scope-header-exact {
                        background-color: #CC0000;
                        color: white;
                        font-weight: bold;
                        padding: 6px 10px;
                        margin-bottom: 8px;
                        font-size: 9pt;
                        text-transform: uppercase;
                    }
                    .jwo-scope-content-exact {
                        border: none;
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

                    /* Page separator */
                    .jwo-page-separator {
                        border: none;
                        border-top: 2px dashed #ccc;
                        margin: 25px 0;
                    }

                    /* Terms Section */
                    .jwo-terms-section {
                        margin-top: 10px;
                    }
                    .jwo-terms-main-title {
                        background-color: #CC0000;
                        color: white;
                        font-weight: bold;
                        padding: 8px 10px;
                        font-size: 10pt;
                        margin-bottom: 15px;
                        text-transform: uppercase;
                    }
                    .jwo-term-box {
                        margin-bottom: 12px;
                        padding-left: 5px;
                    }
                    .jwo-term-title {
                        font-weight: bold;
                        font-size: 9pt;
                        margin-bottom: 5px;
                        text-transform: uppercase;
                        color: #CC0000;
                    }
                    .jwo-term-box ul {
                        margin-left: 20px;
                    }
                    .jwo-term-box li {
                        margin-bottom: 3px;
                        font-size: 9pt;
                        color: #000;
                    }
                    .jwo-term-box p {
                        margin-left: 15px;
                        font-size: 9pt;
                        margin-bottom: 5px;
                        color: #000;
                    }

                    /* Final Section - Signatures */
                    .jwo-final-section {
                        display: flex;
                        width: 100%;
                        margin-top: 30px;
                        gap: 20px;
                    }
                    .jwo-contact-column {
                        width: 48%;
                    }
                    .jwo-signature-column {
                        width: 48%;
                    }
                    .jwo-contact-title {
                        font-weight: bold;
                        font-size: 9pt;
                        margin-bottom: 8px;
                        text-decoration: underline;
                        text-transform: uppercase;
                    }
                    .jwo-contact-info {
                        font-size: 9pt;
                        line-height: 1.5;
                    }
                    .jwo-signature-box {
                        border: 1px solid #000;
                        padding: 10px;
                        margin-bottom: 10px;
                        height: 70px;
                    }
                    .jwo-sig-label {
                        font-weight: bold;
                        font-size: 9pt;
                        text-transform: uppercase;
                    }
                    .jwo-sig-line {
                        border-top: 1px solid #000;
                        margin-top: 35px;
                        padding-top: 3px;
                        font-size: 8pt;
                    }

                    /* Footer */
                    .jwo-footer-wrapper {
                        margin-top: 30px;
                    }
                    .jwo-footer-top {
                        background-color: #A30000;
                        color: white;
                        text-align: center;
                        padding: 3px 10px;
                        font-size: 7pt;
                    }
                    .jwo-footer-bottom {
                        background-color: #CC0000;
                        color: white;
                        text-align: center;
                        padding: 8px 10px;
                        font-size: 8pt;
                    }
                    .jwo-footer-bottom a {
                        color: white;
                        text-decoration: none;
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

                <!-- PAGE SEPARATOR -->
                <div class="jwo-footer-wrapper">
                    <div class="jwo-footer-top">PRIME FACILITY SERVICES GROUP, INC.</div>
                    <div class="jwo-footer-bottom"><strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>www.primefacilityservicesgroup.com</div>
                </div>
                <hr class="jwo-page-separator">
                <div class="jwo-header-exact">
                    <img src="${logoSrc}" alt="Prime Facility Services Group" onerror="this.style.display='none'">
                    <div class="title-section">
                        <div class="doc-title">JOB WORK ORDER</div>
                        <div class="doc-subtitle">"The best services in the industry or nothing at all"</div>
                    </div>
                </div>

                <!-- PAGE 2: TERMS AND CONDITIONS -->
                <div class="jwo-terms-section">
                    <div class="jwo-terms-main-title">TERMS AND CONDITIONS</div>

                    <div class="jwo-term-box">
                        <div class="jwo-term-title">1. SERVICE LIMITATIONS</div>
                        <ul>
                            <li>Work will be performed during approved service windows.</li>
                            <li>Additional charges may apply for emergency service requests.</li>
                            <li>Separate scheduling is required for areas containing wood-burning equipment.</li>
                        </ul>
                    </div>

                    ${isKitchenService(requestedService) ? `
                    <div class="jwo-term-box">
                        <div class="jwo-term-title">2. AREA PREPARATION</div>
                        <ul>
                            <li>All cooking equipment must be turned off at least two (2) hours before service.</li>
                        </ul>
                    </div>

                    <div class="jwo-term-box">
                        <div class="jwo-term-title">3. KITCHEN PREPARATION</div>
                        <p>The Client must ensure that the kitchen is ready for service, including:</p>
                        <ul>
                            <li>Turning off all kitchen equipment and allowing it to cool completely</li>
                            <li>Removing food, utensils, and personal items from work surfaces</li>
                            <li>Keeping access areas clear for the cleaning crew</li>
                        </ul>
                        <p>Additional time caused by lack of preparation may be billed at <strong>$30.00 USD per hour</strong>.</p>
                    </div>
                    ` : ''}

                    <div class="jwo-term-box">
                        <div class="jwo-term-title">${isKitchenService(requestedService) ? '4' : '2'}. PROPOSAL VALIDITY PERIOD</div>
                        <p>The proposal issued for this Work Order will be valid for fourteen (14) days from the date of issuance. Prime Facility Services Group may revise pricing, scope, or terms if approval is not received within this period.</p>
                        <p>If actual site conditions differ from those observed during the initial inspection, a revised proposal may be issued.</p>
                    </div>

                    <div class="jwo-term-box">
                        <div class="jwo-term-title">${isKitchenService(requestedService) ? '5' : '3'}. CANCELLATIONS</div>
                        <p>Cancellations made with less than twenty-four (24) hours' notice will incur a charge equal to one hundred percent (100%) of the minimum scheduled labor.</p>
                        <p>Cancellations made with more than twenty-four (24) hours' notice will not incur charges unless otherwise specified in the applicable price list.</p>
                    </div>

                    <div class="jwo-term-box">
                        <div class="jwo-term-title">${isKitchenService(requestedService) ? '6' : '4'}. RESCHEDULING</div>
                        <p>Rescheduling requests must be submitted at least twenty-four (24) hours in advance. Requests made within 24 hours may incur a fee of up to the total scheduled labor and are subject to personnel and equipment availability.</p>
                        <p>Availability for rescheduled dates or times is not guaranteed.</p>
                    </div>

                    <div class="jwo-term-box">
                        <div class="jwo-term-title">${isKitchenService(requestedService) ? '7' : '5'}. LACK OF ACCESS</div>
                        <p>If personnel arrive on site and are unable to begin work due to lack of access, incomplete area preparation, or delays caused by the Client, the situation will be treated as a same-day cancellation and the corresponding charges will apply.</p>
                    </div>

                    <div class="jwo-term-box">
                        <div class="jwo-term-title">${isKitchenService(requestedService) ? '8' : '6'}. WEATHER OR SAFETY DELAYS</div>
                        <p>If work cannot be safely performed due to weather conditions, hazardous environments, or other safety-related circumstances beyond the company's control, the service will be rescheduled to the next available date.</p>
                        <p>No penalties will apply; however, labor or material costs may be adjusted if conditions change significantly.</p>
                    </div>

                    <div class="jwo-term-box">
                        <div class="jwo-term-title">${isKitchenService(requestedService) ? '9' : '7'}. POST-SERVICE REQUIREMENTS</div>
                        <ul>
                            <li>Kitchen management must verify completion.</li>
                            <li>Any concerns must be reported within twenty-four (24) hours.</li>
                            <li>Recommended maintenance schedules must be followed.</li>
                        </ul>
                    </div>

                    <div class="jwo-term-box">
                        <div class="jwo-term-title">${isKitchenService(requestedService) ? '10' : '8'}. SITE ACCESS AND SECURITY COORDINATION</div>
                        <ul>
                            <li>The Client must notify on-site security personnel or building management in advance that services will be performed.</li>
                            <li>If the service requires access to rooftops, ceilings, ventilation systems, or other restricted areas, the Client must ensure safe and full access.</li>
                            <li>The Client must provide clear instructions and prior authorization to security or access-control personnel to allow entry for the service team.</li>
                        </ul>
                    </div>

                    <!-- ACCEPTANCE / SIGNATURES SECTION -->
                    <div class="jwo-terms-main-title" style="margin-top: 20px;">ACCEPTANCE / SIGNATURES</div>

                    <div class="jwo-final-section">
                        <div class="jwo-contact-column">
                            <div class="jwo-contact-title">PLEASE SEND TWO COPIES OF YOUR WORK ORDER:</div>
                            <div class="jwo-contact-info">
                                Enter this order in accordance with the prices, terms, and<br>
                                specifications listed above.
                            </div>
                            <br>
                            <div class="jwo-contact-title">SEND ALL CORRESPONDENCES TO:</div>
                            <div class="jwo-contact-info">
                                <strong>Prime Facility Services Group, Inc.</strong><br>
                                8303 Westglen Drive<br>
                                Houston, TX 77063<br><br>
                                customerservice@primefacilityservicesgroup.com<br>
                                (713) 338-2553 Phone<br>
                                (713) 574-3065 Fax
                            </div>
                        </div>
                        <div class="jwo-signature-column">
                            <div class="jwo-signature-box">
                                <div class="jwo-sig-label">AUTHORIZED BY:</div>
                                <div class="jwo-sig-line">Signature & Date</div>
                            </div>
                            <div class="jwo-signature-box">
                                <div class="jwo-sig-label">PRINT NAME:</div>
                                <div class="jwo-sig-line">Name & Title</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="jwo-footer-wrapper">
                    <div class="jwo-footer-top">
                        PRIME FACILITY SERVICES GROUP, INC.
                    </div>
                    <div class="jwo-footer-bottom">
                        <strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>
                        www.primefacilityservicesgroup.com
                    </div>
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
        const companyName = escapeHtml(data.Company_Name || '(company name)');
        const companyAddress = escapeHtml(data.Company_Address || '(address)');
        const clientName = escapeHtml(data.client_name || data.Client_Name || '');
        const clientTitle = escapeHtml(data.Client_Title || '');
        const contractDuration = formatDuration(data.Contract_Duration) || '___________';
        const inflationAdj = escapeHtml(data.inflationAdjustment || '3.1');

        // Determine logo
        const deptLower = (data.Service_Type || '').toLowerCase();
        const logoSrc = deptLower.includes('hospitality') ? '/sales/Images/phospitality.png' : '/sales/Images/pfacility.png';

        return `
            <div class="document-preview contract-preview-exact">
                <style>
                    .contract-preview-exact {
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 9.5pt;
                        color: #000;
                        line-height: 1.4;
                        background: white;
                        padding: 20px;
                    }

                    /* Header */
                    .contract-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        border-bottom: 3px solid #CC0000;
                        padding-bottom: 10px;
                        margin-bottom: 10px;
                    }
                    .contract-header img {
                        max-height: 65px;
                        width: auto;
                    }
                    .contract-header .title-section {
                        text-align: left;
                        padding-left: 15px;
                    }
                    .contract-header .doc-title {
                        color: #CC0000;
                        font-size: 13pt;
                        font-weight: bold;
                        text-transform: uppercase;
                        margin-bottom: 2px;
                    }
                    .contract-header .doc-subtitle {
                        font-size: 16pt;
                        font-weight: bold;
                        color: #000;
                    }

                    /* Section titles */
                    .contract-section-num {
                        color: #CC0000;
                        font-weight: bold;
                        font-size: 10pt;
                        margin-top: 14px;
                        margin-bottom: 6px;
                        text-transform: uppercase;
                    }
                    .contract-subsection {
                        margin-left: 15px;
                        margin-bottom: 6px;
                    }
                    .contract-subsection p {
                        margin-bottom: 5px;
                        text-align: justify;
                    }
                    .contract-subsection ol {
                        margin-left: 20px;
                        margin-bottom: 6px;
                        list-style-type: lower-alpha;
                    }
                    .contract-subsection ol li {
                        margin-bottom: 4px;
                        text-align: justify;
                    }
                    .contract-preamble {
                        margin-bottom: 12px;
                        text-align: justify;
                        line-height: 1.5;
                    }

                    /* Page separator */
                    .contract-page-sep {
                        border: none;
                        border-top: 2px dashed #ccc;
                        margin: 25px 0;
                    }

                    /* Notice block */
                    .contract-notice-block {
                        margin: 10px 15px;
                        line-height: 1.5;
                    }

                    /* Signature blocks */
                    .contract-sig-block {
                        margin-top: 20px;
                    }
                    .contract-sig-block-title {
                        font-weight: bold;
                        font-size: 10pt;
                        margin-bottom: 8px;
                    }
                    .contract-sig-row {
                        display: flex;
                        gap: 20px;
                    }
                    .contract-sig-col {
                        width: 48%;
                    }
                    .contract-sig-item {
                        margin-bottom: 12px;
                    }
                    .contract-sig-label {
                        font-weight: bold;
                        font-size: 9pt;
                        margin-bottom: 2px;
                    }
                    .contract-sig-line {
                        border-bottom: 1px solid #000;
                        height: 22px;
                    }

                    /* Appendix headers */
                    .contract-appendix-title {
                        color: #CC0000;
                        font-weight: bold;
                        font-size: 14pt;
                        text-align: center;
                        margin-bottom: 5px;
                        text-transform: uppercase;
                    }
                    .contract-appendix-subtitle {
                        font-weight: bold;
                        font-size: 11pt;
                        text-align: center;
                        margin-bottom: 15px;
                        text-transform: uppercase;
                    }
                    .contract-appendix-content {
                        margin: 15px 10px;
                        text-align: justify;
                        line-height: 1.5;
                    }

                    /* Footer */
                    .contract-footer-wrapper {
                        margin-top: 30px;
                    }
                    .contract-footer-top {
                        background-color: #A30000;
                        color: white;
                        text-align: center;
                        padding: 3px 10px;
                        font-size: 7pt;
                    }
                    .contract-footer-bottom {
                        background-color: #CC0000;
                        color: white;
                        text-align: center;
                        padding: 8px 10px;
                        font-size: 8pt;
                    }
                </style>

                <!-- HEADER -->
                <div class="contract-header">
                    <img src="${logoSrc}" alt="Prime Hospitality Services" onerror="this.style.display='none'">
                    <div class="title-section">
                        <div class="doc-title">Temporary Staff</div>
                        <div class="doc-subtitle">SERVICES AGREEMENT</div>
                    </div>
                </div>

                <!-- PREAMBLE -->
                <div class="contract-preamble">
                    <strong>PRIME HOSPITALITY SERVICES OF TEXAS</strong>, with main office at 8303 Westglen Dr., Houston, TX 77063 ("<strong>PRIME</strong>"), and <strong>${companyName}</strong>, with its main office at ${companyAddress} ("<strong>CLIENT</strong>"), accept the terms and conditions set forth in this Temporary Staffing Services Agreement (the "Agreement").
                </div>

                <!-- SECTION 1 -->
                <div class="contract-section-num">1. Functions and responsibilities of Prime Hospitality Services of Texas (PRIME)</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> PRIME commits to:</p>
                    <ol>
                        <li>Provide the staffing and operational support services described in Annex A, at the specified locations, providing qualified personnel in accordance with the positions, agreed rates and applicable industry standards.</li>
                        <li>Pay the salaries of Prime Associates and provide them with the benefits that PRIME offers.</li>
                        <li>To provide unemployment insurance and workers' compensation benefits and to handle unemployment and workers' compensation claims involving Prime Associates.</li>
                        <li>Require Senior Associates to sign confidentiality agreements (in the form of Appendix C) before commencing their assignments for the CLIENT.</li>
                    </ol>
                </div>

                <!-- SECTION 2 -->
                <div class="contract-section-num">2. Client's Duties and Responsibilities</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> THE CLIENT must:</p>
                    <ol>
                        <li>The CLIENT will provide general operational direction related solely to the results of the services provided by the Prime Associates at its facilities. The CLIENT will not exercise control over the means, methods, hiring, firing, discipline, wages, schedules, or working conditions of the Prime Associates, which will be the sole responsibility of PRIME.</li>
                        <li>The CLIENT shall be responsible for maintaining and safeguarding its facilities, processes, systems, equipment, merchandise, confidential or proprietary information, cash, keys, negotiable instruments, or other valuables. Nothing in this Agreement shall be construed as authorizing the CLIENT to supervise the Principal Associates as an employer or to establish a joint employment or co-employment relationship.</li>
                        <li>Provide key associates with a safe workplace and adequate safety information, training, and equipment regarding any hazardous substances or conditions to which they may be exposed in the workplace.</li>
                        <li>Do not change the job duties of Prime Associates without the prior and express written approval of PRIME.</li>
                        <li>Provide the Principal Associates with all the equipment, supplies and tools to perform daily tasks as scheduled by the CLIENT.</li>
                        <li>Exclude Prime Associates from the CLIENT's benefit plans, policies and practices, and do not make any offers or promises related to Prime.</li>
                    </ol>
                </div>

                <!-- SECTION 3 -->
                <div class="contract-section-num">3. Payment Terms, Rates and Charges</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> <strong>Billing and Payment Terms</strong><br>
                    PRIME will invoice the CLIENT for services rendered in accordance with Annex A, at the rates specified therein. Billing will be periodic (weekly, monthly, or as otherwise agreed), based on the completion of the tasks described in the Scope of Work. Payment must be made within thirty (30) days of the invoice date.</p>

                    <p><strong>II.</strong> <strong>Fixed Price Services and Hourly Services</strong><br>
                    The CLIENT acknowledges that, while certain services under this Agreement are provided at a fixed price agreed between the CLIENT and PRIME, not all work requested is subject to a single fee.</p>
                    <ol>
                        <li>Depending on the type of work required, some services may be billed hourly at the applicable rates mutually agreed by the CLIENT and PRIME for each category of work.</li>
                        <li>PRIME will issue the corresponding invoices, clearly specifying whether the charges correspond to services provided under a fixed price agreement or to services billed hourly.</li>
                    </ol>

                    <p><strong>III.</strong> <strong>Disputed Invoices</strong><br>
                    In the event that any part of an invoice is disputed, the CLIENT shall promptly pay the undisputed part, while the parties resolve the disputed amount in good faith.</p>

                    <p><strong>IV.</strong> <strong>Overtime, Holidays and Weekends</strong><br>
                    It is presumed that Prime Associates are not exempt from laws requiring additional pay for overtime, holiday work, or weekend work.</p>
                    <ol>
                        <li>PRIME will charge the CLIENT special surcharge rates for overtime work only when a Prime Associate, in working on an assignment for the CLIENT, considered on its own, is legally required to receive surcharge pay and the CLIENT has authorized, directed or permitted such work time.</li>
                        <li>Surcharge hours will be billed at the same multiple of the regular billing rate that PRIME is legally obliged to apply to the Prime Associate's standard pay rate.</li>
                        <li>By way of example, where federal law requires payment of time and a half for work exceeding forty (40) hours per week, the CLIENT will be billed time and a half plus the usual margin on the regular billing rate.</li>
                    </ol>

                    <p><strong>V.</strong> <strong>Adjustments for Increases in Labor Costs</strong><br>
                    In addition to the billing rates specified in Schedule A, CLIENT will pay PRIME the amount of all new or increased labor costs associated with Prime Associates that PRIME is legally obligated to pay, including, but not limited to, wages, benefits, payroll taxes, social program contributions, or charges tied to benefit levels, until the parties agree on new billing rates.</p>

                    <p><strong>VI.</strong> <strong>State Taxes</strong><br>
                    The CLIENT acknowledges and accepts that Prime Hospitality Services of Texas will collect applicable state taxes on all goods and services provided under this Agreement.</p>
                    <ol>
                        <li>The rate and method of calculating such taxes will be determined in accordance with the applicable state tax laws and regulations.</li>
                        <li>The CLIENT will be responsible for paying these taxes in addition to the fees agreed for the contracted services.</li>
                        <li>PRIME will provide the CLIENT with the corresponding documentation and receipts for state tax charges.</li>
                        <li>Any changes in state tax rates or regulations will be applied accordingly.</li>
                    </ol>
                </div>

                <!-- SECTION 4 -->
                <div class="contract-section-num">4. Confidential Information</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> Neither party shall disclose confidential or proprietary information received from the other, including, but not limited to, methods, procedures, or any sensitive information related to the services provided. Both parties agree to maintain such information in strict confidence and not disclose it to third parties or use it for any purpose other than fulfilling this Agreement or as required by law. Prime Associates' access to such information shall not imply any knowledge, possession, or use thereof by Prime.</p>
                </div>

                <!-- SECTION 5 -->
                <div class="contract-section-num">5. Full Agreement</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> This Agreement and the attached Annexes constitute the entire agreement between the parties with respect to the subject matter hereof and supersede any prior agreements, whether oral or written. Any modification to this Agreement must be in writing and signed by a duly authorized representative of the party.</p>
                </div>

                <!-- SECTION 6 -->
                <div class="contract-section-num">6. Resignation</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> Failure by either party to strictly comply with any of the terms, conditions, and provisions of this Agreement shall not be deemed a waiver of future compliance.</p>
                </div>

                <!-- SECTION 7 -->
                <div class="contract-section-num">7. Cooperation</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> The parties agree to fully cooperate and assist each other in investigating and resolving any complaint, scam, fraud, action or proceeding that may be brought by or may involve Prime Associates.</p>
                </div>

                <!-- SECTION 8 -->
                <div class="contract-section-num">8. Indemnification and limitation of liability</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> To the extent permitted by law, PRIME will defend, indemnify and hold harmless the CLIENT from any and all claims, losses and liabilities arising directly from PRIME's performance of the services described in this Agreement.</p>
                    <p><strong>II.</strong> To the extent permitted by law, the CLIENT shall defend, indemnify, and hold harmless PRIME and its parent company, subsidiaries, directors, officers, agents, representatives, and employees from and against any and all claims, losses, and liabilities caused by the CLIENT's breach of this Agreement.</p>
                    <p><strong>III.</strong> Neither party shall be liable for any incidental, consequential, exemplary, special, punitive or lost profit damages.</p>
                    <p><strong>IV.</strong> The party requesting indemnification shall inform the other party within five (5) business days following receipt of notification of any claim.</p>
                    <p><strong>V.</strong> The provisions of this Agreement constitute the entire agreement between the parties with respect to indemnification.</p>
                </div>

                <!-- PAGE SEPARATOR - Section 9 Notices -->
                <div class="contract-footer-wrapper">
                    <div class="contract-footer-top">PRIME HOSPITALITY SERVICES OF TEXAS</div>
                    <div class="contract-footer-bottom"><strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>www.primefacilityservicesgroup.com</div>
                </div>
                <hr class="contract-page-sep">
                <div class="contract-header">
                    <img src="${logoSrc}" alt="Prime Hospitality Services" onerror="this.style.display='none'">
                    <div class="title-section">
                        <div class="doc-title">Temporary Staff</div>
                        <div class="doc-subtitle">SERVICES AGREEMENT</div>
                    </div>
                </div>

                <!-- SECTION 9 -->
                <div class="contract-section-num">9. NOTICES</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> All notices required under this Agreement must be in writing:</p>
                    <div class="contract-notice-block">
                        <strong>Customer:</strong>
                        ${companyName}<br>
                        ${companyAddress}<br><br>
                        <strong>For the attention of:</strong><br>
                        ${clientName || '(customer_name)'}<br>
                        ${clientTitle || '(contact_name)'}
                    </div>

                    <p><strong>II.</strong> For the Contractor:</p>
                    <div class="contract-notice-block">
                        <strong>Service provider:</strong>
                        Prime Hospitality Services of Texas Inc.<br>
                        8303 Westglen Dr<br>
                        Houston, Texas 77063<br><br>
                        <strong>For the attention of:</strong><br>
                        Patty P&eacute;rez &ndash; President<br>
                        <em>or</em><br>
                        Rafael S. P&eacute;rez Jr. &ndash; Senior Vice President
                    </div>
                </div>

                <!-- SECTIONS 10-19 continue -->
                <div class="contract-section-num">10. MISCELLANEOUS</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> The provisions of this Agreement, which by their terms extend beyond termination, will remain in effect.</p>
                    <p><strong>II.</strong> No provision may be modified unless agreed in writing and signed by both parties.</p>
                    <p><strong>III.</strong> If any provision is deemed invalid, it shall be severed without affecting the remaining provisions.</p>
                    <p><strong>IV.</strong> This Agreement and the attached annexes contain the entire understanding between the parties.</p>
                    <p><strong>V.</strong> The attached Annexes are incorporated by reference. Annex B, C, and D shall apply as a condition of service.</p>
                </div>

                <div class="contract-section-num">11. TERMS OF THE AGREEMENT</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> This Agreement shall remain in force for an initial term of ${contractDuration}.</p>
                    <p><strong>II.</strong> Either party may terminate by giving thirty (30) days' written notice.</p>
                    <p><strong>III.</strong> Upon expiration, this Agreement shall automatically renew for successive one (1) year periods.</p>
                </div>

                <!-- Signature block -->
                <div class="contract-sig-block">
                    <div class="contract-sig-row">
                        <div class="contract-sig-col">
                            <div class="contract-sig-block-title">${companyName}:</div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Signature:</div><div class="contract-sig-line"></div></div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Printed name:</div><div class="contract-sig-line"></div></div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Date:</div><div class="contract-sig-line"></div></div>
                        </div>
                        <div class="contract-sig-col">
                            <div class="contract-sig-block-title">Prime Hospitality Services of Texas:</div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Signature:</div><div class="contract-sig-line"></div></div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Printed name:</div><div class="contract-sig-line"></div></div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Date:</div><div class="contract-sig-line"></div></div>
                        </div>
                    </div>
                </div>

                <div class="contract-section-num">12. Emergency provision</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> PRIME may provide certain administrative or operational services remotely when circumstances require it.</p>
                    <p><strong>II.</strong> An "Emergency" refers to any unforeseen event requiring immediate action.</p>
                    <p><strong>III.</strong> Emergency services may be billed separately at mutually agreed rates.</p>
                </div>

                <div class="contract-section-num">13. Price increase</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> Prices will be subject to annual adjustment of (${inflationAdj}%) or by any other percentage agreed in good faith. PRIME will notify the CLIENT at least thirty (30) days prior to the renewal date.</p>
                </div>

                <div class="contract-section-num">14. INSURANCE</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> The Contractor shall obtain and maintain insurance including workers' compensation, general liability, auto liability, and protective liability.</p>
                    <p><strong>II.</strong> Coverage shall not be less than the amounts in Annex (D).</p>
                </div>

                <div class="contract-section-num">15. PENALTY FOR LATE PAYMENT</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> Payment within thirty (30) days. Outstanding balances accrue interest at the legal rate.</p>
                    <p><strong>II.</strong> After forty-five (45) days, PRIME may suspend services upon five (5) business days' notice.</p>
                </div>

                <div class="contract-section-num">16. NO STAFF RECRUITMENT FEE</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> Two (2) year non-solicitation period. Violation incurs 30% placement fee.</p>
                </div>

                <div class="contract-section-num">17. NATURE OF THE RELATIONSHIP</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> PRIME is an independent contractor. No employer-employee relationship.</p>
                </div>

                <div class="contract-section-num">18. HEADINGS</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> Paragraph headings are for reference only.</p>
                </div>

                <div class="contract-section-num">19. ARBITRATION</div>
                <div class="contract-subsection">
                    <p><strong>I.</strong> Disputes resolved by binding arbitration via the American Arbitration Association (AAA) in Texas.</p>
                </div>

                <!-- APPENDIX A -->
                <div class="contract-footer-wrapper">
                    <div class="contract-footer-top">PRIME HOSPITALITY SERVICES OF TEXAS</div>
                    <div class="contract-footer-bottom"><strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>www.primefacilityservicesgroup.com</div>
                </div>
                <hr class="contract-page-sep">
                <div class="contract-header">
                    <img src="${logoSrc}" alt="Prime Hospitality Services" onerror="this.style.display='none'">
                    <div class="title-section">
                        <div class="doc-title">Temporary Staff</div>
                        <div class="doc-subtitle">SERVICES AGREEMENT</div>
                    </div>
                </div>
                <div class="contract-appendix-title">APPENDIX (A)</div>
                <div class="contract-appendix-subtitle">Service Prices</div>
                <div class="contract-appendix-content">
                    <p style="text-align: center; color: #666; font-style: italic;">(Service prices to be detailed here)</p>
                </div>

                <!-- APPENDIX B -->
                <div class="contract-footer-wrapper">
                    <div class="contract-footer-top">PRIME HOSPITALITY SERVICES OF TEXAS</div>
                    <div class="contract-footer-bottom"><strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>www.primefacilityservicesgroup.com</div>
                </div>
                <hr class="contract-page-sep">
                <div class="contract-header">
                    <img src="${logoSrc}" alt="Prime Hospitality Services" onerror="this.style.display='none'">
                    <div class="title-section">
                        <div class="doc-title">Temporary Staff</div>
                        <div class="doc-subtitle">SERVICES AGREEMENT</div>
                    </div>
                </div>
                <div class="contract-appendix-title">APPENDIX (B)</div>
                <div class="contract-appendix-subtitle">Exemption from Benefits for Principal Associates</div>
                <div class="contract-appendix-content">
                    <p>Considering my assignment to CLIENT by PRIME, I acknowledge that I am solely an employee of PRIME for the purposes of the benefits plan and am entitled only to the benefits that PRIME may provide to me as its employee. I further understand and agree that I am not eligible or entitled to participate in or make any claim to any benefits plan, policy, or practice offered by CLIENT.</p>
                </div>
                <div class="contract-sig-block">
                    <div class="contract-sig-row">
                        <div class="contract-sig-col">
                            <div class="contract-sig-block-title">EMPLOYEE:</div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Signature:</div><div class="contract-sig-line"></div></div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Printed name:</div><div class="contract-sig-line"></div></div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Date:</div><div class="contract-sig-line"></div></div>
                        </div>
                        <div class="contract-sig-col">
                            <div class="contract-sig-block-title">Prime Hospitality Services of Texas:</div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Signature:</div><div class="contract-sig-line"></div></div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Printed name:</div><div class="contract-sig-line"></div></div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Date:</div><div class="contract-sig-line"></div></div>
                        </div>
                    </div>
                </div>

                <!-- APPENDIX C -->
                <div class="contract-footer-wrapper">
                    <div class="contract-footer-top">PRIME HOSPITALITY SERVICES OF TEXAS</div>
                    <div class="contract-footer-bottom"><strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>www.primefacilityservicesgroup.com</div>
                </div>
                <hr class="contract-page-sep">
                <div class="contract-header">
                    <img src="${logoSrc}" alt="Prime Hospitality Services" onerror="this.style.display='none'">
                    <div class="title-section">
                        <div class="doc-title">Temporary Staff</div>
                        <div class="doc-subtitle">SERVICES AGREEMENT</div>
                    </div>
                </div>
                <div class="contract-appendix-title">APPENDIX (C)</div>
                <div class="contract-appendix-subtitle">Confidentiality and Non-Disclosure Agreement for Key Associates</div>
                <div class="contract-appendix-content">
                    <p>Considering my assignment to the CLIENT by PRIME, I acknowledge that, during the course of my assignment, I may have access to confidential, proprietary, or trade secret information belonging to the CLIENT. I agree to maintain the strict confidentiality of such information and not to disclose, use, copy, or disseminate it for any purpose other than the performance of my assigned duties, except as required by law.</p>
                </div>
                <div class="contract-sig-block">
                    <div class="contract-sig-row">
                        <div class="contract-sig-col">
                            <div class="contract-sig-block-title">EMPLOYEE:</div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Signature:</div><div class="contract-sig-line"></div></div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Printed name:</div><div class="contract-sig-line"></div></div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Date:</div><div class="contract-sig-line"></div></div>
                        </div>
                        <div class="contract-sig-col">
                            <div class="contract-sig-block-title">WITNESS:</div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Signature:</div><div class="contract-sig-line"></div></div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Printed name:</div><div class="contract-sig-line"></div></div>
                            <div class="contract-sig-item"><div class="contract-sig-label">Date:</div><div class="contract-sig-line"></div></div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="contract-footer-wrapper">
                    <div class="contract-footer-top">
                        PRIME HOSPITALITY SERVICES OF TEXAS
                    </div>
                    <div class="contract-footer-bottom">
                        <strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>
                        www.primefacilityservicesgroup.com
                    </div>
                </div>
            </div>
        `;
    }

    // ========================================
    // UTILIDADES
    // ========================================

    function isKitchenService(requestedService) {
        const svc = (requestedService || '').toLowerCase();
        return svc.indexOf('kitchen') !== -1 || svc.indexOf('hood') !== -1;
    }

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