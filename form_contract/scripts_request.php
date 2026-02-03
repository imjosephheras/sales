<script>
// ===========================================================
// 🧩 scripts_request.php — Motor dinámico con referencias q1–q28
// ===========================================================

// =========================
// 🔹 CONFIGURACIÓN GLOBAL
// =========================
const formConfig = {
  requestTypeOptions: [
    { category: "Janitorial",  requestTypes: ["Contract", "Proposal", "JWO"] },
    { category: "Hospitality", requestTypes: ["Quote", "Contract", "Proposal", "JWO"] },
  ],

  formConditions: [
    // === HOSPITALITY: QUOTE ===
    { type: "Quote", service: "Kitchen Cleaning & Hood Vent", hide: [18,13,14,17,20,21,22,23,24] },

    // === HOSPITALITY: CONTRACT ===
    { type: "Contract", service: "Kitchen Cleaning & Hood Vent", hide: [12,17,18,20,23,25] },
    { type: "Contract", service: "Staff", hide: [12,13,17,18,19,22,23,25] },

    // === HOSPITALITY: PROPOSAL ===
    { type: "Proposal", service: "Kitchen Cleaning & Hood Vent", hide: [12,17,18,20,21,22,23,24,25,29] },
    { type: "Proposal", service: "Staff", hide: [12,13,16,17,18,19,21,22,23,24,25,28] },

    // === HOSPITALITY: KITCHEN & HOODVENT JWO ===
    { type: "JWO", service: "Kitchen Cleaning & Hood Vent", hide: [12,15,17,18,20,25,29] },

    // === JANITORIAL: CONTRACT (comodín) ===
    { type: "Contract", service: "*", hide: [] },

    // === JANITORIAL: PROPOSAL ===
    { type: "Proposal", service: "Staff",   hide: [18,19,21,22,23,24,25,29] },
    { type: "Proposal", service: "Package", hide: [18,19,20] },

    // === JANITORIAL: JWO — TODOS LOS SERVICIOS ===
    { type: "JWO", service: "Restaurant", hide: [15,17,19,20] },
    { type: "JWO", service: "Schools and Universities", hide: [15,17,19,20] },
    { type: "JWO", service: "Corporate Offices", hide: [15,17,19,20] },
    { type: "JWO", service: "Airports", hide: [15,17,19,20] },
    { type: "JWO", service: "Churches", hide: [15,17,19,20] },
    { type: "JWO", service: "Stadiums and Sports Arenas", hide: [15,17,19,20] },
    { type: "JWO", service: "Warehouses and Industrial Facilities", hide: [15,17,19,20] },

    // === JANITORIAL: Contract — TODOS LOS SERVICIOS ===
    { type: "Contract", service: "Restaurant", hide: [17,19,20] },
    { type: "Contract", service: "Schools and Universities", hide: [17,19,20] },
    { type: "Contract", service: "Corporate Offices", hide: [17,19,20] },
    { type: "Contract", service: "Airports", hide: [17,19,20] },
    { type: "Contract", service: "Churches", hide: [17,19,20] },
    { type: "Contract", service: "Stadiums and Sports Arenas", hide: [17,19,20] },
    { type: "Contract", service: "Warehouses and Industrial Facilities", hide: [17,19,20] },

    // === JANITORIAL: Proposal — TODOS LOS SERVICIOS ===
    { type: "Proposal", service: "Restaurant", hide: [17,19,20] },
    { type: "Proposal", service: "Schools and Universities", hide: [17,19,20] },
    { type: "Proposal", service: "Corporate Offices", hide: [17,19,20] },
    { type: "Proposalt", service: "Airports", hide: [17,19,20] },
    { type: "Proposal", service: "Churches", hide: [17,19,20] },
    { type: "Proposal", service: "Stadiums and Sports Arenas", hide: [17,19,20] },
    { type: "Proposal", service: "Warehouses and Industrial Facilities", hide: [17,19,20] },
  ],
};

// ===========================================================
// 🔹 UTILIDADES
// ===========================================================

function normalize(str) {
  return (str || "").replace(/&amp;/g, "&").replace(/\s+/g, " ").trim().toLowerCase();
}

// Wrapper functions for inline onchange handlers
function updateOptions() {
  updateRequestTypeOptions();
  updateRequestedServiceOptions();
  toggleSiteVisitField();
  evaluateRules();
}

function updateScopeOfWork() {
  evaluateRules();
}

function findSectionEl(num) {
  return (
    document.getElementById(`q${num}`) ||
    document.querySelector(`[data-question="${num}"]`) ||
    document.getElementById(`section${num}`) ||
    document.getElementById(`label${num}`)?.parentElement ||
    document.getElementById(`input${num}`)?.parentElement
  );
}

function hideField(num) {
  const section = findSectionEl(num);
  if (section) {
    section.style.display = "none";
    section.querySelectorAll("input, select, textarea").forEach(el => {
      if (el.type === "checkbox" || el.type === "radio") el.checked = false;
      else el.value = "";
    });
  }
}

function showField(num) {
  const section = findSectionEl(num);
  if (section) section.style.display = "block";
}

// ===========================================================
// 🔹 P1 → P2 (Service Type → Request Type)
// ===========================================================
function updateRequestTypeOptions() {
  const st = document.getElementById("Service_Type")?.value;
  const rtEl = document.getElementById("Request_Type");
  const rsEl = document.getElementById("Requested_Service");

  if (!st || !rtEl || !rsEl) return;

  rtEl.innerHTML = '<option value="">-- Select an option --</option>';
  rsEl.innerHTML = '<option value="">-- Select an option --</option>';

  const cfg = formConfig.requestTypeOptions.find(c => c.category === st);
  if (!cfg) return;

  cfg.requestTypes.forEach(rt => {
    const opt = document.createElement("option");
    opt.value = rt;
    opt.textContent = rt;
    rtEl.appendChild(opt);
  });
}

// ===========================================================
// 🔹 P2 → P4 (Request Type → Requested Service)
// ===========================================================
function updateRequestedServiceOptions() {
  const st = document.getElementById("Service_Type")?.value;
  const rt = document.getElementById("Request_Type")?.value;
  const rs = document.getElementById("Requested_Service");

  if (!st || !rt || !rs) return;

  rs.innerHTML = '<option value="">-- Select an option --</option>';

  let services = [];

  if (st === "Hospitality") {

    if (rt === "Quote" || rt === "JWO") {
      services = ["Kitchen Cleaning & Hood Vent"];
    } else if (rt === "Contract" || rt === "Proposal") {
      services = ["Kitchen Cleaning & Hood Vent", "Staff"];
    }

} else if (st === "Janitorial") {

  // Todas las opciones (Contract, JWO, Proposal) mostrarán lo mismo
  services = [
    "Restaurant",
    "Schools and Universities",
    "Corporate Offices",
    "Airports",
    "Churches",
    "Stadiums and Sports Arenas",
    "Warehouses and Industrial Facilities"
  ];
}

services.forEach(s => {
  const opt = document.createElement("option");
  opt.value = s;
  opt.textContent = s;
  rs.appendChild(opt);
});
}


// ===========================================================
// 🔹 P12 Site Visit Toggle
// ===========================================================
function toggleSiteVisitField() {
  const reqType = document.getElementById("Request_Type")?.value;
  const label = document.getElementById("siteVisitLabel");
  const select = document.getElementById("siteVisitSelect");

  if (!label || !select) return;

  if (reqType === "Quote") {
    label.style.display = "block";
    select.style.display = "block";
  } else {
    label.style.display = "none";
    select.style.display = "none";
    select.value = "";
  }
}

// ===========================================================
// 🔹 Motor principal de reglas
// ===========================================================
function evaluateRules() {
  const reqType = normalize(document.getElementById("Request_Type")?.value);
  const reqService = normalize(document.getElementById("Requested_Service")?.value);

  for (let i = 12; i <= 29; i++) showField(i);

  let match = formConfig.formConditions.find(r =>
    normalize(r.type) === reqType && normalize(r.service) === reqService
  );

  if (!match) {
    match = formConfig.formConditions.find(r =>
      normalize(r.type) === reqType && (r.service === "*" || !r.service)
    );
  }

  if (match?.hide?.length) match.hide.forEach(h => hideField(h));

  if (reqType === "quote" || reqType === "kitchen & hoodvent jwo") hideField(15);
}

// ===========================================================
// 🔹 Inicialización
// ===========================================================
document.addEventListener("DOMContentLoaded", () => {
  const st = document.getElementById("Service_Type");
  const rt = document.getElementById("Request_Type");
  const rs = document.getElementById("Requested_Service");

  if (st)
    st.addEventListener("change", () => {
      updateRequestTypeOptions();
      updateRequestedServiceOptions();
      toggleSiteVisitField();
      evaluateRules();
    });

  if (rt)
    rt.addEventListener("change", () => {
      updateRequestedServiceOptions();
      toggleSiteVisitField();
      evaluateRules();
    });

  if (rs)
    rs.addEventListener("change", () => evaluateRules());

  updateRequestTypeOptions();
  updateRequestedServiceOptions();
  toggleSiteVisitField();
  evaluateRules();
});
</script>
