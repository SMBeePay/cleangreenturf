<?php
/**
 * /turf-installation/cost (install SEO plan, Phase 2, "FILE:
 * /turf-installation/cost"). Spec copy; every dollar figure is computed
 * from config/site-facts.php so a price change is a one-line edit.
 */
require __DIR__ . '/../../includes/install-page.php';
$sizes = [
    300 => '300 sq ft (small patio or dog run)',
    500 => '500 sq ft',
    750 => '750 sq ft',
    1000 => '1,000 sq ft',
    1500 => '1,500 sq ft',
    2000 => '2,000 sq ft',
];
?>
<h1>How Much Does Artificial Turf Cost in DFW?</h1>

<p><strong>Artificial turf installation in DFW costs about <?= $priceRange ?> per square foot installed.</strong> That's the range we quote. Your number lands inside it based on three things: the turf you pick, the type of installation, and the site.</p>
<?php render_cta($route); ?>

<h2>Price by yard size</h2>
<div class="table-wrap">
<table class="price-table">
    <thead><tr><th>Yard size</th><th>Low ($<?= $priceLow ?>/sq ft)</th><th>High ($<?= $priceHigh ?>/sq ft)</th></tr></thead>
    <tbody>
    <?php foreach ($sizes as $sqft => $label): ?>
        <tr><td><?= $label ?></td><td><?= fact_price_for($sqft, 'low') ?></td><td><?= fact_price_for($sqft, 'high') ?></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<p>These are installed prices, not material-only. Very small jobs can run above the range because the setup cost doesn't shrink with the yard. <?= fact('min_job_sqft') ?></p>

<h2>What moves your price</h2>
<h3>1. The turf selected</h3>
<p>Blade height, density, face weight, backing and fiber type all change the price per square foot. Pet turf is built to drain and resist odor. Putting green turf is short and dense. A thick landscape turf costs more than a basic one. Higher-end turf usually looks more natural and holds up longer, but you don't need the top tier for every yard.</p>

<h3>2. The type of installation</h3>
<p>A flat, open backyard with a wide gate and a simple shape sits at the low end. These push you up:</p>
<ul>
    <li>Tight access (a narrow gate means hauling material by hand)</li>
    <li>Slopes and drainage corrections</li>
    <li>Curves, planting beds and lots of edging</li>
    <li>Multiple small sections instead of one open area</li>
    <li>Heavy-use areas that need a deeper base</li>
</ul>

<h3>3. What's under the turf</h3>
<p>Removing sod, rock or old turf, hauling it off, fixing grade, and bringing in base material is real labor. It's also where one quote can look cheaper than another. A low quote often has a thin base.<?php if (route_exists('/guides/turf-installation-process-base-prep')): ?> See <?= maybe_link('/guides/turf-installation-process-base-prep', 'what base prep actually involves') ?>.<?php endif; ?></p>

<h2>What a good quote includes</h2>
<p>Our installed price covers <?= fact('scope_included') ?>. When you compare quotes, ask each installer for the same list and check:</p>
<ul>
    <li>How deep is the base, and what is it made of?</li>
    <li>Is there a weed barrier?</li>
    <li>How are the seams joined?</li>
    <li>What infill is used, and how much?</li>
    <li>Who hauls off the old material?</li>
    <li>What is the warranty, and who honors it?<?= fact_ready('warranty_text') ? ' ' . fact('warranty_text') : '' ?></li>
</ul>

<h2>Turf cost vs. sod in North Texas</h2>
<p>Sod is cheaper on the day you install it. Turf costs more up front and then removes mowing, watering and reseeding from your year. Whether the math works for you depends on your water bill, how much you spend on lawn care and how long you'll stay in the house.<?php if (route_exists('/guides/artificial-turf-vs-sod-north-texas')): ?> We walk through it in <?= maybe_link('/guides/artificial-turf-vs-sod-north-texas', 'artificial turf vs. sod in North Texas') ?>.<?php endif; ?></p>

<h2>Get your number</h2>
<p>The fastest way is to <?= route_exists('/turf-installation/cost-calculator') ? maybe_link('/turf-installation/cost-calculator', 'use the cost calculator') . ' or ' : '' ?><?= quote_link() ?>. Send your address and a rough size. We'll tell you where in the <?= $priceRange ?> range your yard is likely to land and why.</p>

<h2>Frequently asked questions</h2>
<?php
render_faq([
    ['How much does a 1,000 square foot turf lawn cost in Texas?',
        '<p>About ' . fact_price_for(1000, 'low') . ' to ' . fact_price_for(1000, 'high') . ' installed with us. Turf choice and site conditions decide where you fall.</p>'],
    ['Is the cheapest quote the best deal?',
        '<p>Not usually. The biggest cost difference between quotes is the base and the prep. A thin base saves money on day one and shows up as dips, pooling and seams that open later.</p>'],
    ['Do you charge per square foot or per project?',
        '<p>We quote by project. We use per-square-foot pricing to explain it. Your quote is itemized.</p>'],
    ['Can I get turf cheaper by doing part of it myself?',
        '<p>Some homeowners remove their own sod to save labor cost. Ask us before you start, because depth and grade matter for what comes next.</p>'],
    ['Do prices change by season?',
        '<p>Turf can be installed year-round in DFW. Wet weather can delay base work. Ask us about current scheduling.</p>'],
]);
?>
