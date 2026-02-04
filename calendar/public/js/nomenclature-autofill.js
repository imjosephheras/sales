/**
 * ============================================================
 * NOMENCLATURE AUTO-FILL SYSTEM - DESACTIVADO
 * ============================================================
 *
 * Este sistema ha sido DESACTIVADO según las instrucciones:
 * - La nomenclatura se usa directamente como: HJ-10000112222026
 * - NO hay auto-fill basado en TYPE-FREQ-DUR
 * - NO hay regex parser/generator
 * - NO hay internalCode H1000
 *
 * El Calendar ahora obtiene los datos directamente del Request Form:
 * - Company Name
 * - Client Name
 * - Business Address
 * - Work Date
 * - Document Date
 *
 * El único campo adicional que se llena manualmente es: Description
 *
 * Frequency y Duration se mantienen pero NO automatizan recurrencias.
 * Las recurrencias se ajustan MANUALMENTE después de crear el evento.
 *
 * ============================================================
 */

(function() {
    'use strict';

    console.log('📋 Nomenclature Auto-Fill System: DESACTIVADO');
    console.log('   La nomenclatura se usa directamente desde Request Form');
    console.log('   Formato: HJ-10000112222026');
    console.log('   No hay auto-fill automático');

})();
