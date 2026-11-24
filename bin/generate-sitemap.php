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

// lastmod = the last git commit that touched the page's content file
// (falls back to today for uncommitted/new files). Accurate lastmod is
// what makes search engines trust it (install SEO plan T-10).
function page_lastmod(string $slug): string {
    $file = 'content/pages/' . $slug . '.php';
    $date = trim((string)shell_exec('cd ' . escapeshellarg(__DIR__ . '/..') . ' && git log -1 --format=%cs -- ' . escapeshellarg($file) . ' 2>/dev/null'));
    $dirty = trim((string)shell_exec('cd ' . escapeshellarg(__DIR__ . '/..') . ' && git status --porcelain -- ' . escapeshellarg($file) . ' 2>/dev/null'));
    return ($date === '' || $dirty !== '') ? date('Y-m-d') : $date;
}
$count = 0;
$xml = new XMLWriter();
$xml->openMemory();
$xml->startDocument('1.0', 'UTF-8');
$xml->startElement('urlset');
$xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

foreach ($routes as $path => $route) {
    if (str_starts_with($route['robots'] ?? '', 'noindex')) {
        continue;
    }
    $xml->startElement('url');
    $xml->writeElement('loc', $businessInfo['domain'] . $path);
    $xml->writeElement('lastmod', page_lastmod($route['slug']));
    $count++;
    $xml->endElement();
}

$xml->endElement();
$xml->endDocument();

file_put_contents(__DIR__ . '/../sitemap.xml', $xml->outputMemory());
echo "sitemap.xml written with $count URLs\n";
