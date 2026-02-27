<!-- ============================================= -->
<!-- TIMESHEET MODAL - Generate Timesheet Feature -->
<!-- ============================================= -->

<!-- SELECTION MODAL: Choose Timesheet Type -->
<div id="timesheetSelectModal" class="ts-modal-overlay" style="display:none;">
  <div class="ts-modal ts-modal-select">
    <div class="ts-modal-header">
      <h3>⏱ <?= ($lang=='en') ? "Generate Timesheet" : "Generar Timesheet"; ?></h3>
      <button type="button" class="ts-modal-close" onclick="closeTimesheetSelect()">&times;</button>
    </div>
    <div class="ts-modal-body">
      <p class="ts-modal-subtitle">
        <?= ($lang=='en') ? "Select the type of timesheet you want to generate:" : "Seleccione el tipo de timesheet que desea generar:"; ?>
      </p>
      <div class="ts-type-cards">
        <div class="ts-type-card" onclick="openTimesheetType(1)">
          <div class="ts-type-icon">📅</div>
          <h4><?= ($lang=='en') ? "Type 1 - Single Employee (Weekly)" : "Tipo 1 - Empleado Individual (Semanal)"; ?></h4>
          <p><?= ($lang=='en')
            ? "Weekly timesheet for a single employee. Tracks daily hours from Saturday to Friday."
            : "Timesheet semanal para un solo empleado. Registra horas diarias de sábado a viernes."; ?></p>
        </div>
        <div class="ts-type-card" onclick="openTimesheetType(2)">
          <div class="ts-type-icon">👥</div>
          <h4><?= ($lang=='en') ? "Type 2 - Multi Employee (Single Day)" : "Tipo 2 - Múltiples Empleados (Un Día)"; ?></h4>
          <p><?= ($lang=='en')
            ? "Single day timesheet for multiple employees. Tracks hours for all workers on one day."
            : "Timesheet de un solo día para múltiples empleados. Registra horas de todos los trabajadores en un día."; ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- TYPE 1 MODAL: Single Employee Weekly -->
<div id="timesheetType1Modal" class="ts-modal-overlay" style="display:none;">
  <div class="ts-modal ts-modal-full">
    <div class="ts-modal-header">
      <h3>📅 <?= ($lang=='en') ? "Timesheet - Single Employee (Weekly)" : "Timesheet - Empleado Individual (Semanal)"; ?></h3>
      <div class="ts-modal-header-actions">
        <button type="button" class="ts-btn ts-btn-print" onclick="printTimesheet(1)">🖨 <?= ($lang=='en') ? "Print" : "Imprimir"; ?></button>
        <button type="button" class="ts-btn ts-btn-back" onclick="backToSelect(1)">← <?= ($lang=='en') ? "Back" : "Volver"; ?></button>
        <button type="button" class="ts-modal-close" onclick="closeTimesheetType(1)">&times;</button>
      </div>
    </div>
    <div class="ts-modal-body" id="timesheetType1Content">
      <div class="ts-print-area" id="tsType1PrintArea">
        <!-- Company Header -->
        <div class="ts-company-header">
          <img src="<?= __DIR__ ?>/Images/Facility.png" alt="Prime Facility Services" class="ts-logo" onerror="this.style.display='none'">
          <div class="ts-company-info">
            <h2>PRIME FACILITY SERVICES</h2>
            <h3><?= ($lang=='en') ? "WEEKLY TIME SHEET" : "HOJA DE TIEMPO SEMANAL"; ?></h3>
          </div>
        </div>

        <!-- Employee Info -->
        <div class="ts-info-row">
          <div class="ts-info-field">
            <label><?= ($lang=='en') ? "Employee Name:" : "Nombre del Empleado:"; ?></label>
            <input type="text" id="ts1EmployeeName" class="ts-input" placeholder="<?= ($lang=='en') ? 'Enter employee name' : 'Ingrese nombre del empleado'; ?>">
          </div>
          <div class="ts-info-field">
            <label><?= ($lang=='en') ? "Week Ending:" : "Semana que Termina:"; ?></label>
            <input type="date" id="ts1WeekEnding" class="ts-input" onchange="updateType1Days()">
          </div>
          <div class="ts-info-field">
            <label><?= ($lang=='en') ? "Job Site:" : "Sitio de Trabajo:"; ?></label>
            <input type="text" id="ts1JobSite" class="ts-input" placeholder="<?= ($lang=='en') ? 'Enter job site' : 'Ingrese sitio de trabajo'; ?>">
          </div>
        </div>

        <!-- Weekly Table -->
        <div class="ts-table-wrapper">
          <table class="ts-table" id="ts1Table">
            <thead>
              <tr>
                <th><?= ($lang=='en') ? "Day" : "Día"; ?></th>
                <th><?= ($lang=='en') ? "Date" : "Fecha"; ?></th>
                <th><?= ($lang=='en') ? "Time In" : "Entrada"; ?></th>
                <th><?= ($lang=='en') ? "Lunch" : "Almuerzo"; ?></th>
                <th><?= ($lang=='en') ? "Time Out" : "Salida"; ?></th>
                <th>OT</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody id="ts1Body">
            </tbody>
            <tfoot>
              <tr class="ts-total-row">
                <td colspan="5" style="text-align:right;font-weight:700;">
                  <?= ($lang=='en') ? "WEEKLY TOTAL" : "TOTAL SEMANAL"; ?>
                </td>
                <td id="ts1OTTotal" class="ts-total-cell">0.00</td>
                <td id="ts1WeeklyTotal" class="ts-total-cell">0.00</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Signatures -->
        <div class="ts-signatures">
          <div class="ts-sig-block">
            <div class="ts-sig-line"></div>
            <label><?= ($lang=='en') ? "Employee Signature" : "Firma del Empleado"; ?></label>
            <div class="ts-sig-date">
              <span><?= ($lang=='en') ? "Date:" : "Fecha:"; ?></span>
              <div class="ts-sig-date-line"></div>
            </div>
          </div>
          <div class="ts-sig-block">
            <div class="ts-sig-line"></div>
            <label><?= ($lang=='en') ? "Manager Signature" : "Firma del Gerente"; ?></label>
            <div class="ts-sig-date">
              <span><?= ($lang=='en') ? "Date:" : "Fecha:"; ?></span>
              <div class="ts-sig-date-line"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- TYPE 2 MODAL: Multi Employee Single Day -->
<div id="timesheetType2Modal" class="ts-modal-overlay" style="display:none;">
  <div class="ts-modal ts-modal-full">
    <div class="ts-modal-header">
      <h3>👥 <?= ($lang=='en') ? "Timesheet - Multi Employee (Single Day)" : "Timesheet - Múltiples Empleados (Un Día)"; ?></h3>
      <div class="ts-modal-header-actions">
        <button type="button" class="ts-btn ts-btn-save" id="btnSaveTimesheet2" onclick="saveTimesheetPDF(2)">💾 <?= ($lang=='en') ? "Save" : "Guardar"; ?></button>
        <button type="button" class="ts-btn ts-btn-print" onclick="printTimesheet(2)">🖨 <?= ($lang=='en') ? "Print" : "Imprimir"; ?></button>
        <button type="button" class="ts-btn ts-btn-back" onclick="backToSelect(2)">← <?= ($lang=='en') ? "Back" : "Volver"; ?></button>
        <button type="button" class="ts-modal-close" onclick="closeTimesheetType(2)">&times;</button>
      </div>
    </div>
    <div class="ts-modal-body" id="timesheetType2Content">
      <div class="ts-print-area" id="tsType2PrintArea">
        <!-- Company Header -->
        <div class="ts-company-header">
          <img src="<?= __DIR__ ?>/Images/Facility.png" alt="Prime Facility Services" class="ts-logo" onerror="this.style.display='none'">
          <div class="ts-company-info">
            <h2>PRIME FACILITY SERVICES</h2>
            <h3><?= ($lang=='en') ? "DAILY TIME SHEET" : "HOJA DE TIEMPO DIARIA"; ?></h3>
          </div>
        </div>

        <!-- Day Info -->
        <div class="ts-info-row">
          <div class="ts-info-field">
            <label><?= ($lang=='en') ? "Date:" : "Fecha:"; ?></label>
            <input type="date" id="ts2Date" class="ts-input">
          </div>
          <div class="ts-info-field">
            <label><?= ($lang=='en') ? "Job Site:" : "Sitio de Trabajo:"; ?></label>
            <input type="text" id="ts2JobSite" class="ts-input" placeholder="<?= ($lang=='en') ? 'Enter job site' : 'Ingrese sitio de trabajo'; ?>">
          </div>
        </div>

        <!-- Multi Employee Table -->
        <div class="ts-table-wrapper">
          <table class="ts-table" id="ts2Table">
            <thead>
              <tr>
                <th>#</th>
                <th><?= ($lang=='en') ? "Name" : "Nombre"; ?></th>
                <th><?= ($lang=='en') ? "Time In" : "Entrada"; ?></th>
                <th><?= ($lang=='en') ? "Lunch" : "Almuerzo"; ?></th>
                <th><?= ($lang=='en') ? "Time Out" : "Salida"; ?></th>
                <th>Total</th>
                <th class="ts-no-print"><?= ($lang=='en') ? "Action" : "Acción"; ?></th>
              </tr>
            </thead>
            <tbody id="ts2Body">
            </tbody>
            <tfoot>
              <tr class="ts-total-row">
                <td colspan="5" style="text-align:right;font-weight:700;">
                  <?= ($lang=='en') ? "TOTAL" : "TOTAL GENERAL"; ?>
                </td>
                <td id="ts2GrandTotal" class="ts-total-cell">0.00</td>
                <td class="ts-no-print"></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Add Employee Button -->
        <div class="ts-add-row-wrap ts-no-print">
          <button type="button" class="ts-btn ts-btn-add" onclick="addType2Employee()">
            + <?= ($lang=='en') ? "Add Employee" : "Agregar Empleado"; ?>
          </button>
        </div>

        <!-- Manager Signature -->
        <div class="ts-signatures">
          <div class="ts-sig-block">
            <div class="ts-sig-line"></div>
            <label><?= ($lang=='en') ? "Manager Signature" : "Firma del Gerente"; ?></label>
            <div class="ts-sig-date">
              <span><?= ($lang=='en') ? "Date:" : "Fecha:"; ?></span>
              <div class="ts-sig-date-line"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- ============================================= -->
<!-- TIMESHEET STYLES                              -->
<!-- ============================================= -->
<style>
/* Overlay */
.ts-modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,15,40,0.7);
  backdrop-filter: blur(4px);
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: tsFadeIn 0.3s ease;
}

@keyframes tsFadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Modal Box */
.ts-modal {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
  max-height: 92vh;
  display: flex;
  flex-direction: column;
  animation: tsSlideIn 0.3s cubic-bezier(0.16,1,0.3,1);
}

@keyframes tsSlideIn {
  from { opacity: 0; transform: translateY(30px) scale(0.97); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.ts-modal-select {
  width: 650px;
  max-width: 95vw;
}

.ts-modal-full {
  width: 1100px;
  max-width: 96vw;
}

/* Header */
.ts-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 24px;
  background: linear-gradient(135deg, #001f54, #003080);
  color: #fff;
  border-radius: 16px 16px 0 0;
  flex-shrink: 0;
}

.ts-modal-header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
}

.ts-modal-header-actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.ts-modal-close {
  background: rgba(255,255,255,0.2);
  border: none;
  color: #fff;
  width: 34px; height: 34px;
  border-radius: 50%;
  font-size: 20px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.ts-modal-close:hover {
  background: rgba(255,255,255,0.35);
  transform: scale(1.1);
}

/* Body */
.ts-modal-body {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
}

.ts-modal-subtitle {
  font-size: 15px;
  color: #555;
  margin-bottom: 20px;
  text-align: center;
}

/* Type Selection Cards */
.ts-type-cards {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.ts-type-card {
  border: 2px solid #e1e8ed;
  border-radius: 14px;
  padding: 28px 22px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #f9fbfd;
}

.ts-type-card:hover {
  border-color: #001f54;
  background: #e6f0ff;
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0,31,84,0.15);
}

.ts-type-icon {
  font-size: 42px;
  margin-bottom: 14px;
}

.ts-type-card h4 {
  font-size: 16px;
  font-weight: 700;
  color: #001f54;
  margin-bottom: 10px;
}

.ts-type-card p {
  font-size: 13px;
  color: #666;
  line-height: 1.5;
}

/* Company Header (inside timesheet) */
.ts-company-header {
  display: flex;
  align-items: center;
  gap: 18px;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 3px solid #001f54;
}

.ts-logo {
  height: 60px;
  width: auto;
}

.ts-company-info h2 {
  font-size: 22px;
  font-weight: 700;
  color: #001f54;
  margin: 0;
}

.ts-company-info h3 {
  font-size: 15px;
  font-weight: 600;
  color: #555;
  margin: 4px 0 0 0;
}

/* Info Row */
.ts-info-row {
  display: flex;
  gap: 16px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.ts-info-field {
  flex: 1;
  min-width: 180px;
}

.ts-info-field label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #001f54;
  margin-bottom: 5px;
}

.ts-input {
  width: 100%;
  padding: 10px 14px;
  border: 2px solid #dde3ea;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
  transition: border-color 0.2s;
}

.ts-input:focus {
  outline: none;
  border-color: #001f54;
  box-shadow: 0 0 0 3px rgba(0,31,84,0.08);
}

/* Table */
.ts-table-wrapper {
  overflow-x: auto;
  margin-bottom: 16px;
}

.ts-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.ts-table thead {
  background: linear-gradient(135deg, #001f54, #003080);
}

.ts-table th {
  color: #fff;
  padding: 12px 8px;
  font-weight: 600;
  text-align: center;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  white-space: nowrap;
}

.ts-table td {
  padding: 6px 4px;
  text-align: center;
  border-bottom: 1px solid #e8ecf0;
  vertical-align: middle;
}

.ts-table tbody tr:hover {
  background: #f0f5ff;
}

.ts-table input[type="time"],
.ts-table input[type="text"],
.ts-table input[type="number"] {
  width: 100%;
  min-width: 80px;
  padding: 7px 6px;
  border: 1.5px solid #dde3ea;
  border-radius: 6px;
  font-size: 13px;
  text-align: center;
  font-family: inherit;
  transition: border-color 0.2s;
  margin: 0;
}

.ts-table input:focus {
  outline: none;
  border-color: #001f54;
  box-shadow: 0 0 0 2px rgba(0,31,84,0.08);
}

.ts-day-label {
  font-weight: 600;
  color: #001f54;
  white-space: nowrap;
}

.ts-date-label {
  font-size: 12px;
  color: #888;
  white-space: nowrap;
}

.ts-daily-total,
.ts-ot-cell {
  font-weight: 700;
  color: #001f54;
  font-size: 14px;
  min-width: 60px;
}

/* Total Row */
.ts-total-row {
  background: linear-gradient(135deg, #001f54, #003080) !important;
}

.ts-total-row td {
  color: #fff !important;
  font-weight: 700;
  font-size: 15px;
  padding: 14px 8px;
  border: none;
}

.ts-total-cell {
  font-size: 18px !important;
  color: #ffd700 !important;
}

/* Signatures */
.ts-signatures {
  display: flex;
  gap: 50px;
  margin-top: 30px;
  padding-top: 20px;
  flex-wrap: wrap;
}

.ts-sig-block {
  flex: 1;
  min-width: 220px;
  text-align: center;
}

.ts-sig-line {
  border-bottom: 2px solid #333;
  height: 50px;
  margin-bottom: 6px;
}

.ts-sig-block label {
  font-size: 13px;
  font-weight: 600;
  color: #333;
}

.ts-sig-date {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 10px;
  justify-content: center;
}

.ts-sig-date span {
  font-size: 12px;
  font-weight: 600;
  color: #555;
}

.ts-sig-date-line {
  flex: 1;
  max-width: 150px;
  border-bottom: 1px solid #999;
  height: 1px;
}

/* Buttons */
.ts-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 18px;
  border: none;
  border-radius: 25px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-family: inherit;
}

.ts-btn-save {
  background: #007bff;
  color: #fff;
}

.ts-btn-save:hover {
  background: #0069d9;
  transform: translateY(-1px);
}

.ts-btn-save:disabled {
  background: #6c757d;
  cursor: not-allowed;
  transform: none;
  opacity: 0.7;
}

.ts-btn-print {
  background: #28a745;
  color: #fff;
}

.ts-btn-print:hover {
  background: #218838;
  transform: translateY(-1px);
}

.ts-btn-back {
  background: rgba(255,255,255,0.2);
  color: #fff;
}

.ts-btn-back:hover {
  background: rgba(255,255,255,0.35);
}

.ts-btn-add {
  background: linear-gradient(135deg, #001f54, #003080);
  color: #fff;
  padding: 11px 28px;
  font-size: 14px;
}

.ts-btn-add:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,31,84,0.3);
}

.ts-btn-remove {
  background: #dc3545;
  color: #fff;
  padding: 6px 14px;
  font-size: 12px;
  border-radius: 20px;
}

.ts-btn-remove:hover {
  background: #c82333;
}

.ts-add-row-wrap {
  text-align: center;
  margin: 12px 0;
}

/* Responsive */
@media (max-width: 700px) {
  .ts-type-cards {
    grid-template-columns: 1fr;
  }
  .ts-modal-full {
    max-width: 99vw;
    max-height: 96vh;
  }
  .ts-modal-header {
    flex-wrap: wrap;
    gap: 8px;
  }
  .ts-info-row {
    flex-direction: column;
  }
  .ts-signatures {
    flex-direction: column;
    gap: 30px;
  }
}

/* ============ PRINT STYLES (Timesheet) ============ */
@media print {
  /* Hide everything except the print area */
  body > *:not(#tsPrintContainer) {
    display: none !important;
  }

  #tsPrintContainer {
    display: block !important;
    position: absolute;
    top: 0; left: 0;
    width: 100%;
    padding: 20px;
    background: #fff;
  }

  .ts-no-print {
    display: none !important;
  }

  .ts-table input[type="time"],
  .ts-table input[type="text"],
  .ts-table input[type="number"] {
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
    padding: 4px !important;
    font-weight: 600;
  }

  .ts-input {
    border: none !important;
    border-bottom: 1px solid #333 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    background: transparent !important;
    font-weight: 600;
  }

  .ts-table {
    box-shadow: none;
  }

  .ts-table thead {
    background: #001f54 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .ts-total-row {
    background: #001f54 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .ts-total-row td {
    color: #fff !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .ts-total-cell {
    color: #c70734 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>

<!-- Hidden container used when printing -->
<div id="tsPrintContainer" style="display:none;"></div>


<!-- ============================================= -->
<!-- TIMESHEET JAVASCRIPT                          -->
<!-- ============================================= -->
<script>
(function() {
  'use strict';

  // ─── LANG helper ───
  const LANG = '<?= $lang ?>';
  const t = (en, es) => LANG === 'en' ? en : es;

  // ─── Days of the week (Sat-Fri) ───
  const DAYS_EN = ['Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday'];
  const DAYS_ES = ['Sábado','Domingo','Lunes','Martes','Miércoles','Jueves','Viernes'];
  const DAYS = LANG === 'en' ? DAYS_EN : DAYS_ES;

  // ─── Open Selection Modal ───
  document.getElementById('btnGenerateTimesheet').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('timesheetSelectModal').style.display = 'flex';
  });

  window.closeTimesheetSelect = function() {
    document.getElementById('timesheetSelectModal').style.display = 'none';
  };

  // ─── Open specific type ───
  window.openTimesheetType = function(type) {
    closeTimesheetSelect();
    if (type === 1) {
      initType1();
      document.getElementById('timesheetType1Modal').style.display = 'flex';
    } else {
      initType2();
      document.getElementById('timesheetType2Modal').style.display = 'flex';
    }
  };

  window.closeTimesheetType = function(type) {
    document.getElementById('timesheetType' + type + 'Modal').style.display = 'none';
  };

  window.backToSelect = function(type) {
    closeTimesheetType(type);
    document.getElementById('timesheetSelectModal').style.display = 'flex';
  };

  // Close on overlay click
  document.querySelectorAll('.ts-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
      if (e.target === this) this.style.display = 'none';
    });
  });

  // ═══════════════════════════════════════════
  // TYPE 1 - Single Employee Weekly
  // ═══════════════════════════════════════════
  let type1Initialized = false;

  function initType1() {
    if (type1Initialized) return;
    type1Initialized = true;

    // Set default week ending to this Friday
    const today = new Date();
    const dayOfWeek = today.getDay(); // 0=Sun
    const diff = (5 - dayOfWeek + 7) % 7;
    const friday = new Date(today);
    friday.setDate(today.getDate() + diff);
    document.getElementById('ts1WeekEnding').value = formatDate(friday);

    buildType1Table();
  }

  function formatDate(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    const dd = String(d.getDate()).padStart(2,'0');
    return y + '-' + m + '-' + dd;
  }

  function formatDateShort(d) {
    return String(d.getMonth()+1) + '/' + String(d.getDate());
  }

  window.updateType1Days = function() {
    type1Initialized = false;
    buildType1Table();
    type1Initialized = true;
  };

  function buildType1Table() {
    const weekEndVal = document.getElementById('ts1WeekEnding').value;
    const tbody = document.getElementById('ts1Body');
    tbody.innerHTML = '';

    // Friday = week ending. Saturday = 6 days before
    let friday;
    if (weekEndVal) {
      friday = new Date(weekEndVal + 'T12:00:00');
    } else {
      friday = new Date();
    }

    const saturday = new Date(friday);
    saturday.setDate(friday.getDate() - 6);

    for (let i = 0; i < 7; i++) {
      const d = new Date(saturday);
      d.setDate(saturday.getDate() + i);

      const tr = document.createElement('tr');
      tr.innerHTML =
        '<td class="ts-day-label">' + DAYS[i] + '</td>' +
        '<td class="ts-date-label">' + formatDateShort(d) + '</td>' +
        '<td><input type="time" data-row="' + i + '" data-col="timeIn" onchange="calcType1Row(' + i + ')"></td>' +
        '<td><input type="number" data-row="' + i + '" data-col="lunchMin" min="0" max="240" placeholder="min" style="min-width:60px;" onchange="calcType1Row(' + i + ')"></td>' +
        '<td><input type="time" data-row="' + i + '" data-col="timeOut" onchange="calcType1Row(' + i + ')"></td>' +
        '<td class="ts-ot-cell" id="ts1OT' + i + '">0.00</td>' +
        '<td class="ts-daily-total" id="ts1Total' + i + '">0.00</td>';
      tbody.appendChild(tr);
    }
  }

  window.calcType1Row = function(row) {
    const get = (col) => {
      const el = document.querySelector('#ts1Body input[data-row="' + row + '"][data-col="' + col + '"]');
      return el ? el.value : '';
    };

    const timeIn = get('timeIn');
    const lunchMin = parseInt(get('lunchMin'), 10) || 0;
    const timeOut = get('timeOut');

    let totalHours = 0;

    if (timeIn && timeOut) {
      const inMin = timeToMin(timeIn);
      let outMin = timeToMin(timeOut);

      // Handle overnight shifts
      if (outMin <= inMin) outMin += 1440;

      let worked = outMin - inMin;

      // Subtract lunch break
      if (lunchMin > 0 && lunchMin < 240) {
        worked -= lunchMin;
      }

      totalHours = Math.max(0, worked / 60);
    }

    const otHours = Math.max(0, totalHours - 8);

    document.getElementById('ts1OT' + row).textContent = otHours.toFixed(2);
    document.getElementById('ts1Total' + row).textContent = totalHours.toFixed(2);

    calcType1Totals();
  };

  function calcType1Totals() {
    let grandTotal = 0;
    let otTotal = 0;
    for (let i = 0; i < 7; i++) {
      grandTotal += parseFloat(document.getElementById('ts1Total' + i).textContent) || 0;
      otTotal += parseFloat(document.getElementById('ts1OT' + i).textContent) || 0;
    }
    document.getElementById('ts1WeeklyTotal').textContent = grandTotal.toFixed(2);
    document.getElementById('ts1OTTotal').textContent = otTotal.toFixed(2);
  }

  // ═══════════════════════════════════════════
  // TYPE 2 - Multi Employee Single Day
  // ═══════════════════════════════════════════
  let type2RowCount = 0;
  let type2Initialized = false;

  function initType2() {
    if (type2Initialized) return;
    type2Initialized = true;

    // Default today
    document.getElementById('ts2Date').value = formatDate(new Date());

    // Start with 5 empty rows
    const tbody = document.getElementById('ts2Body');
    tbody.innerHTML = '';
    type2RowCount = 0;
    for (let i = 0; i < 5; i++) {
      addType2Employee();
    }
  }

  window.addType2Employee = function() {
    const tbody = document.getElementById('ts2Body');
    const idx = type2RowCount++;
    const tr = document.createElement('tr');
    tr.id = 'ts2Row' + idx;
    tr.innerHTML =
      '<td style="font-weight:600;color:#001f54;">' + (tbody.children.length + 1) + '</td>' +
      '<td><input type="text" data-row="' + idx + '" data-col="name" placeholder="' + t('Employee name','Nombre del empleado') + '" style="min-width:120px;"></td>' +
      '<td><input type="time" data-row="' + idx + '" data-col="timeIn" onchange="calcType2Row(' + idx + ')"></td>' +
      '<td><input type="number" data-row="' + idx + '" data-col="lunchMin" min="0" max="240" placeholder="min" style="min-width:60px;" onchange="calcType2Row(' + idx + ')"></td>' +
      '<td><input type="time" data-row="' + idx + '" data-col="timeOut" onchange="calcType2Row(' + idx + ')"></td>' +
      '<td class="ts-daily-total" id="ts2Total' + idx + '">0.00</td>' +
      '<td class="ts-no-print"><button type="button" class="ts-btn ts-btn-remove" onclick="removeType2Employee(' + idx + ')">✕</button></td>';
    tbody.appendChild(tr);
  };

  window.removeType2Employee = function(idx) {
    const row = document.getElementById('ts2Row' + idx);
    if (row) {
      row.remove();
      renumberType2();
      calcType2Totals();
    }
  };

  function renumberType2() {
    const rows = document.querySelectorAll('#ts2Body tr');
    rows.forEach((tr, i) => {
      tr.querySelector('td').textContent = i + 1;
    });
  }

  window.calcType2Row = function(idx) {
    const get = (col) => {
      const el = document.querySelector('#ts2Row' + idx + ' input[data-col="' + col + '"]');
      return el ? el.value : '';
    };

    const timeIn = get('timeIn');
    const lunchMin = parseInt(get('lunchMin'), 10) || 0;
    const timeOut = get('timeOut');

    let totalHours = 0;

    if (timeIn && timeOut) {
      const inMin = timeToMin(timeIn);
      let outMin = timeToMin(timeOut);
      if (outMin <= inMin) outMin += 1440;

      let worked = outMin - inMin;

      // Subtract lunch break
      if (lunchMin > 0 && lunchMin < 240) {
        worked -= lunchMin;
      }

      totalHours = Math.max(0, worked / 60);
    }

    document.getElementById('ts2Total' + idx).textContent = totalHours.toFixed(2);

    calcType2Totals();
  };

  function calcType2Totals() {
    let grandTotal = 0;
    document.querySelectorAll('#ts2Body tr').forEach(tr => {
      const id = tr.id.replace('ts2Row','');
      const totalEl = document.getElementById('ts2Total' + id);
      if (totalEl) grandTotal += parseFloat(totalEl.textContent) || 0;
    });
    document.getElementById('ts2GrandTotal').textContent = grandTotal.toFixed(2);
  }

  // ═══════════════════════════════════════════
  // HELPERS
  // ═══════════════════════════════════════════
  function timeToMin(t) {
    const parts = t.split(':');
    return parseInt(parts[0],10) * 60 + parseInt(parts[1],10);
  }

  // ═══════════════════════════════════════════
  // PRINT
  // ═══════════════════════════════════════════
  window.printTimesheet = function(type) {
    const printArea = document.getElementById('tsType' + type + 'PrintArea');
    const clone = printArea.cloneNode(true);

    // Replace inputs with their display values for cleaner print
    clone.querySelectorAll('input').forEach(inp => {
      const span = document.createElement('span');
      if (inp.type === 'number' && inp.value) {
        span.textContent = inp.value + ' min';
      } else {
        span.textContent = inp.value || '';
      }
      span.style.fontWeight = '600';
      inp.parentNode.replaceChild(span, inp);
    });

    // Remove action column cells from print
    clone.querySelectorAll('.ts-no-print').forEach(el => el.remove());

    // Move print container to body so CSS selector body > *:not(#tsPrintContainer) works
    let printContainer = document.getElementById('tsPrintContainer');
    if (printContainer.parentNode !== document.body) {
      document.body.appendChild(printContainer);
    }

    printContainer.innerHTML = '';
    printContainer.appendChild(clone);
    printContainer.style.display = 'block';

    // Hide modals during print
    document.querySelectorAll('.ts-modal-overlay').forEach(m => m.style.visibility = 'hidden');

    window.print();

    // Restore after print
    setTimeout(() => {
      printContainer.style.display = 'none';
      printContainer.innerHTML = '';
      document.querySelectorAll('.ts-modal-overlay').forEach(m => m.style.visibility = '');
    }, 500);
  };

  // ═══════════════════════════════════════════
  // SAVE TIMESHEET AS PDF → BILLING
  // ═══════════════════════════════════════════
  window.saveTimesheetPDF = function(type) {
    // Verify a form is loaded
    if (typeof currentFormId === 'undefined' || !currentFormId) {
      alert(t(
        '⚠️ No Work Order selected. Please load a form first before saving the timesheet.',
        '⚠️ No hay una Orden de Trabajo seleccionada. Por favor cargue un formulario antes de guardar el timesheet.'
      ));
      return;
    }

    const printArea = document.getElementById('tsType' + type + 'PrintArea');
    if (!printArea) return;

    const btn = document.getElementById('btnSaveTimesheet' + type);
    const originalText = btn ? btn.innerHTML : '';

    // Disable button and show loading
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '⏳ ' + t('Saving...', 'Guardando...');
    }

    // Clone the print area and replace inputs with their values
    const clone = printArea.cloneNode(true);

    clone.querySelectorAll('input').forEach(function(inp) {
      const span = document.createElement('span');
      if (inp.type === 'number' && inp.value) {
        span.textContent = inp.value + ' min';
      } else if (inp.type === 'date' && inp.value) {
        // Format date nicely
        var parts = inp.value.split('-');
        span.textContent = parts[1] + '/' + parts[2] + '/' + parts[0];
      } else {
        span.textContent = inp.value || '';
      }
      span.style.fontWeight = '600';
      inp.parentNode.replaceChild(span, inp);
    });

    // Remove action column cells (buttons)
    clone.querySelectorAll('.ts-no-print').forEach(function(el) { el.remove(); });

    var htmlContent = clone.innerHTML;

    // Send to server
    fetch('save_timesheet_pdf.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        form_id: currentFormId,
        html_content: htmlContent,
        timesheet_type: type
      })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      if (data.success) {
        alert(t(
          '✅ Timesheet saved successfully!\n\n' +
          '• PDF generated: ' + (data.data.pdf_filename || '') + '\n' +
          '• Linked to Work Order: ' + (data.data.order_number || '') + '\n' +
          '• Available in Billing module',
          '✅ ¡Timesheet guardado exitosamente!\n\n' +
          '• PDF generado: ' + (data.data.pdf_filename || '') + '\n' +
          '• Vinculado a Orden de Trabajo: ' + (data.data.order_number || '') + '\n' +
          '• Disponible en el módulo de Billing'
        ));
      } else {
        alert(t(
          '❌ Error saving timesheet: ' + (data.error || 'Unknown error'),
          '❌ Error al guardar timesheet: ' + (data.error || 'Error desconocido')
        ));
      }
    })
    .catch(function(err) {
      console.error('Error saving timesheet:', err);
      alert(t(
        '❌ Connection error. Please try again.',
        '❌ Error de conexión. Por favor intente de nuevo.'
      ));
    })
    .finally(function() {
      // Re-enable button
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    });
  };

  // ─── ESC key to close modals ───
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.ts-modal-overlay').forEach(m => {
        if (m.style.display === 'flex') m.style.display = 'none';
      });
    }
  });

})();
</script>
