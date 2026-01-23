<script>
// ===============================================
// ⚙️ scripts_operativo.php
// Section 3: Operational / Service Details
// ===============================================

// 🔹 Mostrar u ocultar la pregunta 12 (Site Visit Conducted)
// Solo se muestra si Request_Type = "Quote"
function toggleSiteVisitField() {
  const requestType = document.getElementById("Request_Type")?.value;
  const siteVisitLabel = document.getElementById("siteVisitLabel");
  const siteVisitSelect = document.getElementById("siteVisitSelect");

  if (!siteVisitLabel || !siteVisitSelect) return;

  if (requestType === "Quote") {
    siteVisitLabel.style.display = "block";
    siteVisitSelect.style.display = "block";
  } else {
    siteVisitLabel.style.display = "none";
    siteVisitSelect.style.display = "none";
    siteVisitSelect.value = ""; // limpiar valor si se oculta
  }
}


// ===============================================
// 🔹 Función para asegurar que siempre haya listener activo
// ===============================================
function bindSiteVisitListener() {
  const requestType = document.getElementById("Request_Type");

  if (requestType) {
    // Elimina listeners duplicados si el select fue regenerado
    requestType.removeEventListener("change", toggleSiteVisitField);
    requestType.addEventListener("change", toggleSiteVisitField);
  }
}


// ===============================================
// 🔹 EVENTOS INICIALES
// ===============================================
document.addEventListener("DOMContentLoaded", () => {
  // Ejecutar al cargar la página
  toggleSiteVisitField();
  bindSiteVisitListener();

  // 🔸 Reforzar el listener cuando se actualicen opciones dinámicas
  const serviceType = document.getElementById("Service_Type");
  if (serviceType) {
    serviceType.addEventListener("change", () => {
      // Espera un momento para que se regeneren los selects de Request_Type
      setTimeout(() => {
        bindSiteVisitListener();
        toggleSiteVisitField();
      }, 300);
    });
  }
});
</script>
