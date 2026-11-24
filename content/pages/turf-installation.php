<?php
/**
 * Turf installation hub. Rewritten for the install SEO/AEO plan (Phase 1,
 * "FILE: /turf-installation"). Copy is the owner-supplied spec text; do not
 * rewrite it for flow. Unconfirmed facts come from config/site-facts.php
 * via fact(); links to pages from later phases go through maybe_link() so
 * nothing here is a dead link before those pages ship.
 *
 * The old "Installation Work by Our Team" gallery was removed on purpose:
 * the owner confirmed (Nov 24) there's no finished-install portfolio of our
 * own yet, so the page must not present photos as our past installs. See
 * docs/audit-findings.md "Install SEO Phase 1".
 */
require_once __DIR__ . '/../../includes/facts.php';
require_once __DIR__ . '/../../includes/faq.php';
require_once __DIR__ . '/../../includes/links.php';

$low = fact_raw('price_low_per_sqft');
$high = fact_raw('price_high_per_sqft');
$range = '$' . $low . ' to $' . $high;
$installCities = [
    'Royse City' => '/turf-installation/royse-city',
    'Rockwall' => '/turf-installation/rockwall',
    'Heath' => '/turf-installation/heath',
    'Fate' => '/turf-installation/fate',
    'Forney' => '/turf-installation/forney',
    'Wylie' => '/turf-installation/wylie',
    'McKinney' => '/turf-installation/mckinney',
    'Sachse' => '/turf-installation/sachse',
];
?>
<img src="/assets/images/turf-installation-2.webp" srcset="/assets/images/turf-installation-2-800w.webp 800w, /assets/images/turf-installation-2.webp 1200w" sizes="100vw" alt="Artificial turf front yard with a brick walkway" loading="eager" fetchpriority="high" width="1200" height="900"><h1>Artificial Turf Installation in Dallas-Fort Worth</h1>

<div class="install-intro">
    <div class="install-intro__answer">
        <p class="lead"><strong>Artificial turf installation in DFW runs about <?= $range ?> per square foot installed.</strong> Where you land in that range depends on the turf you pick, the type of installation, and what your yard needs underneath. A 1,000 square foot backyard usually comes in between <?= fact_price_for(1000, 'low') ?> and <?= fact_price_for(1000, 'high') ?>.</p>
    </div>
    <div class="install-intro__form">
        <?php require __DIR__ . '/../../includes/install-quote-form.php'; ?>
    </div>
</div>

<h2>What we install</h2>
<p>Backyard lawns, side yards, dog runs, front yards where the HOA allows it, putting greens and play areas. Most of our installs in this area are backyard lawn replacements and pet areas for homes in the East DFW cluster: Royse City, Rockwall, Heath, Fate, Forney, Wylie, McKinney and Sachse. We'll look at other DFW addresses too.</p>
<?php
$serviceTypes = [
    '/turf-installation/backyard-lawn-replacement' => 'Backyard lawn replacement',
    '/turf-installation/pet-turf' => 'Pet turf',
    '/turf-installation/putting-greens' => 'Backyard putting greens',
];
$liveServiceTypes = array_filter($serviceTypes, fn($p) => route_exists($p), ARRAY_FILTER_USE_KEY);
if ($liveServiceTypes): ?>
<ul>
    <?php foreach ($liveServiceTypes as $path => $label): ?>
    <li><a href="<?= htmlspecialchars($path) ?>"><?= htmlspecialchars($label) ?></a></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>

<h2>What it costs</h2>
<p>We publish our range because you should know before you call. Installed pricing is roughly <?= $range ?> per square foot. Three things move it:</p>
<ol>
    <li><strong>The turf you pick.</strong> Blade height, density, backing and the type of fiber change the price per square foot. Pet turf, putting green turf and a thick landscape turf do not cost the same.</li>
    <li><strong>The type of installation.</strong> A flat, open yard with easy access is the low end. Tight gates, slopes, curves, lots of edging and small awkward sections push it up.</li>
    <li><strong>What's under the turf.</strong> Removing old grass or rock, fixing drainage and bringing in base material is where most price differences between quotes come from.</li>
</ol>
<?php if (route_exists('/turf-installation/cost') || route_exists('/turf-installation/cost-calculator')): ?>
<p>See the full breakdown with examples on the <?= maybe_link('/turf-installation/cost', 'cost page') ?>, or use the <?= maybe_link('/turf-installation/cost-calculator', 'cost calculator') ?>.</p>
<?php endif; ?>

<h2>How we install turf</h2>
<p>Turf looks great or looks bad in three years depending on what happens before the turf goes down.</p>
<ol>
    <li><strong>Measure and plan.</strong> We walk the yard, measure it, check drainage and access, and talk through how you'll use it.</li>
    <li><strong>Remove what's there.</strong> Existing grass, roots and debris come out. We dig to the depth the base needs.</li>
    <li><strong>Build the base.</strong> <?= fact('base_spec') ?>. It is compacted in layers and graded so water moves off the surface. In North Texas clay, this step matters more than anything else.</li>
    <li><strong>Weed barrier and turf.</strong> The turf is laid, cut to fit and seamed so the seams don't show.</li>
    <li><strong>Secure and edge.</strong> Edges are fastened down, so the turf doesn't lift or shift.</li>
    <li><strong>Infill and brush.</strong> <?= fact('infill_default') ?>. The fibers are brushed so they stand up.</li>
    <li><strong>Final walk-through.</strong> We go over it with you before we leave.</li>
</ol>
<p>Typical timeline: <?= fact('timeline_typical') ?>.</p>
<?php if (route_exists('/guides/turf-installation-process-base-prep')): ?>
<p>More detail in our guide to <?= maybe_link('/guides/turf-installation-process-base-prep', 'turf base prep') ?>.</p>
<?php endif; ?>

<h2>Why homeowners ask us about installs</h2>
<p><?= fact('repair_experience_line') ?> Seams that open up, turf that holds water, a yard that smells after the dog goes out: most of it traces back to something that was skipped on day one. We plan the install so those problems don't show up later.</p>

<h2>Where we install</h2>
<p>We install turf across the East DFW cluster and will quote other addresses in DFW. Pick your city for local details on soil, HOAs and pricing:</p>
<p class="city-list"><?= implode(' &middot; ', array_map(fn($city, $path) => maybe_link($path, $city), array_keys($installCities), $installCities)) ?></p>
<?php if (route_exists('/turf-installation/areas')): ?>
<p>Not on the list? <?= maybe_link('/turf-installation/areas', 'See the full areas page') ?>.</p>
<?php endif; ?>

<h2>Frequently asked questions</h2>
<?php
render_faq([
    ['How much does artificial turf installation cost in DFW?',
        '<p>About ' . $range . ' per square foot installed. A 500 square foot yard is roughly ' . fact_price_for(500, 'low') . ' to ' . fact_price_for(500, 'high')
        . ', a 1,000 square foot yard ' . fact_price_for(1000, 'low') . ' to ' . fact_price_for(1000, 'high')
        . ', and a 1,500 square foot yard ' . fact_price_for(1500, 'low') . ' to ' . fact_price_for(1500, 'high')
        . '. Your exact price depends on the turf, the installation type and the site.</p>'],
    ['How long does turf installation take?',
        '<p>' . ucfirst(fact('timeline_typical')) . '. Rain can push the schedule, because the base needs to be dry to compact properly.</p>'],
    ["What's included in the price?",
        '<p>Our installed price covers ' . fact('scope_included') . '. Your written quote lists each item so you can compare it to other quotes line by line.</p>'],
    ['Does artificial turf get hot in Texas summers?',
        '<p>Yes. Turf in direct sun gets hotter than natural grass and hotter than the air around it. Shade, lighter-colored turf and cooling infill options help, and a quick rinse cools the surface for a while.'
        . (route_exists('/guides/does-artificial-turf-get-hot-in-texas') ? ' Read more in our ' . maybe_link('/guides/does-artificial-turf-get-hot-in-texas', 'guide to turf and heat') . '.' : '') . '</p>'],
    ['Is turf okay for dogs?',
        '<p>Yes, if the turf and base are built for it. Pet turf needs good drainage, a turf backing that lets liquid through, and an infill plan for odor.'
        . (route_exists('/turf-installation/pet-turf') ? ' See our ' . maybe_link('/turf-installation/pet-turf', 'pet turf page') . '.' : '') . '</p>'],
    ['Do I need HOA approval?',
        '<p>Often, yes. Many DFW HOAs require architectural approval before you replace a lawn with turf, and some restrict front yards. Check your HOA rules first. We can provide product samples and a project description for your submission.'
        . (route_exists('/guides/hoa-approval-artificial-turf-dfw') ? ' More in our ' . maybe_link('/guides/hoa-approval-artificial-turf-dfw', 'HOA guide') . '.' : '') . '</p>'],
    ['How does turf handle Texas clay soil and drainage?',
        '<p>The base handles it. Clay holds water and moves with the weather, so we excavate, build a compacted base and grade it so water drains off the surface instead of pooling.</p>'],
    fact_ready('warranty_text') ? ['What warranty do you offer?', '<p>' . fact('warranty_text') . '</p>'] : null,
    ['How do I maintain artificial turf?',
        '<p>Rinse it, brush it now and then, remove debris, and clean it if pets use it. Turf that is never cleaned can hold odor and bacteria. We also <a href="/">clean</a> and <a href="/turf-repair">repair</a> turf across DFW if you need help later.</p>'],
    ['How do I get a quote?',
        '<p><a href="' . htmlspecialchars(fact_raw('estimate_url')) . '">Request a free quote</a> with your address and a rough size. We\'ll come back with a real price range.</p>'],
]);
?>
