<?php
/**
 * UNIVERSAL SERVICE REPORT CONFIGURATION
 * ========================================
 * Single source of truth for all service type definitions.
 * Each service type defines its own:
 *   - title (dynamic document title)
 *   - scope_of_work (System / Area Serviced checkboxes)
 *   - initial_condition (Inspection checklist with Yes/No/N/A/Comment)
 *   - initial_condition_header (sub-header label for the inspection section)
 *   - service_performed (Service Performed checkboxes)
 *   - service_performed_header (section header override if needed)
 *   - post_service_condition (Post-Service Condition checkboxes)
 *   - post_service_header (section header override if needed)
 *   - technical_data (Technical Data fields with type: text or number)
 *
 * IMPORTANT: No "Before / After photos taken" items are included.
 * Photo Documentation is NOT part of this report.
 */

return [

    // =========================================================================
    // 1. KITCHEN EXHAUST (HOOD VENT) CLEANING SERVICE
    // =========================================================================
    'kitchen_exhaust_cleaning' => [
        'title' => 'Kitchen Exhaust Cleaning Service',
        'scope_of_work' => [
            'Main Hood',
            'Secondary Hood',
            'Extraction Ducts',
            'Exhaust Fan',
            'Roof Fan Base',
            'Grease Containment Area',
            'Other: __________________',
        ],
        'initial_condition_header' => 'BEFORE SERVICE',
        'initial_condition' => [
            'Grease accumulation visible in hood',
            'Grease buildup in ductwork',
            'Filters saturated with grease',
            'Exhaust fan operating properly',
            'Grease accumulation on roof area',
            'Grease containment system present',
            'Visible structural damage',
            'Unsafe conditions detected',
        ],
        'service_performed_header' => 'SERVICE PERFORMED',
        'service_performed' => [
            'Complete hood cleaning',
            'Filter removal and cleaning',
            'Duct cleaning (accessible areas)',
            'Exhaust fan cleaning',
            'Roof fan base cleaning',
            'Grease containment area cleaned',
            'Degreaser applied',
            'Service sticker placed',
            'Other: __________________',
        ],
        'post_service_header' => 'POST-SERVICE CONDITION',
        'post_service_condition' => [
            'System clean and free of visible grease',
            'Exhaust fan operational at completion',
            'Roof area cleaned',
            'No excessive grease remaining',
            'Work area delivered clean',
            'Client informed of final status',
        ],
        'technical_data' => [
            ['label' => 'Number of Hoods', 'type' => 'number'],
            ['label' => 'Number of Fans', 'type' => 'number'],
            ['label' => 'Fan Type', 'type' => 'text'],
            ['label' => 'Duct Type', 'type' => 'text'],
            ['label' => 'Additional Observations', 'type' => 'text'],
        ],
    ],

    // =========================================================================
    // 2. ROOF & EXTERIOR SERVICE
    // =========================================================================
    'roof_exterior' => [
        'title' => 'Roof & Exterior Service',
        'scope_of_work' => [
            'Roof Surface',
            'Roof Around Exhaust Fans',
            'Grease Containment Area',
            'Roof Drains',
            'Exterior Walls',
            'Loading / Service Area',
            'Parking Area',
            'Other: __________________',
        ],
        'initial_condition_header' => 'BEFORE SERVICE',
        'initial_condition' => [
            'Debris accumulation present',
            'Grease buildup observed',
            'Roof drains obstructed',
            'Standing water present',
            'Roof membrane damage observed',
            'Sealant deterioration observed',
            'Exterior surface staining',
            'Unsafe conditions detected',
        ],
        'service_performed_header' => 'SERVICE PERFORMED',
        'service_performed' => [
            'Debris removal',
            'Grease removal',
            'Drain cleaning / clearing',
            'Surface washing performed',
            'Degreaser applied',
            'Minor sealing performed',
            'Other: __________________',
        ],
        'post_service_header' => 'POST-SERVICE CONDITION',
        'post_service_condition' => [
            'Roof area cleaned',
            'Drains functioning properly',
            'No standing water observed',
            'No loose debris remaining',
            'Exterior surfaces cleaned',
            'Area safe at completion',
            'Client informed of final status',
        ],
        'technical_data' => [
            ['label' => 'Roof Type', 'type' => 'text'],
            ['label' => 'Area Serviced', 'type' => 'text'],
            ['label' => 'Access Method', 'type' => 'text'],
            ['label' => 'Additional Observations', 'type' => 'text'],
        ],
    ],

    // =========================================================================
    // 3. KITCHEN DEEP CLEANING SERVICE
    // =========================================================================
    'kitchen_deep_cleaning' => [
        'title' => 'Kitchen Deep Cleaning Service',
        'scope_of_work' => [
            'Cooking Line',
            'Prep Area',
            'Fryers',
            'Grills / Ovens',
            'Floors',
            'Walls',
            'Ceilings',
            'Storage Area',
            'Other: __________________',
        ],
        'initial_condition_header' => 'BEFORE SERVICE',
        'initial_condition' => [
            'Heavy grease accumulation present',
            'Food debris buildup observed',
            'Floor contamination present',
            'Equipment exterior heavily soiled',
            'Wall / ceiling residue present',
            'Drainage issues observed',
            'Unsafe or unsanitary conditions detected',
        ],
        'service_performed_header' => 'SERVICE PERFORMED',
        'service_performed' => [
            'Deep degreasing of cooking line',
            'Fryer exterior cleaning',
            'Grill / oven cleaning',
            'Floor scrubbing and degreasing',
            'Wall cleaning',
            'Ceiling cleaning',
            'Drain cleaning',
            'Equipment detailing',
            'Degreaser / chemical applied',
            'Other: __________________',
        ],
        'post_service_header' => 'POST-SERVICE CONDITION',
        'post_service_condition' => [
            'Kitchen area thoroughly cleaned',
            'Equipment exterior free of grease',
            'Floors clean and degreased',
            'No visible residue remaining',
            'Area sanitized and safe',
            'Client informed of final status',
        ],
        'technical_data' => [
            ['label' => 'Area Size', 'type' => 'text'],
            ['label' => 'Number of Equipment Units', 'type' => 'number'],
            ['label' => 'Chemicals Used', 'type' => 'text'],
            ['label' => 'Additional Observations', 'type' => 'text'],
        ],
    ],

    // =========================================================================
    // 4. EQUIPMENT CLEANING SERVICE
    // =========================================================================
    'equipment_cleaning' => [
        'title' => 'Equipment Cleaning Service',
        'scope_of_work' => [
            'HVAC Unit',
            'Exhaust Fan',
            'Air Handling Unit',
            'Refrigeration Equipment',
            'Mechanical Room Equipment',
            'Industrial Equipment',
            'Other: __________________',
        ],
        'initial_condition_header' => 'BEFORE SERVICE',
        'initial_condition' => [
            'Dirt / dust accumulation present',
            'Grease buildup observed',
            'Coil contamination present',
            'Airflow obstruction observed',
            'Corrosion detected',
            'Loose components observed',
            'Unsafe conditions detected',
        ],
        'service_performed_header' => 'SERVICE PERFORMED',
        'service_performed' => [
            'Exterior surface cleaning',
            'Interior component cleaning',
            'Coil cleaning',
            'Fan blade cleaning',
            'Filter replacement / cleaning',
            'Debris removal',
            'Degreaser / chemical applied',
            'Minor adjustments performed',
            'Other: __________________',
        ],
        'post_service_header' => 'POST-SERVICE CONDITION',
        'post_service_condition' => [
            'Equipment cleaned',
            'Airflow improved',
            'No visible residue remaining',
            'Equipment operational at completion',
            'Area delivered clean',
            'Client informed of final status',
        ],
        'technical_data' => [
            ['label' => 'Equipment Type', 'type' => 'text'],
            ['label' => 'Model / Serial Number', 'type' => 'text'],
            ['label' => 'Location', 'type' => 'text'],
            ['label' => 'Additional Observations', 'type' => 'text'],
        ],
    ],

    // =========================================================================
    // 5. PREVENTIVE MAINTENANCE SERVICE
    // =========================================================================
    'preventive_maintenance' => [
        'title' => 'Preventive Maintenance Service',
        'scope_of_work' => [
            'Kitchen Exhaust System',
            'Roof Area',
            'HVAC Equipment',
            'Mechanical Room',
            'Exterior Area',
            'Other: __________________',
        ],
        'initial_condition_header' => 'BEFORE SERVICE',
        'initial_condition' => [
            'System operating properly',
            'Minor accumulation present',
            'Components securely fastened',
            'No visible leaks detected',
            'No abnormal noise observed',
            'No visible structural damage',
            'Safe operating conditions confirmed',
        ],
        'service_performed_header' => 'SERVICE PERFORMED',
        'service_performed' => [
            'Routine inspection completed',
            'Preventive cleaning performed',
            'Minor adjustments completed',
            'Tightening of accessible components',
            'Drain inspection / clearing',
            'Functional testing performed',
            'Service label updated',
            'Other: __________________',
        ],
        'post_service_header' => 'POST-SERVICE CONDITION',
        'post_service_condition' => [
            'System operating within normal parameters',
            'No immediate corrective action required',
            'Area delivered clean',
            'Equipment functioning at completion',
            'Client informed of findings',
        ],
        'technical_data' => [
            ['label' => 'Maintenance Frequency', 'type' => 'text'],
            ['label' => 'Service Interval', 'type' => 'text'],
            ['label' => 'Next Recommended Service Date', 'type' => 'text'],
            ['label' => 'Additional Observations', 'type' => 'text'],
        ],
    ],

    // =========================================================================
    // 6. REPAIR & CORRECTIONS SERVICE
    // =========================================================================
    'repair_corrections' => [
        'title' => 'Repair & Corrections Service',
        'scope_of_work' => [
            'Kitchen Exhaust System',
            'Roof Area',
            'Exhaust Fan',
            'Electrical Components',
            'Grease Containment System',
            'Mechanical Equipment',
            'Other: __________________',
        ],
        'initial_condition_header' => 'BEFORE REPAIR',
        'initial_condition' => [
            'Equipment malfunction observed',
            'Electrical issue detected',
            'Component damaged',
            'Loose or deteriorated parts',
            'Sealant failure observed',
            'Excessive vibration or noise',
            'Unsafe condition identified',
        ],
        'service_performed_header' => 'REPAIR / CORRECTION PERFORMED',
        'service_performed' => [
            'Component replaced',
            'Electrical repair performed',
            'Sealant / silicone applied',
            'Switch replaced',
            'Grease containment replaced',
            'Mechanical adjustment completed',
            'Functional testing performed',
            'Other: __________________',
        ],
        'post_service_header' => 'POST-REPAIR CONDITION',
        'post_service_condition' => [
            'Equipment operational',
            'Issue resolved',
            'System functioning properly',
            'Area delivered clean',
            'No visible defects remaining',
            'Client informed of repair status',
        ],
        'technical_data' => [
            ['label' => 'Parts Replaced', 'type' => 'text'],
            ['label' => 'Quantity', 'type' => 'number'],
            ['label' => 'Model / Specification', 'type' => 'text'],
            ['label' => 'Additional Observations', 'type' => 'text'],
        ],
    ],

    // =========================================================================
    // 7. EMERGENCY SERVICE
    // =========================================================================
    'emergency_service' => [
        'title' => 'Emergency Service',
        'scope_of_work' => [
            'Kitchen Exhaust System',
            'Roof Area',
            'HVAC Equipment',
            'Electrical Component',
            'Drainage System',
            'Exterior Area',
            'Other: __________________',
        ],
        'initial_condition_header' => 'AT TIME OF ARRIVAL',
        'initial_condition' => [
            'Active leak observed',
            'System not operational',
            'Electrical failure detected',
            'Excessive grease accumulation',
            'Blocked drainage',
            'Structural damage observed',
            'Unsafe condition present',
        ],
        'service_performed_header' => 'SERVICE PERFORMED',
        'service_performed' => [
            'Emergency cleaning performed',
            'Temporary repair completed',
            'Component replaced',
            'Electrical correction performed',
            'Drain cleared',
            'Containment installed',
            'Functional testing performed',
            'Other: __________________',
        ],
        'post_service_header' => 'POST-SERVICE CONDITION',
        'post_service_condition' => [
            'Immediate issue contained',
            'System operational',
            'Area secured',
            'Temporary repair completed',
            'Further repair recommended',
            'Client informed of emergency status',
        ],
        'technical_data' => [
            ['label' => 'Cause of Emergency', 'type' => 'text'],
            ['label' => 'Parts Used', 'type' => 'text'],
            ['label' => 'Temporary or Permanent Repair', 'type' => 'text'],
            ['label' => 'Additional Observations', 'type' => 'text'],
        ],
    ],

];
