<?php
/**
 * Regenerates the static /sitemap.xml from config/routes.php. Run this
 * whenever a route is added or removed:
 *
 *   php bin/generate-sitemap.php
 *
 * Only canonical, indexable routes go in (nothing noindex, no duplicates,
 * no parameter URLs) — see docs/migration-requirements.md #11.
 */
declare(strict_types=1);

$businessInfo = require __DIR__ . '/../config/business-info.php';
$routes = require __DIR__ . '/../config/routes.php';

$today = date('Y-m-d');
$xml = new XMLWriter();
$xml->openMemory();
$xml->startDocument('1.0', 'UTF-8');
$xml->startElement('urlset');
$xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

foreach ($routes as $path => $route) {
    if (($route['robots'] ?? '') === 'noindex, follow' || ($route['robots'] ?? '') === 'noindex') {
        continue;
    }
    $xml->startElement('url');
    $xml->writeElement('loc', $businessInfo['domain'] . $path);
    $xml->writeElement('lastmod', $today);
    $xml->endElement();
}

$xml->endElement();
$xml->endDocument();

file_put_contents(__DIR__ . '/../sitemap.xml', $xml->outputMemory());
echo "sitemap.xml written with " . count($routes) . " URLs\n";
