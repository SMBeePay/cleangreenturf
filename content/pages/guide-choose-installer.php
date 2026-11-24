<?php
/** /guides/how-to-choose-a-turf-installer-dfw (install SEO plan, Phase 4). Spec copy. */
require __DIR__ . '/../../includes/install-page.php';
?>
<h1>How to Choose an Artificial Turf Installer in DFW</h1>

<p><strong>Compare installers on base, scope, warranty and who shows up, not just on price.</strong> In DFW, installed pricing generally runs about <?= $priceRange ?> per square foot, and we publish ours.</p>

<h2>8 questions to ask</h2>
<ol>
    <li><strong>What's the installed price per square foot, and what does it cover?</strong> Get the scope in writing.</li>
    <li><strong>How deep is the base, and how is it compacted?</strong></li>
    <li><strong>What's the drainage plan?</strong></li>
    <li><strong>Who does the work?</strong> Employees, a crew or subcontractors. Who is responsible if something goes wrong?</li>
    <li><strong>What turf will you use, and why for my yard?</strong></li>
    <li><strong>What's the warranty, and what does it cover?</strong></li>
    <li><strong>How do you handle the HOA submission?</strong></li>
    <li><strong>Can I talk to someone about turf you installed a few years ago?</strong> (If an installer can't point you to older work, that tells you something.)</li>
</ol>

<h2>Quote red flags</h2>
<ul>
    <li>A price that's far below others with no explanation</li>
    <li>No mention of base depth or drainage</li>
    <li>Vague "turf installation" with no itemized scope</li>
    <li>Pressure to sign today</li>
    <li>No written warranty</li>
</ul>

<h2>What we can tell you about ourselves</h2>
<p><?= fact('repair_experience_line') ?><?= fact_ready('warranty_text') ? ' ' . fact('warranty_text') : '' ?><?= fact_ready('license_insurance') ? ' ' . fact('license_insurance') : '' ?></p>

<h2>Next step</h2>
<p><?= ucfirst(quote_link()) ?> and compare it line by line with the others. Related: <?= maybe_link('/turf-installation/cost', 'turf installation cost') ?>, <?= maybe_link('/guides/turf-installation-process-base-prep', 'turf base prep') ?>.</p>
