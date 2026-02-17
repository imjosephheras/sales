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
    <option value="Kenny Howe">Kenny Howe</option>
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
  /* Staff Autocomplete Search */
  .staff-search-wrapper {
    position: relative;
    margin-bottom: 20px;
  }

  .staff-search-input {
    width: 100%;
    padding: 10px 14px;
    font-size: 14px;
    border: 2px solid #ddd;
    border-radius: 8px;
    box-sizing: border-box;
    transition: border-color 0.2s;
  }

  .staff-search-input:focus {
    outline: none;
    border-color: #c00;
  }

  .staff-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    max-height: 280px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    display: none;
  }

  .staff-dropdown.open {
    display: block;
  }

  .staff-dropdown-category {
    padding: 6px 12px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
    color: #fff;
    background-color: #c00;
    position: sticky;
    top: 0;
  }

  .staff-dropdown-item {
    padding: 8px 16px;
    font-size: 13px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.15s;
  }

  .staff-dropdown-item:hover {
    background-color: #fff3f3;
  }

  .staff-dropdown-empty {
    padding: 12px 16px;
    font-size: 13px;
    color: #999;
    text-align: center;
  }

  /* Staff Category Tables */
  .staff-category {
    margin-top: 15px;
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

  .staff-table td input[type="number"],
  .staff-table td input[type="text"] {
    width: 100%;
    box-sizing: border-box;
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

  .staff-delete-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: #c00;
    font-size: 18px;
    padding: 4px 8px;
    border-radius: 4px;
    transition: background-color 0.2s, color 0.2s;
  }

  .staff-delete-btn:hover {
    background-color: #fee;
    color: #900;
  }

  /* + New Position option in dropdown */
  .staff-dropdown-separator {
    border-top: 1px solid #e0e0e0;
    margin: 4px 0;
  }

  .staff-new-position {
    color: #1a73e8;
    font-weight: 600;
  }

  .staff-new-position:hover {
    background-color: #e8f0fe;
  }

  /* New position input bar */
  .new-position-input-wrapper {
    display: flex;
    gap: 8px;
    margin-top: 8px;
    align-items: center;
  }

  .new-position-input-wrapper input[type="text"] {
    flex: 1;
  }

  .btn-add-position {
    background: #1a73e8;
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    white-space: nowrap;
  }

  .btn-add-position:hover {
    background: #1558b0;
  }

  .btn-cancel-position {
    background: #f1f1f1;
    border: 1px solid #ccc;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    line-height: 1;
  }

  .btn-cancel-position:hover {
    background: #e0e0e0;
  }
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const container = document.getElementById("staffTablesContainer");

  // ── Central position catalog ──
  const staffPositionsCatalog = [
    { category: "<?= ($lang=='en') ? 'HOUSEKEEPING' : 'HOUSEKEEPING / AMA DE LLAVES'; ?>", name: "<?= ($lang=='en') ? 'Housekeeper' : 'Camarista'; ?>" },
    { category: "<?= ($lang=='en') ? 'HOUSEKEEPING' : 'HOUSEKEEPING / AMA DE LLAVES'; ?>", name: "<?= ($lang=='en') ? 'Housekeeping' : 'Housekeeping'; ?>" },
    { category: "<?= ($lang=='en') ? 'HOUSEKEEPING' : 'HOUSEKEEPING / AMA DE LLAVES'; ?>", name: "<?= ($lang=='en') ? 'Laundry Attendant' : 'Encargado de Lavandería'; ?>" },
    { category: "<?= ($lang=='en') ? 'HOUSEKEEPING' : 'HOUSEKEEPING / AMA DE LLAVES'; ?>", name: "<?= ($lang=='en') ? 'Houseman' : 'Houseman / Auxiliar General'; ?>" },
    { category: "<?= ($lang=='en') ? 'HOUSEKEEPING' : 'HOUSEKEEPING / AMA DE LLAVES'; ?>", name: "<?= ($lang=='en') ? 'Public Areas Attendant' : 'Encargado de Áreas Públicas'; ?>" },
    { category: "<?= ($lang=='en') ? 'HOUSEKEEPING' : 'HOUSEKEEPING / AMA DE LLAVES'; ?>", name: "<?= ($lang=='en') ? 'Lobby Attendant' : 'Encargado de Lobby'; ?>" },
    { category: "<?= ($lang=='en') ? 'HOUSEKEEPING' : 'HOUSEKEEPING / AMA DE LLAVES'; ?>", name: "<?= ($lang=='en') ? 'Lobby Runner' : 'Lobby Runner'; ?>" },
    { category: "<?= ($lang=='en') ? 'HOUSEKEEPING' : 'HOUSEKEEPING / AMA DE LLAVES'; ?>", name: "<?= ($lang=='en') ? 'Turndown Attendant' : 'Encargado de Turn Down'; ?>" },
    { category: "<?= ($lang=='en') ? 'FOOD & BEVERAGE' : 'ALIMENTOS Y BEBIDAS'; ?>", name: "<?= ($lang=='en') ? 'Dishwasher' : 'Lavaplatos'; ?>" },
    { category: "<?= ($lang=='en') ? 'FOOD & BEVERAGE' : 'ALIMENTOS Y BEBIDAS'; ?>", name: "<?= ($lang=='en') ? 'Cook' : 'Cocinero'; ?>" },
    { category: "<?= ($lang=='en') ? 'FOOD & BEVERAGE' : 'ALIMENTOS Y BEBIDAS'; ?>", name: "<?= ($lang=='en') ? 'Prep Cook' : 'Cocinero de Preparación'; ?>" },
    { category: "<?= ($lang=='en') ? 'FOOD & BEVERAGE' : 'ALIMENTOS Y BEBIDAS'; ?>", name: "<?= ($lang=='en') ? 'Busser' : 'Ayudante de Mesero'; ?>" },
    { category: "<?= ($lang=='en') ? 'FOOD & BEVERAGE' : 'ALIMENTOS Y BEBIDAS'; ?>", name: "<?= ($lang=='en') ? 'Runner' : 'Runner'; ?>" },
    { category: "<?= ($lang=='en') ? 'FOOD & BEVERAGE' : 'ALIMENTOS Y BEBIDAS'; ?>", name: "<?= ($lang=='en') ? 'Server' : 'Mesero'; ?>" },
    { category: "<?= ($lang=='en') ? 'FOOD & BEVERAGE' : 'ALIMENTOS Y BEBIDAS'; ?>", name: "<?= ($lang=='en') ? 'Host / Hostess' : 'Host / Hostess'; ?>" },
    { category: "<?= ($lang=='en') ? 'FOOD & BEVERAGE' : 'ALIMENTOS Y BEBIDAS'; ?>", name: "<?= ($lang=='en') ? 'Barista' : 'Barista'; ?>" },
    { category: "<?= ($lang=='en') ? 'FOOD & BEVERAGE' : 'ALIMENTOS Y BEBIDAS'; ?>", name: "<?= ($lang=='en') ? 'Bartender' : 'Bartender'; ?>" },
    { category: "<?= ($lang=='en') ? 'FOOD & BEVERAGE' : 'ALIMENTOS Y BEBIDAS'; ?>", name: "<?= ($lang=='en') ? 'Barback' : 'Barback / Auxiliar de Barra'; ?>" },
    { category: "<?= ($lang=='en') ? 'FOOD & BEVERAGE' : 'ALIMENTOS Y BEBIDAS'; ?>", name: "<?= ($lang=='en') ? 'Banquet Houseman' : 'Houseman de Banquetes'; ?>" },
    { category: "<?= ($lang=='en') ? 'FOOD & BEVERAGE' : 'ALIMENTOS Y BEBIDAS'; ?>", name: "<?= ($lang=='en') ? 'Cashier' : 'Cajero(a)'; ?>" },
    { category: "<?= ($lang=='en') ? 'MAINTENANCE' : 'MANTENIMIENTO'; ?>", name: "<?= ($lang=='en') ? 'Maintenance Helper' : 'Auxiliar de Mantenimiento'; ?>" },
    { category: "<?= ($lang=='en') ? 'MAINTENANCE' : 'MANTENIMIENTO'; ?>", name: "<?= ($lang=='en') ? 'Movers' : 'Mover / Mudanzas'; ?>" },
    { category: "<?= ($lang=='en') ? 'RECREATION & POOL' : 'RECREACIÓN Y PISCINA'; ?>", name: "<?= ($lang=='en') ? 'Pool Attendant' : 'Encargado de Piscina'; ?>" },
    { category: "<?= ($lang=='en') ? 'RECREATION & POOL' : 'RECREACIÓN Y PISCINA'; ?>", name: "<?= ($lang=='en') ? 'Recreation Slide Attendant' : 'Encargado de Tobogán'; ?>" },
    { category: "<?= ($lang=='en') ? 'RECREATION & POOL' : 'RECREACIÓN Y PISCINA'; ?>", name: "<?= ($lang=='en') ? 'Recreation Supervisor' : 'Supervisor de Recreación'; ?>" },
    { category: "<?= ($lang=='en') ? 'SECURITY' : 'SEGURIDAD'; ?>", name: "<?= ($lang=='en') ? 'Security Guard' : 'Guardia de Seguridad'; ?>" },
    { category: "<?= ($lang=='en') ? 'VALET PARKING' : 'VALET PARKING'; ?>", name: "<?= ($lang=='en') ? 'Valet Attendant' : 'Valet Attendant'; ?>" },
    { category: "<?= ($lang=='en') ? 'FRONT DESK' : 'RECEPCIÓN'; ?>", name: "<?= ($lang=='en') ? 'Front Desk Attendant' : 'Recepcionista'; ?>" },
    { category: "<?= ($lang=='en') ? 'FRONT DESK' : 'RECEPCIÓN'; ?>", name: "<?= ($lang=='en') ? 'Night Auditor' : 'Auditor Nocturno'; ?>" }
  ];

  // Track selected positions by slug
  const selectedSlugs = new Set();

  // ── Slugify ──
  function slugify(text) {
    return text.toLowerCase().replace(/['']/g, "").replace(/[^a-z0-9]+/g, "_").replace(/^_+|_+$/g, "");
  }

  // ── Get available (not yet selected) positions ──
  function getAvailablePositions(filter) {
    const term = (filter || "").toLowerCase();
    return staffPositionsCatalog.filter(p => {
      const slug = slugify(p.category + "_" + p.name);
      if (selectedSlugs.has(slug)) return false;
      if (!term) return true;
      return p.name.toLowerCase().includes(term) || p.category.toLowerCase().includes(term);
    });
  }

  // ── Render the dropdown list ──
  function renderDropdown(filter) {
    const dropdown = document.getElementById("staffDropdown");
    const available = getAvailablePositions(filter);

    let html = "";
    let lastCat = "";
    available.forEach(p => {
      if (p.category !== lastCat) {
        lastCat = p.category;
        html += `<div class="staff-dropdown-category">${p.category}</div>`;
      }
      const slug = slugify(p.category + "_" + p.name);
      html += `<div class="staff-dropdown-item" data-slug="${slug}" data-name="${p.name}" data-category="${p.category}">${p.name}</div>`;
    });

    if (available.length === 0) {
      html += `<div class="staff-dropdown-empty"><?= ($lang=='en') ? 'No positions available' : 'No hay posiciones disponibles'; ?></div>`;
    }

    // Always show "+ New Position" at the end
    html += `<div class="staff-dropdown-separator"></div>`;
    html += `<div class="staff-dropdown-item staff-new-position" id="newPositionOption"><?= ($lang=='en') ? '+ New Position' : '+ Nueva Posición'; ?></div>`;

    dropdown.innerHTML = html;
    dropdown.classList.add("open");

    // Attach click handlers for catalog items
    dropdown.querySelectorAll(".staff-dropdown-item:not(.staff-new-position)").forEach(item => {
      item.addEventListener("click", function () {
        const slug = this.dataset.slug;
        const name = this.dataset.name;
        const category = this.dataset.category;
        selectPosition(slug, name, category);
      });
    });

    // Attach click handler for "+ New Position"
    const newPosOption = document.getElementById("newPositionOption");
    if (newPosOption) {
      newPosOption.addEventListener("click", function () {
        showNewPositionInput();
      });
    }
  }

  // ── Select a position: add row to its category table ──
  function selectPosition(slug, name, category) {
    if (selectedSlugs.has(slug)) return;
    selectedSlugs.add(slug);

    // Find or create the category section
    const catSlug = slugify(category);
    let catSection = document.getElementById("staff-cat-" + catSlug);

    if (!catSection) {
      catSection = document.createElement("div");
      catSection.className = "staff-category expanded";
      catSection.id = "staff-cat-" + catSlug;
      catSection.innerHTML = `
        <div class="staff-header" onclick="this.parentElement.classList.toggle('expanded')">
          ${category}
          <span class="toggle-icon">&#9660;</span>
        </div>
        <table class="staff-table">
          <thead>
            <tr>
              <th><?= ($lang=='en') ? 'Position' : 'Puesto'; ?></th>
              <th><?= ($lang=='en') ? 'Base Rate' : 'Tarifa Base'; ?></th>
              <th><?= ($lang=='en') ? '% Increase' : '% Incremento'; ?></th>
              <th><?= ($lang=='en') ? 'Bill Rate' : 'Tarifa Final'; ?></th>
              <th style="width:50px;"></th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      `;
      // Insert categories in catalog order (custom categories go at the end)
      const catOrder = [...new Set(staffPositionsCatalog.map(p => slugify(p.category)))];
      const currentIdx = catOrder.indexOf(catSlug);
      let inserted = false;
      if (currentIdx !== -1) {
        const existingSections = container.querySelectorAll(".staff-category");
        for (const sec of existingSections) {
          const secCatSlug = sec.id.replace("staff-cat-", "");
          const secIdx = catOrder.indexOf(secCatSlug);
          if (secIdx > currentIdx) {
            container.insertBefore(catSection, sec);
            inserted = true;
            break;
          }
        }
      }
      if (!inserted) container.appendChild(catSection);
    }

    // Add the row
    const tbody = catSection.querySelector("tbody");
    const tr = document.createElement("tr");
    tr.id = "staff-row-" + slug;
    tr.innerHTML = `
      <td>${name}</td>
      <td><input type="number" name="base_${slug}" step="0.01"
        placeholder="0.00"
        oninput="updateBillRate('${slug}')"></td>
      <td><input type="number" name="increase_${slug}" step="0.01"
        placeholder="0%"
        oninput="updateBillRate('${slug}')"></td>
      <td><input type="text" name="bill_${slug}" class="readonly" readonly
        placeholder="$0.00"></td>
      <td><button type="button" class="staff-delete-btn" onclick="removeStaffPosition('${slug}')" title="<?= ($lang=='en') ? 'Remove' : 'Eliminar'; ?>">&#128465;</button></td>
    `;
    tbody.appendChild(tr);

    // Clear and close the search
    const searchInput = document.getElementById("staffSearchInput");
    searchInput.value = "";
    document.getElementById("staffDropdown").classList.remove("open");
  }

  // ── Remove a position ──
  window.removeStaffPosition = function (slug) {
    const row = document.getElementById("staff-row-" + slug);
    if (!row) return;

    const tbody = row.closest("tbody");
    const catSection = row.closest(".staff-category");
    row.remove();
    selectedSlugs.delete(slug);

    // If no more rows in this category, remove the whole section
    if (tbody && tbody.children.length === 0 && catSection) {
      catSection.remove();
    }
  };

  // ── Bill Rate calculation ──
  window.updateBillRate = function (slug) {
    const base = parseFloat(document.querySelector(`[name="base_${slug}"]`)?.value) || 0;
    const inc = parseFloat(document.querySelector(`[name="increase_${slug}"]`)?.value) || 0;
    const bill = document.querySelector(`[name="bill_${slug}"]`);
    const total = base + (base * inc / 100);
    bill.value = total > 0 ? `$${total.toFixed(2)}` : "$0.00";
  };

  // ── Toggle staff section ──
  window.toggleStaffTables = function () {
    const select = document.getElementById("includeStaff");
    if (select.value === "Yes") {
      container.style.display = "block";
      if (!document.getElementById("staffSearchWrapper")) {
        loadStaffSearch();
      }
    } else {
      container.innerHTML = "";
      container.style.display = "none";
      selectedSlugs.clear();
    }
  };

  // ── Render the search bar ──
  function loadStaffSearch() {
    const wrapper = document.createElement("div");
    wrapper.className = "staff-search-wrapper";
    wrapper.id = "staffSearchWrapper";
    wrapper.innerHTML = `
      <input type="text" id="staffSearchInput" class="staff-search-input"
        placeholder="<?= ($lang=='en') ? 'Search positions...' : 'Buscar posiciones...'; ?>"
        autocomplete="off">
      <div id="staffDropdown" class="staff-dropdown"></div>
    `;
    container.insertBefore(wrapper, container.firstChild);

    const searchInput = document.getElementById("staffSearchInput");
    const dropdown = document.getElementById("staffDropdown");

    // Filter on input
    searchInput.addEventListener("input", function () {
      renderDropdown(this.value);
    });

    // Show dropdown on focus
    searchInput.addEventListener("focus", function () {
      renderDropdown(this.value);
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", function (e) {
      if (!wrapper.contains(e.target)) {
        dropdown.classList.remove("open");
      }
    });
  }

  // ── Show input bar for new custom position ──
  function showNewPositionInput() {
    const dropdown = document.getElementById("staffDropdown");
    dropdown.classList.remove("open");

    let newPosWrapper = document.getElementById("newPositionInputWrapper");
    if (!newPosWrapper) {
      const searchWrapper = document.getElementById("staffSearchWrapper");
      newPosWrapper = document.createElement("div");
      newPosWrapper.id = "newPositionInputWrapper";
      newPosWrapper.className = "new-position-input-wrapper";
      newPosWrapper.innerHTML = `
        <input type="text" id="newPositionNameInput" class="staff-search-input"
          placeholder="<?= ($lang=='en') ? 'Enter new position name...' : 'Escriba el nombre de la nueva posición...'; ?>"
          autocomplete="off">
        <button type="button" id="addNewPositionBtn" class="btn-add-position"><?= ($lang=='en') ? 'Add' : 'Agregar'; ?></button>
        <button type="button" id="cancelNewPositionBtn" class="btn-cancel-position">&times;</button>
      `;
      searchWrapper.after(newPosWrapper);

      document.getElementById("addNewPositionBtn").addEventListener("click", addCustomPosition);
      document.getElementById("cancelNewPositionBtn").addEventListener("click", hideNewPositionInput);
      document.getElementById("newPositionNameInput").addEventListener("keypress", function (e) {
        if (e.key === "Enter") { e.preventDefault(); addCustomPosition(); }
      });
    }

    newPosWrapper.style.display = "flex";
    document.getElementById("newPositionNameInput").value = "";
    document.getElementById("newPositionNameInput").focus();
  }

  // ── Add the custom position ──
  function addCustomPosition() {
    const input = document.getElementById("newPositionNameInput");
    const name = (input.value || "").trim();
    if (!name) return;

    const slug = slugify(name);
    if (selectedSlugs.has(slug)) {
      alert("<?= ($lang=='en') ? 'This position has already been added.' : 'Esta posición ya ha sido agregada.'; ?>");
      return;
    }

    const category = "<?= ($lang=='en') ? 'OTHER' : 'OTROS'; ?>";
    selectPosition(slug, name, category);
    input.value = "";
    hideNewPositionInput();
  }

  // ── Hide the new position input bar ──
  function hideNewPositionInput() {
    const wrapper = document.getElementById("newPositionInputWrapper");
    if (wrapper) wrapper.style.display = "none";
  }
});
</script>