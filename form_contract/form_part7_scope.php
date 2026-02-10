<!-- ======================================= -->
<!-- Section 7: Scope of Work (Dynamic) -->
<!-- ======================================= -->

<!-- 28 Scope Of Work -->
<div class="question-block" id="q28">

  <label for="Scope_Of_Work" class="question-label">
    <?= ($lang=='en')
        ? "28. Scope of Work (select applicable tasks)"
        : "28. Alcance del Trabajo (seleccione las tareas aplicables)"; ?>
  </label>

  <!-- Dynamic container -->
  <div id="scopeOfWorkContainer" class="checkbox-group" style="display:none;"></div>
</div>

<!-- ======================================= -->
<!-- Add Products Section Button            -->
<!-- ======================================= -->
<div class="question-block product-catalog-btn-wrap" id="qAddProducts">
  <button type="button" class="btn-add-products" onclick="openProductCatalogModal()">
    + <?= ($lang=='en') ? "Add Products Section" : "Agregar Productos"; ?>
  </button>
</div>

<!-- ======================================= -->
<!-- Selected Products Display (Punto 7)    -->
<!-- ======================================= -->
<div id="selectedProductsContainer" style="display:none;">
  <label class="question-label">
    <?= ($lang=='en')
        ? "Selected Products"
        : "Productos Seleccionados"; ?>
  </label>
  <div id="selectedProductsList"></div>

  <!-- Hidden inputs for form submission -->
  <div id="selectedProductsHiddenInputs"></div>
</div>

<!-- ======================================= -->
<!-- Description Area                       -->
<!-- ======================================= -->
<div class="question-block" id="qScopeDescription">
  <label for="Scope_Description" class="question-label">
    <?= ($lang=='en')
        ? "Description / Additional Notes"
        : "Descripcion / Notas Adicionales"; ?>
  </label>
  <textarea
    name="Scope_Description"
    id="Scope_Description"
    rows="4"
    placeholder="<?= ($lang=='en') ? 'Enter additional details about the scope of work...' : 'Ingrese detalles adicionales sobre el alcance del trabajo...'; ?>"
  ></textarea>
</div>

<!-- ======================================= -->
<!-- Signature Area                         -->
<!-- ======================================= -->
<div class="question-block" id="qScopeSignature">
  <label class="question-label">
    <?= ($lang=='en') ? "Authorized Signature" : "Firma Autorizada"; ?>
  </label>
  <div class="signature-area">
    <div class="signature-line">
      <div class="signature-field">
        <div class="signature-blank"></div>
        <span class="signature-label"><?= ($lang=='en') ? "Client Signature" : "Firma del Cliente"; ?></span>
      </div>
      <div class="signature-field">
        <input type="text" name="Scope_Client_Name" id="Scope_Client_Name"
          placeholder="<?= ($lang=='en') ? 'Full Name' : 'Nombre Completo'; ?>">
        <span class="signature-label"><?= ($lang=='en') ? "Printed Name" : "Nombre Impreso"; ?></span>
      </div>
      <div class="signature-field">
        <input type="date" name="Scope_Sign_Date" id="Scope_Sign_Date">
        <span class="signature-label"><?= ($lang=='en') ? "Date" : "Fecha"; ?></span>
      </div>
    </div>
  </div>
</div>


<!-- ======================================= -->
<!-- PRODUCT CATALOG MODAL (not in document)-->
<!-- ======================================= -->
<div id="productCatalogModal" class="product-modal-overlay" style="display:none;">
  <div class="product-modal">

    <!-- Header -->
    <div class="product-modal-header">
      <h3><?= ($lang=='en') ? "Product Catalog" : "Catalogo de Productos"; ?></h3>
      <button type="button" class="product-modal-close" onclick="closeProductCatalogModal()">&times;</button>
    </div>

    <!-- Search -->
    <div class="product-modal-search">
      <input type="text" id="productCatalogSearch"
        placeholder="<?= ($lang=='en') ? 'Search products...' : 'Buscar productos...'; ?>"
        oninput="filterProductCatalog()">
      <div class="product-modal-tab-bar">
        <button type="button" class="product-tab active" data-tab="all" onclick="switchProductTab('all')">
          <?= ($lang=='en') ? "All" : "Todos"; ?>
        </button>
        <button type="button" class="product-tab" data-tab="kitchen" onclick="switchProductTab('kitchen')">
          <?= ($lang=='en') ? "Kitchen & Hood" : "Cocina y Campana"; ?>
        </button>
        <button type="button" class="product-tab" data-tab="janitorial" onclick="switchProductTab('janitorial')">
          <?= ($lang=='en') ? "Janitorial" : "Limpieza"; ?>
        </button>
      </div>
    </div>

    <!-- Body with catalog cards -->
    <div class="product-modal-body" id="productCatalogBody">
      <!-- Rendered dynamically -->
    </div>

    <!-- Footer with action buttons -->
    <div class="product-modal-footer">
      <span id="productSelectionCount">0 <?= ($lang=='en') ? "selected" : "seleccionados"; ?></span>
      <div class="product-modal-footer-btns">
        <button type="button" class="product-btn-cancel" onclick="closeProductCatalogModal()">
          <?= ($lang=='en') ? "Cancel" : "Cancelar"; ?>
        </button>
        <button type="button" class="product-btn-add" onclick="addSelectedProducts()">
          <?= ($lang=='en') ? "Add Selected" : "Agregar Seleccionados"; ?>
        </button>
      </div>
    </div>

  </div>
</div>


<!-- ======================================= -->
<!-- STYLES                                 -->
<!-- ======================================= -->
<style>
  /* ===== ADD PRODUCTS BUTTON ===== */
  .btn-add-products {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background: linear-gradient(135deg, #001f54 0%, #003080 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,31,84,0.25);
  }
  .btn-add-products:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,31,84,0.35);
    background: linear-gradient(135deg, #003080 0%, #004ab5 100%);
  }

  /* ===== SELECTED PRODUCTS CONTAINER ===== */
  #selectedProductsContainer {
    margin-top: 15px;
  }
  .selected-product-card {
    background: #fff;
    border: 2px solid #e1e8ed;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 12px;
    position: relative;
    transition: all 0.2s ease;
  }
  .selected-product-card:hover {
    border-color: #001f54;
    box-shadow: 0 2px 8px rgba(0,31,84,0.1);
  }
  .selected-product-name {
    font-size: 15px;
    font-weight: 700;
    color: #001f54;
    margin-bottom: 4px;
  }
  .selected-product-category {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #999;
    margin-bottom: 10px;
  }
  .selected-product-scope {
    list-style: none;
    padding: 0;
    margin: 0;
  }
  .selected-product-scope li {
    position: relative;
    padding: 5px 0 5px 18px;
    font-size: 13px;
    color: #444;
    line-height: 1.5;
  }
  .selected-product-scope li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 12px;
    width: 8px;
    height: 8px;
    background: #001f54;
    border-radius: 50%;
  }
  .selected-product-remove {
    position: absolute;
    top: 12px;
    right: 12px;
    background: #ff4d4d;
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 26px;
    height: 26px;
    font-size: 16px;
    line-height: 26px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 0;
  }
  .selected-product-remove:hover {
    background: #cc0000;
    transform: scale(1.1);
  }

  /* ===== SIGNATURE AREA ===== */
  .signature-area {
    margin-top: 10px;
  }
  .signature-line {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
    align-items: flex-end;
  }
  .signature-field {
    flex: 1;
    min-width: 180px;
    text-align: center;
  }
  .signature-blank {
    width: 100%;
    height: 60px;
    border-bottom: 2px solid #333;
    margin-bottom: 6px;
  }
  .signature-field input[type="text"],
  .signature-field input[type="date"] {
    text-align: center;
    border: none;
    border-bottom: 2px solid #333;
    border-radius: 0;
    padding: 8px 4px;
    background: transparent;
    margin-bottom: 6px;
  }
  .signature-field input[type="text"]:focus,
  .signature-field input[type="date"]:focus {
    box-shadow: none;
    border-color: #001f54;
    transform: none;
  }
  .signature-label {
    display: block;
    font-size: 12px;
    color: #666;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* ===== PRODUCT CATALOG MODAL ===== */
  .product-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 10002;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }
  .product-modal {
    background: #fff;
    border-radius: 14px;
    width: 100%;
    max-width: 720px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    overflow: hidden;
  }
  .product-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    background: linear-gradient(135deg, #001f54 0%, #003080 100%);
    color: #fff;
  }
  .product-modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
  }
  .product-modal-close {
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
  .product-modal-close:hover {
    opacity: 1;
  }
  .product-modal-search {
    padding: 16px 24px 8px;
  }
  .product-modal-search input {
    width: 100%;
    padding: 10px 16px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
  }
  .product-modal-search input:focus {
    border-color: #001f54;
  }

  /* Tab bar */
  .product-modal-tab-bar {
    display: flex;
    gap: 6px;
    margin-top: 10px;
  }
  .product-tab {
    padding: 6px 16px;
    border: 2px solid #ddd;
    border-radius: 20px;
    background: #fff;
    color: #666;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .product-tab:hover {
    border-color: #001f54;
    color: #001f54;
  }
  .product-tab.active {
    background: #001f54;
    color: #fff;
    border-color: #001f54;
  }

  /* Body */
  .product-modal-body {
    padding: 16px 24px;
    overflow-y: auto;
    flex: 1;
  }

  /* Category headers */
  .product-catalog-category {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #999;
    margin: 16px 0 8px;
    padding-bottom: 4px;
    border-bottom: 1px solid #eee;
  }
  .product-catalog-category:first-child {
    margin-top: 0;
  }

  /* Product cards grid */
  .product-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
  }
  .product-catalog-card {
    border: 2px solid #e1e8ed;
    border-radius: 10px;
    padding: 12px 14px;
    cursor: pointer;
    background: #fafbfc;
    transition: all 0.2s ease;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    user-select: none;
  }
  .product-catalog-card:hover {
    border-color: #001f54;
    background: #f0f4ff;
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(0,31,84,0.12);
  }
  .product-catalog-card.selected {
    border-color: #001f54;
    background: #e0ecff;
    box-shadow: 0 0 0 1px #001f54;
  }
  .product-catalog-card input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-top: 2px;
    accent-color: #001f54;
    cursor: pointer;
    flex-shrink: 0;
  }
  .product-catalog-card-info {
    flex: 1;
  }
  .product-catalog-card-name {
    font-size: 13px;
    font-weight: 700;
    color: #001f54;
  }
  .product-catalog-card-scope-count {
    font-size: 11px;
    color: #888;
    margin-top: 2px;
  }
  .product-catalog-no-results {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px 20px;
    color: #999;
    font-style: italic;
  }

  /* Footer */
  .product-modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 24px;
    border-top: 1px solid #eee;
    background: #fafbfc;
  }
  #productSelectionCount {
    font-size: 13px;
    font-weight: 600;
    color: #001f54;
  }
  .product-modal-footer-btns {
    display: flex;
    gap: 10px;
  }
  .product-btn-cancel {
    padding: 8px 20px;
    border: 2px solid #ddd;
    border-radius: 8px;
    background: #fff;
    color: #666;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .product-btn-cancel:hover {
    border-color: #999;
    color: #333;
  }
  .product-btn-add {
    padding: 8px 24px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(135deg, #001f54 0%, #003080 100%);
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .product-btn-add:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,31,84,0.3);
  }

  /* ===== PRINT: hide catalog button & modal ===== */
  @media print {
    .product-catalog-btn-wrap,
    .product-modal-overlay,
    .selected-product-remove {
      display: none !important;
    }
  }
</style>


<script>
// =======================================
// TRANSLATION WRAPPER
// =======================================
function t(en, es) {
  return <?= ($lang=='en') ? "en" : "es" ?> === 'en' ? en : es;
}

// =======================================
// OPTIONS MAP (Translated)
// =======================================
const scopeOfWorkOptions = {

  "Schools and Universities": [
    t("Classrooms and Lecture Halls", "Aulas y Salones de Clase"),
    t("Restrooms", "Banos"),
    t("Offices and Administrative Areas", "Oficinas y Areas Administrativas"),
    t("Common Areas and Hallways", "Areas Comunes y Pasillos"),
    t("Cafeterias, Dining Halls, and Kitchens", "Cafeterias, Comedores y Cocinas"),
    t("Gymnasiums, Auditoriums, and Sports Facilities", "Gimnasios, Auditorios y Areas Deportivas"),
    t("Libraries and Study Areas", "Bibliotecas y Areas de Estudio"),
    t("Laboratories and Specialized Areas", "Laboratorios y Areas Especializadas"),
    t("Exterior Cleaning and Grounds", "Limpieza Exterior y Terrenos"),
    t("Floors and Carpets", "Pisos y Alfombras"),
    t("Waste and Recycling Areas", "Areas de Basura y Reciclaje")
  ],

  "Corporate Offices": [
    t("Workstations and Offices", "Oficinas y Estaciones de Trabajo"),
    t("Conference Rooms and Meeting Spaces", "Salas de Conferencias y Reuniones"),
    t("Reception and Lobby Areas", "Recepcion y Lobby"),
    t("Restrooms", "Banos"),
    t("Break Rooms and Office Kitchens", "Salas de Descanso y Cocinas de Oficina"),
    t("Common Areas and Hallways", "Areas Comunes y Pasillos"),
    t("Floors and Carpets", "Pisos y Alfombras"),
    t("Waste and Recycling Rooms", "Cuartos de Basura y Reciclaje"),
    t("Exterior Entryways and Sidewalks", "Entradas Exteriores y Banquetas")
  ],

  "Airports": [
    t("Terminals and Gate Areas", "Terminales y Areas de Puertas"),
    t("Security Checkpoints", "Puntos de Seguridad"),
    t("Baggage Claim Areas", "Areas de Reclamo de Equipaje"),
    t("Restrooms", "Banos"),
    t("Lounges and VIP Areas", "Salas VIP"),
    t("Food Courts and Concessions", "Areas de Comida y Concesiones"),
    t("Circulation Spaces and Concourses", "Areas de Circulacion y Pasillos"),
    t("Operational Zones and Handling Areas", "Zonas Operativas y de Manejo"),
    t("Exterior Drop-off and Pick-up Zones", "Zonas de Ascenso/Descenso Exteriores"),
    t("Hangars and Maintenance Areas", "Hangares y Areas de Mantenimiento")
  ],

  "Churches": [
    t("Sanctuary and Worship Areas", "Santuario y Areas de Culto"),
    t("Classrooms and Meeting Rooms", "Aulas y Salas de Reunion"),
    t("Restrooms", "Banos"),
    t("Fellowship Halls and Event Spaces", "Salones de Eventos"),
    t("Kitchens", "Cocinas"),
    t("Offices and Administrative Areas", "Oficinas y Areas Administrativas"),
    t("Common Areas and Hallways", "Areas Comunes y Pasillos"),
    t("Exterior Entryways and Grounds", "Entradas Exteriores y Terrenos")
  ],

  "Stadiums and Sports Arenas": [
    t("Seating Areas and Stands", "Zonas de Asientos y Gradas"),
    t("Food Service Areas", "Areas de Comida"),
    t("Restrooms", "Banos"),
    t("VIP Lounges and Hospitality Spaces", "Areas VIP y Hospitalidad"),
    t("Locker Rooms and Player Facilities", "Vestidores y Areas de Jugadores"),
    t("Corridors and Common Areas", "Pasillos y Areas Comunes"),
    t("Exterior and Parking Areas", "Zonas Exteriores y Estacionamientos"),
    t("Waste and Recycling Zones", "Zonas de Basura y Reciclaje")
  ],

  "Warehouses and Industrial Facilities": [
    t("Warehouse Floors and Storage Areas", "Pisos y Areas de Almacenaje"),
    t("Loading Docks and Receiving Zones", "Muelles de Carga y Zonas de Recepcion"),
    t("Restrooms", "Banos")
  ],

  "Kitchen Cleaning": [
    t("Food Preparation Surfaces", "Superficies de Preparacion de Alimentos"),
    t("Equipment Cleaning", "Limpieza de Equipos"),
    t("Sinks and Dishwashing Areas", "Fregaderos y Areas de Lavado"),
    t("Kitchen Floors", "Pisos de Cocina"),
    t("Storage Areas", "Areas de Almacenaje"),
    t("Trash and Recycling Zones", "Basura y Reciclaje")
  ],

  "Hood Vent": [
    t("Hood and Canopy", "Campana y Cubierta"),
    t("Filters", "Filtros"),
    t("Ductwork", "Ductos"),
    t("Exhaust Fans", "Extractores"),
    t("Surrounding Walls and Floors", "Paredes y Pisos Circundantes")
  ],

  "Kitchen Cleaning & Hood Vent": [
    t("Food Preparation Surfaces", "Superficies de Preparacion"),
    t("Equipment Cleaning", "Limpieza de Equipos"),
    t("Dishwashing Areas", "Areas de Lavado"),
    t("Kitchen Floors", "Pisos de Cocina"),
    t("Hood and Canopy", "Campana y Cubierta"),
    t("Filters", "Filtros"),
    t("Ductwork", "Ductos"),
    t("Exhaust Fans", "Extractores"),
    t("Surrounding Areas", "Areas Circundantes")
  ]
};

// =======================================
// SCOPE OF WORK - DYNAMIC CHECKBOXES
// =======================================
document.addEventListener("DOMContentLoaded", () => {
  const serviceSelect = document.getElementById("Requested_Service");
  const scopeContainer = document.getElementById("scopeOfWorkContainer");

  serviceSelect.addEventListener("change", function () {
    const selectedService = this.value;
    scopeContainer.innerHTML = "";

    if (scopeOfWorkOptions[selectedService]) {
      scopeContainer.style.display = "grid";
      scopeContainer.style.gridTemplateColumns = "repeat(auto-fit, minmax(250px, 1fr))";
      scopeContainer.style.gap = "6px";

      scopeOfWorkOptions[selectedService].forEach(item => {
        const label = document.createElement("label");
        label.style.display = "flex";
        label.style.alignItems = "center";
        label.style.gap = "8px";
        label.style.padding = "8px 10px";
        label.style.background = "#f4f7fc";
        label.style.border = "2px solid #e1e8ed";
        label.style.borderRadius = "8px";
        label.style.transition = "all 0.2s ease";
        label.style.cursor = "pointer";

        label.onmouseover = function() {
          this.style.background = "#e6f0ff";
          this.style.transform = "translateX(3px)";
        };

        label.onmouseout = function() {
          this.style.background = "#f4f7fc";
          this.style.transform = "translateX(0)";
        };

        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.name = "Scope_Of_Work[]";
        checkbox.value = item;
        checkbox.style.width = "20px";
        checkbox.style.height = "20px";
        checkbox.style.cursor = "pointer";
        checkbox.style.accentColor = "#001f54";

        label.appendChild(checkbox);
        label.appendChild(document.createTextNode(item));
        scopeContainer.appendChild(label);
      });

    } else {
      scopeContainer.style.display = "none";
    }
  });
});


// =======================================
// PRODUCT CATALOG MODAL LOGIC
// =======================================
let productModalSelectedIds = new Set();
let productModalTab = 'all';

// Build a unified catalog from both sources
function getUnifiedCatalog() {
  const catalog = [];

  if (typeof servicesCatalog !== 'undefined') {
    servicesCatalog.forEach(svc => {
      catalog.push({
        id: svc.id,
        name: svc.name,
        category: svc.category,
        scope: svc.scope || [],
        source: 'kitchen'
      });
    });
  }

  if (typeof janitorialServicesCatalog !== 'undefined') {
    janitorialServicesCatalog.forEach(svc => {
      catalog.push({
        id: svc.id,
        name: svc.name,
        category: svc.category,
        scope: svc.scope || [],
        source: 'janitorial'
      });
    });
  }

  return catalog;
}

// Open modal
function openProductCatalogModal() {
  productModalSelectedIds = new Set();
  const modal = document.getElementById("productCatalogModal");
  const search = document.getElementById("productCatalogSearch");

  // Reset tabs
  productModalTab = 'all';
  document.querySelectorAll('.product-tab').forEach(tab => {
    tab.classList.toggle('active', tab.dataset.tab === 'all');
  });

  search.value = "";
  renderProductCatalog();
  updateProductSelectionCount();

  modal.style.display = "flex";
  setTimeout(() => search.focus(), 100);
}

// Close modal
function closeProductCatalogModal() {
  document.getElementById("productCatalogModal").style.display = "none";
  productModalSelectedIds = new Set();
}

// Close on overlay click
document.addEventListener("click", function(e) {
  if (e.target.id === "productCatalogModal") closeProductCatalogModal();
});

// Close on Escape
document.addEventListener("keydown", function(e) {
  if (e.key === "Escape" && document.getElementById("productCatalogModal").style.display === "flex") {
    closeProductCatalogModal();
  }
});

// Switch tab
function switchProductTab(tab) {
  productModalTab = tab;
  document.querySelectorAll('.product-tab').forEach(t => {
    t.classList.toggle('active', t.dataset.tab === tab);
  });
  renderProductCatalog();
}

// Filter catalog
function filterProductCatalog() {
  renderProductCatalog();
}

// Render catalog cards in modal
function renderProductCatalog() {
  const body = document.getElementById("productCatalogBody");
  const searchVal = document.getElementById("productCatalogSearch").value.toLowerCase();
  const catalog = getUnifiedCatalog();

  // Filter by tab
  let filtered = catalog;
  if (productModalTab === 'kitchen') {
    filtered = filtered.filter(svc => svc.source === 'kitchen');
  } else if (productModalTab === 'janitorial') {
    filtered = filtered.filter(svc => svc.source === 'janitorial');
  }

  // Filter by search
  if (searchVal) {
    filtered = filtered.filter(svc =>
      svc.name.toLowerCase().includes(searchVal) ||
      svc.category.toLowerCase().includes(searchVal)
    );
  }

  let html = "";

  if (filtered.length === 0) {
    html += '<div class="product-cards-grid"><div class="product-catalog-no-results">';
    html += '<?= ($lang=="en") ? "No products found" : "No se encontraron productos"; ?>';
    html += '</div></div>';
  } else {
    // Group by category
    const grouped = {};
    filtered.forEach(svc => {
      if (!grouped[svc.category]) grouped[svc.category] = [];
      grouped[svc.category].push(svc);
    });

    for (const cat in grouped) {
      html += '<div class="product-catalog-category">' + cat + '</div>';
      html += '<div class="product-cards-grid">';
      grouped[cat].forEach(svc => {
        const isSelected = productModalSelectedIds.has(svc.id);
        const checkedAttr = isSelected ? 'checked' : '';
        const selectedClass = isSelected ? ' selected' : '';
        html += '<div class="product-catalog-card' + selectedClass + '" onclick="toggleProductSelection(\'' + svc.id + '\', this)">';
        html += '<input type="checkbox" ' + checkedAttr + ' onclick="event.stopPropagation(); toggleProductSelection(\'' + svc.id + '\', this.closest(\'.product-catalog-card\'))">';
        html += '<div class="product-catalog-card-info">';
        html += '<div class="product-catalog-card-name">' + svc.name + '</div>';
        html += '<div class="product-catalog-card-scope-count">' + svc.scope.length + ' <?= ($lang=="en") ? "scope items" : "elementos"; ?></div>';
        html += '</div>';
        html += '</div>';
      });
      html += '</div>';
    }
  }

  body.innerHTML = html;
}

// Toggle product selection
function toggleProductSelection(productId, cardEl) {
  if (productModalSelectedIds.has(productId)) {
    productModalSelectedIds.delete(productId);
    cardEl.classList.remove('selected');
    cardEl.querySelector('input[type="checkbox"]').checked = false;
  } else {
    productModalSelectedIds.add(productId);
    cardEl.classList.add('selected');
    cardEl.querySelector('input[type="checkbox"]').checked = true;
  }
  updateProductSelectionCount();
}

// Update selection count
function updateProductSelectionCount() {
  const count = productModalSelectedIds.size;
  const label = count === 1
    ? '1 <?= ($lang=="en") ? "selected" : "seleccionado"; ?>'
    : count + ' <?= ($lang=="en") ? "selected" : "seleccionados"; ?>';
  document.getElementById("productSelectionCount").textContent = label;
}

// Add selected products to Section 7
function addSelectedProducts() {
  if (productModalSelectedIds.size === 0) {
    closeProductCatalogModal();
    return;
  }

  const catalog = getUnifiedCatalog();
  const catalogById = {};
  catalog.forEach(svc => { catalogById[svc.id] = svc; });

  const container = document.getElementById("selectedProductsList");
  const hiddenContainer = document.getElementById("selectedProductsHiddenInputs");
  const wrapper = document.getElementById("selectedProductsContainer");

  productModalSelectedIds.forEach(id => {
    const svc = catalogById[id];
    if (!svc) return;

    // Skip if already added
    if (document.getElementById("selected-product-" + id)) return;

    // Create visual card
    const card = document.createElement("div");
    card.className = "selected-product-card";
    card.id = "selected-product-" + id;

    let scopeHtml = '';
    svc.scope.forEach(item => {
      scopeHtml += '<li>' + item + '</li>';
    });

    card.innerHTML =
      '<button type="button" class="selected-product-remove" onclick="removeSelectedProduct(\'' + id + '\')">&times;</button>' +
      '<div class="selected-product-name">' + svc.name + '</div>' +
      '<div class="selected-product-category">' + svc.category + '</div>' +
      '<ul class="selected-product-scope">' + scopeHtml + '</ul>';

    container.appendChild(card);

    // Create hidden inputs for form submission
    const hiddenName = document.createElement("input");
    hiddenName.type = "hidden";
    hiddenName.name = "Selected_Products_Name[]";
    hiddenName.value = svc.name;
    hiddenName.id = "hidden-product-name-" + id;
    hiddenContainer.appendChild(hiddenName);

    const hiddenId = document.createElement("input");
    hiddenId.type = "hidden";
    hiddenId.name = "Selected_Products_Id[]";
    hiddenId.value = id;
    hiddenId.id = "hidden-product-id-" + id;
    hiddenContainer.appendChild(hiddenId);

    const hiddenScope = document.createElement("input");
    hiddenScope.type = "hidden";
    hiddenScope.name = "Selected_Products_Scope[]";
    hiddenScope.value = JSON.stringify(svc.scope);
    hiddenScope.id = "hidden-product-scope-" + id;
    hiddenContainer.appendChild(hiddenScope);
  });

  // Show the container
  wrapper.style.display = "block";

  closeProductCatalogModal();
}

// Remove a selected product
function removeSelectedProduct(id) {
  const card = document.getElementById("selected-product-" + id);
  if (card) card.remove();

  const hiddenName = document.getElementById("hidden-product-name-" + id);
  if (hiddenName) hiddenName.remove();

  const hiddenId = document.getElementById("hidden-product-id-" + id);
  if (hiddenId) hiddenId.remove();

  const hiddenScope = document.getElementById("hidden-product-scope-" + id);
  if (hiddenScope) hiddenScope.remove();

  // Hide container if empty
  const container = document.getElementById("selectedProductsList");
  if (container.children.length === 0) {
    document.getElementById("selectedProductsContainer").style.display = "none";
  }
}
</script>
