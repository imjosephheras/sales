// ============================================================
// services_catalog.js — Catalogo centralizado de servicios
// Cada servicio tiene un nombre y su Scope of Work predefinido
// ============================================================

const servicesCatalog = [

  {
    id: "hood_cleaning_basic",
    name: "Hood Cleaning (Basic)",
    category: "Hood & Ventilation",
    scope: [
      "Perform hood cleaning to remove grease, residue, and accumulated contaminants from accessible surfaces.",
      "Clean accessible hood surfaces and visible components.",
      "Clean accessible exhaust ductwork (reachable/visible areas).",
      "Remove existing grease filters, clean them, inspect/diagnose to determine if replacement is needed, and reinstall properly (or replace when applicable).",
      "Clean the grease box (where present) and secure/reinstall properly.",
      "Replace fan pillow block bearings (\"pillow blocks\"), only when the HoodVent unit is equipped with this adaptation (where applicable).",
      "Inspect hinges on access doors/panels (where applicable) and report visible conditions.",
      "Visually inspect the electrical system and the fan (visible wiring/connections) and report any observed conditions.",
      "Use appropriate degreasing products and methods for the surfaces being cleaned.",
      "Follow all required safety procedures during the performance of the service.",
      "Coordinate access and work conditions with the Client prior to service.",
      "Perform a final inspection upon completion to confirm the system has been serviced.",
      "Leave the work area clean, safe, and ready for normal operations upon completion of the service."
    ]
  }

];

// Index for quick lookup by id
const servicesCatalogById = {};
servicesCatalog.forEach(svc => { servicesCatalogById[svc.id] = svc; });

// Index for quick lookup by name
const servicesCatalogByName = {};
servicesCatalog.forEach(svc => { servicesCatalogByName[svc.name] = svc; });

// Get unique categories
function getServiceCategories() {
  const cats = [];
  const seen = {};
  servicesCatalog.forEach(svc => {
    if (!seen[svc.category]) {
      seen[svc.category] = true;
      cats.push(svc.category);
    }
  });
  return cats;
}
