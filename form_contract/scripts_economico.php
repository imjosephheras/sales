<script>
// ===============================================
// 💰 scripts_economico.php
// Section 4: Economic Information Logic
// ===============================================

// 🔹 Control de la pregunta 18 (Hood Vent)
function toggleHoodVentField() {
  const requestType = document.getElementById("Request_Type")?.value || "";
  const requestedService = document.getElementById("Requested_Service")?.value || "";
  const hoodVentLabel = document.getElementById("hoodVentLabel");
  const hoodVentInput = document.getElementById("hoodVentInput");

  if (!hoodVentLabel || !hoodVentInput) return;

  const req = requestType.toLowerCase().trim();
  const serv = requestedService.toLowerCase().trim();

  // Combinaciones que OCULTAN la pregunta 18
  const hideCombinations = [
    { req: "quote", serv: "kitchen cleaning" },
    { req: "contract", serv: "kitchen cleaning" },
    { req: "contract", serv: "staff" },
    { req: "proposal", serv: "kitchen cleaning" },
    { req: "kitchen & hoodvent jwo", serv: "kitchen cleaning" }
  ];

  const shouldHide = hideCombinations.some(
    combo => req === combo.req && serv === combo.serv
  );

  if (shouldHide) {
    hoodVentLabel.style.display = "none";
    hoodVentInput.style.display = "none";
    hoodVentInput.value = "";
  } else {
    hoodVentLabel.style.display = "block";
    hoodVentInput.style.display = "block";
  }
}


// ==================================================
// 🔹 Re-vincular listeners tras regenerar selects
// ==================================================
function bindHoodVentListeners() {
  const requestType = document.getElementById("Request_Type");
  const requestedService = document.getElementById("Requested_Service");

  if (requestType) {
    requestType.removeEventListener("change", toggleHoodVentField);
    requestType.addEventListener("change", toggleHoodVentField);
  }
  if (requestedService) {
    requestedService.removeEventListener("change", toggleHoodVentField);
    requestedService.addEventListener("change", toggleHoodVentField);
  }
}


// ==================================================
// 🔹 Inicialización y sincronización
// ==================================================
document.addEventListener("DOMContentLoaded", () => {
  toggleHoodVentField();
  bindHoodVentListeners();

  // Cada vez que cambia el Service Type se regeneran los selects,
  // por eso re-enlazamos los listeners y re-evaluamos la condición
  const serviceType = document.getElementById("Service_Type");
  if (serviceType) {
    serviceType.addEventListener("change", () => {
      setTimeout(() => {
        bindHoodVentListeners();
        toggleHoodVentField();
      }, 300); // Espera breve para que se reconstruyan los selects
    });
  }
});
</script>
