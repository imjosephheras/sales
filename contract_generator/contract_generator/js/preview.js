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
                    <p><strong>Contact:</strong> ${data.Contact_Name || 'N/A'}</p>
                    <p><strong>Email:</strong> ${data.Contact_Email || 'N/A'}</p>
                    <p><strong>Phone:</strong> ${data.Contact_Phone || 'N/A'}</p>
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
        // Use Company_Address or fallback
        const address = data.Company_Address || 'N/A';

        const totalPrice = formatPrice(data.Total_Price || data.Prime_Quoted_Price || data.PriceInput);

        const termsMap = {
            '15': 'Net 15',
            '30': 'Net 30',
            '50_deposit': '50% Deposit',
            'completion': 'Upon Completion'
        };
        const terms = termsMap[data.Invoice_Frequency] || 'Upon Completion';

        // Get contact info with fallbacks
        const contactName = data.client_name || '';
        const contactTitle = data.Client_Title || '';
        const contactEmail = data.Email || '';
        const contactPhone = data.Number_Phone || '';
        const seller = data.Seller || 'N/A';
        const isNewClient = data.Is_New_Client === 'Yes' ? ' (New Client)' : '';

        // Build scope of work list
        let scopeWorkHtml = '';
        if (data.Scope_Of_Work && Array.isArray(data.Scope_Of_Work) && data.Scope_Of_Work.length > 0) {
            scopeWorkHtml = '<ul>' + data.Scope_Of_Work.map(item => `<li>${escapeHtml(item)}</li>`).join('') + '</ul>';
        } else {
            scopeWorkHtml = `
                <ul>
                    <li>Pre-cleaning and preparation of all areas listed above</li>
                    <li>Professional execution of requested services</li>
                    <li>Quality inspection during and after service completion</li>
                    <li>Cleaning of work area to maintain professional standards</li>
                    <li>Final inspection to ensure quality standards are met</li>
                </ul>
            `;
        }

        return `
            <div class="document-preview jwo-preview">
                <style>
                    .jwo-preview { font-family: Arial, sans-serif; font-size: 11pt; }
                    .jwo-header { display: flex; justify-content: space-between; align-items: center;
                                  border-bottom: 3px solid #8B1A1A; padding-bottom: 10px; margin-bottom: 15px; }
                    .jwo-logo { background: #8B1A1A; color: white; padding: 12px 20px;
                                position: relative; display: inline-block; }
                    .jwo-logo-name { font-size: 20pt; font-weight: bold; letter-spacing: 2px; }
                    .jwo-logo-tag { font-size: 7pt; margin-top: 2px; }
                    .jwo-title { text-align: right; }
                    .jwo-title h1 { color: #8B1A1A; font-size: 18pt; margin: 0; }
                    .jwo-title p { font-size: 8pt; font-style: italic; margin: 5px 0 0 0; }

                    .jwo-info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                    .jwo-info-table td { border: 1px solid #333; padding: 8px; }
                    .jwo-info-table .label { background: #f0f0f0; font-weight: bold; width: 25%; }
                    .jwo-info-table .label-sm { background: #f0f0f0; font-weight: bold; width: 20%; }

                    .jwo-services { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                    .jwo-services th { background: #8B1A1A; color: white; padding: 8px;
                                      border: 1px solid #333; font-size: 9pt; }
                    .jwo-services td { border: 1px solid #333; padding: 6px 8px; text-align: center; }
                    .jwo-services .amount { text-align: right; font-weight: bold; }
                    .jwo-services .total-row { background: #f0f0f0; font-weight: bold; }

                    .jwo-scope { margin-top: 15px; }
                    .jwo-scope-header { background: #8B1A1A; color: white; padding: 6px 10px;
                                       font-weight: bold; font-size: 9pt; margin-bottom: 8px; }
                    .jwo-scope-content { border: 1px solid #333; padding: 10px; font-size: 9pt; }
                    .jwo-scope-content h4 { font-size: 9pt; text-decoration: underline; margin: 8px 0 4px 0; }
                    .jwo-scope-content ul { margin-left: 20px; }
                    .jwo-scope-content li { margin-bottom: 3px; }
                </style>

                <!-- Header -->
                <div class="jwo-header">
                    <div class="jwo-logo">
                        <div class="jwo-logo-name">PRIME</div>
                        <div class="jwo-logo-tag">Facility Services Group</div>
                    </div>
                    <div class="jwo-title">
                        <h1>JOB WORK ORDER</h1>
                        <p>"The best services in the industry or nothing at all"</p>
                    </div>
                </div>

                <!-- Bill To -->
                <table class="jwo-info-table">
                    <tr>
                        <td class="label" rowspan="3" style="vertical-align: middle; text-align: center;">
                            <strong>BILL TO</strong>
                        </td>
                        <td>
                            <strong>${data.Company_Name || 'N/A'}${isNewClient}</strong><br>
                            ${contactName}${contactTitle ? ' - ' + contactTitle : ''}<br>
                            ${contactEmail}<br>
                            ${contactPhone}
                        </td>
                    </tr>
                </table>

                <!-- Work Info -->
                <table class="jwo-info-table">
                    <tr>
                        <td class="label-sm">Work Site</td>
                        <td style="width: 30%;">${address}</td>
                        <td class="label-sm">Sales Person</td>
                        <td style="width: 30%;">${seller}</td>
                    </tr>
                    <tr>
                        <td class="label-sm">Work Date</td>
                        <td>${data.Work_Date || new Date().toLocaleDateString()}</td>
                        <td class="label-sm">Department</td>
                        <td>${data.Service_Type || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td class="label-sm">Terms</td>
                        <td>${terms}</td>
                        <td class="label-sm">Work Order Number</td>
                        <td><strong>${data.docnum || 'DRAFT'}</strong></td>
                    </tr>
                </table>

                <!-- Services -->
                <table class="jwo-services">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Type of Services</th>
                            <th style="width: 10%;">Day</th>
                            <th style="width: 15%;">Frequency</th>
                            <th style="width: 15%;">Duration</th>
                            <th style="width: 15%;">Amount per Service</th>
                            <th style="width: 15%;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: left;">${data.Requested_Service || 'Service'}</td>
                            <td>${data.Service_Day || '1'}</td>
                            <td>${data.Service_Frequency || 'One Time'}</td>
                            <td>${data.Service_Duration || '4-5 Hours'}</td>
                            <td class="amount">$${totalPrice}</td>
                            <td class="amount">$${totalPrice}</td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="5" style="text-align: right; padding-right: 10px;">TOTAL</td>
                            <td class="amount">$${totalPrice}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Scope -->
                <div class="jwo-scope">
                    <div class="jwo-scope-header">
                        SCOPE OF WORK - ${(data.Requested_Service || 'SERVICE').toUpperCase()}
                    </div>
                    <div class="jwo-scope-content">
                        ${data.Site_Observation ? `
                            <h4>Area to be Serviced:</h4>
                            <p>${data.Site_Observation.replace(/\n/g, '<br>')}</p>
                        ` : ''}

                        <h4>Work to be Performed:</h4>
                        ${scopeWorkHtml}

                        ${data.Additional_Comments ? `
                            <h4>Additional Notes:</h4>
                            <p>${data.Additional_Comments.replace(/\n/g, '<br>')}</p>
                        ` : ''}
                    </div>
                </div>

                <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;
                            border-radius: 4px; text-align: center; font-size: 9pt; color: #666;">
                    <p style="margin: 0;">
                        📄 <strong>Preview Mode</strong> - This is a simplified preview.
                        The PDF will include additional pages with requirements and signature sections.
                    </p>
                </div>
            </div>
        `;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
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