<?php
/**
 * JSON-LD structured data. Preserves the schema.org @type already present on
 * each live page (see $route['schema_types'], sourced from the pre-migration
 * crawl) and adds an Organization + LocalBusiness graph sitewide — additive
 * only, per docs/audit-findings.md finding #7 (the live site had no
 * LocalBusiness schema at all). Only a TX LocalBusiness entry is emitted —
 * the CA LocalBusiness entry was removed since the business no longer
 * services California; see docs/audit-findings.md "California service
 * discontinued" for the full list of what changed.
 */
require_once __DIR__ . '/breadcrumbs.php';
$schemaGraph = [];

// The page's visible H1 (set by the template, rendered before <head>),
// falling back to the title tag without the brand suffix.
// A few migrated H1s carry a " | Clean Green Turf" suffix; drop it here only.
$pageHeading = $heroH1 !== null
    ? trim(html_entity_decode(strip_tags($heroH1), ENT_QUOTES | ENT_HTML5, 'UTF-8'))
    : $route['title'];
$pageHeading = trim(preg_replace('/\s*\|\s*' . preg_quote($businessInfo['name'], '/') . '\s*$/', '', $pageHeading));

$tx = $businessInfo['regions']['tx'];

$schemaGraph[] = [
    '@type' => 'Organization',
    '@id' => $businessInfo['domain'] . '/#organization',
    'name' => $businessInfo['name'],
    'url' => $businessInfo['domain'],
    'logo' => $businessInfo['domain'] . '/assets/images/logo.png',
    'email' => $businessInfo['email'],
    'sameAs' => array_values($businessInfo['social']),
];

// HomeAndConstructionBusiness is a LocalBusiness subtype. One stable @id
// (#business) so every page's Service node can point at the same entity.
// Name is the exact business name (NAP consistency, spec T-03/T-04).
// areaServed = the existing DFW cleaning list plus the install cluster.
$areaServed = array_values(array_unique(array_merge(
    $businessInfo['service_areas']['dfw'],
    $businessInfo['service_areas']['install'] ?? []
)));
$schemaGraph[] = [
    '@type' => 'HomeAndConstructionBusiness',
    '@id' => $businessInfo['domain'] . '/#business',
    'name' => $businessInfo['name'],
    'url' => $businessInfo['domain'] . '/',
    'description' => 'Artificial turf installation, cleaning and repair in Dallas-Fort Worth.',
    'image' => $businessInfo['domain'] . '/assets/images/logo.png',
    'sameAs' => array_values($businessInfo['social']),
    'telephone' => $tx['phone_display'],
    'email' => $businessInfo['email'],
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => $tx['address']['street'],
        'addressLocality' => $tx['address']['city'],
        'addressRegion' => $tx['address']['state'],
        'postalCode' => $tx['address']['zip'],
        'addressCountry' => 'US',
    ],
    'areaServed' => array_map(fn($c) => ['@type' => 'City', 'name' => $c . ', TX'], $areaServed),
    'parentOrganization' => ['@id' => $businessInfo['domain'] . '/#organization'],
    'hasOfferCatalog' => [
        '@type' => 'OfferCatalog',
        'name' => 'Turf Services',
        'itemListElement' => [
            [
                '@type' => 'Offer',
                'itemOffered' => [
                    '@type' => 'Service',
                    'name' => 'Artificial Turf Cleaning',
                    'description' => 'Deep debris extraction, pet-safe odor elimination, and infill replenishment for artificial turf.',
                ],
            ],
            [
                '@type' => 'Offer',
                'itemOffered' => [
                    '@type' => 'Service',
                    'name' => 'Artificial Turf Installation',
                    'description' => 'Full artificial turf installation for backyards, side yards, pet runs, and putting greens.',
                ],
            ],
            [
                '@type' => 'Offer',
                'itemOffered' => [
                    '@type' => 'Service',
                    'name' => 'Artificial Turf Repair',
                    'description' => 'Seam and edge repair, patching sunken or lumpy areas, and sun-damaged fiber replacement.',
                ],
            ],
        ],
    ],
];

$schemaTypes = $route['schema_types'] ?? ['WebPage'];
foreach ($schemaTypes as $type) {
    if ($type === 'WebSite') {
        $schemaGraph[] = [
            '@type' => 'WebSite',
            'name' => $businessInfo['name'],
            'url' => $businessInfo['domain'],
            'description' => $route['description'] ?? '',
        ];
    } elseif ($type === 'Article') {
        $schemaGraph[] = [
            '@type' => 'Article',
            'headline' => $route['title'],
            'description' => $route['description'] ?? '',
            'url' => $canonicalUrl,
            'datePublished' => $route['date_published'] ?? null,
            'author' => ['@type' => 'Organization', 'name' => $businessInfo['name']],
            'publisher' => ['@id' => $businessInfo['domain'] . '/#organization'],
        ];
    } elseif ($type === 'WebPage') {
        $schemaGraph[] = [
            '@type' => 'WebPage',
            'name' => $route['title'],
            'description' => $route['description'] ?? '',
            'url' => $canonicalUrl,
        ];
    }
}

// Service (install pages): route 'service_schema' => ['area' => 'Dallas-Fort Worth'].
// Price range comes from config/site-facts.php, never hard-coded.
if (!empty($route['service_schema'])) {
    $schemaGraph[] = [
        '@type' => 'Service',
        'serviceType' => 'Artificial turf installation',
        'name' => $pageHeading,
        'description' => $route['description'] ?? '',
        'url' => $canonicalUrl,
        'provider' => ['@id' => $businessInfo['domain'] . '/#business'],
        'areaServed' => ['@type' => $route['service_schema']['area_type'] ?? 'AdministrativeArea', 'name' => $route['service_schema']['area']],
        'offers' => [
            '@type' => 'Offer',
            'priceSpecification' => [
                '@type' => 'UnitPriceSpecification',
                'priceCurrency' => 'USD',
                'minPrice' => fact_raw('price_low_per_sqft'),
                'maxPrice' => fact_raw('price_high_per_sqft'),
                'unitText' => 'SQFT',
            ],
        ],
    ];
}

// FAQPage: built from the visible FAQ block (includes/faq.php), word for word.
if (!empty($GLOBALS['pageFaq'])) {
    $schemaGraph[] = [
        '@type' => 'FAQPage',
        'url' => $canonicalUrl,
        'mainEntity' => array_map(fn($f) => [
            '@type' => 'Question',
            'name' => $f['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
        ], $GLOBALS['pageFaq']),
    ];
}

// BreadcrumbList on every page below the homepage (and not on the 404).
if ($currentPath !== '/' && ($route['template'] ?? '') !== '404') {
    $crumbs = page_breadcrumbs($route, $currentPath, $pageHeading);
    $schemaGraph[] = [
        '@type' => 'BreadcrumbList',
        'itemListElement' => array_map(fn($i, $c) => [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $c[0],
            'item' => $businessInfo['domain'] . $c[1],
        ], array_keys($crumbs), $crumbs),
    ];
}

$jsonLd = [
    '@context' => 'https://schema.org',
    '@graph' => $schemaGraph,
];
?>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES) ?></script>
