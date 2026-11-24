<?php
/** /turf-installation/backyard-lawn-replacement (install SEO plan, Phase 2). Spec copy. */
require __DIR__ . '/../../includes/install-page.php';
?>
<h1>Backyard Artificial Turf Installation in DFW</h1>

<p><strong>Replacing a backyard lawn with artificial turf in DFW costs roughly <?= $priceRange ?> per square foot installed.</strong> Most backyards are done in <?= fact('timeline_typical') ?>.</p>
<?php render_cta($route); ?>

<h2>Who this is for</h2>
<p>Homeowners who are tired of mowing, watering and patching a yard that never quite works. New-build homes where the builder left bare dirt. Families with kids and dogs who want something usable all year. People who don't want to pay for lawn care.</p>

<h2>Who it isn't for</h2>
<p>If your yard is shaded all day, you want a yard that feels cool underfoot in July, or you love gardening in the soil, tell us. We'll say so if turf isn't the right call for part of the yard, and we'll talk about mixing turf with beds.</p>

<h2>What the job looks like</h2>
<ol>
    <li>We measure and check access, slope and drainage.</li>
    <li>Old grass and material are removed.</li>
    <li><?= fact('base_spec') ?>, goes down and is compacted.</li>
    <li>Weed barrier, turf, seams and edging.</li>
    <li>Infill and brushing. <?= fact('infill_default') ?>.</li>
    <li>Walk-through.</li>
</ol>

<h2>What changes your price</h2>
<p>The turf you pick, the type of installation, and what's under the lawn. Details and examples are on the <?= maybe_link('/turf-installation/cost', 'cost page') ?>.</p>

<h2>Common backyard questions</h2>
<?php
render_faq([
    ['Will turf drain in North Texas clay?',
        "<p>Yes, if the base is built for it. Clay doesn't drain on its own, so the base and the grade do the work.</p>"],
    ['Can I keep trees and beds?',
        '<p>Usually, yes. We cut around trunks and bed edges and set the base to protect roots. Tell us what you want to keep.</p>'],
    ['What about the HOA?',
        '<p>Check the rules before you sign.' . (route_exists('/guides/hoa-approval-artificial-turf-dfw') ? ' See our ' . maybe_link('/guides/hoa-approval-artificial-turf-dfw', 'HOA guide') . '.' : '') . '</p>'],
    ['Does turf get hot?',
        "<p>It does in direct sun. Shade, turf color and infill choices affect it." . (route_exists('/guides/does-artificial-turf-get-hot-in-texas') ? ' See ' . maybe_link('/guides/does-artificial-turf-get-hot-in-texas', 'turf and heat in Texas') . '.' : '') . '</p>'],
    ['How do I get started?',
        '<p>' . ucfirst(quote_link()) . '.</p>'],
]);
