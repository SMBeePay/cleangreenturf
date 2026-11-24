<?php
/**
 * Shared setup for the turf-installation content pages (install SEO plan):
 * facts, FAQ, forward links and the [CTA] block, plus a few values every
 * page uses. Content files `require` this at the top.
 */
require_once __DIR__ . '/facts.php';
require_once __DIR__ . '/faq.php';
require_once __DIR__ . '/links.php';
require_once __DIR__ . '/page-cta.php';

$priceLow = fact_raw('price_low_per_sqft');
$priceHigh = fact_raw('price_high_per_sqft');
$priceRange = '$' . $priceLow . ' to $' . $priceHigh;
$estimateUrl = htmlspecialchars((string)fact_raw('estimate_url'));
$installCities = require __DIR__ . '/../config/install-cities.php';

/** "Request a free quote" link to the estimate page (spec's standard inline link). */
function quote_link(string $text = 'request a free quote'): string {
    return '<a href="' . htmlspecialchars((string)fact_raw('estimate_url')) . '">' . htmlspecialchars($text) . '</a>';
}
