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
$schemaGraph = [];

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

$schemaGraph[] = [
    '@type' => 'LocalBusiness',
    'name' => $businessInfo['name'] . ' — ' . $tx['label'],
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
    'areaServed' => array_map(fn($c) => $c . ', TX', $businessInfo['service_areas']['dfw']),
    'parentOrganization' => ['@id' => $businessInfo['domain'] . '/#organization'],
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

$jsonLd = [
    '@context' => 'https://schema.org',
    '@graph' => $schemaGraph,
];
?>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES) ?></script>
