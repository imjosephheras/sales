<head>
  <meta charset="UTF-8">
  <title>Cost Calculator | Prime Facility Services</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    /* ==========================================
       VARIABLES DE COLOR MEJORADAS
    ========================================== */
    :root {
      /* Azules principales (manteniendo la paleta original) */
      --primary-dark: #03194e;
      --primary-base: #0b2a57;
      --primary-light: #1e4fa3;
      --primary-lighter: #48577f;
      --primary-bg: #f0f3f8;
      
      /* Rojos de acento */
      --accent-red: #c70734;
      --accent-red-dark: #8b0000;
      --accent-red-light: #ff4d6d;
      
      /* Colores neutros */
      --white: #ffffff;
      --gray-50: #f6f7f9;
      --gray-100: #ececec;
      --gray-200: #e6e9ef;
      --gray-300: #c9ccd1;
      --gray-600: #6b7280;
      --gray-900: #333333;
      
      /* Efectos */
      --shadow-sm: 0 2px 8px rgba(3, 25, 78, 0.08);
      --shadow-md: 0 4px 16px rgba(3, 25, 78, 0.12);
      --shadow-lg: 0 8px 24px rgba(3, 25, 78, 0.15);
      --shadow-xl: 0 12px 32px rgba(3, 25, 78, 0.18);
      
      /* Bordes */
      --radius-sm: 6px;
      --radius-md: 8px;
      --radius-lg: 12px;
      --radius-xl: 20px;
      --radius-full: 50px;
      
      /* Transiciones */
      --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ==========================================
       RESET BASE
    ========================================== */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-lighter) 100%);
      min-height: 100vh;
      padding: 30px 20px;
      color: var(--gray-900);
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    /* ==========================================
       CONTENEDOR PRINCIPAL
    ========================================== */
    .container-calculator {
      max-width: 1100px;
      margin: 0 auto;
      padding: 0;
      background: var(--white);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-xl);
      overflow: hidden;
      animation: slideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(30px) scale(0.96);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    /* ==========================================
       HEADER PRINCIPAL
    ========================================== */
    .container-calculator > h2 {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-base) 50%, var(--primary-light) 100%);
      color: var(--white);
      padding: 2.5rem 2rem;
      margin: 0;
      font-size: 2rem;
      font-weight: 800;
      text-align: center;
      letter-spacing: -0.02em;
      position: relative;
      overflow: hidden;
      text-transform: uppercase;
    }

    .container-calculator > h2::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 400px;
      height: 400px;
      background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
      animation: pulse 8s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 0.5; }
      50% { transform: scale(1.1); opacity: 0.8; }
    }

    /* ==========================================
       CONTENIDO INTERNO
    ========================================== */
    .container-calculator > form,
    .container-calculator > hr,
    .container-calculator > .section-bar,
    .container-calculator > .print-btn {
      margin-left: 20px;
      margin-right: 20px;
    }

    /* ==========================================
       BLOQUES DE PREGUNTAS
    ========================================== */
    .question-block {
      margin-bottom: 20px;
      animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .question-block label {
      font-weight: 600;
      margin-bottom: 8px;
      display: block;
      color: var(--gray-900);
      font-size: 14px;
      letter-spacing: 0.01em;
    }

    /* ==========================================
       INPUTS Y SELECTS MEJORADOS
    ========================================== */
    input, 
    select,
    .form-control {
      width: 100%;
      background: var(--gray-50);
      border: 1.5px solid var(--gray-300);
      border-radius: var(--radius-md);
      padding: 12px 14px;
      font-size: 14px;
      font-family: inherit;
      margin-bottom: 15px;
      transition: var(--transition);
      font-weight: 500;
      color: var(--gray-900);
    }

    input:hover,
    select:hover {
      border-color: var(--primary-light);
      background: var(--white);
    }

    input:focus,
    select:focus {
      outline: none;
      border-color: var(--primary-base);
      background: var(--white);
      box-shadow: 0 0 0 3px rgba(11, 42, 87, 0.1);
      transform: translateY(-1px);
    }

    input[readonly],
    .form-control[readonly] {
      background: #f1f1f1;
      color: var(--gray-600);
      cursor: not-allowed;
      border-color: var(--gray-200);
    }

    /* ==========================================
       SELECT SERVICE TYPE DESTACADO
    ========================================== */
    select[name="Service_Type"] {
      background: linear-gradient(135deg, var(--white) 0%, var(--gray-50) 100%);
      border: 2px solid var(--primary-base);
      color: var(--primary-base);
      font-weight: 700;
      font-size: 15px;
      padding: 14px 16px;
      cursor: pointer;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    select[name="Service_Type"]:hover {
      border-color: var(--primary-light);
      background: var(--white);
      box-shadow: var(--shadow-sm);
    }

    select[name="Service_Type"]:focus {
      box-shadow: 0 0 0 4px rgba(11, 42, 87, 0.15);
    }

    /* ==========================================
       TABLAS MEJORADAS
    ========================================== */
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      margin-top: 15px;
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: var(--shadow-md);
      background: var(--white);
    }

    table thead {
      background: linear-gradient(135deg, var(--accent-red) 0%, var(--accent-red-dark) 100%);
      color: var(--white);
    }

    table th {
      padding: 14px 12px;
      text-align: center;
      font-weight: 700;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    table tbody tr {
      background: var(--white);
      transition: var(--transition);
      border-bottom: 1px solid var(--gray-100);
    }

    table tbody tr:nth-child(even) {
      background: var(--gray-50);
    }

    table tbody tr:hover {
      background: #e8f0fe;
      transform: scale(1.01);
      box-shadow: var(--shadow-sm);
    }

    table tbody tr:last-child {
      border-bottom: none;
    }

    table td {
      padding: 12px 10px;
      text-align: center;
      border-bottom: 1px solid var(--gray-100);
    }

    table input,
    table select {
      margin-bottom: 0;
      padding: 8px 10px;
      font-size: 13px;
    }

    /* ==========================================
       BARRAS DE SECCIÓN MEJORADAS
    ========================================== */
    .section-bar {
      background: linear-gradient(180deg, var(--primary-base) 0%, var(--primary-dark) 100%);
      color: var(--white);
      padding: 14px 18px;
      border-radius: var(--radius-md);
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: pointer;
      margin: 25px 20px 0 20px;
      user-select: none;
      transition: var(--transition);
      box-shadow: var(--shadow-md);
      position: relative;
      overflow: hidden;
    }

    .section-bar::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
      transition: left 0.5s;
    }

    .section-bar:hover::before {
      left: 100%;
    }

    .section-bar:hover {
      transform: translateX(4px);
      box-shadow: var(--shadow-lg);
    }

    .section-bar:active {
      transform: translateX(2px) scale(0.98);
    }

    .section-bar .icon {
      font-size: 1.1rem;
      transition: transform 0.3s ease;
      font-weight: 700;
    }

    /* ==========================================
       CONTENIDO DE SECCIONES
    ========================================== */
    .section-content {
      padding: 20px 20px 10px 20px;
      margin: 0 20px 5px 20px;
      background: var(--gray-50);
      border-radius: 0 0 var(--radius-md) var(--radius-md);
    }

    /* ==========================================
       SECTION TITLE (títulos internos)
    ========================================== */
    .section-title {
      background: linear-gradient(to right, #e8f0fe, var(--white));
      color: var(--primary-base);
      padding: 12px 16px;
      font-size: 15px;
      font-weight: 700;
      border-radius: var(--radius-sm);
      margin-bottom: 15px;
      box-shadow: var(--shadow-sm);
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }

    /* ==========================================
       BOTONES SECUNDARIOS
    ========================================== */
    .btn-outline-secondary {
      background: var(--white);
      color: var(--primary-base);
      border: 2px solid var(--primary-base);
      border-radius: var(--radius-md);
      padding: 0;
      width: 52px;
      height: 52px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
      cursor: pointer;
      font-size: 22px;
    }

    .btn-outline-secondary:hover {
      background: var(--primary-base);
      transform: translateY(-2px) scale(1.05);
      box-shadow: var(--shadow-md);
    }

    .btn-outline-secondary:active {
      transform: translateY(0) scale(1);
    }

    /* ==========================================
       MARKUP SLIDER MEJORADO
    ========================================== */
    .markup-bar-wrapper {
      position: relative;
      margin-bottom: 15px;
      margin-top: 10px;
    }

    .markup-slider {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 40px;
      opacity: 0;
      cursor: pointer;
      z-index: 3;
    }

    .markup-bar {
      height: 40px;
      background: var(--gray-200);
      border-radius: var(--radius-full);
      overflow: hidden;
      box-shadow: inset 0 2px 4px rgba(0,0,0,0.06);
      position: relative;
    }

    .markup-fill {
      height: 100%;
      width: 0%;
      background: linear-gradient(90deg, var(--primary-base) 0%, var(--primary-light) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--white);
      font-weight: 700;
      font-size: 14px;
      transition: width 0.3s ease;
      box-shadow: 0 2px 8px rgba(11, 42, 87, 0.3);
      position: relative;
    }

    .markup-fill::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }

    /* ==========================================
       BOTÓN DE IMPRESIÓN DESTACADO
    ========================================== */
    .print-btn {
      background: linear-gradient(135deg, var(--accent-red) 0%, var(--accent-red-dark) 100%);
      color: var(--white);
      border: none;
      padding: 14px 40px;
      border-radius: var(--radius-full);
      font-weight: 700;
      font-size: 16px;
      cursor: pointer;
      margin: 30px auto 20px;
      display: block;
      text-transform: uppercase;
      letter-spacing: 1px;
      transition: var(--transition);
      box-shadow: var(--shadow-lg);
      position: relative;
      overflow: hidden;
    }

    .print-btn::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.3);
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }

    .print-btn:hover::before {
      width: 300px;
      height: 300px;
    }

    .print-btn:hover {
      transform: translateY(-3px) scale(1.02);
      box-shadow: 0 8px 24px rgba(199, 7, 52, 0.4);
    }

    .print-btn:active {
      transform: translateY(-1px) scale(1);
    }

    /* ==========================================
       GAUGE (PROFIT NET INDICATOR)
    ========================================== */
    #profitGauge {
      max-width: 220px;
      margin: 0 auto;
      display: block;
      filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
    }

    #profitGaugeLabel {
      text-align: center;
      margin-top: 10px;
      font-weight: 700;
      font-size: 18px;
      color: var(--primary-base);
    }

    /* ==========================================
       DIVISOR HR
    ========================================== */
    hr {
      border: none;
      border-top: 2px solid var(--gray-200);
      margin: 25px 20px;
    }

    /* ==========================================
       RESPONSIVE
    ========================================== */
    @media (max-width: 768px) {
      body {
        padding: 15px 10px;
      }

      .container-calculator {
        border-radius: var(--radius-md);
      }

      .container-calculator > h2 {
        font-size: 1.5rem;
        padding: 2rem 1.5rem;
      }

      .section-bar {
        margin: 20px 15px 0 15px;
        padding: 12px 15px;
        font-size: 13px;
      }

      .section-content {
        padding: 15px;
        margin: 0 15px 5px 15px;
      }

      .print-btn {
        margin: 20px 15px;
        width: calc(100% - 30px);
      }

      table {
        font-size: 12px;
      }

      table th,
      table td {
        padding: 10px 6px;
      }

      input,
      select {
        font-size: 13px;
        padding: 10px 12px;
      }
    }

    @media (max-width: 480px) {
      .container-calculator > h2 {
        font-size: 1.25rem;
        padding: 1.5rem 1rem;
      }

      .section-bar {
        font-size: 12px;
      }

      .btn-outline-secondary {
        width: 44px;
        height: 44px;
        font-size: 18px;
      }
    }

    /* ==========================================
       ESTILOS DE IMPRESIÓN
    ========================================== */
    @media print {
      * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }

      body {
        background: #ffffff !important;
        padding: 0;
      }

      .container-calculator {
        box-shadow: none;
        border-radius: 0;
      }

      .section-content {
        display: block !important;
      }

      #q_hood_vent,
      #q_labor_table,
      #q2, #q3, #q4, #q5, #q6,
      #subcontractor_price_block {
        display: block !important;
      }

      button,
      select,
      input,
      .no-print {
        display: none !important;
      }

      .print-only {
        display: block !important;
        width: 100%;
        min-height: 40px;
        padding: 10px 12px;
        margin-top: 4px;
        background: var(--gray-50) !important;
        border: 1px solid var(--gray-300) !important;
        border-radius: var(--radius-md);
        font-size: 14px;
        font-weight: 500;
        color: var(--gray-900);
        box-sizing: border-box;
      }

      .section-bar {
        background: linear-gradient(180deg, var(--primary-base) 0%, var(--primary-dark) 100%) !important;
        color: var(--white) !important;
        box-shadow: none;
        page-break-after: avoid;
      }

      .section-bar span {
        color: var(--white) !important;
      }

      .markup-bar {
        background: var(--gray-200) !important;
      }

      .markup-fill {
        background: linear-gradient(90deg, var(--primary-base), var(--primary-light)) !important;
        color: var(--white) !important;
      }

      #markupLabel,
      #subcontractMarkupLabel,
      #markupLabelPrint,
      #subcontractMarkupLabelPrint {
        color: var(--white) !important;
        font-weight: 600;
      }

      table thead {
        background: linear-gradient(135deg, var(--accent-red) 0%, var(--accent-red-dark) 100%) !important;
      }

      .section-content {
        page-break-inside: avoid;
        border: 1px solid var(--gray-300);
      }
    }

    /* ==========================================
       UTILIDADES
    ========================================== */
    .no-print {
      /* Se oculta en impresión */
    }

    .print-only {
      display: none;
    }
  </style>

<script>
  /* ⛔ DETECTAR IMPRESIÓN */
const IS_PRINTING =
  window.matchMedia &&
  window.matchMedia('print').matches;

/* =====================================================
   TOGGLE SECTIONS
===================================================== */
function toggleSection(id, icon) {
  const el = document.getElementById(id);
  if (!el) return;

  const open = el.style.display !== "none";
  el.style.display = open ? "none" : "block";
  if (icon) icon.textContent = open ? "▲" : "▼";
}

/* =====================================================
   LABOR TABLE → LABOR COST
===================================================== */
function calculateLaborCostFromTable() {
  let total = 0;

  document.querySelectorAll('select[name="labor_workers[]"]').forEach((w, i) => {
    const h = document.querySelectorAll('select[name="labor_hours[]"]')[i];
    const r = document.querySelectorAll('input[name="labor_rate[]"]')[i];
    const d = document.querySelectorAll('select[name="labor_days[]"]')[i];

    total += (+w.value || 0) *
             (+h.value || 0) *
             (+r.value || 0) *
             (+d.value || 0);
  });

  const laborEl = document.querySelector('[name="Labor_Cost"]');
  if (laborEl) {
    laborEl.value = total.toFixed(2);

    // 🪞 print mirror
    const span = laborEl.nextElementSibling;
    if (span && span.classList.contains('print-only')) {
      span.textContent = laborEl.value;
    }
  }

  calculateFringe();
  calculateDirectSubtotal();
}

/* =====================================================
   FRINGE (Tx, Insurance, Benefits)
===================================================== */
function calculateFringe() {
  const labor =
    +document.querySelector('[name="Labor_Cost"]')?.value || 0;

  const fringe = labor * 0.1993;

  const fringeEl = document.getElementById('labor_fringe');
  if (fringeEl) {
    fringeEl.value = fringe.toFixed(2);

    // 🪞 PRINT MIRROR
    const span = fringeEl.nextElementSibling;
    if (span && span.classList.contains('print-only')) {
      span.textContent = fringeEl.value;
    }
  }
}

/* =====================================================
   HOOD VENT TOTAL (PRECIO DE VENTA)
===================================================== */
function calculateHoodVentDisplayedTotal() {
  const hoodBlock = document.getElementById('q_hood_vent');
  if (!hoodBlock) return 0;

  let total = 0;

  hoodBlock.querySelectorAll('select[name="hood_qty[]"]').forEach((q, i) => {
    const p = hoodBlock.querySelectorAll('select[name="hood_price[]"]')[i];
    total += (+q.value || 0) * (+p?.value || 0);
  });

  const out = document.getElementById('HoodVent_Total');
  if (out) out.value = total.toFixed(2);

  return total;
}

/* =====================================================
   UI: mostrar/ocultar markup y total según Service Type
===================================================== */
function toggleMarkupByServiceType() {
  const type = document.querySelector('[name="Service_Type"]')?.value || "";

  const normalMarkupBlock = document.getElementById('normal_markup_block');
  const subcontractMarkupBlock = document.getElementById('subcontract_markup_block');

  const normalTotalBlock = document.getElementById('normal_total_block');
  const hoodTotalBlock = document.getElementById('hoodvent_total_block');

  // MARKUP blocks
  if (type === "subcontract") {
    if (normalMarkupBlock) normalMarkupBlock.style.display = "none";
    if (subcontractMarkupBlock) subcontractMarkupBlock.style.display = "block";
  } else {
    if (normalMarkupBlock) normalMarkupBlock.style.display = "block";
    if (subcontractMarkupBlock) subcontractMarkupBlock.style.display = "none";
  }

  // TOTAL blocks
  if (type === "hoodvent") {
    if (normalTotalBlock) normalTotalBlock.style.display = "none";
    if (hoodTotalBlock) hoodTotalBlock.style.display = "block";
  } else {
    if (normalTotalBlock) normalTotalBlock.style.display = "block";
    if (hoodTotalBlock) hoodTotalBlock.style.display = "none";
  }
}

/* =====================================================
   DIRECT SUBTOTAL (COSTO REAL)
===================================================== */
function calculateDirectSubtotal() {
  const serviceType =
    document.querySelector('[name="Service_Type"]')?.value || "";

  // SUBCONTRACT: Direct_Subtotal = Subcontract_Price
  if (serviceType === "subcontract") {
    const subcontract =
      +document.querySelector('[name="Subcontract_Price"]')?.value || 0;

    const directEl = document.querySelector('[name="Direct_Subtotal"]');
    if (directEl) {
      directEl.value = subcontract.toFixed(2);

      // 🪞 print mirror
      const span = directEl.nextElementSibling;
      if (span && span.classList.contains('print-only')) {
        span.textContent = directEl.value;
      }
    }

    calculateMarkup();
    return;
  }

  // TIMESHEET / HOODVENT COST BASE
  const values = [
    'Labor_Cost',
    'Labor_Fringe',
    'Transport_Cost',
    'Material_Cost',
    'Equipment_Cost'
  ].map(n => +document.querySelector(`[name="${n}"]`)?.value || 0);

  const subtotal = values.reduce((a, b) => a + b, 0);

  const directEl = document.querySelector('[name="Direct_Subtotal"]');
  if (directEl) {
    directEl.value = subtotal.toFixed(2);

    // 🪞 print mirror
    const span = directEl.nextElementSibling;
    if (span && span.classList.contains('print-only')) {
      span.textContent = directEl.value;
    }
  }

  calculateMarkup();
}

/* =====================================================
   MARKUP / TOTAL
===================================================== */
function calculateMarkup() {
  const serviceType =
    document.querySelector('[name="Service_Type"]')?.value || "";

  /* 🔴 HOODVENT */
  if (serviceType === "hoodvent") {
    const sellPrice = calculateHoodVentDisplayedTotal();
    const cost =
      +document.querySelector('[name="Direct_Subtotal"]')?.value || 0;

    const profit = sellPrice - cost;
    const percent = sellPrice > 0 ? (profit / sellPrice) * 100 : 0;

    const amountEl = document.querySelector('[name="Markup_Amount"]');
    const totalEl  = document.querySelector('[name="Total"]');

    if (amountEl) {
      amountEl.value = profit.toFixed(2);

      // 🪞 print mirror
      const span = amountEl.nextElementSibling;
      if (span && span.classList.contains('print-only')) {
        span.textContent = amountEl.value;
      }
    }

    if (totalEl) {
      totalEl.value = sellPrice.toFixed(2);

      // 🪞 print mirror
      const span = totalEl.nextElementSibling;
      if (span && span.classList.contains('print-only')) {
        span.textContent = totalEl.value;
      }
    }

    // % label
    const label = document.getElementById('markupLabel');
    const fill  = document.getElementById('markupFill');

    if (label) label.textContent = percent.toFixed(1) + '%';
    if (fill)  fill.style.width  = Math.min(Math.max(percent, 0), 100) + '%';

    // 🪞 print mirror % 
    const labelPrint = document.getElementById('markupLabelPrint');
    if (labelPrint) labelPrint.textContent = percent.toFixed(1) + '%';

    calculateTaxes();
    calculateFixedCosts();
    calculateProfitMarginGauge();
    return;
  }

  /* 🟡 SUBCONTRACT */
  if (serviceType === "subcontract") {

    const base =
      +document.querySelector('[name="Subcontract_Price"]')?.value || 0;

    const pct =
      +document.getElementById('SubcontractMarkupSlider')?.value || 0;

    const amount = base * pct / 100;
    const total  = base + amount;

    const directEl = document.querySelector('[name="Direct_Subtotal"]');
    if (directEl) directEl.value = base.toFixed(2);

    const amountEl = document.querySelector('[name="Markup_Amount"]');
    const totalEl  = document.querySelector('[name="Total"]');

    if (amountEl) {
      amountEl.value = amount.toFixed(2);

      const span = amountEl.nextElementSibling;
      if (span && span.classList.contains('print-only')) {
        span.textContent = amountEl.value;
      }
    }

    if (totalEl) {
      totalEl.value = total.toFixed(2);

      const span = totalEl.nextElementSibling;
      if (span && span.classList.contains('print-only')) {
        span.textContent = totalEl.value;
      }
    }

    const label = document.getElementById('subcontractMarkupLabel');
    const fill  = document.getElementById('subcontractMarkupFill');

    if (label) label.textContent = pct.toFixed(1) + '%';
    if (fill)  fill.style.width  = Math.min(Math.max(pct, 0), 100) + '%';

    // 🪞 print mirror %
    const labelPrint = document.getElementById('subcontractMarkupLabelPrint');
    if (labelPrint) labelPrint.textContent = pct.toFixed(1) + '%';

    calculateTaxes();
    calculateFixedCosts();
    calculateProfitMarginGauge();
    return;
  }

  /* 🟢 NORMAL / TIMESHEET */
  const base =
    +document.querySelector('[name="Direct_Subtotal"]')?.value || 0;

  const pct =
    +document.getElementById('MarkupSlider')?.value || 0;

  const amount = base * pct / 100;
  const total  = base + amount;

  const amountEl = document.querySelector('[name="Markup_Amount"]');
  const totalEl  = document.querySelector('[name="Total"]');

  if (amountEl) {
    amountEl.value = amount.toFixed(2);

    const span = amountEl.nextElementSibling;
    if (span && span.classList.contains('print-only')) {
      span.textContent = amountEl.value;
    }
  }

  if (totalEl) {
    totalEl.value = total.toFixed(2);

    const span = totalEl.nextElementSibling;
    if (span && span.classList.contains('print-only')) {
      span.textContent = totalEl.value;
    }
  }

  const label = document.getElementById('markupLabel');
  const fill  = document.getElementById('markupFill');

  if (label) label.textContent = pct.toFixed(1) + '%';
  if (fill)  fill.style.width  = Math.min(Math.max(pct, 0), 100) + '%';

  // 🪞 print mirror %
  const labelPrint = document.getElementById('markupLabelPrint');
  if (labelPrint) labelPrint.textContent = pct.toFixed(1) + '%';

  calculateTaxes();
  calculateFixedCosts();
  calculateProfitMarginGauge();
}

/* =====================================================
   TAXES
===================================================== */
function calculateTaxes() {
  const total =
    +document.querySelector('[name="Total"]')?.value || 0;

  const taxesEl = document.querySelector('[name="Taxes"]');
  const grandEl = document.querySelector('[name="Grand_Total"]');

  if (taxesEl) {
    taxesEl.value = (total * 0.0825).toFixed(2);

    // 🪞 print mirror
    const span = taxesEl.nextElementSibling;
    if (span && span.classList.contains('print-only')) {
      span.textContent = taxesEl.value;
    }
  }

  if (grandEl) {
    grandEl.value = (total * 1.0825).toFixed(2);

    // 🪞 print mirror
    const span = grandEl.nextElementSibling;
    if (span && span.classList.contains('print-only')) {
      span.textContent = grandEl.value;
    }
  }
}

/* =====================================================
   FIXED COSTS
===================================================== */
function calculateFixedCosts() {

  const serviceType =
    document.querySelector('[name="Service_Type"]')?.value || "";

  let base = 0;

  /* ================================
     🟡 SUBCONTRACT
     → Fixed costs SOLO sobre la ganancia
  ================================ */
  if (serviceType === 'subcontract') {

    base =
      +document.querySelector('[name="Markup_Amount"]')?.value || 0;

  } else {

    /* 🟢 NORMAL / HOODVENT */
    const total =
      +document.querySelector('[name="Total"]')?.value || 0;

    const direct =
      +document.querySelector('[name="Direct_Subtotal"]')?.value || 0;

    base = total - direct;
  }

  const overhead  = base * 0.44;
  const netProfit = base * 0.56;

  const overheadEl = document.querySelector('[name="Overhead"]');
  const netEl      = document.querySelector('[name="Fixed_Subtotal"]');

  if (overheadEl) {
    overheadEl.value = overhead.toFixed(2);

    // 🪞 print mirror
    const span = overheadEl.nextElementSibling;
    if (span && span.classList.contains('print-only')) {
      span.textContent = overheadEl.value;
    }
  }

  if (netEl) {
    netEl.value = netProfit.toFixed(2);

    // 🪞 print mirror
    const span = netEl.nextElementSibling;
    if (span && span.classList.contains('print-only')) {
      span.textContent = netEl.value;
    }
  }
}

/* =====================================================
   PROFIT NET GAUGE (COLORED, 0–28%)
===================================================== */
function drawProfitGauge(percent) {
  const canvas = document.getElementById('profitGauge');
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  const w = canvas.width;
  const h = canvas.height;
  const cx = w / 2;
  const cy = h;
  const r = Math.min(w / 2, h) - 12;

  ctx.clearRect(0, 0, w, h);

  const MAX = 28;
  const safe = Math.max(0, Math.min(MAX, percent));

  function arc(from, to, color) {
    ctx.beginPath();
    ctx.arc(cx, cy, r, Math.PI + (from / MAX) * Math.PI, Math.PI + (to / MAX) * Math.PI);
    ctx.strokeStyle = color;
    ctx.lineWidth = 14;
    ctx.stroke();
  }

  arc(0, 10, '#c70734');
  arc(10, 18, '#f08c00');
  arc(18, 24, '#f2d200');
  arc(24, 28, '#2e9e44');

  const angle = Math.PI + (safe / MAX) * Math.PI;

  ctx.beginPath();
  ctx.moveTo(cx, cy);
  ctx.lineTo(cx + r * Math.cos(angle), cy + r * Math.sin(angle));
  ctx.strokeStyle = '#0b2a57';
  ctx.lineWidth = 3;
  ctx.stroke();

  ctx.beginPath();
  ctx.arc(cx, cy, 5, 0, Math.PI * 2);
  ctx.fillStyle = '#ffffff';
  ctx.fill();
}

/* =====================================================
   NET PROFIT MARGIN → GAUGE
===================================================== */
function calculateProfitMarginGauge() {
  const total = +document.querySelector('[name="Total"]')?.value || 0;
  const netProfit = +document.querySelector('[name="Fixed_Subtotal"]')?.value || 0;
  const label = document.getElementById('profitGaugeLabel');

  if (total <= 0) {
    drawProfitGauge(0);
    if (label) label.textContent = '0%';
    return;
  }

  const margin = (netProfit / total) * 100;
  drawProfitGauge(margin);
  if (label) label.textContent = margin.toFixed(1) + '%';
}

/* =====================================================
   EVENTS (GLOBAL)
===================================================== */
document.addEventListener('input', (e) => {

  // SUBCONTRACT PRICE
  if (e.target.name === 'Subcontract_Price') {
    calculateDirectSubtotal();
    return;
  }

  // TIMESHEET direct costs
  if (['Labor_Cost', 'Transport_Cost', 'Material_Cost', 'Equipment_Cost'].includes(e.target.name)) {
    calculateFringe();
    calculateDirectSubtotal();
    return;
  }

  // NORMAL slider (timesheet)
  if (e.target.id === 'MarkupSlider') {
    calculateMarkup();
    return;
  }

  // SUBCONTRACT slider
  if (e.target.id === 'SubcontractMarkupSlider') {
    calculateMarkup();
    return;
  }
});

document.addEventListener('change', (e) => {

  // HOODVENT table
  if (e.target.name === 'hood_qty[]' || e.target.name === 'hood_price[]') {
    calculateHoodVentDisplayedTotal();
    calculateMarkup();
    return;
  }

  // TIMESHEET labor table
  if (e.target.name?.includes('labor_')) {
    calculateLaborCostFromTable();
    return;
  }

  // Service type change (si existe input change)
  if (e.target.name === 'Service_Type') {
    toggleMarkupByServiceType();
    calculateDirectSubtotal();
    return;
  }
});

document.addEventListener('DOMContentLoaded', () => {
  toggleMarkupByServiceType();
  calculateHoodVentDisplayedTotal();
  calculateDirectSubtotal();
});
</script>
</head>