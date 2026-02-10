// ============================================================
// janitorial_services_catalog.js — Catalogo de Janitorial Services
// Cada servicio tiene un nombre y su Scope of Work predefinido
// ============================================================

const janitorialServicesCatalog = [

  // ===== PAINTING =====
  {
    id: "painting_basic",
    name: "Painting (Basic)",
    category: "Painting",
    scope: [
      "Perform basic painting services to improve the overall appearance of the area.",
      "Prepare surfaces through light cleaning and protection of adjacent areas.",
      "Apply one coat of paint to interior or exterior surfaces as specified.",
      "Use paint and materials appropriate for the surface type.",
      "Remove protective coverings and clean the work area upon completion.",
      "Leave the area clean and presentable upon completion of the service."
    ]
  },

  {
    id: "painting_detailed",
    name: "Painting (Detailed)",
    category: "Painting",
    scope: [
      "Perform full painting services for aesthetic improvement and ongoing maintenance.",
      "Prepare surfaces through cleaning, sanding, minor surface repairs, and protection of adjacent areas.",
      "Apply primer where required.",
      "Apply one or more coats of paint per project specifications.",
      "Paint interior and/or exterior surfaces, including walls, ceilings, trim, and accessible areas.",
      "Use professional-grade paint, equipment, and materials.",
      "Follow applicable safety procedures during the performance of the service.",
      "Perform a final inspection to confirm uniform finish and quality workmanship.",
      "Remove protective coverings and clean the work area upon completion.",
      "Leave the area clean, safe, and ready for normal use upon completion of the service."
    ]
  },

  // ===== FLOOR STRIP & WAX =====
  {
    id: "floor_strip_wax_basic",
    name: "Floor Strip & Wax (Basic)",
    category: "Floor Services",
    scope: [
      "Perform basic floor stripping to remove surface layers of existing wax.",
      "Clean and prepare the floor surface prior to wax application.",
      "Apply basic coats of wax/finish for protection and appearance enhancement.",
      "Use appropriate products and equipment for the floor type.",
      "Allow adequate drying time between processes.",
      "Leave floors clean, protected, and ready for normal use upon completion of the service."
    ]
  },

  {
    id: "floor_strip_wax_detailed",
    name: "Floor Strip & Wax (Detailed)",
    category: "Floor Services",
    scope: [
      "Perform complete floor stripping to fully remove existing wax, dirt, and accumulated residues.",
      "Neutralize and clean the floor surface after the stripping process.",
      "Properly prepare the floor for finish application.",
      "Apply multiple coats of wax/finish to achieve enhanced durability, gloss, and protection.",
      "Burnish or polish floors when applicable to improve the final finish.",
      "Use professional-grade products, chemicals, and equipment appropriate for the floor type.",
      "Follow applicable safety procedures during the performance of the service.",
      "Perform a final inspection to confirm quality, uniformity, and finish.",
      "Leave floors clean, protected, and ready for normal operations upon completion of the service."
    ]
  },

  // ===== FLOOR REPLACEMENT =====
  {
    id: "floor_replacement",
    name: "Floor Replacement",
    category: "Floor Services",
    scope: [
      "Provide floor replacement services in accordance with the client's specific needs.",
      "Evaluate existing floor conditions and the work area prior to beginning the service.",
      "Remove existing flooring as required and prepare the surface for new installation.",
      "Install new flooring based on the selected material and scope approved by the client.",
      "Adjust installation methods according to site conditions and flooring type.",
      "Use appropriate, professional-grade materials, tools, and equipment.",
      "Follow applicable safety procedures during the performance of the service.",
      "Perform a final inspection to confirm proper installation and finish.",
      "Leave the work area clean, safe, and ready for normal use upon completion of the service."
    ]
  },

  // ===== POWER WASHING =====
  {
    id: "power_washing_basic",
    name: "Power Washing (Basic)",
    category: "Power Washing",
    scope: [
      "Perform basic power washing to remove surface dirt, dust, mud, and debris.",
      "Clean accessible exterior surfaces such as sidewalks, walkways, patios, and common areas.",
      "Use appropriate pressure and products to prevent damage to treated surfaces.",
      "Rinse all cleaned areas thoroughly.",
      "Leave surfaces clean and safe upon completion of the service."
    ]
  },

  {
    id: "power_washing_detailed",
    name: "Power Washing (Detailed)",
    category: "Power Washing",
    scope: [
      "Perform deep power washing to remove embedded dirt, stains, mold, mildew, and accumulated contaminants.",
      "Pre-treat surfaces with appropriate cleaning solutions when necessary.",
      "Clean exterior surfaces including sidewalks, parking areas, entrances, walls, fences, and accessible areas (where applicable).",
      "Adjust pressure levels and techniques based on surface type to prevent damage.",
      "Thoroughly rinse and remove loosened debris.",
      "Follow applicable safety and environmental control procedures.",
      "Perform a final inspection to confirm cleanliness and surface condition.",
      "Leave areas clean, safe, and ready for normal use upon completion of the service."
    ]
  },

  // ===== ELECTRICAL SERVICES =====
  {
    id: "electrical_services",
    name: "Electrical Services",
    category: "Electrical",
    scope: [
      "Provide electrical services in accordance with the specific needs of the client.",
      "Perform maintenance, repair, replacement, or installation of electrical components as required.",
      "Evaluate site conditions or electrical systems prior to performing the service.",
      "Execute electrical work based on the scope defined and approved by the client.",
      "Comply with applicable local, state, and federal electrical codes.",
      "Use appropriate and approved materials, equipment, and tools.",
      "Follow electrical safety procedures during the performance of the service.",
      "Perform a final inspection to confirm proper operation of the completed work.",
      "Leave the work area clean, safe, and ready for normal use upon completion of the service."
    ]
  },

  // ===== ESCORT / HOST SUPPORT =====
  {
    id: "escort_host_support",
    name: "Escort / Host Support Services",
    category: "Support Services",
    scope: [
      "Provide escort/host support services during the scheduled service period, as requested by the Client.",
      "Escort assigned personnel to required locations, including sites, meetings, or designated service areas.",
      "Maintain professional conduct and a presentable appearance at all times.",
      "Remain visible and available throughout the duration of the service; escort staff shall not be concealed or out of sight.",
      "Coordinate access, scheduling, and timing with the Client or Facilities representative.",
      "Report directly to Facilities or the designated supervising authority during the service period.",
      "Assist with check-in, entry, and access procedures as required (badges, directions, visitor protocols).",
      "Comply with all site rules, policies, and safety procedures.",
      "Confirm completion of the service with Facilities and promptly report any changes, issues, or incidents.",
      "Services are strictly professional, non-intimate, and limited to escort and host support functions only."
    ]
  },

  // ===== GREASE TRAP CLEANING =====
  {
    id: "grease_trap_emergency",
    name: "Grease Trap Cleaning Emergency Call-Out",
    category: "Grease Trap",
    scope: [
      "Provide emergency grease trap cleaning services during the scheduled call-out period, as requested by the Client.",
      "Respond promptly to grease trap overflows or urgent conditions requiring immediate attention.",
      "Perform initial pumping and cleaning necessary to restore proper operation of the grease trap system.",
      "Coordinate site access and service timing with the Client or Facilities representative.",
      "Maintain professional conduct and comply with all site rules and safety procedures.",
      "Ensure the work area is left clean, safe, and operational upon completion of the service.",
      "Confirm completion of the emergency service with Facilities and report any observed issues or recommendations.",
      "Services are limited to professional emergency grease trap cleaning support only."
    ]
  },

  {
    id: "grease_trap_waste_removal",
    name: "Grease Trap Cleaning Waste Removal",
    category: "Grease Trap",
    scope: [
      "Provide grease waste removal and disposal services as part of scheduled grease trap cleaning operations.",
      "Remove collected grease, solids, and liquid waste from the designated grease trap system.",
      "Transport and dispose of grease waste in accordance with applicable local, state, and federal regulations, as well as site requirements.",
      "Coordinate site access and service timing with the Client or Facilities representative prior to service.",
      "Comply with all applicable safety, environmental, and sanitation procedures during removal and disposal operations.",
      "Confirm proper disposal and completion of the waste removal service with Facilities.",
      "Leave all serviced areas clean, safe, and ready for normal operations upon completion.",
      "Services are limited to grease waste removal and disposal only."
    ]
  },

  // ===== WINDOW CLEANING =====
  {
    id: "window_cleaning_basic",
    name: "Window Cleaning (Basic)",
    category: "Window Services",
    scope: [
      "Perform basic window cleaning to remove dust, dirt, and light stains.",
      "Clean interior and/or exterior glass in accessible areas as specified.",
      "Clean accessible frames and edges.",
      "Use appropriate products and tools for glass surfaces.",
      "Leave windows clean and presentable upon completion of the service."
    ]
  },

  {
    id: "window_cleaning_detailed",
    name: "Window Cleaning (Detailed)",
    category: "Window Services",
    scope: [
      "Perform deep window cleaning to remove embedded dirt, smudges, fingerprints, and accumulated contaminants.",
      "Clean interior and exterior glass, including higher or extended reach areas when accessible and safe.",
      "Clean accessible frames, tracks, and edges.",
      "Remove residues such as light construction dust, splashes, and adhered marks (where applicable).",
      "Use professional-grade cleaning products, equipment, and methods.",
      "Follow applicable safety procedures during the performance of the service.",
      "Perform a final inspection to confirm clarity and quality of the finished work.",
      "Leave windows clean, safe, and ready for presentation or normal use upon completion of the service."
    ]
  },

  // ===== WINDOW TINTING =====
  {
    id: "window_tinting",
    name: "Window Tinting / Window Film Installation",
    category: "Window Services",
    scope: [
      "Provide window tinting or window film installation services in accordance with the client's specific needs.",
      "Evaluate glass surfaces and site conditions prior to installation.",
      "Install window films for solar control, privacy, safety, decorative, or other purposes as requested.",
      "Prepare and clean glass surfaces prior to installation to ensure proper adhesion.",
      "Perform installation based on the scope defined and approved by the client.",
      "Use appropriate, professional-grade films, materials, and tools.",
      "Follow applicable safety procedures during the performance of the service.",
      "Perform a final inspection to confirm proper installation and finish.",
      "Leave the work area clean, safe, and ready for normal use upon completion of the service."
    ]
  },

  // ===== HIGH DUSTING =====
  {
    id: "high_dusting_basic",
    name: "High Dusting of Ceilings (Basic)",
    category: "High Dusting",
    scope: [
      "Perform basic high dusting to remove surface dust and cobwebs from elevated areas and ceilings.",
      "Clean accessible high areas using extension tools (no special access equipment included).",
      "Clean visible surfaces such as beams, vents, pipes, and accessible overhead structures.",
      "Use appropriate equipment and methods to safely perform high-level cleaning.",
      "Leave treated areas clean and safe upon completion of the service."
    ]
  },

  {
    id: "high_dusting_detailed",
    name: "High Dusting of Ceilings (Detailed)",
    category: "High Dusting",
    scope: [
      "Perform deep high dusting to remove accumulated dust, cobwebs, and contaminants from elevated areas and ceilings.",
      "Clean ceilings, beams, pipes, vents, light fixtures, and accessible overhead structures.",
      "Use specialized access equipment such as ladders, lifts, or platforms when applicable.",
      "Adjust cleaning methods based on height and surface type.",
      "Follow all applicable safety procedures for working at heights.",
      "Perform a final inspection to confirm proper dust removal.",
      "Leave treated areas clean, safe, and ready for normal use upon completion of the service."
    ]
  },

  // ===== COMMERCIAL DUCT CLEANING =====
  {
    id: "duct_cleaning_basic",
    name: "Commercial Duct Cleaning (Basic)",
    category: "Duct Cleaning",
    scope: [
      "Perform basic commercial duct cleaning to remove surface dust, dirt, and debris.",
      "Clean accessible sections of the duct system based on the defined scope.",
      "Remove visible dust and contaminant buildup in reachable areas.",
      "Use appropriate equipment and methods for commercial duct cleaning.",
      "Follow applicable safety procedures during the performance of the service.",
      "Leave serviced areas clean and safe upon completion of the service."
    ]
  },

  {
    id: "duct_cleaning_detailed",
    name: "Commercial Duct Cleaning (Detailed)",
    category: "Duct Cleaning",
    scope: [
      "Perform deep commercial duct cleaning to remove accumulated dust, debris, and internal contaminants.",
      "Clean supply and return ducts in accessible areas of the system.",
      "Use specialized equipment for internal duct cleaning (brushing, vacuuming, or other appropriate methods).",
      "Clean accessible grilles, diffusers, and duct system components.",
      "Visually assess system conditions and report observations when applicable.",
      "Follow applicable safety and environmental control procedures.",
      "Perform a final inspection to confirm cleaning has been completed per the approved scope.",
      "Leave work areas clean, safe, and ready for normal operations upon completion of the service."
    ]
  }

];

// Index for quick lookup by id
const janitorialCatalogById = {};
janitorialServicesCatalog.forEach(svc => { janitorialCatalogById[svc.id] = svc; });

// Index for quick lookup by name
const janitorialCatalogByName = {};
janitorialServicesCatalog.forEach(svc => { janitorialCatalogByName[svc.name] = svc; });

// Get unique categories
function getJanitorialCategories() {
  const cats = [];
  const seen = {};
  janitorialServicesCatalog.forEach(svc => {
    if (!seen[svc.category]) {
      seen[svc.category] = true;
      cats.push(svc.category);
    }
  });
  return cats;
}
