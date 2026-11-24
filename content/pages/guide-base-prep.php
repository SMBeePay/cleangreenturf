<?php
/**
 * /guides/turf-installation-process-base-prep (install SEO plan, Phase 4).
 * Spec copy. "What we do" opens with "We use" so the base fact reads as a
 * sentence (spec had the bare fact as a fragment).
 */
require __DIR__ . '/../../includes/install-page.php';
?>
<h1>Artificial Turf Base Prep: What a Good Install Includes</h1>

<p><strong>The base decides how the turf looks in year three.</strong> The turf is the part you see. The base is the part that keeps it flat, drained and in place.</p>

<h2>What a base does</h2>
<p>It gives the turf a firm, flat surface. It moves water away. It keeps weeds down and keeps the surface from shifting or settling.</p>

<h2>What we do</h2>
<p>We use <?= fact_lc('base_spec') ?>. Existing material is excavated first. The base goes down in layers and is compacted, then graded so water leaves the surface. A weed barrier goes on top, then the turf.</p>

<h2>Why North Texas clay raises the stakes</h2>
<p>Clay holds water and moves as it swells and dries. A thin or poorly compacted base on clay can settle, pool water and show dips. A deeper, well-compacted base and proper grading reduce those problems.</p>

<h2>Signs of a bad base</h2>
<ul>
    <li>Dips and low spots that hold water</li>
    <li>Ripples or seams that open</li>
    <li>Edges that lift</li>
    <li>Odor that comes back after cleaning</li>
</ul>

<h2>Questions to ask every installer</h2>
<ol>
    <li>How deep is your base, and what's it made of?</li>
    <li>How do you compact it?</li>
    <li>How do you grade for drainage?</li>
    <li>What happens if I have a drainage problem?</li>
    <li>What's in the warranty?<?= fact_ready('warranty_text') ? ' ' . fact('warranty_text') : '' ?></li>
</ol>

<p>Related: <?= maybe_link('/guides/how-to-choose-a-turf-installer-dfw', 'how to choose a turf installer') ?>, <?= maybe_link('/turf-installation/cost', 'cost breakdown') ?>, <a href="/turf-installation">turf installation in DFW</a>.</p>
