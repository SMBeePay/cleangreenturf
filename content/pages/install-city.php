<?php
/**
 * City page template for /turf-installation/{city} (install SEO plan,
 * Phase 3, "CITY PAGE TEMPLATE"). The route sets 'city' => slug; unique
 * copy comes from config/install-cities.php, shared copy lives here.
 *
 * Publishing standard from the spec: no two city pages may overlap more
 * than 60% (checked by `php bin/check-city-overlap.php`), and no "we've
 * installed hundreds of yards in {City}" style claims.
 */
require __DIR__ . '/../../includes/install-page.php';

$citySlug = $route['city'];
$city = $installCities[$citySlug];
$cityName = $city['name'];

/** Resolve {fact:id} and {link:/path|text} markers in city copy. */
$cityText = function (string $text): string {
    $text = htmlspecialchars($text, ENT_QUOTES);
    $text = preg_replace_callback('/\{fact:([a-z_]+)\}/', fn($m) => fact($m[1]), $text);
    return preg_replace_callback('/\{link:([^|}]+)\|([^}]+)\}/', fn($m) => maybe_link(html_entity_decode($m[1]), html_entity_decode($m[2])), $text);
};

$cleaningPage = '/' . str_replace(' ', '-', strtolower($cityName)) . '-tx-turf-cleaning';
?>
<h1>Artificial Turf Installation in <?= htmlspecialchars($cityName) ?>, TX</h1>

<p><strong>Artificial turf installation in <?= htmlspecialchars($cityName) ?> costs about <?= $priceRange ?> per square foot installed.</strong> <?= $cityText($city['opening_line']) ?></p>
<?php render_cta($route); ?>

<h2>Turf installation in <?= htmlspecialchars($cityName) ?>: what to know</h2>
<p><?= $cityText($city['local_conditions']) ?></p>

<h2>HOAs and approvals in <?= htmlspecialchars($cityName) ?></h2>
<p><?= $cityText($city['hoa']) ?></p>

<h2>What it costs in <?= htmlspecialchars($cityName) ?></h2>
<p>Installed pricing is <?= $priceRange ?> per square foot. <?= $cityText($city['cost_note']) ?> See the <?= maybe_link('/turf-installation/cost', 'full cost breakdown') ?> or <?= maybe_link('/turf-installation/cost-calculator', 'use the calculator') ?>.</p>

<h2>How we install</h2>
<ol>
    <li>Measure the yard and check drainage and access.</li>
    <li>Remove the existing grass and dig to depth.</li>
    <li>Build and compact the base, graded to drain.</li>
    <li>Lay, seam and secure the turf.</li>
    <li>Infill, brush and walk through it with you.</li>
</ol>
<p>The full process is on our <a href="/turf-installation">turf installation page</a><?= route_exists('/guides/turf-installation-process-base-prep') ? ', and the base work is explained in ' . maybe_link('/guides/turf-installation-process-base-prep', 'turf base prep') : '' ?>.</p>

<h2>Nearby areas we install</h2>
<p><?= implode(' &middot; ', array_map(fn($slug) => maybe_link('/turf-installation/' . $slug, $installCities[$slug]['name']), $city['neighbors'])) ?> &middot; <?= maybe_link('/turf-installation/areas', 'All areas') ?></p>
<?php if (route_exists($cleaningPage)): ?>
<p>Already have turf? See our <a href="<?= htmlspecialchars($cleaningPage) ?>"><?= htmlspecialchars($cityName) ?> turf cleaning</a> page.</p>
<?php endif; ?>

<h2><?= htmlspecialchars($cityName) ?> turf questions</h2>
<?php
$heatLink = route_exists('/guides/does-artificial-turf-get-hot-in-texas')
    ? ' See ' . maybe_link('/guides/does-artificial-turf-get-hot-in-texas', 'turf and heat in Texas') . '.'
    : '';
render_faq([
    [$city['faq'][0][0], '<p>' . $cityText($city['faq'][0][1]) . '</p>'],
    [$city['faq'][1][0], '<p>' . $cityText($city['faq'][1][1]) . '</p>'],
    ['How much does artificial turf cost per square foot in ' . $cityName . '?',
        '<p>About ' . $priceRange . ' per square foot installed. A 1,000 square foot yard runs about ' . fact_price_for(1000, 'low') . ' to ' . fact_price_for(1000, 'high') . ', depending on the turf, the type of installation and the site.</p>'],
    ['How long does turf installation take?',
        '<p>' . ucfirst(fact('timeline_typical')) . '. Rain can push the schedule, because the base needs to be dry to compact properly.</p>'],
    ['Does artificial turf get hot in ' . $cityName . '?',
        '<p>Yes, in direct sun. Shade, lighter-colored turf and cooling infill options help.' . $heatLink . '</p>'],
]);
