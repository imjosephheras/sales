<!-- ==================================== -->
<!-- 💰 Section 4: Economic Information -->
<!-- ==================================== -->

<div class="section-title">
  <?= ($lang=='en') ? "Section 4: Economic Information" : "Sección 4: Información Económica"; ?>
</div>

<!-- 16️⃣ Seller -->
<div class="question-block" id="q16">
  <label for="Seller" class="question-label">
    <?= ($lang=='en') ? "16. Seller" : "16. Vendedor"; ?>
  </label>

  <select name="Seller" id="Seller" onchange="updatePriceLabel()">
    <option value="">
      <?= ($lang=='en') ? "Select a seller" : "Seleccione un vendedor"; ?>
    </option>
    <option value="Keny Howe">Keny Howe</option>
    <option value="Norma Bustos">Norma Bustos</option>
    <option value="Sandra Hernandez">Sandra Hernandez</option>
    <option value="Miguel Palma">Miguel Palma</option>
    <option value="Rafael Perez JR">Rafael Perez JR</option>
    <option value="Patty Perez">Patty Perez</option>
  </select>
</div>

<!-- 17️⃣ Price -->
<div class="question-block" id="q17">
  <label for="PriceInput" id="PriceLabel" class="question-label">
    <?= ($lang=='en') ? "17. Prime Quote Price" : "17. Precio de la cotización (Prime)"; ?>
  </label>

  <input
    type="text"
    name="PriceInput"
    id="PriceInput"
    placeholder="<?= ($lang=='en') ? 'Enter price' : 'Ingrese precio'; ?>"
  >
</div>

<script>
function updatePriceLabel() {
  const seller = document.getElementById("Seller").value;
  const label = document.getElementById("PriceLabel");

  if (seller === "Miguel Palma" || seller === "Sandra Hernandez") {
    label.textContent = "17. <?= ($lang=='en') ? "Subcontractor Price" : "Precio Subcontratista"; ?>";
  } else {
    label.textContent = "17. <?= ($lang=='en') ? "Prime Quote Price" : "Precio Prime"; ?>";
  }
}
</script>

<!-- Load Janitorial Services Catalog -->
<script src="janitorial_services_catalog.js"></script>

<!-- 18️⃣ Janitorial Services -->
<div class="question-block" id="q18">
  <label for="includeJanitorial" class="question-label">
    <?= ($lang=='en') ? "18. Janitorial Services" : "18. Servicios de Limpieza"; ?>
  </label>

  <select id="includeJanitorial" name="includeJanitorial" onchange="toggleSection18()">
    <option value="">
      <?= ($lang=='en') ? "-- Select an option --" : "-- Seleccione una opción --"; ?>
    </option>
    <option value="No"><?= ($lang=='en') ? "No" : "No"; ?></option>
    <option value="Yes"><?= ($lang=='en') ? "Yes" : "Sí"; ?></option>
  </select>

  <div id="section18Container" style="display:none; margin-top:20px;">

    <!-- ADD / REMOVE / BUNDLE -->
    <div style="margin-bottom:15px; display:flex; flex-wrap:wrap; gap:8px;">
      <button type="button" class="btn18 addRow18" onclick="addRow18()">
        ➕ <?= ($lang=='en') ? "Add Row" : "Agregar Fila"; ?>
      </button>

      <button type="button" class="btn18 removeRow18" onclick="removeRow18()">
        🗑 <?= ($lang=='en') ? "Remove" : "Eliminar"; ?>
      </button>

      <button type="button" class="btn18 bundleBtn18" onclick="bundleSelectedRows18()">
        🔗 <?= ($lang=='en') ? "Bundle Price" : "Unir Precio"; ?>
      </button>

      <button type="button" class="btn18 unbundleBtn18" onclick="unbundleSelectedRows18()">
        ✂️ <?= ($lang=='en') ? "Unbundle" : "Separar Precio"; ?>
      </button>
    </div>

    <!-- TABLE -->
    <table class="service-table18">
      <thead>
        <tr>
          <th class="th-check">&nbsp;</th>
          <th><?= ($lang=='en') ? "Type of Services" : "Tipo de Servicio"; ?></th>
          <th><?= ($lang=='en') ? "Service Time" : "Tiempo de Servicio"; ?></th>
          <th><?= ($lang=='en') ? "Frequency" : "Frecuencia"; ?></th>
          <th><?= ($lang=='en') ? "Description" : "Descripción"; ?></th>
          <th><?= ($lang=='en') ? "Subtotal" : "Subtotal"; ?></th>
        </tr>
      </thead>

      <tbody id="table18body">

        <!-- ONE INITIAL ROW -->
        <tr>

          <!-- CHECKBOX -->
          <td class="td-check">
            <input type="checkbox" class="bundle-check18">
            <input type="hidden" class="bundleGroup18" name="bundleGroup18[]" value="">
          </td>

          <!-- TYPE OF SERVICES (MODAL SELECTOR) -->
          <td>
            <input type="hidden" class="type18" name="type18[]" value="">
            <input type="hidden" class="scope18" name="scope18[]" value="">
            <div class="janitorial-selector-btn" onclick="openJanitorialModal(this)">
              <span class="janitorial-selector-text"><?= ($lang=='en') ? "Select Service..." : "Seleccionar Servicio..."; ?></span>
              <span class="janitorial-selector-icon">&#9662;</span>
            </div>
          </td>

          <!-- SERVICE TIME -->
          <td>
            <select class="time18" name="time18[]">
              <option value="">
                <?= ($lang=='en') ? "-- Select Time --" : "-- Seleccione tiempo --"; ?>
              </option>
              <option><?= ($lang=='en') ? "1 Day" : "1 Dia"; ?></option>
              <option><?= ($lang=='en') ? "1-2 Days" : "1-2 Dias"; ?></option>
              <option><?= ($lang=='en') ? "3 Days" : "3 Dias"; ?></option>
              <option><?= ($lang=='en') ? "4 Days" : "4 Dias"; ?></option>
              <option><?= ($lang=='en') ? "5 Days" : "5 Dias"; ?></option>
              <option><?= ($lang=='en') ? "6 Days" : "6 Dias"; ?></option>
              <option><?= ($lang=='en') ? "7 Days" : "7 Dias"; ?></option>
            </select>
          </td>

          <!-- FREQUENCY -->
          <td>
            <select class="freq18" name="freq18[]">
              <option value="">
                <?= ($lang=='en') ? "-- Select Period --" : "-- Seleccione periodo --"; ?>
              </option>
              <option><?= ($lang=='en') ? "One Time" : "Una Vez"; ?></option>
              <option><?= ($lang=='en') ? "Weekly" : "Semanal"; ?></option>
              <option><?= ($lang=='en') ? "Every 2 Weeks" : "Cada 2 Semanas"; ?></option>
              <option><?= ($lang=='en') ? "Every 3 Weeks" : "Cada 3 Semanas"; ?></option>
              <option><?= ($lang=='en') ? "Monthly" : "Mensual"; ?></option>
              <option><?= ($lang=='en') ? "Bimonthly" : "Bimestral"; ?></option>
              <option><?= ($lang=='en') ? "Quarterly" : "Trimestral"; ?></option>
              <option><?= ($lang=='en') ? "Every 4 Months" : "Cada 4 Meses"; ?></option>
              <option><?= ($lang=='en') ? "Semiannual" : "Semestral"; ?></option>
              <option><?= ($lang=='en') ? "Annual" : "Anual"; ?></option>
            </select>
          </td>

          <!-- DESCRIPTION -->
          <td>
            <input type="text"
              class="desc18"
              name="desc18[]"
              placeholder="<?= ($lang=='en') ? 'Write description...' : 'Escriba la descripcion...'; ?>">
          </td>

          <!-- SUBTOTAL -->
          <td class="subtotal-cell18">
            <input type="number" step="0.01"
              class="subtotal18"
              name="subtotal18[]"
              placeholder="0.00"
              oninput="calcTotals18()">
          </td>

        </tr>

      </tbody>
    </table>

    <!-- TOTAL BOXES -->
    <div class="totals18-container">

      <div class="tot-box-18">
        <div class="tot-header-18">
          <?= ($lang=='en') ? "TOTAL" : "TOTAL"; ?>
        </div>
        <input type="text" id="total18" readonly name="total18">
      </div>

      <div class="tot-box-18">
        <div class="tot-header-18">
          <?= ($lang=='en') ? "TAXES (8.25%)" : "IMPUESTOS (8.25%)"; ?>
        </div>
        <input type="text" id="taxes18" readonly name="taxes18">
      </div>

      <div class="tot-box-18">
        <div class="tot-header-18">
          <?= ($lang=='en') ? "GRAND TOTAL" : "TOTAL GENERAL"; ?>
        </div>
        <input type="text" id="grand18" readonly name="grand18">
      </div>

    </div>

  </div>
</div>

<!-- ======================================= -->
<!-- JANITORIAL SERVICE SELECTOR MODAL -->
<!-- ======================================= -->
<div id="janitorialModal" class="janitorial-modal-overlay" style="display:none;">
  <div class="janitorial-modal">
    <div class="janitorial-modal-header">
      <h3><?= ($lang=='en') ? "Select a Janitorial Service" : "Seleccionar un Servicio de Limpieza"; ?></h3>
      <button type="button" class="janitorial-modal-close" onclick="closeJanitorialModal()">&times;</button>
    </div>

    <div class="janitorial-modal-search">
      <input type="text" id="janitorialModalSearch"
        placeholder="<?= ($lang=='en') ? 'Search services...' : 'Buscar servicios...'; ?>"
        oninput="filterJanitorialCards()">
    </div>

    <div class="janitorial-modal-body" id="janitorialModalBody">
      <!-- Cards are generated dynamically from janitorialServicesCatalog -->
    </div>
  </div>
</div>

<style>
  /* ===== TABLE 18 STYLES ===== */
  .service-table18 {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-size: 14px;
  }
  .service-table18 th {
    background-color: #c00;
    color: #fff;
    padding: 8px;
    text-align: center;
  }
  .service-table18 td {
    border: 1px solid #ddd;
    padding: 8px;
  }
  .service-table18 select,
  .service-table18 input[type="text"],
  .service-table18 input[type="number"] {
    width: 100%;
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 4px;
  }
  .btn18 {
    padding: 6px 14px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
  }
  .addRow18 { background-color:#008c4a; color:white; }
  .removeRow18 { background-color:#777; color:white; }
  .bundleBtn18 { background-color:#0066cc; color:white; }
  .unbundleBtn18 { background-color:#cc6600; color:white; }

  /* Checkbox column */
  .service-table18 .th-check,
  .service-table18 .td-check,
  .service-table19 .th-check,
  .service-table19 .td-check {
    width: 32px;
    min-width: 32px;
    text-align: center;
    padding: 4px;
  }

  /* Bundle visual styles */
  .bundle-row {
    position: relative;
  }
  .bundle-row > td:first-child {
    border-left: 4px solid #0066cc;
  }
  .bundle-row-primary {
    background-color: #e8f4fd;
  }
  .bundle-row-secondary {
    background-color: #f0f7fd;
  }
  .bundle-included-label {
    display: block;
    text-align: center;
    color: #0066cc;
    font-weight: 600;
    font-size: 13px;
    font-style: italic;
    padding: 6px;
  }
  /* Merged bundle price cell */
  .bundle-price-merged {
    vertical-align: middle;
    background: linear-gradient(135deg, #e8f4fd 0%, #d0e8fa 100%);
    border-left: 3px solid #0066cc;
    text-align: center;
  }
  .bundle-price-merged input[type="number"] {
    font-size: 16px;
    font-weight: 700;
    color: #001f54;
    text-align: center;
    border: 2px solid #0066cc;
    border-radius: 6px;
    background: #fff;
    padding: 8px;
  }
  .bundle-price-label {
    display: block;
    font-size: 11px;
    color: #0066cc;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
  }
  .totals18-container {
    margin-top: 25px;
    display: flex;
    gap: 25px;
    flex-wrap: wrap;
  }
  .tot-box-18 {
    width: 220px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background:white;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
  }
  .tot-header-18 {
    background-color:#c00;
    color:white;
    padding:8px;
    text-align:center;
    font-weight:bold;
  }
  .tot-box-18 input {
    width:100%;
    padding:10px;
    text-align:right;
    font-weight:bold;
    background:#f7f7f7;
    border:none;
  }

  /* ===== JANITORIAL SELECTOR BUTTON ===== */
  .janitorial-selector-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    border: 2px solid #ccc;
    border-radius: 6px;
    cursor: pointer;
    background: #fff;
    min-height: 38px;
    transition: all 0.2s ease;
    user-select: none;
  }
  .janitorial-selector-btn:hover {
    border-color: #001f54;
    background: #f0f4ff;
  }
  .janitorial-selector-btn.has-value {
    border-color: #001f54;
    background: #e8f0fe;
  }
  .janitorial-selector-text {
    flex: 1;
    color: #666;
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .janitorial-selector-btn.has-value .janitorial-selector-text {
    color: #001f54;
    font-weight: 600;
  }
  .janitorial-selector-icon {
    margin-left: 8px;
    color: #999;
    font-size: 12px;
  }

  /* ===== JANITORIAL MODAL ===== */
  .janitorial-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 10001;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }
  .janitorial-modal {
    background: #fff;
    border-radius: 12px;
    width: 100%;
    max-width: 800px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    overflow: hidden;
  }
  .janitorial-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    background: #001f54;
    color: #fff;
  }
  .janitorial-modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
  }
  .janitorial-modal-close {
    background: none;
    border: none;
    color: #fff;
    font-size: 28px;
    cursor: pointer;
    line-height: 1;
    padding: 0 4px;
    opacity: 0.8;
    transition: opacity 0.2s;
  }
  .janitorial-modal-close:hover {
    opacity: 1;
  }
  .janitorial-modal-search {
    padding: 16px 24px 8px;
  }
  .janitorial-modal-search input {
    width: 100%;
    padding: 10px 16px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
  }
  .janitorial-modal-search input:focus {
    border-color: #001f54;
  }
  .janitorial-modal-body {
    padding: 16px 24px 24px;
    overflow-y: auto;
    flex: 1;
  }

  /* ===== JANITORIAL CATEGORY HEADER ===== */
  .janitorial-modal-category {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #999;
    margin: 16px 0 8px;
    padding-bottom: 4px;
    border-bottom: 1px solid #eee;
  }
  .janitorial-modal-category:first-child {
    margin-top: 0;
  }

  /* ===== JANITORIAL SERVICE CARDS ===== */
  .janitorial-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 12px;
  }
  .janitorial-card {
    border: 2px solid #e1e8ed;
    border-radius: 10px;
    padding: 16px;
    cursor: pointer;
    background: #fafbfc;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .janitorial-card:hover {
    border-color: #001f54;
    background: #e8f0fe;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,31,84,0.15);
  }
  .janitorial-card-name {
    font-size: 14px;
    font-weight: 700;
    color: #001f54;
  }
  .janitorial-card-scope-count {
    font-size: 11px;
    color: #888;
  }
  .janitorial-card-no-results {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px 20px;
    color: #999;
    font-style: italic;
  }
  .janitorial-card-other {
    border-style: dashed;
    border-color: #999;
    background: #f9f9f9;
  }
  .janitorial-card-other:hover {
    border-color: #c00;
    background: #fff5f5;
  }
  .janitorial-card-other .janitorial-card-name {
    color: #666;
  }
</style>

<script>
// ===== JANITORIAL MODAL STATE =====
let activeJanitorialRow = null;

// ===== TOGGLE SECTION 18 =====
function toggleSection18() {
  document.getElementById("section18Container").style.display =
    document.getElementById("includeJanitorial").value === "Yes"
    ? "block" : "none";
}

// ===== OPEN JANITORIAL MODAL =====
function openJanitorialModal(btn) {
  activeJanitorialRow = btn.closest("tr");
  const modal = document.getElementById("janitorialModal");
  const search = document.getElementById("janitorialModalSearch");

  search.value = "";
  renderJanitorialCards("");

  modal.style.display = "flex";
  setTimeout(() => search.focus(), 100);
}

// ===== CLOSE JANITORIAL MODAL =====
function closeJanitorialModal() {
  document.getElementById("janitorialModal").style.display = "none";
  activeJanitorialRow = null;
}

// Close modal on overlay click
document.addEventListener("click", function(e) {
  if (e.target.id === "janitorialModal") closeJanitorialModal();
});

// Close modal on Escape key
document.addEventListener("keydown", function(e) {
  if (e.key === "Escape" && document.getElementById("janitorialModal").style.display === "flex") {
    closeJanitorialModal();
  }
});

// ===== RENDER JANITORIAL SERVICE CARDS =====
function renderJanitorialCards(filter) {
  const body = document.getElementById("janitorialModalBody");
  const lowerFilter = filter.toLowerCase();

  const filtered = janitorialServicesCatalog.filter(svc =>
    svc.name.toLowerCase().includes(lowerFilter) ||
    svc.category.toLowerCase().includes(lowerFilter)
  );

  let html = "";

  if (filtered.length === 0) {
    html += '<div class="janitorial-cards-grid"><div class="janitorial-card-no-results">' +
      '<?= ($lang=="en") ? "No services found" : "No se encontraron servicios"; ?>' +
      '</div></div>';
  } else {
    // Group by category
    const grouped = {};
    filtered.forEach(svc => {
      if (!grouped[svc.category]) grouped[svc.category] = [];
      grouped[svc.category].push(svc);
    });

    for (const cat in grouped) {
      html += '<div class="janitorial-modal-category">' + cat + '</div>';
      html += '<div class="janitorial-cards-grid">';
      grouped[cat].forEach(svc => {
        const scopeCount = svc.scope ? svc.scope.length : 0;
        html += '<div class="janitorial-card" onclick="selectJanitorialService(\'' + svc.id + '\')">';
        html += '<div class="janitorial-card-name">' + svc.name + '</div>';
        html += '<div class="janitorial-card-scope-count">' + scopeCount + ' <?= ($lang=="en") ? "scope items" : "elementos de scope"; ?></div>';
        html += '</div>';
      });
      html += '</div>';
    }
  }

  // Always show "Other" option at the bottom
  html += '<div class="janitorial-modal-category"><?= ($lang=="en") ? "Other" : "Otro"; ?></div>';
  html += '<div class="janitorial-cards-grid">';
  html += '<div class="janitorial-card janitorial-card-other" onclick="selectCustomJanitorialService()">';
  html += '<div class="janitorial-card-name"><?= ($lang=="en") ? "Other (Custom)" : "Otro (Personalizado)"; ?></div>';
  html += '<div class="janitorial-card-scope-count"><?= ($lang=="en") ? "Enter service name manually" : "Ingrese el nombre del servicio manualmente"; ?></div>';
  html += '</div>';
  html += '</div>';

  body.innerHTML = html;
}

// ===== FILTER JANITORIAL SERVICE CARDS =====
function filterJanitorialCards() {
  const val = document.getElementById("janitorialModalSearch").value;
  renderJanitorialCards(val);
}

// ===== SELECT JANITORIAL SERVICE FROM MODAL =====
function selectJanitorialService(serviceId) {
  if (!activeJanitorialRow) return;

  const svc = janitorialCatalogById[serviceId];
  if (!svc) return;

  // Set hidden type18 value
  const typeInput = activeJanitorialRow.querySelector(".type18");
  typeInput.value = svc.name;

  // Set hidden scope18 value (JSON array of scope items)
  const scopeInput = activeJanitorialRow.querySelector(".scope18");
  scopeInput.value = JSON.stringify(svc.scope || []);

  // Update button display
  const btn = activeJanitorialRow.querySelector(".janitorial-selector-btn");
  const textSpan = btn.querySelector(".janitorial-selector-text");
  textSpan.textContent = svc.name;
  btn.classList.add("has-value");

  closeJanitorialModal();
}

// ===== SELECT CUSTOM JANITORIAL SERVICE (Other) =====
function selectCustomJanitorialService() {
  if (!activeJanitorialRow) return;

  const customName = prompt(
    '<?= ($lang=="en") ? "Enter the service name:" : "Ingrese el nombre del servicio:"; ?>'
  );

  if (!customName || customName.trim() === '') return;

  const trimmedName = customName.trim();

  // Set hidden type18 value with custom name
  const typeInput = activeJanitorialRow.querySelector(".type18");
  typeInput.value = trimmedName;

  // Set empty scope18 (no predefined scope for custom services)
  const scopeInput = activeJanitorialRow.querySelector(".scope18");
  scopeInput.value = '[]';

  // Update button display
  const btn = activeJanitorialRow.querySelector(".janitorial-selector-btn");
  const textSpan = btn.querySelector(".janitorial-selector-text");
  textSpan.textContent = trimmedName;
  btn.classList.add("has-value");

  closeJanitorialModal();
}

// ===== ADD ROW =====
function addRow18() {
  const tbody = document.getElementById("table18body");
  const newRow = tbody.children[0].cloneNode(true);

  // Reset all inputs
  newRow.querySelectorAll("select, input").forEach(el => el.value = "");

  // Reset janitorial selector button
  const btn = newRow.querySelector(".janitorial-selector-btn");
  const textSpan = btn.querySelector(".janitorial-selector-text");
  textSpan.textContent = "<?= ($lang=='en') ? 'Select Service...' : 'Seleccionar Servicio...'; ?>";
  btn.classList.remove("has-value");

  // Reset bundle state
  newRow.classList.remove("bundle-row", "bundle-row-primary", "bundle-row-secondary");
  const chk = newRow.querySelector(".bundle-check18");
  if (chk) chk.checked = false;
  const subtotalCell = newRow.querySelector(".subtotal-cell18");
  if (subtotalCell) {
    const lbl = subtotalCell.querySelector(".bundle-included-label");
    if (lbl) lbl.remove();
    const inp = subtotalCell.querySelector(".subtotal18");
    if (inp) inp.style.display = "";
  }

  tbody.appendChild(newRow);
}

// ===== REMOVE ROW =====
function removeRow18() {
  const tbody = document.getElementById("table18body");
  if (tbody.children.length > 1) {
    const rowToRemove = tbody.lastElementChild;
    const bundleGroup = rowToRemove.querySelector(".bundleGroup18")?.value;

    // If removing a bundled row, unbundle the entire group first
    if (bundleGroup) {
      unbundleGroup18(bundleGroup);
    }

    tbody.lastElementChild.remove();
    calcTotals18();
  } else {
    alert("<?= ($lang=='en')
      ? 'At least one row must remain.'
      : 'Debe permanecer al menos una fila.'; ?>");
  }
}

// ===== CALCULATE TOTALS =====
function calcTotals18() {
  let total = 0;

  document.querySelectorAll(".subtotal18").forEach(input => {
    // Skip secondary bundle rows (their price is included in the primary row)
    const row = input.closest("tr");
    if (row && row.classList.contains("bundle-row-secondary")) return;

    // Also skip if input is explicitly hidden (legacy support)
    if (input.style.display === "none") return;

    const val = parseFloat(input.value);
    if (!isNaN(val)) total += val;
  });

  document.getElementById("total18").value = "$" + total.toFixed(2);

  const taxes = total * 0.0825;
  document.getElementById("taxes18").value = "$" + taxes.toFixed(2);

  document.getElementById("grand18").value = "$" + (total + taxes).toFixed(2);
}

// ===== BUNDLE SELECTED ROWS =====
function bundleSelectedRows18() {
  const tbody = document.getElementById("table18body");
  const checked = tbody.querySelectorAll(".bundle-check18:checked");

  if (checked.length < 2) {
    alert("<?= ($lang=='en') ? 'Select at least 2 rows to bundle.' : 'Seleccione al menos 2 filas para unir.'; ?>");
    return;
  }

  // Collect selected rows and check none are already bundled
  const rows = [];
  let hasExisting = false;
  checked.forEach(chk => {
    const row = chk.closest("tr");
    const existing = row.querySelector(".bundleGroup18").value;
    if (existing) {
      hasExisting = true;
    }
    rows.push(row);
  });

  if (hasExisting) {
    alert("<?= ($lang=='en') ? 'Some selected rows are already bundled. Unbundle them first.' : 'Algunas filas ya están unidas. Sepárelas primero.'; ?>");
    return;
  }

  if (rows.length < 2) return;

  // Generate unique bundle group ID
  const groupId = "bg_" + Date.now();

  // Sum subtotals from all selected rows
  let sum = 0;
  rows.forEach(row => {
    const inp = row.querySelector(".subtotal18");
    const val = parseFloat(inp.value);
    if (!isNaN(val)) sum += val;
  });

  // Apply bundle to each row
  rows.forEach((row, idx) => {
    row.querySelector(".bundleGroup18").value = groupId;
    row.classList.add("bundle-row");

    const subtotalCell = row.querySelector(".subtotal-cell18");
    const subtotalInput = row.querySelector(".subtotal18");

    if (idx === 0) {
      // Primary row: merged price cell with rowspan
      row.classList.add("bundle-row-primary");
      subtotalCell.rowSpan = rows.length;
      subtotalCell.classList.add("bundle-price-merged");
      subtotalInput.value = sum.toFixed(2);

      // Add bundle price label
      const label = document.createElement("span");
      label.className = "bundle-price-label";
      label.textContent = "<?= ($lang=='en') ? 'Bundle Price' : 'Precio Unido'; ?>";
      subtotalCell.insertBefore(label, subtotalInput);
    } else {
      // Secondary rows: hide subtotal cell entirely (covered by rowspan)
      row.classList.add("bundle-row-secondary");
      row.dataset.originalSubtotal = subtotalInput.value || "0";
      subtotalInput.value = "";
      subtotalCell.style.display = "none";
    }

    // Uncheck
    row.querySelector(".bundle-check18").checked = false;
  });

  calcTotals18();
}

// ===== UNBUNDLE SELECTED ROWS =====
function unbundleSelectedRows18() {
  const tbody = document.getElementById("table18body");
  const checked = tbody.querySelectorAll(".bundle-check18:checked");

  if (checked.length === 0) {
    alert("<?= ($lang=='en') ? 'Select at least one bundled row to unbundle.' : 'Seleccione al menos una fila unida para separar.'; ?>");
    return;
  }

  // Collect unique bundle groups from selected rows
  const groups = new Set();
  checked.forEach(chk => {
    const row = chk.closest("tr");
    const g = row.querySelector(".bundleGroup18").value;
    if (g) groups.add(g);
  });

  if (groups.size === 0) {
    alert("<?= ($lang=='en') ? 'Selected rows are not bundled.' : 'Las filas seleccionadas no están unidas.'; ?>");
    return;
  }

  groups.forEach(g => unbundleGroup18(g));

  // Uncheck all
  checked.forEach(chk => chk.checked = false);
  calcTotals18();
}

// ===== UNBUNDLE A SPECIFIC GROUP =====
function unbundleGroup18(groupId) {
  const tbody = document.getElementById("table18body");
  const rows = tbody.querySelectorAll("tr");

  rows.forEach(row => {
    const bg = row.querySelector(".bundleGroup18");
    if (bg && bg.value === groupId) {
      bg.value = "";
      row.classList.remove("bundle-row", "bundle-row-primary", "bundle-row-secondary");

      const subtotalCell = row.querySelector(".subtotal-cell18");
      const subtotalInput = row.querySelector(".subtotal18");

      // Remove bundle price label if present
      const bundleLabel = subtotalCell.querySelector(".bundle-price-label");
      if (bundleLabel) bundleLabel.remove();

      // Remove legacy included label if present
      const includedLabel = subtotalCell.querySelector(".bundle-included-label");
      if (includedLabel) includedLabel.remove();

      // Reset rowspan and merged styling on primary
      subtotalCell.removeAttribute("rowspan");
      subtotalCell.classList.remove("bundle-price-merged");

      // Restore hidden cells on secondary rows
      subtotalCell.style.display = "";
      subtotalInput.style.display = "";

      // Restore original subtotal if available
      if (row.dataset.originalSubtotal) {
        subtotalInput.value = row.dataset.originalSubtotal !== "0" ? row.dataset.originalSubtotal : "";
        delete row.dataset.originalSubtotal;
      }
    }
  });
}

// ===== APPLY BUNDLE VISUAL STATE (for loading saved data) =====
function applyBundleVisuals18() {
  const tbody = document.getElementById("table18body");
  if (!tbody) return;

  const rows = Array.from(tbody.querySelectorAll("tr"));
  const groups = {};

  // Group rows by bundle_group
  rows.forEach(row => {
    const bg = row.querySelector(".bundleGroup18");
    if (bg && bg.value) {
      if (!groups[bg.value]) groups[bg.value] = [];
      groups[bg.value].push(row);
    }
  });

  // Apply visuals to each group
  for (const groupId in groups) {
    const groupRows = groups[groupId];
    groupRows.forEach((row, idx) => {
      row.classList.add("bundle-row");
      const subtotalCell = row.querySelector(".subtotal-cell18");
      const subtotalInput = row.querySelector(".subtotal18");

      if (idx === 0) {
        // Primary row: merged price cell with rowspan
        row.classList.add("bundle-row-primary");
        subtotalCell.rowSpan = groupRows.length;
        subtotalCell.classList.add("bundle-price-merged");

        // Add bundle price label if not present
        if (!subtotalCell.querySelector(".bundle-price-label")) {
          const label = document.createElement("span");
          label.className = "bundle-price-label";
          label.textContent = "<?= ($lang=='en') ? 'Bundle Price' : 'Precio Unido'; ?>";
          subtotalCell.insertBefore(label, subtotalInput);
        }
      } else {
        // Secondary rows: hide subtotal cell (covered by rowspan)
        row.classList.add("bundle-row-secondary");
        row.dataset.originalSubtotal = subtotalInput.value || "0";
        subtotalInput.value = "";
        subtotalCell.style.display = "none";
      }
    });
  }
}
</script>

<!-- Load Services Catalog (for Q19) -->
<script src="services_catalog.js"></script>

<!-- 19️⃣ Hoodvent & Kitchen Cleaning -->
<div class="question-block" id="q19">
  <label for="includeKitchen" class="question-label">
    <?= ($lang=='en')
      ? "19. Hoodvent & Kitchen Cleaning"
      : "19. Limpieza de Cocina y Campanas (Hoodvent)"; ?>
  </label>

  <select id="includeKitchen" name="includeKitchen" onchange="toggleSection19()">
    <option value="">
      <?= ($lang=='en') ? "-- Select an option --" : "-- Seleccione una opción --"; ?>
    </option>
    <option value="No"><?= ($lang=='en') ? "No" : "No"; ?></option>
    <option value="Yes"><?= ($lang=='en') ? "Yes" : "Sí"; ?></option>
  </select>

  <div id="section19Container" style="display:none; margin-top:20px;">

    <!-- ADD / REMOVE / BUNDLE -->
    <div style="margin-bottom:15px; display:flex; flex-wrap:wrap; gap:8px;">
      <button type="button" class="btn19 addRow19" onclick="addRow19()">
        ➕ <?= ($lang=='en') ? "Add Row" : "Agregar Fila"; ?>
      </button>

      <button type="button" class="btn19 removeRow19" onclick="removeRow19()">
        🗑 <?= ($lang=='en') ? "Remove" : "Eliminar"; ?>
      </button>

      <button type="button" class="btn19 bundleBtn19" onclick="bundleSelectedRows19()">
        🔗 <?= ($lang=='en') ? "Bundle Price" : "Unir Precio"; ?>
      </button>

      <button type="button" class="btn19 unbundleBtn19" onclick="unbundleSelectedRows19()">
        ✂️ <?= ($lang=='en') ? "Unbundle" : "Separar Precio"; ?>
      </button>
    </div>

    <!-- TABLE -->
    <table class="service-table19">
      <thead>
        <tr>
          <th class="th-check">&nbsp;</th>
          <th><?= ($lang=='en') ? "Type of Services" : "Tipo de Servicio"; ?></th>
          <th><?= ($lang=='en') ? "Service Time" : "Tiempo de Servicio"; ?></th>
          <th><?= ($lang=='en') ? "Frequency" : "Frecuencia"; ?></th>
          <th><?= ($lang=='en') ? "Service Description" : "Descripción del Servicio"; ?></th>
          <th><?= ($lang=='en') ? "SUBTOTAL" : "SUBTOTAL"; ?></th>
        </tr>
      </thead>

      <tbody id="table19body">

        <!-- ONE INITIAL ROW -->
        <tr>

          <!-- CHECKBOX -->
          <td class="td-check">
            <input type="checkbox" class="bundle-check19">
            <input type="hidden" class="bundleGroup19" name="bundleGroup19[]" value="">
          </td>

          <!-- TYPE OF SERVICES (MODAL SELECTOR) -->
          <td>
            <input type="hidden" class="type19" name="type19[]" value="">
            <input type="hidden" class="scope19" name="scope19[]" value="">
            <div class="service-selector-btn" onclick="openServiceModal(this)">
              <span class="service-selector-text"><?= ($lang=='en') ? "Select Service..." : "Seleccionar Servicio..."; ?></span>
              <span class="service-selector-icon">&#9662;</span>
            </div>
          </td>

          <!-- SERVICE TIME -->
          <td>
            <select class="time19" name="time19[]">
              <option value="">
                <?= ($lang=='en') ? "-- Select Time --" : "-- Seleccione tiempo --"; ?>
              </option>
              <option><?= ($lang=='en') ? "1 Day" : "1 Dia"; ?></option>
              <option><?= ($lang=='en') ? "1-2 Days" : "1-2 Dias"; ?></option>
              <option><?= ($lang=='en') ? "3 Days" : "3 Dias"; ?></option>
              <option><?= ($lang=='en') ? "4 Days" : "4 Dias"; ?></option>
              <option><?= ($lang=='en') ? "5 Days" : "5 Dias"; ?></option>
              <option><?= ($lang=='en') ? "6 Days" : "6 Dias"; ?></option>
              <option><?= ($lang=='en') ? "7 Days" : "7 Dias"; ?></option>
            </select>
          </td>

          <!-- FREQUENCY -->
          <td>
            <select class="freq19" name="freq19[]">
              <option value="">
                <?= ($lang=='en') ? "-- Select Period --" : "-- Seleccione periodo --"; ?>
              </option>
              <option><?= ($lang=='en') ? "One Time" : "Una Vez"; ?></option>
              <option><?= ($lang=='en') ? "Weekly" : "Semanal"; ?></option>
              <option><?= ($lang=='en') ? "Every 2 Weeks" : "Cada 2 Semanas"; ?></option>
              <option><?= ($lang=='en') ? "Every 3 Weeks" : "Cada 3 Semanas"; ?></option>
              <option><?= ($lang=='en') ? "Monthly" : "Mensual"; ?></option>
              <option><?= ($lang=='en') ? "Bimonthly" : "Bimestral"; ?></option>
              <option><?= ($lang=='en') ? "Quarterly" : "Trimestral"; ?></option>
              <option><?= ($lang=='en') ? "Every 4 Months" : "Cada 4 Meses"; ?></option>
              <option><?= ($lang=='en') ? "Semiannual" : "Semestral"; ?></option>
              <option><?= ($lang=='en') ? "Annual" : "Anual"; ?></option>
            </select>
          </td>

          <!-- DESCRIPTION -->
          <td>
            <input type="text"
              class="desc19"
              name="desc19[]"
              placeholder="<?= ($lang=='en') ? 'Write description...' : 'Escriba la descripcion...'; ?>">
          </td>

          <!-- SUBTOTAL -->
          <td class="subtotal-cell19">
            <input type="number" step="0.01"
              class="subtotal19"
              name="subtotal19[]"
              placeholder="0.00"
              oninput="calcTotals19()">
          </td>

        </tr>

      </tbody>
    </table>

    <!-- TOTAL BOXES -->
    <div class="totals19-container">

      <div class="tot-box">
        <div class="tot-header">
          <?= ($lang=='en') ? "TOTAL" : "TOTAL"; ?>
        </div>
        <input type="text" id="total19" readonly name="total19">
      </div>

      <div class="tot-box">
        <div class="tot-header">
          <?= ($lang=='en') ? "TAXES (8.25%)" : "IMPUESTOS (8.25%)"; ?>
        </div>
        <input type="text" id="taxes19" readonly name="taxes19">
      </div>

      <div class="tot-box">
        <div class="tot-header">
          <?= ($lang=='en') ? "GRAND TOTAL" : "TOTAL GENERAL"; ?>
        </div>
        <input type="text" id="grand19" readonly name="grand19">
      </div>

    </div>

  </div>
</div>

<!-- ======================================= -->
<!-- SERVICE SELECTOR MODAL -->
<!-- ======================================= -->
<div id="serviceModal" class="service-modal-overlay" style="display:none;">
  <div class="service-modal">
    <div class="service-modal-header">
      <h3><?= ($lang=='en') ? "Select a Service" : "Seleccionar un Servicio"; ?></h3>
      <button type="button" class="service-modal-close" onclick="closeServiceModal()">&times;</button>
    </div>

    <div class="service-modal-search">
      <input type="text" id="serviceModalSearch"
        placeholder="<?= ($lang=='en') ? 'Search services...' : 'Buscar servicios...'; ?>"
        oninput="filterServiceCards()">
    </div>

    <div class="service-modal-body" id="serviceModalBody">
      <!-- Cards are generated dynamically from servicesCatalog -->
    </div>
  </div>
</div>

<style>
  /* ===== TABLE 19 STYLES ===== */
  .service-table19 {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-size: 14px;
  }
  .service-table19 th {
    background-color: #c00;
    color: #fff;
    padding: 8px;
    text-align: center;
  }
  .service-table19 td {
    border: 1px solid #ddd;
    padding: 8px;
  }
  .service-table19 select,
  .service-table19 input[type="text"],
  .service-table19 input[type="number"] {
    width: 100%;
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 4px;
  }
  .btn19 {
    padding: 6px 14px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
  }
  .addRow19 { background-color:#008c4a; color:white; }
  .removeRow19 { background-color:#777; color:white; }
  .bundleBtn19 { background-color:#0066cc; color:white; }
  .unbundleBtn19 { background-color:#cc6600; color:white; }
  .totals19-container {
    margin-top: 25px;
    display: flex;
    gap: 25px;
    flex-wrap: wrap;
  }
  .tot-box {
    width: 220px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background:white;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
  }
  .tot-header {
    background-color:#c00;
    color:white;
    padding:8px;
    text-align:center;
    font-weight:bold;
  }
  .tot-box input {
    width:100%;
    padding:10px;
    text-align:right;
    font-weight:bold;
    background:#f7f7f7;
    border:none;
  }

  /* ===== SERVICE SELECTOR BUTTON ===== */
  .service-selector-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    border: 2px solid #ccc;
    border-radius: 6px;
    cursor: pointer;
    background: #fff;
    min-height: 38px;
    transition: all 0.2s ease;
    user-select: none;
  }
  .service-selector-btn:hover {
    border-color: #001f54;
    background: #f0f4ff;
  }
  .service-selector-btn.has-value {
    border-color: #001f54;
    background: #e8f0fe;
  }
  .service-selector-text {
    flex: 1;
    color: #666;
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .service-selector-btn.has-value .service-selector-text {
    color: #001f54;
    font-weight: 600;
  }
  .service-selector-icon {
    margin-left: 8px;
    color: #999;
    font-size: 12px;
  }

  /* ===== SERVICE MODAL ===== */
  .service-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }
  .service-modal {
    background: #fff;
    border-radius: 12px;
    width: 100%;
    max-width: 800px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    overflow: hidden;
  }
  .service-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    background: #001f54;
    color: #fff;
  }
  .service-modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
  }
  .service-modal-close {
    background: none;
    border: none;
    color: #fff;
    font-size: 28px;
    cursor: pointer;
    line-height: 1;
    padding: 0 4px;
    opacity: 0.8;
    transition: opacity 0.2s;
  }
  .service-modal-close:hover {
    opacity: 1;
  }
  .service-modal-search {
    padding: 16px 24px 8px;
  }
  .service-modal-search input {
    width: 100%;
    padding: 10px 16px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
  }
  .service-modal-search input:focus {
    border-color: #001f54;
  }
  .service-modal-body {
    padding: 16px 24px 24px;
    overflow-y: auto;
    flex: 1;
  }

  /* ===== CATEGORY HEADER ===== */
  .service-modal-category {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #999;
    margin: 16px 0 8px;
    padding-bottom: 4px;
    border-bottom: 1px solid #eee;
  }
  .service-modal-category:first-child {
    margin-top: 0;
  }

  /* ===== SERVICE CARDS ===== */
  .service-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 12px;
  }
  .service-card {
    border: 2px solid #e1e8ed;
    border-radius: 10px;
    padding: 16px;
    cursor: pointer;
    background: #fafbfc;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .service-card:hover {
    border-color: #001f54;
    background: #e8f0fe;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,31,84,0.15);
  }
  .service-card-name {
    font-size: 14px;
    font-weight: 700;
    color: #001f54;
  }
  .service-card-scope-count {
    font-size: 11px;
    color: #888;
  }
  .service-card-no-results {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px 20px;
    color: #999;
    font-style: italic;
  }
  .service-card-other {
    border-style: dashed;
    border-color: #999;
    background: #f9f9f9;
  }
  .service-card-other:hover {
    border-color: #c00;
    background: #fff5f5;
  }
  .service-card-other .service-card-name {
    color: #666;
  }
</style>

<script>
// ===== MODAL STATE =====
let activeServiceRow = null;

// ===== TOGGLE SECTION 19 =====
function toggleSection19() {
  document.getElementById("section19Container").style.display =
    document.getElementById("includeKitchen").value === "Yes"
    ? "block" : "none";
}

// ===== OPEN SERVICE MODAL =====
function openServiceModal(btn) {
  activeServiceRow = btn.closest("tr");
  const modal = document.getElementById("serviceModal");
  const body = document.getElementById("serviceModalBody");
  const search = document.getElementById("serviceModalSearch");

  search.value = "";
  renderServiceCards("");

  modal.style.display = "flex";
  setTimeout(() => search.focus(), 100);
}

// ===== CLOSE SERVICE MODAL =====
function closeServiceModal() {
  document.getElementById("serviceModal").style.display = "none";
  activeServiceRow = null;
}

// Close modal on overlay click
document.addEventListener("click", function(e) {
  if (e.target.id === "serviceModal") closeServiceModal();
});

// Close modal on Escape key
document.addEventListener("keydown", function(e) {
  if (e.key === "Escape") closeServiceModal();
});

// ===== RENDER SERVICE CARDS =====
function renderServiceCards(filter) {
  const body = document.getElementById("serviceModalBody");
  const lowerFilter = filter.toLowerCase();

  const filtered = servicesCatalog.filter(svc =>
    svc.name.toLowerCase().includes(lowerFilter) ||
    svc.category.toLowerCase().includes(lowerFilter)
  );

  let html = "";

  if (filtered.length === 0) {
    html += '<div class="service-cards-grid"><div class="service-card-no-results">' +
      '<?= ($lang=="en") ? "No services found" : "No se encontraron servicios"; ?>' +
      '</div></div>';
  } else {
    // Group by category
    const grouped = {};
    filtered.forEach(svc => {
      if (!grouped[svc.category]) grouped[svc.category] = [];
      grouped[svc.category].push(svc);
    });

    for (const cat in grouped) {
      html += '<div class="service-modal-category">' + cat + '</div>';
      html += '<div class="service-cards-grid">';
      grouped[cat].forEach(svc => {
        const scopeCount = svc.scope ? svc.scope.length : 0;
        html += '<div class="service-card" onclick="selectService(\'' + svc.id + '\')">';
        html += '<div class="service-card-name">' + svc.name + '</div>';
        html += '<div class="service-card-scope-count">' + scopeCount + ' <?= ($lang=="en") ? "scope items" : "elementos de scope"; ?></div>';
        html += '</div>';
      });
      html += '</div>';
    }
  }

  // Always show "Other" option at the bottom
  html += '<div class="service-modal-category"><?= ($lang=="en") ? "Other" : "Otro"; ?></div>';
  html += '<div class="service-cards-grid">';
  html += '<div class="service-card service-card-other" onclick="selectCustomService()">';
  html += '<div class="service-card-name"><?= ($lang=="en") ? "Other (Custom)" : "Otro (Personalizado)"; ?></div>';
  html += '<div class="service-card-scope-count"><?= ($lang=="en") ? "Enter service name manually" : "Ingrese el nombre del servicio manualmente"; ?></div>';
  html += '</div>';
  html += '</div>';

  body.innerHTML = html;
}

// ===== FILTER SERVICE CARDS =====
function filterServiceCards() {
  const val = document.getElementById("serviceModalSearch").value;
  renderServiceCards(val);
}

// ===== SELECT SERVICE FROM MODAL =====
function selectService(serviceId) {
  if (!activeServiceRow) return;

  const svc = servicesCatalogById[serviceId];
  if (!svc) return;

  // Set hidden type19 value
  const typeInput = activeServiceRow.querySelector(".type19");
  typeInput.value = svc.name;

  // Set hidden scope19 value (JSON array of scope items)
  const scopeInput = activeServiceRow.querySelector(".scope19");
  scopeInput.value = JSON.stringify(svc.scope || []);

  // Update button display
  const btn = activeServiceRow.querySelector(".service-selector-btn");
  const textSpan = btn.querySelector(".service-selector-text");
  textSpan.textContent = svc.name;
  btn.classList.add("has-value");

  closeServiceModal();
}

// ===== SELECT CUSTOM SERVICE (Other) =====
function selectCustomService() {
  if (!activeServiceRow) return;

  const customName = prompt(
    '<?= ($lang=="en") ? "Enter the service name:" : "Ingrese el nombre del servicio:"; ?>'
  );

  if (!customName || customName.trim() === '') return;

  const trimmedName = customName.trim();

  // Set hidden type19 value with custom name
  const typeInput = activeServiceRow.querySelector(".type19");
  typeInput.value = trimmedName;

  // Set empty scope19 (no predefined scope for custom services)
  const scopeInput = activeServiceRow.querySelector(".scope19");
  scopeInput.value = '[]';

  // Update button display
  const btn = activeServiceRow.querySelector(".service-selector-btn");
  const textSpan = btn.querySelector(".service-selector-text");
  textSpan.textContent = trimmedName;
  btn.classList.add("has-value");

  closeServiceModal();
}

// ===== ADD ROW =====
function addRow19() {
  const tbody = document.getElementById("table19body");
  const newRow = tbody.children[0].cloneNode(true);

  // Reset all inputs
  newRow.querySelectorAll("select, input").forEach(el => el.value = "");

  // Reset service selector button
  const btn = newRow.querySelector(".service-selector-btn");
  const textSpan = btn.querySelector(".service-selector-text");
  textSpan.textContent = "<?= ($lang=='en') ? 'Select Service...' : 'Seleccionar Servicio...'; ?>";
  btn.classList.remove("has-value");

  // Reset bundle state
  newRow.classList.remove("bundle-row", "bundle-row-primary", "bundle-row-secondary");
  const chk = newRow.querySelector(".bundle-check19");
  if (chk) chk.checked = false;
  const subtotalCell = newRow.querySelector(".subtotal-cell19");
  if (subtotalCell) {
    const lbl = subtotalCell.querySelector(".bundle-included-label");
    if (lbl) lbl.remove();
    const inp = subtotalCell.querySelector(".subtotal19");
    if (inp) inp.style.display = "";
  }

  tbody.appendChild(newRow);
}

// ===== REMOVE ROW =====
function removeRow19() {
  const tbody = document.getElementById("table19body");
  if (tbody.children.length > 1) {
    const rowToRemove = tbody.lastElementChild;
    const bundleGroup = rowToRemove.querySelector(".bundleGroup19")?.value;

    if (bundleGroup) {
      unbundleGroup19(bundleGroup);
    }

    tbody.lastElementChild.remove();
    calcTotals19();
  } else {
    alert("<?= ($lang=='en')
      ? 'At least one row must remain.'
      : 'Debe permanecer al menos una fila.'; ?>");
  }
}

// ===== CALCULATE TOTALS =====
function calcTotals19() {
  let total = 0;

  document.querySelectorAll(".subtotal19").forEach(input => {
    // Skip secondary bundle rows (their price is included in the primary row)
    const row = input.closest("tr");
    if (row && row.classList.contains("bundle-row-secondary")) return;

    // Also skip if input is explicitly hidden (legacy support)
    if (input.style.display === "none") return;

    const val = parseFloat(input.value);
    if (!isNaN(val)) total += val;
  });

  document.getElementById("total19").value = "$" + total.toFixed(2);

  const taxes = total * 0.0825;
  document.getElementById("taxes19").value = "$" + taxes.toFixed(2);

  document.getElementById("grand19").value = "$" + (total + taxes).toFixed(2);
}

// ===== BUNDLE SELECTED ROWS (Q19) =====
function bundleSelectedRows19() {
  const tbody = document.getElementById("table19body");
  const checked = tbody.querySelectorAll(".bundle-check19:checked");

  if (checked.length < 2) {
    alert("<?= ($lang=='en') ? 'Select at least 2 rows to bundle.' : 'Seleccione al menos 2 filas para unir.'; ?>");
    return;
  }

  const rows = [];
  let hasExisting = false;
  checked.forEach(chk => {
    const row = chk.closest("tr");
    const existing = row.querySelector(".bundleGroup19").value;
    if (existing) {
      hasExisting = true;
    }
    rows.push(row);
  });

  if (hasExisting) {
    alert("<?= ($lang=='en') ? 'Some selected rows are already bundled. Unbundle them first.' : 'Algunas filas ya están unidas. Sepárelas primero.'; ?>");
    return;
  }

  if (rows.length < 2) return;

  const groupId = "bg_" + Date.now();

  let sum = 0;
  rows.forEach(row => {
    const inp = row.querySelector(".subtotal19");
    const val = parseFloat(inp.value);
    if (!isNaN(val)) sum += val;
  });

  rows.forEach((row, idx) => {
    row.querySelector(".bundleGroup19").value = groupId;
    row.classList.add("bundle-row");

    const subtotalCell = row.querySelector(".subtotal-cell19");
    const subtotalInput = row.querySelector(".subtotal19");

    if (idx === 0) {
      // Primary row: merged price cell with rowspan
      row.classList.add("bundle-row-primary");
      subtotalCell.rowSpan = rows.length;
      subtotalCell.classList.add("bundle-price-merged");
      subtotalInput.value = sum.toFixed(2);

      // Add bundle price label
      const label = document.createElement("span");
      label.className = "bundle-price-label";
      label.textContent = "<?= ($lang=='en') ? 'Bundle Price' : 'Precio Unido'; ?>";
      subtotalCell.insertBefore(label, subtotalInput);
    } else {
      // Secondary rows: hide subtotal cell entirely (covered by rowspan)
      row.classList.add("bundle-row-secondary");
      row.dataset.originalSubtotal = subtotalInput.value || "0";
      subtotalInput.value = "";
      subtotalCell.style.display = "none";
    }

    row.querySelector(".bundle-check19").checked = false;
  });

  calcTotals19();
}

// ===== UNBUNDLE SELECTED ROWS (Q19) =====
function unbundleSelectedRows19() {
  const tbody = document.getElementById("table19body");
  const checked = tbody.querySelectorAll(".bundle-check19:checked");

  if (checked.length === 0) {
    alert("<?= ($lang=='en') ? 'Select at least one bundled row to unbundle.' : 'Seleccione al menos una fila unida para separar.'; ?>");
    return;
  }

  const groups = new Set();
  checked.forEach(chk => {
    const row = chk.closest("tr");
    const g = row.querySelector(".bundleGroup19").value;
    if (g) groups.add(g);
  });

  if (groups.size === 0) {
    alert("<?= ($lang=='en') ? 'Selected rows are not bundled.' : 'Las filas seleccionadas no están unidas.'; ?>");
    return;
  }

  groups.forEach(g => unbundleGroup19(g));
  checked.forEach(chk => chk.checked = false);
  calcTotals19();
}

// ===== UNBUNDLE A SPECIFIC GROUP (Q19) =====
function unbundleGroup19(groupId) {
  const tbody = document.getElementById("table19body");
  const rows = tbody.querySelectorAll("tr");

  rows.forEach(row => {
    const bg = row.querySelector(".bundleGroup19");
    if (bg && bg.value === groupId) {
      bg.value = "";
      row.classList.remove("bundle-row", "bundle-row-primary", "bundle-row-secondary");

      const subtotalCell = row.querySelector(".subtotal-cell19");
      const subtotalInput = row.querySelector(".subtotal19");

      // Remove bundle price label if present
      const bundleLabel = subtotalCell.querySelector(".bundle-price-label");
      if (bundleLabel) bundleLabel.remove();

      // Remove legacy included label if present
      const includedLabel = subtotalCell.querySelector(".bundle-included-label");
      if (includedLabel) includedLabel.remove();

      // Reset rowspan and merged styling on primary
      subtotalCell.removeAttribute("rowspan");
      subtotalCell.classList.remove("bundle-price-merged");

      // Restore hidden cells on secondary rows
      subtotalCell.style.display = "";
      subtotalInput.style.display = "";

      // Restore original subtotal if available
      if (row.dataset.originalSubtotal) {
        subtotalInput.value = row.dataset.originalSubtotal !== "0" ? row.dataset.originalSubtotal : "";
        delete row.dataset.originalSubtotal;
      }
    }
  });
}

// ===== APPLY BUNDLE VISUAL STATE (Q19 - for loading saved data) =====
function applyBundleVisuals19() {
  const tbody = document.getElementById("table19body");
  if (!tbody) return;

  const rows = Array.from(tbody.querySelectorAll("tr"));
  const groups = {};

  rows.forEach(row => {
    const bg = row.querySelector(".bundleGroup19");
    if (bg && bg.value) {
      if (!groups[bg.value]) groups[bg.value] = [];
      groups[bg.value].push(row);
    }
  });

  for (const groupId in groups) {
    const groupRows = groups[groupId];
    groupRows.forEach((row, idx) => {
      row.classList.add("bundle-row");
      const subtotalCell = row.querySelector(".subtotal-cell19");
      const subtotalInput = row.querySelector(".subtotal19");

      if (idx === 0) {
        // Primary row: merged price cell with rowspan
        row.classList.add("bundle-row-primary");
        subtotalCell.rowSpan = groupRows.length;
        subtotalCell.classList.add("bundle-price-merged");

        // Add bundle price label if not present
        if (!subtotalCell.querySelector(".bundle-price-label")) {
          const label = document.createElement("span");
          label.className = "bundle-price-label";
          label.textContent = "<?= ($lang=='en') ? 'Bundle Price' : 'Precio Unido'; ?>";
          subtotalCell.insertBefore(label, subtotalInput);
        }
      } else {
        // Secondary rows: hide subtotal cell (covered by rowspan)
        row.classList.add("bundle-row-secondary");
        row.dataset.originalSubtotal = subtotalInput.value || "0";
        subtotalInput.value = "";
        subtotalCell.style.display = "none";
      }
    });
  }
}
</script>


<!-- 20️⃣ Include Staff Section -->
<div class="question-block" id="q20">
  <label for="includeStaff" class="question-label">
    <?= ($lang=='en') ? "20. Include Staff?" : "20. ¿Incluir Personal?"; ?>
  </label>

  <select id="includeStaff" name="includeStaff" onchange="toggleStaffTables()">
    <option value=""><?= ($lang=='en') ? "-- Select an option --" : "-- Seleccione una opción --"; ?></option>
    <option value="No"><?= ($lang=='en') ? "No" : "No"; ?></option>
    <option value="Yes"><?= ($lang=='en') ? "Yes" : "Sí"; ?></option>
  </select>

  <div id="staffTablesContainer" style="display:none; margin-top: 10px;"></div>
</div>

<style>
  .staff-category {
    margin-top: 20px;
    border-top: 3px solid #c00;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  }

  .staff-header {
    background-color: #c00;
    color: #fff;
    padding: 10px 15px;
    font-size: 15px;
    font-weight: bold;
    text-transform: uppercase;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
  }

  .staff-header:hover {
    background-color: #a00000;
  }

  .toggle-icon {
    font-weight: bold;
    font-size: 18px;
    transition: transform 0.3s ease;
  }

  .staff-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 5px;
    font-size: 14px;
    display: none;
    background-color: #fff;
  }

  .staff-table th {
    background-color: #c00;
    color: white;
    padding: 6px;
    text-align: center;
  }

  .staff-table tr {
    background-color: #fff;
  }

  .staff-table td {
    padding: 8px;
    text-align: center;
  }

  .readonly {
    background-color: #f9f9f9;
  }

  .expanded .staff-table {
    display: table;
  }

  .expanded .toggle-icon {
    transform: rotate(180deg);
  }
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const container = document.getElementById("staffTablesContainer");

  window.toggleStaffTables = function () {
    const select = document.getElementById("includeStaff");
    if (select.value === "Yes") {
      container.style.display = "block";
      if (container.childElementCount === 0) loadStaffSections();
    } else {
      container.innerHTML = "";
      container.style.display = "none";
    }
  };

  function loadStaffSections() {
    container.innerHTML = `
      ${createCategory("<?= ($lang=='en') ? 'HOUSEKEEPING' : 'HOUSEKEEPING / AMA DE LLAVES'; ?>", [
        "<?= ($lang=='en') ? 'Housekeeper / GRA' : 'Camarista / GRA'; ?>",
        "<?= ($lang=='en') ? 'Housekeeping Inspector / GRA Supervisor' : 'Inspector(a) / Supervisor(a) de Housekeeping'; ?>",
        "<?= ($lang=='en') ? 'Laundry Attendant' : 'Encargado de Lavandería'; ?>",
        "<?= ($lang=='en') ? 'Houseman' : 'Houseman / Auxiliar General'; ?>",
        "<?= ($lang=='en') ? 'Public Areas Attendant' : 'Encargado de Áreas Públicas'; ?>",
        "<?= ($lang=='en') ? 'Lobby Attendant' : 'Encargado de Lobby'; ?>",
        "<?= ($lang=='en') ? 'Lobby Runner (AM/PM/Overnight)' : 'Lobby Runner (AM/PM/Noche)'; ?>",
        "<?= ($lang=='en') ? 'Turndown Attendant' : 'Encargado de Turn Down'; ?>"
      ])}

      ${createCategory("<?= ($lang=='en') ? 'FOOD & BEVERAGE' : 'ALIMENTOS Y BEBIDAS'; ?>", [
        "<?= ($lang=='en') ? 'Dishwasher' : 'Lavaplatos'; ?>",
        "<?= ($lang=='en') ? 'Cook (Main Kitchen / Back Table / Cool Water)' : 'Cocinero (Cocina Principal / Back Table / Cool Water)'; ?>",
        "<?= ($lang=='en') ? 'Prep Cook (Main Kitchen / Back Table / Cool Water)' : 'Cocinero de Preparación (Cocina Principal / Back Table / Cool Water)'; ?>",
        "<?= ($lang=='en') ? 'Busser (Restaurant / Banquet)' : 'Ayudante de Mesero (Restaurante / Banquetes)'; ?>",
        "<?= ($lang=='en') ? 'Runner (Food / Restaurant / Banquet)' : 'Runner (Alimentos / Restaurante / Banquetes)'; ?>",
        "<?= ($lang=='en') ? 'Server (Restaurant / Banquet)' : 'Mesero (Restaurante / Banquetes)'; ?>",
        "<?= ($lang=='en') ? 'Host / Hostess' : 'Host / Hostess'; ?>",
        "<?= ($lang=='en') ? 'Barista' : 'Barista'; ?>",
        "<?= ($lang=='en') ? 'Bartender' : 'Bartender'; ?>",
        "<?= ($lang=='en') ? 'Barback' : 'Barback / Auxiliar de Barra'; ?>",
        "<?= ($lang=='en') ? 'Banquet Houseman' : 'Houseman de Banquetes'; ?>",
        "<?= ($lang=='en') ? 'Cashier' : 'Cajero(a)'; ?>"
      ])}

      ${createCategory("<?= ($lang=='en') ? 'MAINTENANCE' : 'MANTENIMIENTO'; ?>", [
        "<?= ($lang=='en') ? 'Maintenance Helper' : 'Auxiliar de Mantenimiento'; ?>",
        "<?= ($lang=='en') ? 'Movers' : 'Mover / Mudanzas'; ?>"
      ])}

      ${createCategory("<?= ($lang=='en') ? 'RECREATION & POOL' : 'RECREACIÓN Y PISCINA'; ?>", [
        "<?= ($lang=='en') ? 'Pool Attendant' : 'Encargado de Piscina'; ?>",
        "<?= ($lang=='en') ? 'Recreation Slide Attendant' : 'Encargado de Tobogán'; ?>",
        "<?= ($lang=='en') ? 'Recreation Supervisor' : 'Supervisor de Recreación'; ?>"
      ])}

      ${createCategory("<?= ($lang=='en') ? 'SECURITY' : 'SEGURIDAD'; ?>", [
        "<?= ($lang=='en') ? 'Security Guard (Noncommissioned)' : 'Guardia de Seguridad (No Armado)'; ?>"
      ])}

      ${createCategory("<?= ($lang=='en') ? 'VALET PARKING' : 'VALET PARKING'; ?>", [
        "<?= ($lang=='en') ? 'Valet Attendant (AM/PM/Overnight)' : 'Valet Attendant (AM/PM/Noche)'; ?>"
      ])}

      ${createCategory("<?= ($lang=='en') ? 'FRONT DESK' : 'RECEPCIÓN'; ?>", [
        "<?= ($lang=='en') ? 'Front Desk Attendant' : 'Recepcionista'; ?>",
        "<?= ($lang=='en') ? 'Night Auditor' : 'Auditor Nocturno'; ?>"
      ])}
    `;
  }

  function createCategory(title, positions) {
    const rows = positions
      .map(pos => {
        const slug = slugify(title + "_" + pos);
        return `
          <tr>
            <td>${pos}</td>
            <td><input type="number" name="base_${slug}" step="0.01"
              placeholder="<?= ($lang=='en') ? '0.00' : '0.00'; ?>"
              oninput="updateBillRate('${slug}')"></td>

            <td><input type="number" name="increase_${slug}" step="0.01"
              placeholder="<?= ($lang=='en') ? '0%' : '0%'; ?>"
              oninput="updateBillRate('${slug}')"></td>

            <td><input type="text" name="bill_${slug}" class="readonly" readonly
              placeholder="$0.00"></td>
          </tr>
        `;
      })
      .join("");

    return `
      <div class="staff-category">
        <div class="staff-header" onclick="this.parentElement.classList.toggle('expanded')">
          ${title}
          <span class="toggle-icon">▼</span>
        </div>
        <table class="staff-table">
          <thead>
            <tr>
              <th><?= ($lang=='en') ? 'Position' : 'Puesto'; ?></th>
              <th><?= ($lang=='en') ? 'Base Rate' : 'Tarifa Base'; ?></th>
              <th><?= ($lang=='en') ? '% Increase' : '% Incremento'; ?></th>
              <th><?= ($lang=='en') ? 'Bill Rate' : 'Tarifa Final'; ?></th>
            </tr>
          </thead>
          <tbody>${rows}</tbody>
        </table>
      </div>
    `;
  }

  window.updateBillRate = function (slug) {
    const base = parseFloat(document.querySelector(`[name="base_${slug}"]`)?.value) || 0;
    const inc = parseFloat(document.querySelector(`[name="increase_${slug}"]`)?.value) || 0;
    const bill = document.querySelector(`[name="bill_${slug}"]`);
    const total = base + (base * inc / 100);
    bill.value = total > 0 ? `$${total.toFixed(2)}` : "$0.00";
  };

  function slugify(text) {
    return text.toLowerCase().replace(/[’']/g, "").replace(/[^a-z0-9]+/g, "_").replace(/^_+|_+$/g, "");
  }
});
</script>