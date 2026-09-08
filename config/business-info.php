<?php
/**
 * Single source of truth for business info, contact details, tracking IDs,
 * and service-area lists. Everything else (includes, templates, schema)
 * should read from here instead of hard-coding values.
 *
 * See docs/business-info.md for where these values were captured from.
 */

return [
    'name' => 'Clean Green Turf',
    'domain' => 'https://cleangreenturf.com',
    'email' => 'andrew@cleangreenturf.com',
    'social' => [
        'facebook' => 'https://www.facebook.com/profile.php?id=61551839379497',
        'instagram' => 'https://www.instagram.com/clean_green_turf/',
    ],

    // Regional business units — same brand, two NAP records.
    'regions' => [
        'tx' => [
            'label' => 'Texas / DFW',
            'address' => [
                'street' => '11900 Presario Road',
                'city' => 'McKinney',
                'state' => 'TX',
                'zip' => '75071',
            ],
            'phone_display' => '(469) 796-0034',
            'phone_e164' => '+14697960034',
        ],
        'ca' => [
            'label' => 'California / Bay Area',
            'address' => [
                'street' => '2709 Holly Oak Ct',
                'city' => 'Brentwood',
                'state' => 'CA',
                'zip' => '94513',
            ],
            'phone_display' => '925-238-3178',
            'phone_e164' => '+19252383178',
        ],
    ],

    // Service-area city lists as shown sitewide in the footer.
    'service_areas' => [
        'dfw' => ['Addison', 'Allen', 'Celina', 'Dallas', 'Frisco', 'McKinney', 'Plano', 'Prosper', 'Richardson', 'The Colony', 'Rockwall', 'Royse City'],
        'austin' => ['Austin', 'Round Rock', 'Cedar Park', 'Pflugerville', 'Georgetown', 'San Marcos', 'Kyle', 'Buda', 'Leander', 'Lakeway', 'Hutto'],
        'ca' => ['Antioch', 'Berkeley', 'Brentwood', 'Clayton', 'Concord', 'Danville', 'Discovery Bay', 'Dublin', 'Fremont', 'Livermore', 'Oakland', 'Walnut Creek', 'San Ramon'],
    ],

    // Tracking — preserved exactly from the live site. Do not duplicate
    // elsewhere; includes/tracking.php is the only place these render.
    'tracking' => [
        'ga4_id' => 'G-SWCYCC0DG8',
        'hubspot_portal_id' => '244728809',
    ],
];
