<?php
/**
 * Facts register for the turf-installation content (see BUILD_NOTES.md and
 * the install SEO/AEO spec, Section 1). Page copy never hard-codes these:
 * it calls fact('id') / fact_ready('id') from includes/facts.php.
 *
 * value:     the text that renders. null = nothing usable yet (a
 *            placeholder), so any sentence or block that needs it is
 *            hidden rather than printed.
 * confirmed: false = still waiting on Andrew. Renders the default value
 *            where one exists, and is listed by `php bin/check-facts.php`.
 *
 * Phone, address and business name are NOT duplicated here; they stay in
 * config/business-info.php (single source of truth for NAP).
 */
return [
    'price_low_per_sqft' => ['value' => 9, 'confirmed' => true],
    'price_high_per_sqft' => ['value' => 13, 'confirmed' => true],
    'price_drivers' => ['value' => 'turf selected, type of installation, and site conditions', 'confirmed' => true],
    'estimate_url' => ['value' => '/schedule-turf-installation-estimate', 'confirmed' => true],
    'hub_url' => ['value' => '/turf-installation', 'confirmed' => true],

    // Decision D1: website NAP says McKinney, Google profile says Royse City.
    'base_city' => ['value' => 'Royse City, TX', 'confirmed' => false],

    'scope_included' => ['value' => 'excavation of existing material, compacted base, weed barrier, turf, seaming, infill, edging and haul-off', 'confirmed' => false],
    'timeline_typical' => ['value' => '2 to 4 working days for a typical backyard, depending on size and weather', 'confirmed' => false],
    'base_spec' => ['value' => 'A compacted crushed-stone base, typically 3 to 4 inches deep', 'confirmed' => false],
    'infill_default' => ['value' => 'Silica sand, with an antimicrobial option for pets', 'confirmed' => false],
    'repair_experience_line' => ['value' => 'We clean and repair artificial turf across DFW, so we see what fails years after an install.', 'confirmed' => false],

    // Placeholders: no default text. Blocks using these stay hidden until filled in.
    'warranty_text' => ['value' => null, 'confirmed' => false, 'needed' => 'Manufacturer and workmanship warranty terms'],
    'min_job_sqft' => ['value' => null, 'confirmed' => false, 'needed' => 'Minimum job size'],
    'turf_brands' => ['value' => null, 'confirmed' => false, 'needed' => 'Turf brands we install'],
    'license_insurance' => ['value' => null, 'confirmed' => false, 'needed' => 'Insurance / license line'],
];
