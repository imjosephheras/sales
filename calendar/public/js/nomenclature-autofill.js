/**
 * ============================================================
 * NOMENCLATURE AUTO-FILL SYSTEM - SIMPLIFIED VERSION
 * Uses Form Contract nomenclature: HJ-XXXXXX MMDDYYYY
 * Example: HJ-10000112222026
 * ============================================================
 */

(function() {
    'use strict';

    console.log('Loading Nomenclature Auto-Fill System (Simplified)...');

    // Wait for DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        console.log('Initializing Nomenclature System...');

        // Update dropdowns
        updateDropdowns();

        // Setup parser for Form Contract nomenclature
        setupNomenclatureParser();

        console.log('Nomenclature Auto-Fill System loaded');
        console.log('Format: HJ-{OrderNumber}{MMDDYYYY}');
        console.log('Example: HJ-10000112222026');
    }

    /**
     * UPDATE DROPDOWNS
     */
    function updateDropdowns() {
        // Frequency dropdown
        const frequencySelect = document.getElementById('frequencyMonths');
        if (frequencySelect) {
            frequencySelect.innerHTML = `
                <option value="">No recurrence</option>
                <option value="1">1 - Monthly</option>
                <option value="2">2 - Bimonthly</option>
                <option value="3">3 - Quarterly</option>
                <option value="4">4 - Every 4 months</option>
                <option value="6">6 - Semi-annual</option>
                <option value="12">12 - Annual</option>
            `;
            console.log('Frequency dropdown updated');
        }

        // Duration dropdown
        const durationSelect = document.getElementById('frequencyYears');
        if (durationSelect) {
            let options = '';
            for (let i = 1; i <= 10; i++) {
                options += `<option value="${i}">${i} year${i > 1 ? 's' : ''}</option>`;
            }
            durationSelect.innerHTML = options;
            console.log('Duration dropdown updated');
        }
    }

    /**
     * SETUP PARSER: Detects Form Contract nomenclature and auto-fills date
     */
    function setupNomenclatureParser() {
        const titleInput = document.getElementById('eventTitle');

        if (!titleInput) {
            console.warn('eventTitle input not found');
            return;
        }

        console.log('Parser attached to eventTitle');

        // Multiple events to ensure detection
        titleInput.addEventListener('input', parseNomenclature);
        titleInput.addEventListener('blur', parseNomenclature);
        titleInput.addEventListener('change', parseNomenclature);
    }

    /**
     * PARSER: Extracts date from Form Contract nomenclature
     * Format: HJ-{6-digit number}{MMDDYYYY}
     * Example: HJ-10000112222026
     */
    function parseNomenclature() {
        const titleInput = document.getElementById('eventTitle');
        if (!titleInput) return;

        let title = titleInput.value.trim();

        if (!title) return;

        // Remove "Service - " prefix if present
        if (title.toLowerCase().startsWith('service - ')) {
            title = title.substring(10);
        }

        console.log('Parsing:', title);

        // Regex for Form Contract nomenclature: HJ-XXXXXX MMDDYYYY
        // HJ- + 6 digits (100000-999999) + 8 digits (MMDDYYYY)
        const regex = /^HJ-(\d{6})(\d{8})$/i;
        const match = title.match(regex);

        if (!match) {
            console.log('No Form Contract nomenclature pattern detected');
            return;
        }

        const [full, orderNumber, dateStr] = match;

        console.log('Form Contract nomenclature detected!', {
            orderNumber,
            date: dateStr
        });

        // Extract and validate date (MMDDYYYY)
        const month = dateStr.substring(0, 2);
        const day = dateStr.substring(2, 4);
        const year = dateStr.substring(4, 8);
        const parsedDate = `${year}-${month}-${day}`;

        console.log('Parsed date:', parsedDate);

        if (!isValidDate(parsedDate)) {
            console.error('Invalid date:', parsedDate);
            return;
        }

        // AUTO-FILL FIELDS
        let fieldsUpdated = 0;

        // 1. Document Date
        const documentDateInput = document.getElementById('documentDate');
        if (documentDateInput) {
            documentDateInput.value = parsedDate;
            highlightField(documentDateInput);
            fieldsUpdated++;
            console.log('Document Date:', parsedDate);
        }

        // 2. Start Date
        const startDateInput = document.getElementById('eventStartDate');
        if (startDateInput && !startDateInput.value) {
            startDateInput.value = parsedDate;
            highlightField(startDateInput);
            fieldsUpdated++;
            console.log('Start Date:', parsedDate);
        }

        // 3. End Date (same day by default)
        const endDateInput = document.getElementById('eventEndDate');
        if (endDateInput && !endDateInput.value) {
            endDateInput.value = parsedDate;
            highlightField(endDateInput);
            fieldsUpdated++;
            console.log('End Date:', parsedDate);
        }

        if (fieldsUpdated > 0) {
            showNotification(`${fieldsUpdated} fields auto-filled from nomenclature!`, 'success');
        }
    }

    /**
     * Highlight field temporarily
     */
    function highlightField(field) {
        const originalBg = field.style.background;
        field.style.background = '#fef3c7';
        field.style.transition = 'background 0.3s';

        setTimeout(() => {
            field.style.background = originalBg;
        }, 1500);
    }

    /**
     * Validate date
     */
    function isValidDate(dateString) {
        const date = new Date(dateString);
        return date instanceof Date && !isNaN(date);
    }

    /**
     * Simple notification
     */
    function showNotification(message, type) {
        console.log(message);

        if (typeof window.showNotification === 'function') {
            window.showNotification(message, type);
        }
    }

})();
