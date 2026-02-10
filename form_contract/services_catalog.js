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
  },

  {
    id: "hood_cleaning_detailed",
    name: "Hood Cleaning (Detailed)",
    category: "Hood & Ventilation",
    scope: [
      "Perform detailed hood cleaning to remove grease, residue, and accumulated contaminants, requiring additional manual cleaning (\"elbow grease\") to meet standards.",
      "Clean accessible hood surfaces and visible components, including hard-to-remove grease buildup on accessible areas.",
      "Clean accessible exhaust ductwork, including grease buildup in reachable areas requiring additional manual cleaning.",
      "Remove existing grease filters, clean them thoroughly, inspect/diagnose to determine if replacement is needed, and reinstall properly (or replace when applicable).",
      "Clean grease troughs/grease cups (where present) and reinstall properly.",
      "Clean the grease box (where present) thoroughly and secure/reinstall properly.",
      "Replace fan pillow block bearings (\"pillow blocks\"), only when the HoodVent unit is equipped with this adaptation (where applicable).",
      "Inspect hinges on access doors/panels (where applicable) and report any observed conditions (wear/damage/looseness).",
      "Visually inspect the electrical system and the fan (wiring, connections, and visible components) and provide observations/recommendations if any abnormal conditions are noted.",
      "Use appropriate degreasing products and methods for the surfaces being cleaned.",
      "Follow all required safety procedures during the performance of the service.",
      "Coordinate access and work conditions with the Client prior to service.",
      "Perform a final inspection upon completion to confirm the hood system has been properly serviced.",
      "Leave the work area clean, safe, and ready for normal operations upon completion of the service."
    ]
  },

  {
    id: "wall_cleaning",
    name: "Wall Cleaning",
    category: "Hood & Ventilation",
    scope: [
      "Perform wall cleaning to remove dust, dirt, grease, stains, and surface contaminants.",
      "Clean accessible wall surfaces in designated areas based on the approved scope.",
      "Degrease wall surfaces where applicable, particularly in kitchen or food service environments.",
      "Remove visible marks and buildup without damaging existing finishes.",
      "Use appropriate cleaning products, chemicals, and methods suitable for the wall surface type.",
      "Follow applicable safety procedures during the performance of the service.",
      "Perform a final visual inspection to confirm proper cleaning.",
      "Leave wall areas clean, safe, and ready for normal operations upon completion of the service."
    ]
  },

  {
    id: "kitchen_equipment_cleaning",
    name: "Kitchen Equipment Cleaning",
    category: "Hood & Ventilation",
    scope: [
      "Perform kitchen equipment cleaning to remove dirt, grease, residue, and accumulated contaminants.",
      "Clean exterior surfaces of kitchen equipment in accordance with the approved scope.",
      "Focus on high-use and high-grease areas without disassembling equipment unless otherwise specified.",
      "Use appropriate and professional-grade cleaning and degreasing products.",
      "Follow applicable safety and sanitation procedures during the performance of the service.",
      "Perform a final inspection to confirm proper equipment cleaning.",
      "Leave kitchen equipment clean, safe, and ready for normal operations upon completion of the service."
    ]
  },

  {
    id: "carpet_cleaning_shampooing",
    name: "Carpet Cleaning & Shampooing",
    category: "Carpet & Upholstery",
    scope: [
      "Perform professional carpet cleaning to remove dirt, stains, and accumulated contaminants.",
      "Apply shampooing/extraction process using equipment and products appropriate for the carpet type.",
      "Treat visible stains as applicable (complete removal of permanent stains is not guaranteed).",
      "Use appropriate and safe cleaning products for the treated surfaces.",
      "Allow adequate drying time prior to normal use of the area.",
      "Leave the area clean, safe, and ready for normal operations upon completion of the service."
    ]
  },

  {
    id: "dining_room_cleaning",
    name: "Dining Room Cleaning",
    category: "Dining & FOH",
    scope: [
      "Perform general cleaning of the dining room area to remove dust, dirt, and debris.",
      "Clean and disinfect tables, chairs, and accessible surfaces.",
      "Clean horizontal surfaces and high-touch points (where applicable).",
      "Vacuum and/or mop floors according to the surface type.",
      "Empty trash receptacles and replace liners as needed.",
      "Use appropriate and safe cleaning products for the treated surfaces.",
      "Leave the area clean, orderly, and ready for normal use upon completion of the service."
    ]
  },

  {
    id: "foh_cleaning",
    name: "FOH (Front of House) Cleaning",
    category: "Dining & FOH",
    scope: [
      "Perform general cleaning of FOH areas to remove dust, dirt, and debris.",
      "Clean and disinfect accessible surfaces, including counters, tables, and furnishings (where applicable).",
      "Clean horizontal surfaces and high-touch points.",
      "Vacuum, sweep, scrub, and mop floors according to the surface type.",
      "Empty trash receptacles and replace liners as needed.",
      "Clean entry doors, handles, and public-facing visible areas.",
      "Use appropriate and safe cleaning products for the treated surfaces.",
      "Leave FOH areas clean, orderly, and ready for normal use upon completion of the service."
    ]
  },

  {
    id: "deep_kitchen_cleaning",
    name: "Deep Kitchen Cleaning",
    category: "Kitchen Cleaning",
    scope: [
      "Perform intensive deep kitchen cleaning to remove heavy grease, dirt, and accumulated contaminants.",
      "Degrease and thoroughly clean accessible surfaces, including work tables, food preparation areas, and food-contact surfaces.",
      "Clean exterior surfaces of kitchen equipment, including ranges, ovens, grills, fryers, refrigerators, hoods, and surrounding areas (where applicable).",
      "Move small and medium equipment (when safe and feasible) to clean underneath and behind.",
      "Clean vertical and horizontal surfaces, including walls, doors, backsplashes, and accessible areas.",
      "Degrease areas with severe grease buildup and adhered residues.",
      "Clean and disinfect sinks, accessible drains, and wash areas.",
      "Sweep, mop, degrease, and disinfect floors according to the surface type.",
      "Empty trash receptacles and replace liners as needed.",
      "Use appropriate, safe, and professional-grade cleaning and degreasing products and methods.",
      "Follow applicable safety and sanitation procedures during the performance of the service.",
      "Perform a final inspection to confirm deep cleaning has been completed.",
      "Leave the kitchen area clean, disinfected, and ready for normal operations upon completion of the service."
    ]
  },

  {
    id: "kitchen_cleaning_basic",
    name: "Kitchen Cleaning (Basic)",
    category: "Kitchen Cleaning",
    scope: [
      "Perform general kitchen cleaning to remove dirt, light grease, and debris.",
      "Clean and disinfect accessible surfaces, including work tables and visible surfaces.",
      "Clean exterior surfaces of kitchen equipment (visible surfaces only).",
      "Clean horizontal surfaces and high-touch points.",
      "Sweep and mop floors according to the surface type.",
      "Empty trash receptacles and replace liners as needed.",
      "Use appropriate and safe cleaning products for the treated surfaces.",
      "Leave the kitchen area clean and ready for normal operations upon completion of the service."
    ]
  },

  {
    id: "kitchen_cleaning_detailed",
    name: "Kitchen Cleaning (Detailed)",
    category: "Kitchen Cleaning",
    scope: [
      "Perform deep kitchen cleaning to remove accumulated grease, dirt, and contaminants.",
      "Clean and disinfect accessible surfaces, including work tables, food preparation areas, and equipment.",
      "Clean exterior surfaces of kitchen equipment, including ranges, ovens, refrigerators, hoods, and surrounding areas (where applicable).",
      "Degrease surfaces with heavy grease buildup.",
      "Clean vertical and horizontal surfaces and high-touch points.",
      "Sweep, mop, and degrease floors according to the surface type.",
      "Empty trash receptacles and replace liners as needed.",
      "Use appropriate cleaning and degreasing products and methods for the treated surfaces.",
      "Follow applicable safety and sanitation procedures during the performance of the service.",
      "Leave the kitchen area clean, safe, and ready for normal operations upon completion of the service."
    ]
  },

  {
    id: "post_construction_kitchen_cleaning_basic",
    name: "Post-Construction Kitchen Cleaning (Basic)",
    category: "Kitchen Cleaning",
    scope: [
      "Perform initial kitchen cleaning following construction or remodeling work.",
      "Remove dust, light construction debris, and loose materials from accessible surfaces.",
      "Clean visible surfaces, including work tables, equipment, and preparation areas.",
      "Clean exterior surfaces of kitchen equipment (visible surfaces only).",
      "Sweep and mop floors to remove construction dust and debris.",
      "Empty trash receptacles and remove cleaning-generated waste (does not include removal of heavy construction debris).",
      "Use appropriate cleaning products and methods for new or recently installed surfaces.",
      "Leave the kitchen area clean and ready for initial use or normal operations."
    ]
  },

  {
    id: "post_construction_kitchen_cleaning_detailed",
    name: "Post-Construction Kitchen Cleaning (Detailed)",
    category: "Kitchen Cleaning",
    scope: [
      "Perform deep kitchen cleaning following construction or remodeling work.",
      "Remove construction dust, debris, light adhesives, and accumulated contaminants.",
      "Clean and disinfect accessible surfaces, including work tables, food preparation areas, and equipment.",
      "Clean exterior surfaces of kitchen equipment, including ranges, ovens, refrigerators, hoods, and surrounding areas (where applicable).",
      "Clean vertical and horizontal surfaces, including walls, doors, and accessible areas.",
      "Degrease and clean surfaces with fine dust buildup or adhered residues.",
      "Sweep, mop, and disinfect floors according to the surface type.",
      "Empty trash receptacles and remove cleaning-generated waste (does not include removal of heavy construction debris).",
      "Use appropriate and safe cleaning products and methods for new or recently installed surfaces.",
      "Follow applicable safety and sanitation procedures during the performance of the service.",
      "Leave the kitchen area clean, safe, and ready for use or normal operations."
    ]
  },

  {
    id: "kitchen_floor_cleaning_basic",
    name: "Kitchen Floor Cleaning (Basic)",
    category: "Kitchen Cleaning",
    scope: [
      "Perform basic kitchen floor cleaning to remove dirt, debris, and light grease.",
      "Sweep and mop floors according to the surface type.",
      "Clean accessible areas around fixed equipment.",
      "Use appropriate and safe cleaning products for kitchen floors.",
      "Leave floors clean and ready for normal use upon completion of the service."
    ]
  },

  {
    id: "kitchen_floor_cleaning_detailed",
    name: "Kitchen Floor Cleaning (Detailed)",
    category: "Kitchen Cleaning",
    scope: [
      "Perform deep kitchen floor cleaning to remove heavy grease, dirt, and accumulated contaminants.",
      "Sweep, mop, degrease, and disinfect floors according to the surface type.",
      "Clean grout lines, edges, and hard-to-reach areas.",
      "Clean accessible areas under and around fixed equipment (where applicable).",
      "Clean accessible floor drains (surface level only).",
      "Use professional-grade cleaning and degreasing products and methods.",
      "Follow applicable safety and sanitation procedures during the performance of the service.",
      "Leave kitchen floors clean, safe, and ready for normal operations upon completion of the service."
    ]
  },

  {
    id: "bar_cleaning_basic",
    name: "Bar Cleaning (Basic)",
    category: "Bar Cleaning",
    scope: [
      "Perform general bar cleaning to remove dirt, debris, and light spills.",
      "Clean and disinfect accessible surfaces, including bar tops, counters, and visible surfaces.",
      "Clean exterior surfaces of bar equipment (visible surfaces only).",
      "Clean horizontal surfaces and high-touch points.",
      "Sweep and mop floors according to the surface type.",
      "Empty trash receptacles and replace liners as needed.",
      "Use appropriate and safe cleaning products for the treated surfaces.",
      "Leave the bar area clean and ready for normal use upon completion of the service."
    ]
  },

  {
    id: "bar_cleaning_detailed",
    name: "Bar Cleaning (Detailed)",
    category: "Bar Cleaning",
    scope: [
      "Perform deep bar cleaning to remove dirt, debris, spills, and buildup of grease or sugar.",
      "Clean and disinfect accessible surfaces, including bar tops, counters, service stations, and preparation areas.",
      "Clean exterior surfaces of bar equipment, including refrigerators, ice machines (exterior), beverage stations, and surrounding areas (where applicable).",
      "Clean vertical and horizontal surfaces and high-touch points.",
      "Degrease and clean areas with heavy residue buildup.",
      "Sweep, mop, and/or degrease floors according to the surface type.",
      "Empty trash receptacles and replace liners as needed.",
      "Use appropriate and safe cleaning products and methods for the treated surfaces.",
      "Follow applicable safety and sanitation procedures during the performance of the service.",
      "Leave the bar area clean, safe, and ready for normal use upon completion of the service."
    ]
  },

  {
    id: "restroom_cleaning_basic",
    name: "Restroom Cleaning (Basic)",
    category: "Restroom Cleaning",
    scope: [
      "Perform general restroom cleaning to remove visible dirt and debris.",
      "Clean and disinfect toilets, urinals, and sinks.",
      "Clean accessible surfaces, including mirrors, counters, and visible fixtures.",
      "Clean and disinfect high-touch points (handles, dispensers, switches).",
      "Sweep and mop floors.",
      "Empty trash receptacles and replace liners as needed.",
      "Restock basic supplies (toilet paper, soap, towels) when applicable and provided by the client.",
      "Use appropriate and safe cleaning and disinfecting products.",
      "Leave restrooms clean and ready for normal use upon completion of the service."
    ]
  },

  {
    id: "restroom_cleaning_detailed",
    name: "Restroom Cleaning (Detailed)",
    category: "Restroom Cleaning",
    scope: [
      "Perform deep restroom cleaning to remove accumulated dirt, stains, and contaminants.",
      "Clean, disinfect, and descale toilets, urinals, and sinks.",
      "Clean and disinfect accessible surfaces, including mirrors, counters, partitions, and fixtures.",
      "Clean walls, doors, and accessible vertical areas (where applicable).",
      "Clean and disinfect high-touch points.",
      "Sweep, mop, and disinfect floors.",
      "Empty trash receptacles and replace liners as needed.",
      "Restock basic supplies (toilet paper, soap, towels) when applicable and provided by the client.",
      "Use appropriate cleaning and disinfecting products and methods.",
      "Follow applicable safety and sanitation procedures during the performance of the service.",
      "Leave restrooms clean, disinfected, and ready for normal use upon completion of the service."
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
