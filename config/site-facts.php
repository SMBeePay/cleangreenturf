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

    // Decision D1 (Andrew, Nov 24): McKinney. The Google Business Profile is
    // tied to the McKinney address, so NAP stays as config/business-info.php has it.
    'base_city' => ['value' => 'McKinney, TX', 'confirmed' => true],

    'scope_included' => ['value' => 'excavation of existing material, compacted base, weed barrier, turf, seaming, infill, edging and haul-off', 'confirmed' => false],
    'timeline_typical' => ['value' => '2 to 4 working days for a typical backyard, depending on size and weather', 'confirmed' => true],
    'base_spec' => ['value' => 'A crushed-rock base, typically 3 inches deep', 'confirmed' => true],
    'infill_default' => ['value' => 'Silica sand, with an antimicrobial option for pets', 'confirmed' => false],
    'repair_experience_line' => ['value' => 'We clean and repair artificial turf across DFW, so we see what fails years after an install.', 'confirmed' => false],

    // Placeholders: no default text. Blocks using these stay hidden until filled in.
    // Turf comes from TurfHub (Carrollton). Their warranty terms aren't
    // published anywhere findable, so this says only what's certainly true;
    // no length, no workmanship promise. Replace with the real terms.
    'warranty_text' => ['value' => 'The turf carries its manufacturer\'s warranty, which varies by product. We give you the written warranty terms for the turf you choose along with your quote.', 'confirmed' => false, 'needed' => 'Exact manufacturer warranty (from TurfHub) and any workmanship warranty'],
    'min_job_sqft' => ['value' => "There's no minimum job size.", 'confirmed' => true],
    'turf_brands' => ['value' => null, 'confirmed' => false, 'needed' => 'Turf brands we install'],
    'license_insurance' => ['value' => null, 'confirmed' => false, 'needed' => 'Insurance / license line'],
];
