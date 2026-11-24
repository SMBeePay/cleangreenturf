<?php
/**
 * /turf-installation/putting-greens (install SEO plan, Phase 2).
 *
 * Decision D2 (Andrew, Nov 24): putting greens MIGHT cost more than the
 * $9 to $13 range. Per the spec's fallback, this page states no price:
 * the opening line asks for a quote instead, the Service schema has no
 * Offer, and the FAQ points to a quote rather than the cost page.
 */
require __DIR__ . '/../../includes/install-page.php';
?>
<h1>Backyard Putting Green Installation in DFW</h1>

<p><strong>Backyard putting green pricing depends on size, shape and how much contour you want.</strong> Greens are quoted per project. Tell us what you're picturing and we'll give you a real number.</p>
<?php render_cta($route); ?>

<h2>What makes a putting green different</h2>
<p>Putting green turf is shorter and denser than lawn turf, and the base has to be smoother and more precise. A lawn can hide small dips. A green can't.</p>

<h2>What affects the price</h2>
<ul>
    <li><strong>Size and shape.</strong> Larger greens cost more total but less per square foot. Curved edges add labor.</li>
    <li><strong>Contour.</strong> Built-up slopes and breaks need more base work.</li>
    <li><strong>Turf.</strong> Green-specific turf for speed and ball roll.</li>
    <li><strong>Extras.</strong> Fringe, a chipping area, cups and flags, borders.</li>
</ul>

<h2>How we build one</h2>
<ol>
    <li>Layout and measurement</li>
    <li>Excavation and base build</li>
    <li>Contouring and compaction</li>
    <li>Turf, seams and edging</li>
    <li>Infill and finishing</li>
</ol>

<h2>Questions</h2>
<?php
render_faq([
    ['How fast will the green play?',
        "<p>Speed depends on turf, infill and base. We'll talk through what you want before we pick materials.</p>"],
    ['Can I add a chipping area?',
        '<p>Yes. Add it to the plan up front, since the turf and base can differ.</p>'],
    ['What does it cost?',
        '<p>Putting greens are quoted per project, because size, shape and contour change the price more than they do on a lawn. ' . ucfirst(quote_link()) . ' for yours.</p>'],
]);
