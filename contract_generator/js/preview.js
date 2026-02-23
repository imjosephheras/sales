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

                <!-- SERVICE REPORT DYNAMIC SECTIONS -->
                ${renderServiceReportSections(data)}

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
        // BUILD SCOPE OF WORK (dynamic sections only)
        // ========================================
        var dynamicScopeSections = data.scope_sections || data.Scope_Sections || [];

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
                            <th style="width: 25%;">${typeof editorSalesMode !== 'undefined' && editorSalesMode === 'product' ? 'PRODUCT' : 'TYPE OF SERVICES'}</th>
                            <th style="width: 12%;">${typeof editorSalesMode !== 'undefined' && editorSalesMode === 'product' ? 'QUANTITY' : 'SERVICE TIME'}</th>
                            <th style="width: 12%;">${typeof editorSalesMode !== 'undefined' && editorSalesMode === 'product' ? 'UNIT PRICE' : 'FREQUENCY'}</th>
                            <th style="width: 36%;">${typeof editorSalesMode !== 'undefined' && editorSalesMode === 'product' ? 'DESCRIPTION' : 'SERVICE DESCRIPTION'}</th>
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

                <!-- SCOPE OF WORK - Dynamic sections only -->
                ${dynamicScopeSections.length > 0 ? dynamicScopeSections.map(function(section) {
                    return `
                    <div class="jwo-scope-exact">
                        <div class="jwo-scope-header-exact">
                            ${escapeHtml(section.title || '').toUpperCase()}
                        </div>
                        <div class="jwo-scope-content-exact">
                            <h4>WORK TO BE PERFORMED:</h4>
                            <p>${escapeHtml(section.scope_content || section.content || '').replace(/\n/g, '<br>')}</p>
                        </div>
                    </div>`;
                }).join('') : ''}

                ${data.Additional_Comments ? `
                <div class="jwo-scope-exact">
                    <div class="jwo-scope-content-exact">
                        <h4>ADDITIONAL NOTES:</h4>
                        <p>${escapeHtml(data.Additional_Comments).replace(/\n/g, '<br>')}</p>
                    </div>
                </div>` : ''}

                <!-- SERVICE REPORT DYNAMIC SECTIONS -->
                ${renderServiceReportSections(data)}

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
        var companyName = escapeHtml(data.Company_Name || '(company_name)');
        var companyAddress = escapeHtml(data.Company_Address || '(address)');
        var contractDuration = formatDuration(data.Contract_Duration) || '(contract_duration)';

        // Determine logo
        var deptLower = (data.Service_Type || '').toLowerCase();
        var logoSrc = deptLower.indexOf('hospitality') !== -1 ? '/sales/Images/phospitality.png' : '/sales/Images/pfacility.png';

        // Group staff by department
        var staffItems = data.contract_staff || [];
        var staffByDept = {};
        staffItems.forEach(function(s) {
            var dept = (s.department && s.department.trim()) ? s.department.trim() : 'GENERAL';
            if (!staffByDept[dept]) staffByDept[dept] = [];
            staffByDept[dept].push(s);
        });

        // Build staffing fees HTML
        var staffingHtml = '';
        var deptNames = Object.keys(staffByDept);
        if (deptNames.length > 0) {
            deptNames.forEach(function(dept) {
                staffingHtml += '<div class="proposal-dept-header">' + escapeHtml(dept).toUpperCase() + '</div>';
                staffingHtml += '<table class="proposal-staff-table"><thead><tr><th style="width:65%;">Position</th><th style="width:35%;">Bill Rate</th></tr></thead><tbody>';
                staffByDept[dept].forEach(function(s) {
                    var billRate = parseFloat(s.bill_rate || 0).toFixed(2);
                    staffingHtml += '<tr><td>' + escapeHtml(s.position || '') + '</td><td class="bill-rate">$' + formatPrice(billRate) + '</td></tr>';
                });
                staffingHtml += '</tbody></table>';
            });
        } else {
            staffingHtml = '<p style="color: #999; font-style: italic; text-align: center; padding: 20px;">No staff positions defined. Add positions in the Staff section above.</p>';
        }

        // Validation warnings
        var warnings = [];
        if (!data.Company_Name) warnings.push('Company Name is required');
        if (!data.Company_Address) warnings.push('Company Address is required');
        if (!data.Contract_Duration || data.Contract_Duration === 'not_applicable') warnings.push('Contract Duration is required');
        if (staffItems.length === 0) warnings.push('At least one staff position is required');

        var warningHtml = '';
        if (warnings.length > 0) {
            warningHtml = '<div class="proposal-warnings"><strong>Missing required fields:</strong><ul>';
            warnings.forEach(function(w) { warningHtml += '<li>' + escapeHtml(w) + '</li>'; });
            warningHtml += '</ul></div>';
        }

        return `
            <div class="document-preview proposal-preview-exact">
                <style>
                    .proposal-preview-exact {
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 9.5pt;
                        color: #000;
                        line-height: 1.4;
                        background: white;
                        padding: 20px;
                    }
                    .proposal-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        border-bottom: 3px solid #CC0000;
                        padding-bottom: 10px;
                        margin-bottom: 15px;
                    }
                    .proposal-header img {
                        max-height: 65px;
                        width: auto;
                    }
                    .proposal-header .title-section {
                        text-align: left;
                        padding-left: 15px;
                    }
                    .proposal-header .doc-title {
                        color: #CC0000;
                        font-size: 13pt;
                        font-weight: bold;
                        text-transform: uppercase;
                        margin-bottom: 2px;
                    }
                    .proposal-header .doc-subtitle {
                        font-size: 16pt;
                        font-weight: bold;
                        color: #000;
                    }
                    .proposal-section-title {
                        color: #CC0000;
                        font-weight: bold;
                        font-size: 12pt;
                        margin-top: 20px;
                        margin-bottom: 10px;
                        text-transform: uppercase;
                        border-bottom: 2px solid #CC0000;
                        padding-bottom: 4px;
                    }
                    .proposal-subsection-title {
                        color: #CC0000;
                        font-weight: bold;
                        font-size: 10pt;
                        margin-top: 14px;
                        margin-bottom: 6px;
                        text-transform: uppercase;
                    }
                    .proposal-content {
                        margin-bottom: 12px;
                        text-align: justify;
                        line-height: 1.5;
                    }
                    .proposal-content p {
                        margin-bottom: 8px;
                    }
                    .proposal-notice-block {
                        margin: 10px 15px;
                        line-height: 1.5;
                    }
                    .proposal-notice-block strong {
                        display: block;
                        margin-bottom: 2px;
                    }
                    .proposal-dept-header {
                        background-color: #CC0000;
                        color: white;
                        font-weight: bold;
                        font-size: 10pt;
                        padding: 6px 10px;
                        margin-top: 12px;
                        margin-bottom: 0;
                        text-transform: uppercase;
                    }
                    .proposal-staff-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 8px;
                    }
                    .proposal-staff-table th {
                        background-color: #f0f0f0;
                        color: #333;
                        font-weight: bold;
                        padding: 6px 10px;
                        text-align: left;
                        border: 1px solid #ccc;
                        font-size: 9pt;
                    }
                    .proposal-staff-table td {
                        padding: 5px 10px;
                        border: 1px solid #ccc;
                        font-size: 9pt;
                    }
                    .proposal-staff-table .bill-rate {
                        text-align: right;
                        font-weight: bold;
                    }
                    .proposal-staffing-intro {
                        margin: 10px 0;
                        font-size: 9pt;
                        line-height: 1.5;
                        color: #333;
                    }
                    .proposal-page-sep {
                        border: none;
                        border-top: 2px dashed #ccc;
                        margin: 25px 0;
                    }
                    .proposal-sig-block {
                        margin-top: 20px;
                    }
                    .proposal-sig-row {
                        display: flex;
                        gap: 20px;
                    }
                    .proposal-sig-col {
                        width: 48%;
                    }
                    .proposal-sig-block-title {
                        font-weight: bold;
                        font-size: 10pt;
                        margin-bottom: 8px;
                    }
                    .proposal-sig-item {
                        margin-bottom: 12px;
                    }
                    .proposal-sig-label {
                        font-weight: bold;
                        font-size: 9pt;
                        margin-bottom: 2px;
                    }
                    .proposal-sig-line {
                        border-bottom: 1px solid #000;
                        height: 22px;
                    }
                    .proposal-footer-wrapper {
                        margin-top: 30px;
                    }
                    .proposal-footer-top {
                        background-color: #A30000;
                        color: white;
                        text-align: center;
                        padding: 3px 10px;
                        font-size: 7pt;
                    }
                    .proposal-footer-bottom {
                        background-color: #CC0000;
                        color: white;
                        text-align: center;
                        padding: 8px 10px;
                        font-size: 8pt;
                    }
                    .proposal-warnings {
                        background: #fff3cd;
                        border: 1px solid #ffc107;
                        border-radius: 4px;
                        padding: 10px 15px;
                        margin-bottom: 15px;
                        font-size: 9pt;
                    }
                    .proposal-warnings strong {
                        color: #856404;
                    }
                    .proposal-warnings ul {
                        margin: 5px 0 0 20px;
                        color: #856404;
                    }
                </style>

                ${warningHtml}

                <!-- HEADER -->
                <div class="proposal-header">
                    <img src="${logoSrc}" alt="Prime Hospitality Services" onerror="this.style.display='none'">
                    <div class="title-section">
                        <div class="doc-title">Service</div>
                        <div class="doc-subtitle">PROPOSAL</div>
                    </div>
                </div>

                <!-- NOTICES -->
                <div class="proposal-section-title">Notices</div>
                <div class="proposal-content">
                    <p>All notices required under this Agreement shall be in writing, and if to the CLIENT shall be sufficient in all respects if delivered in person or sent by a nationally recognized overnight courier service or by registered or certified mail to:</p>

                    <div class="proposal-notice-block">
                        <strong>Client:</strong>
                        ${companyName}<br>
                        ${companyAddress}
                        <br><br>
                        <strong>Attn:</strong><br>
                        Thomas Turner<br>
                        General Manager<br>
                        (817) 879-1702<br>
                        tturner@ambassadorhc.com
                    </div>

                    <p>Moreover, if to Contractor shall be sufficient in all respects if delivered in person or sent by a nationally recognized overnight courier service or by registered or certified mail to:</p>

                    <div class="proposal-notice-block">
                        <strong>Service Provider:</strong><br>
                        Prime Hospitality Services of Texas Inc.<br>
                        8303 Westglen Dr<br>
                        Houston, Texas 77063
                        <br><br>
                        <strong>Attn:</strong><br>
                        Patty Perez &ndash; President<br>
                        <em>or</em><br>
                        Rafael S. Perez Jr. &ndash; Sr. Vice President
                    </div>
                </div>

                <!-- PAGE SEPARATOR -->
                <div class="proposal-footer-wrapper">
                    <div class="proposal-footer-top">PRIME HOSPITALITY SERVICES OF TEXAS</div>
                    <div class="proposal-footer-bottom"><strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>www.primefacilityservicesgroup.com</div>
                </div>
                <hr class="proposal-page-sep">
                <div class="proposal-header">
                    <img src="${logoSrc}" alt="Prime Hospitality Services" onerror="this.style.display='none'">
                    <div class="title-section">
                        <div class="doc-title">Service</div>
                        <div class="doc-subtitle">PROPOSAL</div>
                    </div>
                </div>

                <!-- STAFFING FEES -->
                <div class="proposal-section-title">Staffing Fees</div>
                <div class="proposal-subsection-title">Hourly Rates by Department</div>

                <div class="proposal-staffing-intro">
                    <p>This sheet presents the hourly staffing rates, organized by department.
                    The rates shown correspond to the Bill Rate applicable per position and are billed per hour worked.</p>
                    <p>Positions and rates are grouped by department according to the classification established in the system.</p>
                    <p>All rates are expressed in United States Dollars (USD) and are subject to the terms of the service agreement.</p>
                </div>

                ${staffingHtml}

                <!-- PAGE SEPARATOR -->
                <div class="proposal-footer-wrapper">
                    <div class="proposal-footer-top">PRIME HOSPITALITY SERVICES OF TEXAS</div>
                    <div class="proposal-footer-bottom"><strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>www.primefacilityservicesgroup.com</div>
                </div>
                <hr class="proposal-page-sep">
                <div class="proposal-header">
                    <img src="${logoSrc}" alt="Prime Hospitality Services" onerror="this.style.display='none'">
                    <div class="title-section">
                        <div class="doc-title">Service</div>
                        <div class="doc-subtitle">PROPOSAL</div>
                    </div>
                </div>

                <!-- TERMS AND CONDITIONS -->
                <div class="proposal-section-title">Terms and Conditions</div>

                <div class="proposal-subsection-title">Billing Terms and Rates</div>
                <div class="proposal-content">
                    <p>The rates established in this agreement shall take effect upon execution by both parties and may be seasonally adjusted with prior written notice.</p>
                    <p>Hours worked by any associate assigned to a client will be billed under the following conditions:</p>
                    <p>Hours exceeding forty (40) in one (1) workweek, or any other applicable legal threshold, will be billed at one and a half (1.5) times the associate's regular rate. Hours worked on company-recognized holidays will also be billed at one and a half (1.5) times the associate's regular rate.</p>
                    <p>The company observes the following official holidays: New Year's Day, Memorial Day, Independence Day, Labor Day, Thanksgiving, and Christmas.</p>
                </div>

                <div class="proposal-subsection-title">Staffing Request Deadlines</div>
                <div class="proposal-content">
                    <p>The deadline for staffing requests is 72 hours prior to the requested start date. Requests submitted after this deadline may be subject to emergency rates, calculated as the regular rate multiplied by 1.5. The minimum service request is 6 hours.</p>
                </div>

                <div class="proposal-subsection-title">Governing Law</div>
                <div class="proposal-content">
                    <p>This Agreement shall be governed by and construed in accordance with the laws of the State of Texas, without regard to its conflict of law principles. The parties agree that any legal action or proceeding arising out of or relating to this Agreement shall be brought in a court of competent jurisdiction located in the State of Texas.</p>
                </div>

                <div class="proposal-subsection-title">Liability &amp; Insurance</div>
                <div class="proposal-content">
                    <p>Prime Hospitality Services of Texas will perform all services in a professional manner, adhering to industry safety standards and applicable health regulations specific to food service environments. Prime Hospitality Services of Texas maintains General Liability and Workers' Compensation Insurance to protect both parties against accidents, injuries, or property damage occurring during the performance of hospitality-related services.</p>
                    <p>The client acknowledges that Prime Hospitality Services of Texas shall not be responsible for pre-existing damages, equipment malfunctions, or issues resulting from improper installation or maintenance not related to the work performed.</p>
                </div>

                <!-- PAGE SEPARATOR -->
                <div class="proposal-footer-wrapper">
                    <div class="proposal-footer-top">PRIME HOSPITALITY SERVICES OF TEXAS</div>
                    <div class="proposal-footer-bottom"><strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>www.primefacilityservicesgroup.com</div>
                </div>
                <hr class="proposal-page-sep">
                <div class="proposal-header">
                    <img src="${logoSrc}" alt="Prime Hospitality Services" onerror="this.style.display='none'">
                    <div class="title-section">
                        <div class="doc-title">Service</div>
                        <div class="doc-subtitle">PROPOSAL</div>
                    </div>
                </div>

                <!-- TERMS AND CONDITIONS (continued) -->
                <div class="proposal-section-title">Terms and Conditions</div>

                <div class="proposal-subsection-title">Confidentiality</div>
                <div class="proposal-content">
                    <p>Both parties agree to maintain strict confidentiality regarding all shared information, including business data, pricing, operational details, and client-specific information related to hospitality operations.</p>
                    <p>Such information shall not be disclosed, distributed, or used for any purpose other than fulfilling the scope of this agreement, except when required by law or with prior written consent from the other party.</p>
                </div>

                <div class="proposal-subsection-title">Termination &amp; Payment Terms</div>
                <div class="proposal-content">
                    <p>Payment terms shall be Net 30 (thirty days from the invoice date). Any outstanding balances after the payment due date may accrue interest at the applicable legal rate and may be subject to reasonable administrative fees.</p>
                    <p>If payment remains unpaid sixty (60) days past the invoice date, services may be suspended upon written notice until the outstanding balance is fully settled. Any amounts formally disputed in writing shall not be considered past due while under review.</p>
                    <p>If services are canceled after scheduling, any completed work, reserved personnel time, or prepared materials shall be invoiced accordingly.</p>
                    <p>The prices mentioned do not include taxes; these will be calculated and added in accordance with applicable law.</p>
                </div>

                <div class="proposal-subsection-title">Non-Solicitation</div>
                <div class="proposal-content">
                    <p>During the term of this Agreement and for a period of two (2) years following its termination, the Client shall not, without the prior written consent of Prime Hospitality Services of Texas, directly or indirectly hire, solicit, or engage the services of any employee, associate, or subcontractor of Prime Hospitality Services of Texas who was employed or engaged by the Company at any time during the term of this Agreement.</p>
                    <p>In the event of a breach of this provision, the Client agrees to pay Prime Hospitality Services of Texas a placement and training fee equal to thirty percent (30%) of the employee's or contractor's most recent annual compensation.</p>
                </div>

                <!-- PAGE SEPARATOR -->
                <div class="proposal-footer-wrapper">
                    <div class="proposal-footer-top">PRIME HOSPITALITY SERVICES OF TEXAS</div>
                    <div class="proposal-footer-bottom"><strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>www.primefacilityservicesgroup.com</div>
                </div>
                <hr class="proposal-page-sep">
                <div class="proposal-header">
                    <img src="${logoSrc}" alt="Prime Hospitality Services" onerror="this.style.display='none'">
                    <div class="title-section">
                        <div class="doc-title">Service</div>
                        <div class="doc-subtitle">PROPOSAL</div>
                    </div>
                </div>

                <!-- TERMS OF AGREEMENT -->
                <div class="proposal-section-title">Terms of Agreement</div>
                <div class="proposal-content">
                    <p>This Agreement shall be valid for a period of ${escapeHtml(contractDuration)} from the date it is executed by both parties. In cases of bankruptcy, insolvency, discontinuation of operations, or failure to make required payments, either party may terminate this Agreement with twenty-four (24) hours' written notice.</p>
                    <p>Upon expiration, this Agreement shall automatically renew for successive one (1) year terms unless either party provides written notice of non-renewal at least thirty (30) days prior to the end of the then-current term. All terms, conditions, and provisions shall remain in full force and effect during any renewal period unless modified in writing by mutual consent of both parties.</p>
                </div>

                <!-- SIGNATURE BLOCK -->
                <div class="proposal-sig-block">
                    <div class="proposal-sig-row">
                        <div class="proposal-sig-col">
                            <div class="proposal-sig-block-title">${companyName}</div>
                            <div class="proposal-sig-item"><div class="proposal-sig-label">Signature:</div><div class="proposal-sig-line"></div></div>
                            <div class="proposal-sig-item"><div class="proposal-sig-label">Printed Name:</div><div class="proposal-sig-line"></div></div>
                            <div class="proposal-sig-item"><div class="proposal-sig-label">Title:</div><div class="proposal-sig-line"></div></div>
                            <div class="proposal-sig-item"><div class="proposal-sig-label">Date:</div><div class="proposal-sig-line"></div></div>
                        </div>
                        <div class="proposal-sig-col">
                            <div class="proposal-sig-block-title">Prime Hospitality Services of Texas</div>
                            <div class="proposal-sig-item"><div class="proposal-sig-label">Signature:</div><div class="proposal-sig-line"></div></div>
                            <div class="proposal-sig-item"><div class="proposal-sig-label">Printed Name:</div><div class="proposal-sig-line"></div></div>
                            <div class="proposal-sig-item"><div class="proposal-sig-label">Title:</div><div class="proposal-sig-line"></div></div>
                            <div class="proposal-sig-item"><div class="proposal-sig-label">Date:</div><div class="proposal-sig-line"></div></div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="proposal-footer-wrapper">
                    <div class="proposal-footer-top">PRIME HOSPITALITY SERVICES OF TEXAS</div>
                    <div class="proposal-footer-bottom"><strong>8303 Westglen Dr - Houston, TX 77063 - Phone 713-338-2553 - Fax 713-574-3065</strong><br>www.primefacilityservicesgroup.com</div>
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
    // SERVICE REPORT DYNAMIC SECTIONS RENDERER
    // ========================================

    /**
     * renderServiceReportSections(data)
     * Generates HTML for the 5 dynamic service-report sections
     * (Scope of Work, Initial Condition, Service Performed,
     *  Post-Service Condition, Technical Data) based on the
     *  _serviceConfig attached to the data by editor.js.
     *
     * Returns an HTML string or '' if no config is present.
     */
    function renderServiceReportSections(data) {
        var cfg = data._serviceConfig;
        if (!cfg) return '';

        var html = '';

        // Styles scoped to the service-report preview block
        html += '<style>';
        html += '.sr-preview-block { margin-top: 20px; }';
        html += '.sr-title { background: #001f54; color: #fff; font-weight: bold; padding: 6px 10px; font-size: 10pt; text-transform: uppercase; margin-bottom: 0; }';
        html += '.sr-section { border: 1px solid #ddd; margin-bottom: 8px; border-radius: 2px; overflow: hidden; }';
        html += '.sr-section-header { background: #001f54; color: #fff; padding: 4px 8px; font-weight: bold; font-size: 9pt; }';
        html += '.sr-section-content { padding: 6px 10px; background: #fafafa; font-size: 9pt; }';
        html += '.sr-checkbox-item { display: block; margin: 2px 0; padding-left: 16px; position: relative; font-size: 9pt; }';
        html += '.sr-checkbox-item:before { content: "\\2610"; position: absolute; left: 0; font-size: 11px; }';
        html += '.sr-two-col { display: flex; gap: 10px; }';
        html += '.sr-two-col .sr-col { flex: 1; }';
        html += '.sr-checklist-table { width: 100%; border-collapse: collapse; font-size: 9pt; }';
        html += '.sr-checklist-table th, .sr-checklist-table td { border: 1px solid #ddd; padding: 3px 5px; text-align: left; }';
        html += '.sr-checklist-table th { background: #e8e8e8; color: #001f54; font-weight: bold; }';
        html += '.sr-checklist-table td.center { text-align: center; width: 30px; }';
        html += '.sr-checklist-table .sr-subheader { background: #d0e4f7; font-weight: bold; color: #001f54; }';
        html += '.sr-tech-table { width: 100%; border-collapse: collapse; }';
        html += '.sr-tech-table td { padding: 3px 6px; font-size: 9pt; border-bottom: 1px solid #eee; }';
        html += '.sr-tech-label { font-weight: bold; color: #001f54; width: 40%; }';
        html += '.sr-tech-value { border-bottom: 1px dotted #999; }';
        html += '</style>';

        html += '<div class="sr-preview-block">';

        // Report title
        html += '<div class="sr-title">' + escapeHtml(cfg.title || 'SERVICE REPORT').toUpperCase() + '</div>';

        // -- Scope of Work --
        if (cfg.scope_of_work && cfg.scope_of_work.length > 0) {
            var mid = Math.ceil(cfg.scope_of_work.length / 2);
            var col1 = cfg.scope_of_work.slice(0, mid);
            var col2 = cfg.scope_of_work.slice(mid);
            html += '<div class="sr-section">';
            html += '<div class="sr-section-header">2. SYSTEM / AREA SERVICED</div>';
            html += '<div class="sr-section-content"><div class="sr-two-col"><div class="sr-col">';
            col1.forEach(function(item) { html += '<div class="sr-checkbox-item">' + escapeHtml(item) + '</div>'; });
            html += '</div><div class="sr-col">';
            col2.forEach(function(item) { html += '<div class="sr-checkbox-item">' + escapeHtml(item) + '</div>'; });
            html += '</div></div></div></div>';
        }

        // -- Initial Condition / Inspection --
        if (cfg.initial_condition && cfg.initial_condition.length > 0) {
            html += '<div class="sr-section">';
            html += '<div class="sr-section-header">3. INITIAL CONDITION / INSPECTION</div>';
            html += '<div class="sr-section-content">';
            html += '<table class="sr-checklist-table"><thead><tr><th>Element</th><th class="center">Yes</th><th class="center">No</th><th class="center">N/A</th><th>Comment</th></tr></thead><tbody>';
            html += '<tr><td colspan="5" class="sr-subheader">' + escapeHtml(cfg.initial_condition_header || 'BEFORE SERVICE') + '</td></tr>';
            cfg.initial_condition.forEach(function(item) {
                html += '<tr><td>' + escapeHtml(item) + '</td><td class="center">&#9744;</td><td class="center">&#9744;</td><td class="center">&#9744;</td><td></td></tr>';
            });
            html += '</tbody></table></div></div>';
        }

        // -- Service Performed --
        if (cfg.service_performed && cfg.service_performed.length > 0) {
            var spMid = Math.ceil(cfg.service_performed.length / 2);
            var spCol1 = cfg.service_performed.slice(0, spMid);
            var spCol2 = cfg.service_performed.slice(spMid);
            html += '<div class="sr-section">';
            html += '<div class="sr-section-header">4. ' + escapeHtml(cfg.service_performed_header || 'SERVICE PERFORMED') + '</div>';
            html += '<div class="sr-section-content"><div class="sr-two-col"><div class="sr-col">';
            spCol1.forEach(function(item) { html += '<div class="sr-checkbox-item">' + escapeHtml(item) + '</div>'; });
            html += '</div><div class="sr-col">';
            spCol2.forEach(function(item) { html += '<div class="sr-checkbox-item">' + escapeHtml(item) + '</div>'; });
            html += '</div></div></div></div>';
        }

        // -- Post-Service Condition --
        if (cfg.post_service_condition && cfg.post_service_condition.length > 0) {
            var psMid = Math.ceil(cfg.post_service_condition.length / 2);
            var psCol1 = cfg.post_service_condition.slice(0, psMid);
            var psCol2 = cfg.post_service_condition.slice(psMid);
            html += '<div class="sr-section">';
            html += '<div class="sr-section-header">5. ' + escapeHtml(cfg.post_service_header || 'POST-SERVICE CONDITION') + '</div>';
            html += '<div class="sr-section-content"><div class="sr-two-col"><div class="sr-col">';
            psCol1.forEach(function(item) { html += '<div class="sr-checkbox-item">' + escapeHtml(item) + '</div>'; });
            html += '</div><div class="sr-col">';
            psCol2.forEach(function(item) { html += '<div class="sr-checkbox-item">' + escapeHtml(item) + '</div>'; });
            html += '</div></div></div></div>';
        }

        // -- Technical Data --
        if (cfg.technical_data && cfg.technical_data.length > 0) {
            html += '<div class="sr-section">';
            html += '<div class="sr-section-header">6. TECHNICAL DATA (If Applicable)</div>';
            html += '<div class="sr-section-content"><table class="sr-tech-table">';
            cfg.technical_data.forEach(function(field) {
                var placeholder = field.type === 'number' ? '______' : '__________________';
                html += '<tr><td class="sr-tech-label">' + escapeHtml(field.label) + ':</td><td class="sr-tech-value">' + placeholder + '</td></tr>';
            });
            html += '</table></div></div>';
        }

        html += '</div>'; // close sr-preview-block
        return html;
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