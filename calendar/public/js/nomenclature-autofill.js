/**
 * ============================================================
 * NOMENCLATURE AUTO-FILL SYSTEM - IMPROVED VERSION
 * Con mejor detección de eventos y logging
 * ============================================================
 */

(function() {
    'use strict';
    
    console.log('🔄 Loading Nomenclature Auto-Fill System...');
    
    // Esperar a que DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        console.log('🚀 Initializing Nomenclature System...');
        
        // Actualizar dropdowns primero
        updateDropdowns();
        
        // Configurar auto-fill desde nomenclatura
        setupNomenclatureParser();
        
        // Configurar auto-generación de nomenclatura
        setupNomenclatureGenerator();
        
        console.log('✅ Nomenclature Auto-Fill System loaded');
        console.log('📋 Format: TYPE-CODE(MMDDYYYY)-FREQ-DURATION');
        console.log('   Example: JWO-H100012302025-03-01');
    }
    
    /**
     * ACTUALIZAR DROPDOWNS
     */
    function updateDropdowns() {
        // Frequency dropdown
        const frequencySelect = document.getElementById('frequencyMonths');
        if (frequencySelect) {
            frequencySelect.innerHTML = `
                <option value="">No recurrence</option>
                <option value="1">1 - Mensual</option>
                <option value="2">2 - Bimestral</option>
                <option value="3">3 - Trimestral</option>
                <option value="4">4 - Cuatrimestral</option>
                <option value="6">6 - Semestral</option>
                <option value="12">12 - Anual</option>
            `;
            console.log('✓ Frequency dropdown updated');
        } else {
            console.warn('⚠ frequencyMonths dropdown not found');
        }
        
        // Duration dropdown
        const durationSelect = document.getElementById('frequencyYears');
        if (durationSelect) {
            let options = '';
            for (let i = 1; i <= 10; i++) {
                options += `<option value="${i}">${i} year${i > 1 ? 's' : ''}</option>`;
            }
            durationSelect.innerHTML = options;
            console.log('✓ Duration dropdown updated');
        } else {
            console.warn('⚠ frequencyYears dropdown not found');
        }
    }
    
    /**
     * SETUP PARSER: Detecta nomenclatura y auto-llena campos
     */
    function setupNomenclatureParser() {
        const titleInput = document.getElementById('eventTitle');
        
        if (!titleInput) {
            console.warn('⚠ eventTitle input not found');
            return;
        }
        
        console.log('✓ Parser attached to eventTitle');
        
        // Múltiples eventos para asegurar detección
        titleInput.addEventListener('input', parseNomenclature);
        titleInput.addEventListener('blur', parseNomenclature);
        titleInput.addEventListener('change', parseNomenclature);
        
        // También parsear al abrir modal
        const observer = new MutationObserver(function(mutations) {
            if (titleInput.value) {
                parseNomenclature();
            }
        });
        
        observer.observe(titleInput, { attributes: true, attributeFilter: ['value'] });
    }
    
    /**
     * PARSER: Extrae datos de la nomenclatura
     */
    function parseNomenclature() {
        const titleInput = document.getElementById('eventTitle');
        if (!titleInput) return;
        
        const title = titleInput.value.trim();
        
        if (!title) return;
        
        console.log('🔍 Parsing:', title);
        
        // Regex flexible: TYPE-CODE(MMDDYYYY)-FREQ-DURATION
        // Ejemplos: 
        // JWO-H100012302025-03-01
        // C-H100012302025-03-01
        // P-ABC123012312025-06-02
        const regex = /^([A-Z]+)-([A-Z0-9]+)(\d{8})-(\d{1,2})-(\d{1,2})$/i;
        const match = title.match(regex);
        
        if (!match) {
            console.log('ℹ No nomenclature pattern detected');
            return;
        }
        
        const [full, type, code, dateStr, frequency, duration] = match;
        
        console.log('✅ Nomenclature detected!', {
            type, 
            code, 
            date: dateStr, 
            frequency: frequency + ' months', 
            duration: duration + ' years'
        });
        
        // Extraer y validar fecha (MMDDYYYY)
        const month = dateStr.substring(0, 2);
        const day = dateStr.substring(2, 4);
        const year = dateStr.substring(4, 8);
        const parsedDate = `${year}-${month}-${day}`;
        
        console.log('📅 Parsed date:', parsedDate);
        
        if (!isValidDate(parsedDate)) {
            console.error('❌ Invalid date:', parsedDate);
            return;
        }
        
        // AUTO-LLENAR CAMPOS
        let fieldsUpdated = 0;
        
        // 1. Document Date
        const documentDateInput = document.getElementById('documentDate');
        if (documentDateInput) {
            documentDateInput.value = parsedDate;
            highlightField(documentDateInput);
            fieldsUpdated++;
            console.log('✓ Document Date:', parsedDate);
        }
        
        // 2. Start Date
        const startDateInput = document.getElementById('eventStartDate');
        if (startDateInput && !startDateInput.value) {
            startDateInput.value = parsedDate;
            highlightField(startDateInput);
            fieldsUpdated++;
            console.log('✓ Start Date:', parsedDate);
        }
        
        // 3. End Date (mismo día por defecto)
        const endDateInput = document.getElementById('eventEndDate');
        if (endDateInput && !endDateInput.value) {
            endDateInput.value = parsedDate;
            highlightField(endDateInput);
            fieldsUpdated++;
            console.log('✓ End Date:', parsedDate);
        }
        
        // 4. Frequency
        const frequencyInput = document.getElementById('frequencyMonths');
        if (frequencyInput) {
            const freq = parseInt(frequency);
            frequencyInput.value = freq;
            highlightField(frequencyInput);
            fieldsUpdated++;
            console.log('✓ Frequency:', freq, 'months');
        }
        
        // 5. Duration
        const durationInput = document.getElementById('frequencyYears');
        if (durationInput) {
            const dur = parseInt(duration);
            durationInput.value = dur;
            highlightField(durationInput);
            fieldsUpdated++;
            console.log('✓ Duration:', dur, 'years');
        }
        
        // 6. Category
        const categoryInput = document.getElementById('eventCategory');
        if (categoryInput) {
            const categoryId = getCategoryIdByCode(type);
            if (categoryId) {
                categoryInput.value = categoryId;
                highlightField(categoryInput);
                fieldsUpdated++;
                console.log('✓ Category:', type, '(ID:', categoryId + ')');
            }
        }
        
        if (fieldsUpdated > 0) {
            showNotification(`✨ ${fieldsUpdated} fields auto-filled from nomenclature!`, 'success');
        }
    }
    
    /**
     * Highlight field temporalmente
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
     * Validar fecha
     */
    function isValidDate(dateString) {
        const date = new Date(dateString);
        return date instanceof Date && !isNaN(date);
    }
    
    /**
     * Mapear código a category_id
     */
    function getCategoryIdByCode(code) {
        const categorySelect = document.getElementById('eventCategory');
        if (!categorySelect) return null;
        
        const codeUpper = code.toUpperCase();
        
        // Mapeo de códigos
        const mapping = {
            'JWO': 'JWO',
            'C': 'Contract',
            'P': 'Proposal'
        };
        
        const searchName = mapping[codeUpper];
        if (!searchName) return null;
        
        // Buscar en options
        const options = categorySelect.options;
        for (let i = 0; i < options.length; i++) {
            const text = options[i].text.toUpperCase();
            if (text.includes(searchName.toUpperCase())) {
                return options[i].value;
            }
        }
        
        return null;
    }
    
    /**
     * ============================================================
     * NOMENCLATURE GENERATOR
     * Auto-genera nomenclatura cuando se llenan campos
     * ============================================================
     */
    
    function setupNomenclatureGenerator() {
        const fields = [
            'documentDate',
            'frequencyMonths', 
            'frequencyYears',
            'eventCategory'
        ];
        
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('change', generateNomenclature);
                console.log('✓ Generator attached to', fieldId);
            }
        });
    }
    
    function generateNomenclature() {
        const titleInput = document.getElementById('eventTitle');
        
        // Solo generar si título está vacío
        if (!titleInput || titleInput.value.trim()) {
            return;
        }
        
        const documentDate = document.getElementById('documentDate')?.value;
        const frequency = document.getElementById('frequencyMonths')?.value;
        const duration = document.getElementById('frequencyYears')?.value;
        const categoryId = document.getElementById('eventCategory')?.value;
        
        console.log('🔧 Generating nomenclature...', {
            documentDate,
            frequency,
            duration,
            categoryId
        });
        
        // Necesitamos todos los campos
        if (!documentDate || !frequency || !duration || !categoryId) {
            console.log('ℹ Missing fields for generation');
            return;
        }
        
        // Obtener código de categoría
        const categoryCode = getCategoryCodeFromId(categoryId);
        if (!categoryCode) {
            console.log('⚠ Could not determine category code');
            return;
        }
        
        // Código interno (genérico por ahora)
        const internalCode = 'H1000';
        
        // Formatear fecha a MMDDYYYY
        const date = new Date(documentDate);
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const year = date.getFullYear();
        const dateFormatted = `${month}${day}${year}`;
        
        // Formatear frecuencia y duración
        const freqFormatted = String(frequency).padStart(2, '0');
        const durFormatted = String(duration).padStart(2, '0');
        
        // Generar nomenclatura
        const nomenclature = `${categoryCode}-${internalCode}${dateFormatted}-${freqFormatted}-${durFormatted}`;
        
        console.log('✨ Generated:', nomenclature);
        
        titleInput.value = nomenclature;
        highlightField(titleInput);
        
        showNotification('✨ Nomenclature auto-generated!', 'success');
    }
    
    function getCategoryCodeFromId(categoryId) {
        const categorySelect = document.getElementById('eventCategory');
        if (!categorySelect) return null;
        
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        if (!selectedOption) return null;
        
        const text = selectedOption.text.toUpperCase();
        
        // Mapear nombre a código
        if (text.includes('JWO')) return 'JWO';
        if (text.includes('CONTRACT')) return 'C';
        if (text.includes('PROPOSAL')) return 'P';
        
        // Fallback: primera letra
        return text.charAt(0).toUpperCase();
    }
    
    /**
     * Notificación simple
     */
    function showNotification(message, type) {
        console.log('💬', message);
        
        if (typeof window.showNotification === 'function') {
            window.showNotification(message, type);
        }
    }
    
})();