<!-- ======================================= -->
<!-- 📋 Section 7: Scope of Work (Dynamic) -->
<!-- ======================================= -->

<!-- 28️⃣ Scope Of Work -->
<div class="question-block" id="q28">

  <label for="Scope_Of_Work" class="question-label">
    <?= ($lang=='en')
        ? "28. Scope of Work (select applicable tasks)"
        : "28. Alcance del Trabajo (seleccione las tareas aplicables)"; ?>
  </label>

  <!-- Dynamic container -->
  <div id="scopeOfWorkContainer" class="checkbox-group" style="display:none;"></div>
</div>

<script>
// =======================================
// 📹 TRANSLATION WRAPPER
// =======================================
function t(en, es) {
  return <?= ($lang=='en') ? "en" : "es" ?> === 'en' ? en : es;
}

// =======================================
// 📹 OPTIONS MAP (Translated)
// =======================================
const scopeOfWorkOptions = {

  "Schools and Universities": [
    t("Classrooms and Lecture Halls", "Aulas y Salones de Clase"),
    t("Restrooms", "Baños"),
    t("Offices and Administrative Areas", "Oficinas y Áreas Administrativas"),
    t("Common Areas and Hallways", "Áreas Comunes y Pasillos"),
    t("Cafeterias, Dining Halls, and Kitchens", "Cafeterías, Comedores y Cocinas"),
    t("Gymnasiums, Auditoriums, and Sports Facilities", "Gimnasios, Auditorios y Áreas Deportivas"),
    t("Libraries and Study Areas", "Bibliotecas y Áreas de Estudio"),
    t("Laboratories and Specialized Areas", "Laboratorios y Áreas Especializadas"),
    t("Exterior Cleaning and Grounds", "Limpieza Exterior y Terrenos"),
    t("Floors and Carpets", "Pisos y Alfombras"),
    t("Waste and Recycling Areas", "Áreas de Basura y Reciclaje")
  ],

  "Corporate Offices": [
    t("Workstations and Offices", "Oficinas y Estaciones de Trabajo"),
    t("Conference Rooms and Meeting Spaces", "Salas de Conferencias y Reuniones"),
    t("Reception and Lobby Areas", "Recepción y Lobby"),
    t("Restrooms", "Baños"),
    t("Break Rooms and Office Kitchens", "Salas de Descanso y Cocinas de Oficina"),
    t("Common Areas and Hallways", "Áreas Comunes y Pasillos"),
    t("Floors and Carpets", "Pisos y Alfombras"),
    t("Waste and Recycling Rooms", "Cuartos de Basura y Reciclaje"),
    t("Exterior Entryways and Sidewalks", "Entradas Exteriores y Banquetas")
  ],

  "Airports": [
    t("Terminals and Gate Areas", "Terminales y Áreas de Puertas"),
    t("Security Checkpoints", "Puntos de Seguridad"),
    t("Baggage Claim Areas", "Áreas de Reclamo de Equipaje"),
    t("Restrooms", "Baños"),
    t("Lounges and VIP Areas", "Salas VIP"),
    t("Food Courts and Concessions", "Áreas de Comida y Concesiones"),
    t("Circulation Spaces and Concourses", "Áreas de Circulación y Pasillos"),
    t("Operational Zones and Handling Areas", "Zonas Operativas y de Manejo"),
    t("Exterior Drop-off and Pick-up Zones", "Zonas de Ascenso/Descenso Exteriores"),
    t("Hangars and Maintenance Areas", "Hangares y Áreas de Mantenimiento")
  ],

  "Churches": [
    t("Sanctuary and Worship Areas", "Santuario y Áreas de Culto"),
    t("Classrooms and Meeting Rooms", "Aulas y Salas de Reunión"),
    t("Restrooms", "Baños"),
    t("Fellowship Halls and Event Spaces", "Salones de Eventos"),
    t("Kitchens", "Cocinas"),
    t("Offices and Administrative Areas", "Oficinas y Áreas Administrativas"),
    t("Common Areas and Hallways", "Áreas Comunes y Pasillos"),
    t("Exterior Entryways and Grounds", "Entradas Exteriores y Terrenos")
  ],

  "Stadiums and Sports Arenas": [
    t("Seating Areas and Stands", "Zonas de Asientos y Gradas"),
    t("Food Service Areas", "Áreas de Comida"),
    t("Restrooms", "Baños"),
    t("VIP Lounges and Hospitality Spaces", "Áreas VIP y Hospitalidad"),
    t("Locker Rooms and Player Facilities", "Vestidores y Áreas de Jugadores"),
    t("Corridors and Common Areas", "Pasillos y Áreas Comunes"),
    t("Exterior and Parking Areas", "Zonas Exteriores y Estacionamientos"),
    t("Waste and Recycling Zones", "Zonas de Basura y Reciclaje")
  ],

  "Warehouses and Industrial Facilities": [
    t("Warehouse Floors and Storage Areas", "Pisos y Áreas de Almacenaje"),
    t("Loading Docks and Receiving Zones", "Muelles de Carga y Zonas de Recepción"),
    t("Restrooms", "Baños")
  ],

  "Kitchen Cleaning": [
    t("Food Preparation Surfaces", "Superficies de Preparación de Alimentos"),
    t("Equipment Cleaning", "Limpieza de Equipos"),
    t("Sinks and Dishwashing Areas", "Fregaderos y Áreas de Lavado"),
    t("Kitchen Floors", "Pisos de Cocina"),
    t("Storage Areas", "Áreas de Almacenaje"),
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
    t("Food Preparation Surfaces", "Superficies de Preparación"),
    t("Equipment Cleaning", "Limpieza de Equipos"),
    t("Dishwashing Areas", "Áreas de Lavado"),
    t("Kitchen Floors", "Pisos de Cocina"),
    t("Hood and Canopy", "Campana y Cubierta"),
    t("Filters", "Filtros"),
    t("Ductwork", "Ductos"),
    t("Exhaust Fans", "Extractores"),
    t("Surrounding Areas", "Áreas Circundantes")
  ]
};

// =======================================
// 📹 MAIN FUNCTION
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
      scopeContainer.style.gap = "12px";

      scopeOfWorkOptions[selectedService].forEach(item => {
        const label = document.createElement("label");
        label.style.display = "flex";
        label.style.alignItems = "center";
        label.style.gap = "10px";
        label.style.padding = "12px 14px";
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
</script>