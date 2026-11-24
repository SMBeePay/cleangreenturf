<?php
/**
 * /turf-installation/pet-turf (install SEO plan, Phase 2). Spec copy. The
 * spec's "/artificial-turf-cleaning" link points at the real cleaning
 * service page, which on this site is the homepage.
 */
require __DIR__ . '/../../includes/install-page.php';
?>
<h1>Pet Turf Installation in DFW</h1>

<p><strong>Pet turf installation in DFW runs about <?= $priceRange ?> per square foot installed.</strong> Pet turf works when three things are right: the turf backing lets liquid through, the base drains, and the infill plan deals with odor.</p>
<?php render_cta($route); ?>

<h2>Why pet turf fails</h2>
<p>We clean and repair turf in DFW, and the same pattern shows up. The turf looks fine at install and starts to smell within a season. The reasons are almost always one of these:</p>
<ul>
    <li><strong>A base that holds water.</strong> Liquid soaks through the turf and sits in the base.</li>
    <li><strong>No odor plan.</strong> The wrong infill, or no infill, and no cleaning schedule.</li>
    <li><strong>Turf with a backing that doesn't drain well.</strong> Urine pools on the surface or runs sideways.</li>
    <li><strong>A dog area that gets used as a toilet in one corner.</strong> Concentrated use needs concentrated maintenance.</li>
</ul>

<h2>How we build it to last</h2>
<ol>
    <li><strong>Drainage first.</strong> The base is graded and compacted so liquid moves down and away.</li>
    <li><strong>Pet-rated turf.</strong> Backing and fibers made to drain and clean easily.</li>
    <li><strong>Infill chosen for pets.</strong> <?= fact('infill_default') ?>.</li>
    <li><strong>Seams and edges secured</strong>, so dogs can't dig under the edge.</li>
    <li><strong>A cleaning plan.</strong> We'll tell you how to rinse and treat it. If you'd like us to handle it, we <a href="/">clean turf across DFW</a>.</li>
</ol>

<h2>What it costs</h2>
<p>Pet turf is priced inside our <?= $priceRange ?> range. It tends to be toward the middle or upper part when you add heavy-use areas, a deeper base for drainage or a larger dog run. See the <?= maybe_link('/turf-installation/cost', 'cost page') ?>.</p>
<?php if (route_exists('/guides/best-artificial-turf-for-dogs')): ?>
<p>Choosing turf for your dogs? Read <?= maybe_link('/guides/best-artificial-turf-for-dogs', 'what to look for in the best artificial turf for dogs') ?>.</p>
<?php endif; ?>

<h2>Questions we get from dog owners</h2>
<?php
render_faq([
    ['Does pet turf smell?',
        "<p>It doesn't have to. Smell comes from liquid and bacteria sitting in the base or infill. Good drainage, the right infill and regular rinsing prevent most of it.</p>"],
    ['How do I clean it?',
        '<p>Pick up solids, rinse regularly and use a pet-safe enzyme cleaner when needed. We can do deeper cleaning for you.</p>'],
    ['Will my dog dig it up?',
        "<p>Properly secured turf resists it. Tell us if you have a digger and we'll secure the edges accordingly.</p>"],
    ['Is it safe for dogs?',
        "<p>Ask the manufacturer for the safety information on the turf you choose. We'll share it with you before you decide.</p>"],
    ['Does it get too hot for paws?',
        '<p>Turf in direct sun gets hot in Texas summers. Shade and cooling options help.' . (route_exists('/guides/does-artificial-turf-get-hot-in-texas') ? ' See ' . maybe_link('/guides/does-artificial-turf-get-hot-in-texas', 'turf and heat') . '.' : '') . '</p>'],
]);
